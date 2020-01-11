const mix = require('laravel-mix');
const webpack = require('webpack');

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

const plugin = 'resources/plugins/';
const BundleAnalyzerPlugin = require('webpack-bundle-analyzer').BundleAnalyzerPlugin;
const CompressionPlugin = require('compression-webpack-plugin');

mix.js('resources/js/app.js', 'public/js')
    .combine([
        plugin + 'slimscroll/jquery.slimscroll.js',
        'resources/js/limitless.js',
    ],'public/js/plugin.js')
   .sass('resources/sass/app.scss', 'public/css')
   .sass('resources/sass/limitless_default.scss', 'public/css')
   .sass('resources/sass/limitless_material.scss', 'public/css')
    .webpackConfig({
        devtool: "cheap-module-source-map",     // "eval-source-map" or "inline-source-map" or "cheap-module-source-map" or "eval"
        plugins: [
            // new BundleAnalyzerPlugin(),      // load this package to see which plugins with its size detail
            new CompressionPlugin({             // very import to compress the assets
                asset: "[path].gz[query]",
                algorithm: "gzip",
                test: /\.js$|\.css$|\.html$|\.svg$/,
                threshold: 10240,
                minRatio: 0.8
            }),
            new webpack.IgnorePlugin(/^\.\/locale$/, /moment$/)
        ],
        resolve: {
            alias: {
                '@sass': path.resolve(__dirname, 'resources', 'sass'),
                '@js': path.resolve(__dirname, 'resources', 'js'),
                '@var': path.resolve(__dirname, 'resources', 'var'),
                '@components': path.resolve(__dirname, 'resources', 'js', 'components'),
                '@layouts': path.resolve(__dirname, 'resources', 'js', 'layouts'),
                '@routers': path.resolve(__dirname, 'resources', 'js', 'routers'),
                '@services': path.resolve(__dirname, 'resources', 'js', 'services'),
                '@views': path.resolve(__dirname, 'resources', 'js', 'views'),
                '@widgets': path.resolve(__dirname, 'resources', 'js', 'widgets')
            }
        }
    });
