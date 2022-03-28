const Encore = require("@symfony/webpack-encore");

// Manually configure the runtime environment if not already configured yet by the "encore" command.
// It's useful when you use tools that rely on webpack.config.js file.
if (!Encore.isRuntimeEnvironmentConfigured()) {
  Encore.configureRuntimeEnvironment(process.env.NODE_ENV || "dev");
}

Encore
  // directory where compiled assets will be stored
  .setOutputPath("public/dist/")
  // public path used by the web server to access the output path
  .setPublicPath("/dist")

  .addEntry("App", "./assets/js/App.js")
  .addEntry("Build", "./assets/js/Build.js")
  .addEntry("Builds", "./assets/js/Builds.js")
  .addEntry("CreateBuild", "./assets/js/CreateBuild.js")
  .addEntry("WeaponDashboard", "./assets/js/WeaponDashboard.js")
  .addEntry("CraftDashboard", "./assets/js/CraftDashboard.js")

  // When enabled, Webpack "splits" your files into smaller pieces for greater optimization.
  .splitEntryChunks()

  // will require an extra script tag for runtime.js
  // but, you probably want this, unless you're building a single-page app
  .enableSingleRuntimeChunk()

  .cleanupOutputBeforeBuild()
  .enableBuildNotifications()
  .enableSourceMaps(!Encore.isProduction())
  // enables hashed filenames (e.g. app.abc123.css)
  .enableVersioning(Encore.isProduction())

  .configureBabel((config) => {
    config.plugins.push("@babel/plugin-proposal-class-properties");
  })

  // enables @babel/preset-env polyfills
  .configureBabelPresetEnv((config) => {
    config.useBuiltIns = "usage";
    config.corejs = 3;
  })

  // enables Sass/SCSS support
  .enableSassLoader();

module.exports = Encore.getWebpackConfig();
