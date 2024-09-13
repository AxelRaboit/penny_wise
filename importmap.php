<?php

/**
 * Returns the importmap for this application.
 *
 * - "path" is a path inside the asset mapper system. Use the
 *     "debug:asset-map" command to see the full list of paths.
 *
 * - "entrypoint" (JavaScript only) set to true for any module that will
 *     be used as an "entrypoint" (and passed to the importmap() Twig function).
 *
 * The "importmap:require" command can be used to add new entries to this file.
 */
return [
    // App
    'app' => [
        'path' => './assets/app.js',
        'entrypoint' => true,
    ],
    // Components
    'modal' => [
        'path' => './assets/js/component/modal.js',
        'entrypoint' => true,
    ],
    'notification' => [
        'path' => './assets/js/component/notification.js',
        'entrypoint' => true,
    ],
    'topbar' => [
        'path' => './assets/js/page/topbar.js',
        'entrypoint' => true,
    ],
    // Pages
    'monthly_budget' => [
        'path' => './assets/js/page/monthly_budget.js',
        'entrypoint' => true,
    ],
    // Partials
    'transaction_tab' => [
        'path' => './assets/js/partial/transaction_tab.js',
        'entrypoint' => true,
    ],
    '@hotwired/stimulus' => [
        'version' => '3.2.2',
    ],
    '@symfony/stimulus-bundle' => [
        'path' => './vendor/symfony/stimulus-bundle/assets/dist/loader.js',
    ],
    '@hotwired/turbo' => [
        'version' => '7.3.0',
    ],
    'chart.js' => [
        'version' => '3.9.1',
    ],
];
