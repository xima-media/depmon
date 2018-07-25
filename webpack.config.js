var Encore = require('@symfony/webpack-encore');

Encore
// the project directory where compiled assets will be stored
    .setOutputPath('src/Resources/public/build/')
    // the public path used by the web server to access the previous directory
    .setPublicPath('/bundles/ximadepmon/build')
    .setManifestKeyPrefix('/bundles/ximadepmon/build')
    .cleanupOutputBeforeBuild()
    .enableSourceMaps(!Encore.isProduction())
    // uncomment to create hashed filenames (e.g. app.abc123.css)
    // .enableVersioning(Encore.isProduction())

    // uncomment to define the assets of the project
    .autoProvidejQuery()
    .enableSassLoader()
    .enableVersioning(false)
    .createSharedEntry('js/common', [
        'jquery'
    ])
    .addEntry('js/app', [
        './assets/js/_app.js',
        './assets/js/_filter.js',
        './assets/js/_search.js',
        './assets/js/_tooltip.js',
        './assets/js/_update.js'
    ])
    .addStyleEntry('css/app', [
        './node_modules/tippy.js/src/scss/tippy.scss',
        './assets/scss/variables.scss',
        './assets/scss/app.scss',
        './assets/scss/icon.scss',
        './assets/scss/filter.scss',
        './assets/scss/logo.scss',
        './assets/scss/list.scss',
        './assets/scss/loading.scss',
        './assets/scss/flash_message.scss'
    ])
    // enable source maps during development
    .enableSourceMaps(!Encore.isProduction())
;

module.exports = Encore.getWebpackConfig();