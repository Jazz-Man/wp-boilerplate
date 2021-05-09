const mix = require('laravel-mix');
const File = require('laravel-mix/src/File');
const { resolve, dirname, basename } = require('path');
const fs = require('fs');
const { themeDir } = require('./webpack.mix.config');
const baseStylesDir = resolve(__dirname, 'node_modules/@wordpress/base-styles');
const baseBlockLibraryDir = resolve(__dirname, 'node_modules/@wordpress/block-library/src/');

const wpBlocksDir = `${themeDir}/src/scss/wp-blocks`;

fs.readdir(baseStylesDir, (err, files) => {
    files.forEach((file) => {
        const style = new File(`${baseStylesDir}/${file}`);
        let destination = `${wpBlocksDir}/core/${style.name()}`;

        if (style.extension() === '.scss' && !fs.existsSync(destination)) {
            try {
                style.copyTo(destination);
            } catch (err) {
                console.error(err);
            }
        }
    });
});

const getAllFiles = (dirPath, arrayOfFiles = []) => {
    const files = fs.readdirSync(dirPath);

    files.forEach((file) => {
        let _file = `${dirPath}/${file}`;

        if (fs.statSync(_file).isDirectory()) {
            arrayOfFiles = getAllFiles(_file, arrayOfFiles);
        } else {
            arrayOfFiles.push(_file);
        }
    });

    return arrayOfFiles;
};

const blockLibrary = getAllFiles(baseBlockLibraryDir);

if (blockLibrary.length) {
    blockLibrary.forEach((file) => {
        const style = new File(file);

        const reqaredName = [
          'common.scss',
          'editor.scss',
          'reset.scss',
          'style.scss',
          'theme.scss',
        ];

        if ( style.extension() === '.scss' &&  !style.name().includes('native.scss')) {
            let styleDirName = basename(dirname(file));
            let newFileName = styleDirName === 'src' ? style.name() : `${styleDirName}/${style.name()}`;
            let destination = `${wpBlocksDir}/blocks/${newFileName}`;

            if (!fs.existsSync(destination)) {
                try {
                    style.copyTo(destination);
                } catch (err) {
                    console.error(err);
                }
            }
        }
    });
}
