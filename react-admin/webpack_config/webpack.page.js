const webpackCommon = require('./webpack.common.js');

const webpackConfig = {
    mode: 'production',
};

const webpack_c = Object.assign(webpackCommon, webpackConfig);

module.exports = webpack_c;