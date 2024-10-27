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
    .addEntry('right_side_menu', './assets/js/component/SideMenu/right_side_menu.js')
    .addEntry('left_side_menu', './assets/js/component/SideMenu/left_side_menu.js')
    .addEntry('topbar', './assets/js/component/topbar.js')
    .addEntry('notification', './assets/js/component/Notification/notification.js')

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
    .addEntry('account_list', './assets/js/page/accountList/index.js')

    // Transaction Tag
    .addEntry('transaction_tag_list', './assets/js/page/transactionTag/list/list.js')
    .addEntry('transaction_tag_edit', './assets/js/page/transactionTag/edit/edit.js')
    .addEntry('transaction_tag_new', './assets/js/page/transactionTag/new/new.js')

    // Friendship
    .addEntry('friendship', './assets/js/page/friendship/friendship.js')

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
    '@component': path.resolve(__dirname, 'assets/js/component')
});


module.exports = Encore.getWebpackConfig();
