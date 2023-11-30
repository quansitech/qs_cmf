const webpackCommon = require('./webpack.common.js');
const webpack = require('webpack');

const webpackConfig = {
    mode: 'development',
};

const SpeedMeasurePlugin = require("speed-measure-webpack-plugin")
const BundleAnalyzerPlugin = require('webpack-bundle-analyzer').BundleAnalyzerPlugin;

const webpack_c = Object.assign(webpackCommon, webpackConfig);
// webpack_c.plugins.push(new webpack.SourceMapDevToolPlugin({}));
// webpack_c.plugins.push(new SpeedMeasurePlugin());
// webpack_c.plugins.push(new BundleAnalyzerPlugin({analyzerPort:22222}));

module.exports = webpack_c;