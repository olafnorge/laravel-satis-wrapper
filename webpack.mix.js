'use strict';

let mix = require('laravel-mix');
let glob = require('glob');
let fs = require('fs');
let path = require('path');
let ImageminPlugin = require('imagemin-webpack-plugin').default;

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */
mix.options({
    clearConsole: false
});

mix.webpackConfig({
    resolve: {
        alias: {
            "later": "later/index-browserify.js"
        }
    }
});

mix
    .js('resources/assets/js/app.js', 'public/js')
    .js('resources/assets/js/jsoneditor.js', 'public/js')
    .js('resources/assets/js/prettycron.js', 'public/js')
    .js('resources/assets/js/satis_details.js', 'public/js')
    .js('resources/assets/js/satis_edit.js', 'public/js')
    .sass('resources/assets/sass/app.scss', 'public/css')
    .sass('resources/assets/sass/jsoneditor.scss', 'public/css');

// extract vendor libs that are unlikely to change often
mix.extract([
    'axios',
    'bootstrap',
    'jquery',
    'lodash'
]);

// optimize images
mix.webpackConfig({
    plugins: [
        new ImageminPlugin({
            disable: !mix.inProduction(), // Disable during development
            // pngquant: {
            //     quality: '95-100',
            // },
            test: /\.(jpe?g|png|gif|svg)$/i,
        }),
    ],
});

// copy images
glob('resources/assets/img/**', (err, files) => {
    files.forEach(file => {
        fs.stat(file, (err, stats) => {
            if (stats.isFile()) {
                mix.copy(file, file.replace(/resources\/assets\/img/, 'public/img'));
            }
        });

    });
});

// copy images to specific locations
[
    {"src": 'node_modules/jsoneditor/dist/img/jsoneditor-icons.svg', "dst": null}
].forEach(css => {
    mix.copy(css.src, (css.dst ? css.dst : 'public/img/' + path.basename(css.src)));
});

// copy vendor css
[
    //{"src": 'node_modules/jsoneditor/dist/jsoneditor.min.css', "dst": null}
].forEach(css => {
    mix.copy(css.src, 'public/css/' + (css.dst ? css.dst : path.basename(css.src)));
});

// copy font-awesome 5 fonts
glob('node_modules/@fortawesome/fontawesome-free/webfonts/**', (err, files) => {
    files.forEach(file => {
        fs.stat(file, (err, stats) => {
            if (stats.isFile()) {
                mix.copy(file, file.replace(/node_modules\/@fortawesome\/fontawesome-free\/webfonts/, 'public/webfonts'));
            }
        });

    });
});

mix.sourceMaps(!mix.inProduction(), 'cheap-source-map');
mix.version();
