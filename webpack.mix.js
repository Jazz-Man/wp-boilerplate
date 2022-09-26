const mix = require('laravel-mix');
const fs = require('fs');
const { resolve } = require('path');
const config = require('./webpack.mix.config');

const { CleanWebpackPlugin } = require('clean-webpack-plugin');

const plugins = [];

if (mix.inProduction()) {
    plugins.push(new CleanWebpackPlugin());
}

const directoryPath = resolve(__dirname, `${config.themeDir}/src/scss/entry-points`);
const getFiles = (dir) => fs.readdirSync(dir).filter((file) => /\.[scss|css|tsx|ts|js]/.test(file));

mix.setPublicPath(config.distDir);
mix.setResourceRoot('../');

const mainJs = mix.js(`${config.themeDir}/src/js/main.js`, 'js');
mainJs.js(`${config.themeDir}/src/admin/js/admin.js`, 'js');

const sassOptions = {
    sassOptions: {
        precision: 8,
        quietDeps: true,
        outputStyle: 'expanded',
    },
};

const mainStyle = mix.sass(`${config.themeDir}/src/scss/main.scss`, 'css', sassOptions);
mainStyle.sass(`${config.themeDir}/src/admin/scss/admin.scss`, 'css', sassOptions);
mainStyle.sass(`${config.themeDir}/src/scss/bootstrap-grid.scss`, 'css', sassOptions);

getFiles(directoryPath).forEach((filepath) => mainStyle.sass(`${directoryPath}/${filepath}`, 'css', sassOptions));

mix.extend('rewriteRules', (webpackConfig) => {
    const fontsRule = /(\.(woff2?|ttf|eot|otf)$|font.*\.svg$)/;
    const imagesRule = /(\.(png|jpe?g|gif|webp)$|^((?!font).)*\.svg$)/;

    const fonts = webpackConfig.module.rules.find((rule) => String(rule.test) === String(fontsRule));
    const images = webpackConfig.module.rules.find((rule) => String(rule.test) === String(imagesRule));

    if (fonts && fonts.use && fonts.use[0]) {
        fonts.use[0].options.name = `${Config.assetDirs.fonts}/[name]-[contenthash].[ext]`;
    }

    if (images && images.use && images.use[0]) {
        images.use[0].options.name = `${Config.assetDirs.images}/[name]-[hash].[ext]`;
    }
});

mix.rewriteRules();

mix.webpackConfig((webpack) => {
    return {
        output: {
            publicPath: `${config.publicPath}/`,
            chunkFilename: 'js/[name].[contenthash].js',
        },
        externals: config.externals,
        watchOptions: {
            ignored: /node_modules/,
        },
        plugins,
        devtool: 'source-map',
        stats: {
            children: true,
        },
    };
});

if (!mix.inProduction()) {
    mix.sourceMaps();
}

mix.disableNotifications();
mix.disableSuccessNotifications();

mix.options({
    processCssUrls: true,
    terser: {
        parallel: true,
        // cache: true,
        terserOptions: {
            compress: mix.inProduction(),
        },
    },
});

if (mix.inProduction()) {
    mix.version();

    mix.then(async () => {
        const convertToFileHash = require('laravel-mix-make-file-hash');
        const fileHashedManifest = await convertToFileHash({
            publicPath: `${config.distDir}`,
            blacklist: ['/js/style/*', '*.map'],
            manifestFilePath: `${config.distDir}/mix-manifest.json`,
        });
    });
}
