const mix = require("laravel-mix");

const config = require("./mix-config");


mix.setPublicPath(config.distDir);
mix.setResourceRoot("../");
mix.js(`${config.themeDir}/src/js/main`, "js");
mix.sass(`${config.themeDir}/src/scss/main.scss`, "css");

mix.webpackConfig({
  output: {
    publicPath: `${config.publicPath}/`,
    chunkFilename: "js/[id].js"
  },
  devtool: "source-map",
  externals: config.externals,
});

mix.disableNotifications();
mix.sourceMaps();
