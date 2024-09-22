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
    'app' => [
        'path' => './assets/app.js',
        'entrypoint' => true,
    ],
    'note' => [
        'path' => './assets/js/component/note.js',
        'entrypoint' => true,
    ],
    'topbar' => [
        'path' => './assets/js/component/topbar.js',
        'entrypoint' => true,
    ],
    'monthly' => [
        'path' => './assets/js/page/monthly.js',
        'entrypoint' => true,
    ],
    'transaction_tag_list' => [
        'path' => './assets/js/page/transaction_tag_list.js',
        'entrypoint' => true,
    ],
    'transaction' => [
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
    '@symfony/ux-live-component' => [
        'path' => './vendor/symfony/ux-live-component/assets/dist/live_controller.js',
    ],
    'tom-select' => [
        'version' => '2.3.1',
    ],
    'tom-select/dist/css/tom-select.default.css' => [
        'version' => '2.3.1',
        'type' => 'css',
    ],
    'alpinejs' => [
        'version' => '3.14.1',
    ],
];
