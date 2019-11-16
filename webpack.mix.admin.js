const mix = require("laravel-mix");
const config = require("./mix-config");

mix.setPublicPath(config.distDir);
mix.setResourceRoot("../");
mix.js(`${config.themeDir}/src/admin/js/admin`, "admin/js");
mix.sass(`${config.themeDir}/src/admin/scss/admin.scss`, "admin/css");
mix.webpackConfig({
  output: {
    publicPath: `${config.publicPath}/`,
    chunkFilename: "admin/js/[id].js"
  },
  devtool: "source-map",
  externals: config.externals
});
mix.disableNotifications();
mix.sourceMaps();
