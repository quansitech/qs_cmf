const webpackCommon = require('./webpack.common.js');

const webpackConfig = {
    mode: 'production',
};

const webpack = Object.assign(webpackCommon, webpackConfig);

module.exports = webpack;