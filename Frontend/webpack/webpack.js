/*
 * @Author: 18855190718 1491579574@qq.com
 * @Date: 2023-06-03 14:03:21
 * @LastEditors: 18855190718 1491579574@qq.com
 * @LastEditTime: 2023-08-10 11:02:27
 * @FilePath: \LTPP-CODE\Frontend\webpack\webpack.js
 * @Description: Email:1491579574@qq.com
 * QQ:1491579574
 * Copyright (c) 2023 by SQS, All Rights Reserved. 
 */
// var CopyWebpackPlugin = require('copy-webpack-plugin');
// const ImageminPlugin = require('imagemin-webpack-plugin').default;
const is_dev = process.env.NODE_ENV === "development";

console.log('当前环境为' + (is_dev ? '【开发环境】' : '【生产环境】'));

module.exports = {
    resolve: {
        fallback: {
            "buffer": require.resolve("buffer/"),
            "string_decoder": require.resolve("string_decoder/")
        },
        alias: {
            'core-js/modules/web.url-search-params.delete.js': 'core-js/features/url-search-params/delete.js'
        }
    },
    output: {
        globalObject: 'self',
        filename: '[name].bundle.js',
        path: path.resolve(__dirname, 'dist')
    },
    module: {
        rules: [
            {
                test: /\.css$/,
                use: ['style-loader', 'css-loader']
            },
            {
                test: /\.ttf$/,
                use: ['file-loader']
            }
        ]
    },
    plugins: [
        !is_dev && new CopyWebpackPlugin([{
            from: 'updateCompoents/mavon-editor/dist/highlightjs',
            to: path.resolve(__dirname, '../dist/highlightjs'), // 插件将会把文件导出于/dist/highlightjs之下
            ignore: ['*.md', "*.txt"] // 排除文件
        }, {
            from: 'updateCompoents/mavon-editor/dist/markdown',
            to: path.resolve(__dirname, '../dist/markdown'), // 插件将会把文件导出于/dist/markdown之下
            ignore: ['*.md', "*.txt"] // 排除文件
        }, {
            from: 'updateCompoents/mavon-editor/dist/katex', // 插件将会把文件导出
            to: path.resolve(__dirname, '../dist/katex'),
            ignore: ['*.md', "*.txt"] // 排除文件
        }]),
        !is_dev && new ImageminPlugin({
            test: /\.(jpe?g|png|gif|svg)$/i
        }),
    ],
}