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
    'debug' => true,
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
    'thumbs' => [
        'srcsets' => [
            'default' => [
                '300w'  => ['width' => 300],
                '600w'  => ['width' => 600],
                '900w'  => ['width' => 900],
                '1200w' => ['width' => 1200],
                '1800w' => ['width' => 1800]
            ],
            'square' => [
                '300w'  => ['width' => 300, 'height' => 300, 'crop' => 'center'],
                '600w'  => ['width' => 600, 'height' => 600, 'crop' => 'center'],
                '900w'  => ['width' => 900, 'height' => 900, 'crop' => 'center'],
                '1200w' => ['width' => 1200, 'height' => 1200, 'crop' => 'center'],
                '1800w' => ['width' => 1800, 'height' => 1800, 'crop' => 'center']
            ],
            // hero is cropped to 16:9
            'hero' => [
                '320w'  => ['width' => 320, 'height' => 180, 'crop' => 'center'],
                '640w'  => ['width' => 640, 'height' => 360, 'crop' => 'center'],
                '960w'  => ['width' => 960, 'height' => 540, 'crop' => 'center'],
                '1280w' => ['width' => 1280, 'height' => 720, 'crop' => 'center'],
                '1920w' => ['width' => 1920, 'height' => 1080, 'crop' => 'center']
            ],
            'card' => [
                '320w'  => ['width' => 320, 'height' => 180, 'crop' => 'center'],
                '640w'  => ['width' => 640, 'height' => 360, 'crop' => 'center'],
            ],
            'square-card' => [
                '120w'  => ['width' => 120, 'height' => 120, 'crop' => 'center'],
                '240w'  => ['width' => 240, 'height' => 240, 'crop' => 'center'],
                '480w'  => ['width' => 480, 'height' => 480, 'crop' => 'center'],
                '810w'  => ['width' => 810, 'height' => 810, 'crop' => 'center']
            ],
            'port-card' => [
                '240w'  => ['width' => 240, 'height' => 260, 'crop' => 'center'],
                '360w'  => ['width' => 360, 'height' => 390, 'crop' => 'center'],
                '540w'  => ['width' => 540, 'height' => 585, 'crop' => 'center'],
                '720w'  => ['width' => 720, 'height' => 780, 'crop' => 'center']
            ],
            // more srcsets as needed
            'avif' => [
                '300w'  => ['width' => 300, 'format' => 'avif'],
                '600w'  => ['width' => 600, 'format' => 'avif'],
                '900w'  => ['width' => 900, 'format' => 'avif'],
                '1200w' => ['width' => 1200, 'format' => 'avif'],
                '1800w' => ['width' => 1800, 'format' => 'avif']
            ],
            'webp' => [
                '300w'  => ['width' => 300, 'format' => 'webp'],
                '600w'  => ['width' => 600, 'format' => 'webp'],
                '900w'  => ['width' => 900, 'format' => 'webp'],
                '1200w' => ['width' => 1200, 'format' => 'webp'],
                '1800w' => ['width' => 1800, 'format' => 'webp']
            ],
            // landscape is cropped to 3:2
            'landscape-webp' => [
                '320w'  => ['width' => 320, 'height' => 240, 'crop' => 'center', 'format' => 'webp'],
                '640w'  => ['width' => 640, 'height' => 480, 'crop' => 'center', 'format' => 'webp'],
                '960w'  => ['width' => 960, 'height' => 640, 'crop' => 'center', 'format' => 'webp'],
                '1280w' => ['width' => 1280, 'height' => 960, 'crop' => 'center', 'format' => 'webp'],
                '1920w' => ['width' => 1920, 'height' => 1280, 'crop' => 'center', 'format' => 'webp']
            ],
            // hero port for mobile is cropped to 1:2
            'hero-port-webp' => [
                '320w'  => ['width' => 320, 'height' => 640, 'crop' => 'center', 'format' => 'webp'],
                '450w'  => ['width' => 450, 'height' => 900, 'crop' => 'center', 'format' => 'webp'],
                '600w'  => ['width' => 600, 'height' => 1200, 'crop' => 'center', 'format' => 'webp'],
                '900w'  => ['width' => 900, 'height' => 1800, 'crop' => 'center', 'format' => 'webp'],
                '1200w' => ['width' => 1200, 'height' => 2400, 'crop' => 'center', 'format' => 'webp']
            ],
            // hero is cropped to 3:2
            'hero-webp' => [
                '450w'  => ['width' => 450, 'height' => 300, 'crop' => 'center', 'format' => 'webp'],
                '600w'  => ['width' => 600, 'height' => 400, 'crop' => 'center', 'format' => 'webp'],
                '900w'  => ['width' => 900, 'height' => 600, 'crop' => 'center', 'format' => 'webp'],
                '1200w' => ['width' => 1200, 'height' => 800, 'crop' => 'center', 'format' => 'webp'],
                '1500w' => ['width' => 1500, 'height' => 1000, 'crop' => 'center', 'format' => 'webp'],
                '1800w' => ['width' => 1800, 'height' => 1200, 'crop' => 'center', 'format' => 'webp']
            ],
            // hero wide is cropped to 16:9
            'hero-wide-webp' => [
                '320w'  => ['width' => 320, 'height' => 180, 'crop' => 'center', 'format' => 'webp'],
                '640w'  => ['width' => 640, 'height' => 360, 'crop' => 'center', 'format' => 'webp'],
                '960w'  => ['width' => 960, 'height' => 540, 'crop' => 'center', 'format' => 'webp'],
                '1280w' => ['width' => 1280, 'height' => 720, 'crop' => 'center', 'format' => 'webp'],
                '1920w' => ['width' => 1920, 'height' => 1080, 'crop' => 'center', 'format' => 'webp']
            ],
            'card-webp' => [
                '320w'  => ['width' => 320, 'height' => 180, 'crop' => 'center', 'format' => 'webp'],
                '640w'  => ['width' => 640, 'height' => 360, 'crop' => 'center', 'format' => 'webp'],
            ],
            'square-card-webp' => [
                '120w'  => ['width' => 120, 'height' => 120, 'crop' => 'center', 'format' => 'webp'],
                '240w'  => ['width' => 240, 'height' => 240, 'crop' => 'center', 'format' => 'webp'],
                '480w'  => ['width' => 480, 'height' => 480, 'crop' => 'center', 'format' => 'webp'],
                '810w'  => ['width' => 810, 'height' => 810, 'crop' => 'center', 'format' => 'webp']
            ],
            'port-card-webp' => [
                '240w'  => ['width' => 240, 'height' => 360, 'crop' => 'center', 'format' => 'webp', 'quality' => 60],
                '480w'  => ['width' => 480, 'height' => 720, 'crop' => 'center', 'format' => 'webp', 'quality' => 60],
                '720w'  => ['width' => 720, 'height' => 1080, 'crop' => 'center', 'format' => 'webp', 'quality' => 60],
                '960w'  => ['width' => 960, 'height' => 1440, 'crop' => 'center', 'format' => 'webp', 'quality' => 60],
                '1200w'  => ['width' => 1200, 'height' => 1600, 'crop' => 'center', 'format' => 'webp', 'quality' => 60]
            ],
            'off-square-webp' => [
                '364w'  => ['width' => 364, 'height' => 325, 'crop' => 'center', 'format' => 'webp', 'quality' => 60],
                '560w'  => ['width' => 560, 'height' => 500, 'crop' => 'center', 'format' => 'webp', 'quality' => 60],
                '730w'  => ['width' => 730, 'height' => 650, 'crop' => 'center', 'format' => 'webp', 'quality' => 60],
                '840w'  => ['width' => 840, 'height' => 750, 'crop' => 'center', 'format' => 'webp', 'quality' => 60],
                '1120w'  => ['width' => 1120, 'height' => 1000, 'crop' => 'center', 'format' => 'webp', 'quality' => 60]
            ],
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
        // Team member expertise categories
        // [
        //     'pattern' => 'our-team/expertise/(:any)',
        //     'action' => function ($categorySlug) {
        //         return page('our-team')->render([
        //             'categorySlug' => $categorySlug
        //         ]);
        //     }
        // ]
    ],
    // 'k-cookbook.toc.headlines' => ['h2', 'h3', 'h4'],
];
