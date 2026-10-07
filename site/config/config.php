<?php
/**
 * Kirby Configuration: https://getkirby.com/docs/reference/system/options
 *
 * Brought into git 2026-07-20 — this file existed only on the staging
 * server (added directly, outside the git/FTP deploy pipeline) until now.
 * That's why local dev and staging behaved differently for anything
 * touching blocks: the 'blocks.fieldsets' override below is what
 * organises the block picker into Text/Media/Custom groups, and it's
 * also what was hiding the Gallery block (commented out below) — the
 * blueprint itself was never the problem. Restored Gallery and removed
 * the "Custom" block group (accordion, button, header-section,
 * banner-image, image-with-text, team-summary, expertise-accordion) per
 * Grace's request — none of those had blueprint files backing them
 * anywhere (only site/blueprints/blocks/image.yml exists), so they were
 * likely broken/unconfigured if ever actually used.
 *
 * Known gap carried over as-is (not part of this fix): the 'routes'
 * below reference snippet('robots', ...) and snippet('sitemap', ...),
 * but no site/snippets/robots.php or sitemap.php exist in this repo —
 * /robots.txt and /sitemap.xml are likely already broken on staging.
 *
 * This file is now tracked in git (force-added past the /site/config/*
 * .gitignore rule, which still applies to any other file placed in this
 * folder). If a real secret is ever needed here — an actual API key, an
 * SMTP password — pull it from an environment variable
 * (getenv('SOME_KEY') ?: '') rather than typing the value directly into
 * this file, so the reason this was historically gitignored still holds.
 */
return [
    'home' => 'wove-mind',
    'debug' => true,
    'auth' => [
      'methods' => ['password', 'password-reset']
    ],
    'blocks' => [
        'fieldsets' => [
            'text' => [
                'label' => 'Text',
                'type' => 'group',
                'fieldsets' => [
                    'heading' => [
                        'extends' => 'blocks/heading',
                        'fields' => [
                            'customId' => [
                                'label' => 'Custom ID',
                                'type' => 'text'
                            ],
                            'customClass' => [
                                'label' => 'Custom style / class',
                                'type' => 'text'
                            ],
                            'level' => [
                                'options' => [
                                    'h2',
                                    'h3',
                                    'h4',
                                    'h5'
                                ]
                            ]
                        ]
                    ],
                    'text' => [
                        'extends' => 'blocks/text',
                        'nodes' => [
                            'heading',
                            'bulletList',
                            'orderedList'
                        ]
                    ],
                    'list',
                    'quote',
                    'line',
                    // 'markdown',
                    // 'button'
                ]
            ],
            'media' => [
                'label' => 'Media',
                'type' => 'group',
                'fieldsets' => [
                    'image',
                    'gallery',
                    'video',
                ]
            ],
            // 'code' => [
            //   'label' => 'Code',
            //   'type' => 'group',
            //   // 'open' => 'false',
            //   'fieldsets' => [
            //     'code',
            //     'markdown',
            //   ]
            // ],
        ]
    ],
    // 'cm_apiKey' => '',
    // 'cm_listId' => '',
    'typekitId' => '',
    'gaId' => '',
    'sitemap.ignore' => ['mediastore', 'error'],
    // 'email' => [
    //     'transport' => [
    //     'type' => 'smtp',
    //     'host' => 'mail.bristlebird.com',
    //     'port' => 465,
    //     'security' => true,
    //     'auth' => true,
    //     'username' => '',
    //     'password' => '',
    //     ]
    // ],
    'routes' => [
        [
            'pattern' => 'clear-cache/wove2026',
            'action'  => function () {
                kirby()->cache('pages')->flush();
                kirby()->cache('plugins')->flush();

                $cacheDir = kirby()->root('cache');
                if (is_dir($cacheDir)) {
                    $files = new RecursiveIteratorIterator(
                        new RecursiveDirectoryIterator($cacheDir, FilesystemIterator::SKIP_DOTS),
                        RecursiveIteratorIterator::CHILD_FIRST
                    );
                    foreach ($files as $file) {
                        $file->isDir() ? @rmdir($file->getPathname()) : @unlink($file->getPathname());
                    }
                }

                if (function_exists('opcache_reset')) {
                    opcache_reset();
                }

                return new Kirby\Cms\Response('Cache cleared (Kirby + OPcache).', 'text/plain');
            }
        ],
        [
            'pattern' => 'services/(:any)',
            'action'  => function ($slug) {
                $services = ['digital', 'labs', 'strategy', 'brand'];
                if (!in_array($slug, $services)) return false;

                $page = page('services/' . $slug);
                if (!$page) return false;

                // Render with a per-service template if one exists, otherwise the
                // shared service.php. The content files are named after each
                // service (strategy.txt etc.), so Kirby's own template lookup would
                // fall back to default.php. Setting $kirby->data mirrors
                // Page::render(), so snippets like header.php see $page.
                $kirby    = kirby();
                $template = $kirby->template($slug);
                if (!$template->exists()) $template = $kirby->template('service');

                $kirby->site()->visit($page);
                $kirby->data = [
                    'kirby'       => $kirby,
                    'site'        => $kirby->site(),
                    'pages'       => $kirby->site()->children(),
                    'page'        => $page,
                    'serviceSlug' => $slug,
                ];
                $html = $template->render($kirby->data);
                return new \Kirby\Cms\Response($html, 'text/html');
            }
        ],
        [
            'pattern' => '(digital|labs|strategy|brand)',
            'action'  => function ($slug) {
                return go('services/' . $slug);
            }
        ],
        [
            'pattern' => 'our-work',
            'action'  => function () {
                return page('work');
            }
        ],
        [
            'pattern' => 'robots.txt',
            'action'  => function() {
                $content = snippet('robots', ['production' => true], true);
                return new Kirby\Cms\Response($content, 'text/plain');
            }
        ],
        [
            'pattern' => 'sitemap.xml',
            'action'  => function() {
                $pages = site()->pages()->index();

                // fetch the pages to ignore from the config settings,
                // if nothing is set, we ignore the error page
                $ignore = kirby()->option('sitemap.ignore', ['error']);

                $content = snippet('sitemap', compact('pages', 'ignore'), true);

                // return response with correct header type
                return new Kirby\Cms\Response($content, 'application/xml');
            }
        ],
        [
            'pattern' => 'tag/(:any)',
            'action'  => function ($slug) {
                $sectorLabels = [
                    'arts-and-culture'  => 'Arts and Culture',
                    'public-service'    => 'Public Service',
                    'higher-education'  => 'Higher Education',
                    'non-profit'        => 'Non-profit and Mission-led',
                    'founders-ventures' => 'Founders and Ventures',
                ];

                $tag = site()->tags()->toStructure()->findBy('slug', $slug);
                if ($tag && $tag->active()->toBool() !== false) {
                    return page('tag')->render([
                        'tagSlug'      => $slug,
                        'tagName'      => $tag->name()->value(),
                        'tagIntro'     => $tag->intro()->value(),
                        'filterField'  => 'tags',
                        'filterValue'  => $slug,
                    ]);
                }

                if (isset($sectorLabels[$slug])) {
                    return page('tag')->render([
                        'tagSlug'      => $slug,
                        'tagName'      => $sectorLabels[$slug],
                        'tagIntro'     => '',
                        'filterField'  => 'sectors',
                        'filterValue'  => $slug,
                    ]);
                }

                return false;
            }
        ],
    ],
    // 'k-cookbook.toc.headlines' => ['h2', 'h3', 'h4'],
];
