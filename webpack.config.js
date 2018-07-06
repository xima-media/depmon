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
    .createSharedEntry('js/common', ['jquery'])
    .addEntry('js/app', [
        './assets/js/app.js',
        './assets/js/filter.js',
        './assets/js/search.js'
    ])
    .addStyleEntry('css/app', [
        './assets/scss/variables.scss',
        './assets/scss/app.scss',
        './assets/scss/icon.scss',
        './assets/scss/filter.scss',
        './assets/scss/logo.scss',
        './assets/scss/tooltip.scss',
        './assets/scss/list.scss'
    ])
    // enable source maps during development
    .enableSourceMaps(!Encore.isProduction())
;

module.exports = Encore.getWebpackConfig();