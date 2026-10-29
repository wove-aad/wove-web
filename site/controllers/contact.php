<?php
/**
 * Contact page controller: handles the three forms on site/templates/contact.php.
 *
 * Each form posts its `form_type` (enquiry or tender). A valid
 * submission is emailed to that route's inbox (the Form Routing fields on
 * the contact page, with defaults), with the sender's address as Reply-To,
 * then the page redirects to ?sent={type}#{card} so a reload doesn't send
 * it again. Spam: a hidden `website` field that people leave empty, and
 * Kirby's CSRF token.
 *
 * Options (site/config): `wove.contact.from` is the From address (default
 * noreply@wove.group; the mail server must be allowed to send for it), and
 * `wove.contact.debug` => true builds the email without sending it (local
 * testing).
 */

use Kirby\Toolkit\V;

return function ($kirby, $page) {
    $routes = [
        'enquiry'  => ['anchor' => 'enquiries', 'label' => 'Project enquiry',        'required' => ['name', 'email'], 'fields' => ['name' => 'Name', 'email' => 'Email', 'organisation' => 'Organisation', 'scope' => 'Scope note', 'timeline' => 'Timeline']],
        'tender'   => ['anchor' => 'tenders',   'label' => 'Tender invite',          'required' => ['email'],         'fields' => ['email' => 'Email', 'rft_reference' => 'RFT reference', 'portal' => 'Portal', 'closing_date' => 'Closing date', 'framework' => 'Framework', 'note' => 'Note']],
    ];

    $sent      = in_array(get('sent'), array_keys($routes), true) ? get('sent') : null;
    $error     = null;
    $errorType = null;
    $data      = [];

    if ($kirby->request()->is('POST') && isset($routes[get('form_type')])) {
        $type  = get('form_type');
        $route = $routes[$type];

        foreach ($route['fields'] as $key => $label) {
            $data[$key] = trim(mb_substr((string) get($key), 0, 5000));
        }

        // The hidden field is only filled in by bots: drop quietly
        if (get('website') !== null && get('website') !== '') {
            go($page->url() . '?sent=' . $type . '#' . $route['anchor']);
        }

        if (csrf(get('csrf')) !== true) {
            $error = 'Your session timed out. Please send the form again.';
        } else {
            foreach ($route['required'] as $key) {
                if ($data[$key] === '') {
                    $error = 'Please fill in ' . strtolower($route['fields'][$key]) . '.';
                    break;
                }
            }
            if (!$error && !V::email($data['email'])) {
                $error = 'Please check your email address.';
            }
        }

        if (!$error) {
            $to = match ($type) {
                'enquiry'  => $page->enquiry_email()->or('hello@wove.group')->value(),
                'tender'   => $page->tender_email()->or('tenders@wove.group')->value(),
            };

            $lines = [];
            foreach ($route['fields'] as $key => $label) {
                if ($data[$key] !== '') $lines[] = $label . ': ' . $data[$key];
            }

            try {
                $kirby->email([
                    'from'    => option('wove.contact.from', 'noreply@wove.group'),
                    'replyTo' => $data['email'],
                    'to'      => $to,
                    'subject' => $route['label'] . ' from ' . ($data['name'] ?? $data['email']),
                    'body'    => implode("\n\n", $lines) . "\n\nSent from " . $page->url(),
                ], ['debug' => option('wove.contact.debug', false)]);
                go($page->url() . '?sent=' . $type . '#' . $route['anchor']);
            } catch (\Throwable $e) {
                $error = 'We couldn’t send this just now. Please email us at ' . $to . '.';
            }
        }

        if ($error) $errorType = $type;
    }

    return compact('sent', 'error', 'errorType', 'data');
};
