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
    'base' => [
        'path' => './assets/js/page/base/base.js',
        'entrypoint' => true,
    ],
    'base_no_topbar' => [
        'path' => './assets/js/page/base/base_no_topbar.js',
        'entrypoint' => true,
    ],
    // Components
    'side_menu_modal' => [
        'path' => './assets/js/component/Modal/side_menu_modal.js',
        'entrypoint' => true,
    ],
    'topbar' => [
        'path' => './assets/js/component/topbar.js',
        'entrypoint' => true,
    ],
    // Pages
    // Account
    // Account - Wallet - Dashboard
    'account_wallet_dashboard' => [
        'path' => './assets/js/page/account/wallet/dashboard/index.js',
        'entrypoint' => true,
    ],
    // Account - Wallet - Dashboard - Transaction
    'account_wallet_dashboard_transaction_list' => [
        'path' => './assets/js/page/account/wallet/dashboard/transaction/list/index.js',
        'entrypoint' => true,
    ],
    'account_wallet_dashboard_transaction_new' => [
        'path' => './assets/js/page/account/wallet/dashboard/transaction/new/index.js',
        'entrypoint' => true,
    ],
    'account_wallet_dashboard_transaction_edit' => [
        'path' => './assets/js/page/account/wallet/dashboard/transaction/edit/index.js',
        'entrypoint' => true,
    ],
    'account_wallet_dashboard_transaction_show' => [
        'path' => './assets/js/page/account/wallet/dashboard/transaction/show/index.js',
        'entrypoint' => true,
    ],
    // Account - Wallet - Dashboard - Wallet
    'account_wallet_dashboard_wallet_new' => [
        'path' => './assets/js/page/account/wallet/dashboard/wallet/new/index.js',
        'entrypoint' => true,
    ],
    'account_wallet_dashboard_wallet_edit' => [
        'path' => './assets/js/page/account/wallet/dashboard/wallet/edit/index.js',
        'entrypoint' => true,
    ],
    // Account List
    'account_list' => [
        'path' => './assets/js/page/accountList/index.js',
        'entrypoint' => true,
    ],
    // Transaction Tag
    'transaction_tag_list' => [
        'path' => './assets/js/page/transactionTag/list/list.js',
        'entrypoint' => true,
    ],
    'transaction_tag_edit' => [
        'path' => './assets/js/page/transactionTag/edit/edit.js',
        'entrypoint' => true,
    ],
    'transaction_tag_new' => [
        'path' => './assets/js/page/transactionTag/new/new.js',
        'entrypoint' => true,
    ],
    // Partials
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
