const path = require('path');
const HtmlWebpackPlugin = require('html-webpack-plugin');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const { CleanWebpackPlugin } = require('clean-webpack-plugin');

module.exports = {
    mode: 'production',
    entry: {
        vendor: './src/vendor.js'
    },
    output: {
        filename: 'js/[name]-[chunkhash].js',
        path: path.join(__dirname, '../../www/Public/static/dist/admin/vendor'),
        publicPath: '__PUBLIC__/static/dist/admin/vendor',
        library:{type: 'window'}
    },
    module: {
        rules: [
            {
                test: /\.js$/,
                use: 'babel-loader',
                exclude: /node_modules/
            },
            {
                test: /\.(sa|sc|c)ss$/,
                use: [
                    MiniCssExtractPlugin.loader,
                    {
                        loader: 'css-loader'
                    },
                ],
            },
        ]
    },
    plugins: [
        new CleanWebpackPlugin(),
        new MiniCssExtractPlugin({
            filename: 'css/[name]-[contenthash].css'
        }),
        new HtmlWebpackPlugin({
            filename: path.join(__dirname, '../../app/Admin/View/default/Public', 'vendor.html'),
            template: path.join(__dirname, '../src/template', 'vendor.ejs'),
            inject:false,
            chunks: [
                'vendor'
            ],
            scriptLoading:'blocking'
        })
    ]
};