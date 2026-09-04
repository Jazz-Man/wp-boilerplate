const { resolve, relative } = require("path");

const themeDir = resolve(__dirname, "./web/app/themes/boilerplate");

const distDir = relative(__dirname, `${themeDir}/dist`);

const publicPath = distDir.replace("web", "");

const externals = {
  jquery: "jQuery",
  lodash: "lodash",
  react: "React",
  "react-dom": "ReactDOM",
  wp: "wp",
};

module.exports = {
  distDir,
  externals,
  publicPath,
  themeDir,
};
