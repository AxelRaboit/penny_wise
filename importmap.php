<?php

declare(strict_types=1);

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
    // Components
    'note' => [
        'path' => './assets/js/component/note.js',
        'entrypoint' => true,
    ],
    'graphs' => [
        'path' => './assets/js/component/graphs.js',
        'entrypoint' => true,
    ],
    'actions' => [
        'path' => './assets/js/component/actions.js',
        'entrypoint' => true,
    ],
    'calendar' => [
        'path' => './assets/js/component/calendar.js',
        'entrypoint' => true,
    ],
    'topbar' => [
        'path' => './assets/js/component/topbar.js',
        'entrypoint' => true,
    ],
    // Pages
    'monthly' => [
        'path' => './assets/js/page/monthly/index.js',
        'entrypoint' => true,
    ],
    'account_list' => [
        'path' => './assets/js/page/accountList/index.js',
        'entrypoint' => true,
    ],
    'transaction_tag_list' => [
        'path' => './assets/js/page/transactionTag/transaction_tag_list.js',
        'entrypoint' => true,
    ],
    'transaction_tag_crud' => [
        'path' => './assets/js/page/transactionTag/transaction_tag_crud.js',
        'entrypoint' => true,
    ],
    // Partials
    'transaction' => [
        'path' => './assets/js/partial/transaction_tab.js',
        'entrypoint' => true,
    ],
    'transaction_fields' => [
        'path' => './assets/js/partial/form/transaction/fields.js',
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
