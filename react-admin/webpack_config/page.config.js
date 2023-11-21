const path = require('path');
const HtmlWebpackPlugin = require('html-webpack-plugin');

const pageConfig = {
    reactBuildTest:{
        entry:{
            import: './src/page/react-build-test/index.js',
            dependOn: 'common'
        },
        plugin: new HtmlWebpackPlugin({
            filename: path.join(
                __dirname,
                '../../app/Admin/View/default/ReactBuildTest',
                'index.html'
            ),
            template: path.join(__dirname, '../src/page/react-build-test', 'index.ejs'),
            inject: false,
            chunks: ['reactBuildTest','common'],
        })
    }
};

let pageEntryObj = {};
let pagePlugin = [];

for (const [key, value] of Object.entries(pageConfig)) {
    pageEntryObj[key] = value.entry;
    pagePlugin.push(value.plugin)
}

module.exports = {
    pageEntryObj,
    pagePlugin,
}
