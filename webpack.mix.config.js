const { resolve, relative } = require("path");

const themeDir = resolve(__dirname, "./web/app/themes/boilerplate");

const distDir = relative(__dirname, `${themeDir}/dist`);

const publicPath = distDir.replace("web", "");

const externals = {
  jquery: "jQuery",
  wp: "wp",
  lodash: "lodash",
  react: "React",
  "react-dom": "ReactDOM"
};

module.exports = {
  themeDir,
  distDir,
  publicPath,
  externals
};
