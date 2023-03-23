const path = require('path');
const HtmlWebpackPlugin = require('html-webpack-plugin');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const { CleanWebpackPlugin } = require('clean-webpack-plugin');

module.exports = {
    entry: {
        reactBuildTest: './src/page/react-build-test.js'
    },
    output: {
        filename: 'js/[name]-[chunkhash].js',
        path: path.join(__dirname, '../../www/Public/static/dist/admin/page'),
        publicPath: '__PUBLIC__/static/dist/admin/page',
        library:{type: 'window'}
    },
    module: {
        rules: [
            {
                test: /\.js$/,
                use: 'babel-loader',
                exclude: /node_modules/,
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
    plugins: [
        new CleanWebpackPlugin(),
        new MiniCssExtractPlugin({
            filename: 'css/[name]-[contenthash].css',
        }),
        new HtmlWebpackPlugin({
            filename: path.join(
                __dirname,
                '../../app/Admin/View/default/ReactBuildTest',
                'index.html'
            ),
            template: path.join(__dirname, '../src/template', 'react-build-test.ejs'),
            inject: false,
            chunks: ['reactBuildTest'],
        }),
    ],
};
