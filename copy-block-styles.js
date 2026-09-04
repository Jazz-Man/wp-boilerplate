require("laravel-mix");
const File = require("laravel-mix/src/File");
const { resolve, dirname, basename } = require("node:path");
const { themeDir } = require("./webpack.mix.config.js");
const os = require("node:os");
const baseStylesDir = resolve(__dirname, "node_modules/@wordpress/base-styles");
const baseBlockLibraryDir = resolve(
  __dirname,
  "node_modules/@wordpress/block-library/src",
);

const wpBlocksDir = `${themeDir}/src/scss/wp-blocks`;

const baseStyles = new File(baseStylesDir);

baseStyles
  .listContentsAsync({ hidden: false })
  .then((styles) => {
    if (!styles.length) return;

    const indexStyle = [];

    styles.forEach((style) => {
      if (style.extension() !== ".scss") return;
      if (style.contains(".native")) return;

      const destination = `${wpBlocksDir}/core/${style.name()}`;

      try {
        if (File.exists(destination)) {
          const oldFile = new File(destination);

          if (oldFile.version() !== style.version()) {
            style.copyTo(destination);
          }
        } else {
          style.copyTo(destination);
        }

        const importNames = [
          "colors",
          "variables",
          "mixins",
          "breakpoints",
          "animations",
          "z-index",
          "default-custom-properties",
        ];

        /**
         * @type {String}
         */
        const importName = style.nameWithoutExtension().replace(/_/, "");

        if (importNames.includes(importName)) {
          indexStyle.push(`@import "${importName}";`);
        }
      } catch (e) {
        throw new Error(e);
      }
    });

    try {
      if (indexStyle.length) {
        const indexFile = new File(`${wpBlocksDir}/core/_index.scss`);

        indexFile.write(indexStyle.join(os.EOL));
      }
    } catch (e) {
      throw new Error(e);
    }
  })
  .catch((error) => console.error(error));

const baseBlockLibrary = new File(baseBlockLibraryDir);

baseBlockLibrary
  .listContentsAsync({ hidden: false })
  .then((styles) => {
    styles.forEach((style) => {
      if (style.extension() !== ".scss") return;

      try {
        /**
         * @type {string}
         */
        const path = style.path();

        const fileName = style.name();

        const exclude = [".native", "rich-text", "editor", "theme"];

        for (const string of exclude) {
          if (path.includes(string)) {
            return;
          }
        }

        const styleDirName = basename(dirname(path));

        const newFileName =
          styleDirName === "src" ? fileName : `${styleDirName}/${fileName}`;

        const destination = `${wpBlocksDir}/blocks/${newFileName}`;

        style.copyTo(destination);
      } catch (e) {
        throw new Error(e);
      }
    });
  })
  .catch((error) => console.error(error));
