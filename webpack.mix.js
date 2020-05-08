const mix = require("laravel-mix");
const fs = require("fs");
const { resolve } = require("path");
const config = require("./webpack.mix.config");

let webpackPlugins = [];

const directoryPath = resolve(
  __dirname,
  `${config.themeDir}/src/scss/entry-points`
);


mix.extend('rewriteRules', webpackConfig => {
  let scssRule = webpackConfig.module.rules.find(rule => String(rule.test) === '/\\.scss$/');

  scssRule.loaders[1] = { /* css-loader */
    loader: 'css-loader',
    options: {
      modules: true,
      importLoaders: 1,
      localIdentName: '[name]_[local]_[hash:base64:4]'
    }
  };
});

mix.rewriteRules();


mix.setPublicPath(config.distDir);
mix.setResourceRoot("../");

mix.js(`${config.themeDir}/src/js/main`, "js");
mix.sass(`${config.themeDir}/src/scss/bootstrap-grid.scss`, "css");
mix.sass(`${config.themeDir}/src/admin/scss/admin.scss`, "css");
mix.sass(`${config.themeDir}/src/scss/main.scss`, "css");

let getFiles = dir =>
  fs.readdirSync(dir).filter(file => fs.statSync(`${dir}/${file}`).isFile());

getFiles(directoryPath).forEach(filepath => {
  mix.sass(`${directoryPath}/${filepath}`, "css");
});

mix.webpackConfig({
  entry: {
    admin: `${config.themeDir}/src/admin/js/admin`
  },
  output: {
    publicPath: `${config.publicPath}/`,
    chunkFilename: "js/[id].js",
    filename: chunkData =>
      chunkData.chunk.name === "admin"
        ? "js/[name].js"
        : `${chunkData.chunk.name}.js`,
    crossOriginLoading:'anonymous'
  },
  externals: config.externals,
  plugins: webpackPlugins
});

mix.options({
  autoprefixer: {
    enabled: true,
    options: {
      grid: "autoplace"
    }
  }
});

mix.disableNotifications();
mix.sourceMaps();
