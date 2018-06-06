var Encore = require('@symfony/webpack-encore');

Encore
    .setOutputPath('public/build/')
    .setPublicPath('/build')
    .cleanupOutputBeforeBuild()
    .autoProvidejQuery()
    .enableSassLoader()
    .enableVersioning(false)
    .createSharedEntry('js/common', ['jquery'])
    .addEntry('js/app', './assets/js/app.js')
    .addEntry('js/search', './assets/js/search.js')
    .addStyleEntry('css/app', ['./assets/scss/app.scss'])
    // enable source maps during development
    .enableSourceMaps(!Encore.isProduction())
;

module.exports = Encore.getWebpackConfig();