const Encore = require('@symfony/webpack-encore');
const path = require('path');

// Manually configure the runtime environment if not already configured yet by the "encore" command.
// It's useful when you use tools that rely on webpack.config.js file.
if (!Encore.isRuntimeEnvironmentConfigured()) {
  Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev');
}

Encore
  // directory where compiled assets will be stored
  .setOutputPath('public/build/')
  // public path used by the web server to access the output path
  .setPublicPath('/build')
  // only needed for CDN's or sub-directory deploy
  //.setManifestKeyPrefix('build/')

  /*
   * ENTRY CONFIG
   *
   * Each entry will result in one JavaScript file (e.g. app.js)
   * and one CSS file (e.g. app.css) if your JavaScript imports CSS.
   */
  .addEntry('app', './frontend/app.js')

  // When enabled, Webpack "splits" your files into smaller pieces for greater optimization.
  .splitEntryChunks()

  // will require an extra script tag for runtime.js
  // but, you probably want this, unless you're building a single-page app
  .enableSingleRuntimeChunk()

  // enables Sass/SCSS support
  .enableSassLoader()

  /*
   * FEATURE CONFIG
   *
   * Enable & configure other features below. For a full
   * list of features, see:
   * https://symfony.com/doc/current/frontend.html#adding-more-features
   */
  .cleanupOutputBeforeBuild()
  .enableBuildNotifications()
  .enableSourceMaps(!Encore.isProduction())

  // enable this when jQuery is provided by Contao's layout ("include jQuery" checkbox)
  // .addExternals({
  //   jquery: 'jQuery'
  // })

  // when dev-server is running, only serve css/js files from memory, other files are written to disk
  // Contao's image processing checks source using fs, not http
  .configureDevServerOptions(function (options) {
    options.devMiddleware = {
      writeToDisk: path => !(/\.(css|js)$/.test(path))
    };
    options.https = {
      pfx: path.join(process.env.HOME, '.symfony/certs/default.p12')
    }
  })

  // .configureBabel((config) => {
  //   config.plugins.push('@babel/plugin-proposal-class-properties');
  // })

  // enables @babel/preset-env polyfills
  // .configureBabelPresetEnv((config) => {
  //   config.useBuiltIns = 'usage';
  //   config.corejs = 3;
  // })

  // uncomment if you use TypeScript
  //.enableTypeScriptLoader()

  // uncomment if you use React
  //.enableReactPreset()

  // uncomment to get integrity="..." attributes on your script & link tags
  // requires WebpackEncoreBundle 1.4 or higher
  //.enableIntegrityHashes(Encore.isProduction())

  // uncomment if you're having problems with a jQuery plugin
  //.autoProvidejQuery()
;

module.exports = Encore.getWebpackConfig();