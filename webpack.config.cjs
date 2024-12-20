const path = require('path');
const Encore = require('@symfony/webpack-encore');

// Manually configure the runtime environment if not already configured yet by the "encore" command.
// It's useful when you use tools that rely on webpack.config.js file.
if (!Encore.isRuntimeEnvironmentConfigured()) {
    Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev');
}

Encore.enableStimulusBridge('./assets/controllers.json');

Encore
    // directory where compiled assets will be stored
    .setOutputPath('public/build/')
    // public path used by the web server to access the output path
    .setPublicPath('/build')
    // only needed for CDN's or subdirectory deploy
    //.setManifestKeyPrefix('build/')

    /*
     * ENTRY CONFIG
     *
     * Each entry will result in one JavaScript file (e.g. app.js)
     * and one CSS file (e.g. app.css) if your JavaScript imports CSS.
     */
    .addEntry('app', './assets/app.js')
    .addEntry('base', './assets/js/page/base/base.js')
    .addEntry('base_no_topbar', './assets/js/page/base/base_no_topbar.js')

    // Components
    // Components - Sidemenu
    .addEntry('right_side_menu', './assets/js/component/side_menu/right_side/index.js')
    .addEntry('left_side_menu', './assets/js/component/side_menu/left_side/index.js')

    // Components - Topbar
    .addEntry('topbar', './assets/js/component/topbar/topbar.js')
    .addEntry('topbar_notification', './assets/js/component/topbar/notification/notification.js')
    .addEntry('topbar_messenger', './assets/js/component/topbar/messenger/messenger.js')

    // Account - Wallet - Dashboard
    .addEntry('account_wallet_dashboard', './assets/js/page/account/wallet/dashboard/index.js')

    // Account - Wallet - Dashboard - Transaction
    .addEntry('account_wallet_dashboard_transaction_list', './assets/js/page/account/wallet/dashboard/transaction/list/index.js')
    .addEntry('account_wallet_dashboard_transaction_new', './assets/js/page/account/wallet/dashboard/transaction/new/index.js')
    .addEntry('account_wallet_dashboard_transaction_edit', './assets/js/page/account/wallet/dashboard/transaction/edit/index.js')
    .addEntry('account_wallet_dashboard_transaction_show', './assets/js/page/account/wallet/dashboard/transaction/show/index.js')

    // Account - Wallet - Dashboard - Wallet
    .addEntry('account_wallet_dashboard_wallet_new', './assets/js/page/account/wallet/dashboard/wallet/new/index.js')
    .addEntry('account_wallet_dashboard_wallet_edit', './assets/js/page/account/wallet/dashboard/wallet/edit/index.js')

    // Account List
    .addEntry('account_list', './assets/js/page/account_list/index.js')

    // Transaction Tag
    .addEntry('transaction_tag_list', './assets/js/page/transaction_tag/list/index.js')
    .addEntry('transaction_tag_edit', './assets/js/page/transaction_tag/edit/index.js')
    .addEntry('transaction_tag_new', './assets/js/page/transaction_tag/new/index.js')

    // Friendship
    .addEntry('friendship', './assets/js/page/friendship/index.js')

    // User - Profile - Settings
    .addEntry('user_profile_settings_show', './assets/js/page/user/profile/settings/show/index.js')

    // Messenger
    .addEntry('messenger', './assets/js/page/messenger/index.js')
    // Messenger - Talk - View
    .addEntry('messenger_talk_view', './assets/js/page/messenger/talk/view/index.js')
    // Messenger - Talk - List
    .addEntry('messenger_talk_list', './assets/js/page/messenger/talk/list/index.js')

    // Partials
    .addEntry('transaction_fields', './assets/js/partial/form/transaction/fields.js')

    // When enabled, Webpack "splits" your files into smaller pieces for greater optimization.
    .splitEntryChunks()

    // will require an extra script tag for runtime.js
    .enableSingleRuntimeChunk()

    // Other features
    .cleanupOutputBeforeBuild()
    .enableBuildNotifications()
    .enableSourceMaps(!Encore.isProduction())
    .enableVersioning(Encore.isProduction())

    // Enables and configure @babel/preset-env polyfills
    .configureBabelPresetEnv((config) => {
        config.useBuiltIns = 'usage';
        config.corejs = '3.38';
    })

// Enables hashed filenames (e.g. app.abc123.css)
;

Encore
.enablePostCssLoader(options => {
    options.postcssOptions = {
        plugins: {
            tailwindcss: {},
            autoprefixer: {},
        },
    };
})
.addAliases({
    '@component': path.resolve(__dirname, 'assets/js/component'),
    '@page': path.resolve(__dirname, 'assets/js/page'),
});

module.exports = Encore.getWebpackConfig();
