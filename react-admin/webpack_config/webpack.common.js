const path = require('path');
const HtmlWebpackPlugin = require('html-webpack-plugin');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const { CleanWebpackPlugin } = require('clean-webpack-plugin');

const { pageEntryObj,pagePlugin } = require('./page.config');
const reactAdminDir = path.resolve(__dirname, '..');

module.exports = {
    entry: {
        ...pageEntryObj,
        'common': ['react',`${reactAdminDir+'/src/common.js'}`]
    },
    resolve: {
        alias: {
            '@': `${reactAdminDir+'/src/'}`,
        },
    },
    optimization:{
        // 拆分代码
        splitChunks:{
            chunks:'all'
        }
    },
    output: {
        filename: 'js/[name]-[chunkhash].js',
        path: path.join(__dirname, '../../www/Public/static/dist/admin/page'),
        publicPath: '__PUBLIC__/static/dist/admin/page',
    },
    module: {
        rules: [
            {
                test: /\.js$/,
                exclude: /(node_modules)/,
                use: [
                    {
                        // `.swcrc` can be used to configure swc
                        loader: "swc-loader",
                    }
                ]
            },
            {
                test: /\.(sa|sc|c)ss$/,
                use: [
                    MiniCssExtractPlugin.loader,
                    {
                        loader: 'css-loader',
                    }
                ],
            },
        ],
    },
    devtool: false,
    plugins: [
        new CleanWebpackPlugin(),
        new MiniCssExtractPlugin({
            filename: 'css/[name]-[contenthash].css',
        }),
        ...pagePlugin
    ],
};
