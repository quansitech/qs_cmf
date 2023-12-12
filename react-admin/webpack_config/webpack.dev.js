const webpackCommon = require('./webpack.common.js');

const webpackConfig = {
    mode: 'development',
};

const webpack = Object.assign(webpackCommon, webpackConfig);

module.exports = webpack;