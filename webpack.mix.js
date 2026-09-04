const mix = require("laravel-mix");
const fs = require("node:fs");
const { resolve } = require("node:path");
const config = require("./webpack.mix.config.js");

const postCssPlugins = [];

if (mix.inProduction()) {
  postCssPlugins.push(require("postcss-flexbugs-fixes"));
}

const directoryPath = resolve(
  __dirname,
  `${config.themeDir}/src/scss/entry-points`,
);
const getFiles = (dir) =>
  fs.readdirSync(dir).filter((file) => /\.[scss|css|tsx|ts|js]/.test(file));

mix.setPublicPath(config.distDir);
mix.setResourceRoot("../");

mix.js(`${config.themeDir}/src/js/main.js`, "js");
mix.js(`${config.themeDir}/src/admin/js/admin.js`, "js");

const compileCss = (file) =>
  mix.sass(file, "css", {
    additionalData: `$in-production: ${mix.inProduction()};`,
    sassOptions: {
      charset: true,
      outputStyle: "expanded",
      precision: 8,
      quietDeps: true,
    },
  });

compileCss(`${config.themeDir}/src/scss/main.scss`);
compileCss(`${config.themeDir}/src/admin/scss/admin.scss`);
compileCss(`${config.themeDir}/src/scss/bootstrap-grid.scss`);

getFiles(directoryPath).forEach((filepath) => {
  compileCss(`${directoryPath}/${filepath}`);
});

mix.extend("rewriteRules", (webpackConfig) => {
  const fontsRule = /(\.(woff2?|ttf|eot|otf)$|font.*\.svg$)/;
  const imagesRule = /(\.(png|jpe?g|gif|webp)$|^((?!font).)*\.svg$)/;

  const fonts = webpackConfig.module.rules.find(
    (rule) => String(rule.test) === String(fontsRule),
  );
  const images = webpackConfig.module.rules.find(
    (rule) => String(rule.test) === String(imagesRule),
  );

  if (fonts?.use?.[0]) {
    fonts.use[0].options.name = `${Config.assetDirs.fonts}/[name]-[contenthash].[ext]`;
  }

  if (images?.use?.[0]) {
    images.use[0].options.name = `${Config.assetDirs.images}/[name]-[hash].[ext]`;
  }
});

mix.rewriteRules();

mix.webpackConfig((webpack) => {
  return {
    externals: config.externals,
    output: {
      chunkFilename: "js/[name].[contenthash].js",
      clean: true,
      publicPath: `${config.publicPath}/`,
    },
    stats: {
      children: true,
      loggingDebug: ["sass-loader"],
    },
    watchOptions: {
      ignored: /node_modules/,
    },
  };
});

mix.sourceMaps(false, "source-map");

mix.disableNotifications();
mix.disableSuccessNotifications();

mix.options({
  postCss: postCssPlugins,
  processCssUrls: true,
  terser: {
    parallel: true,
    terserOptions: {
      compress: mix.inProduction(),
    },
  },
});

if (mix.inProduction()) {
  mix.version();

  mix.then(async () => {
    const convertToFileHash = require("laravel-mix-make-file-hash");
    const fileHashedManifest = await convertToFileHash({
      blacklist: ["/js/style/*", "*.map"],
      manifestFilePath: `${config.distDir}/mix-manifest.json`,
      publicPath: `${config.distDir}`,
    });
  });
}
