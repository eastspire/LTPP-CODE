/*
 * @Author: 18855190718 1491579574@qq.com
 * @Date: 2023-01-12 12:38:58
 * @LastEditors: 18855190718 1491579574@qq.com
 * @LastEditTime: 2023-08-10 10:42:37
 * @FilePath: \LTPP-CODE\Frontend\vue.config.js
 * @Description: Email:1491579574@qq.com
 * QQ:1491579574
 * Copyright (c) 2023 by 18855190718 1491579574@qq.com, All Rights Reserved. 
 */
const TerserPlugin = require('terser-webpack-plugin');
const CompressionPlugin = require('compression-webpack-plugin');
const UglifyJsPlugin = require('uglifyjs-webpack-plugin');
const MonacoWebpackPlugin = require('./updateCompoents/monaco-editor-webpack-plugin');

const is_dev = process.env.NODE_ENV === "development";
console.log('当前环境为' + (is_dev ? '【开发环境】' : '【生产环境】'));

module.exports = {
    publicPath: './',
    runtimeCompiler: true,
    devServer: {
        historyApiFallback: true,
        https: false,
    },
    productionSourceMap: false,/* 打包不显示Vue源文件，并且体积会减小，性能会提高 */
    configureWebpack: {
        plugins: [
            new MonacoWebpackPlugin(),
            !is_dev && new TerserPlugin({
                terserOptions: {
                    exclude: /\.txt$/,
                    compress: {
                        drop_console: true // 去除 console.log 语句
                    }
                }
            }),
            !is_dev && new UglifyJsPlugin({
                uglifyOptions: {
                    compress: {
                        drop_console: true
                    }
                },
                sourceMap: false,
                parallel: true
            }),
            !is_dev && new CompressionPlugin({
                algorithm: 'gzip',
                compressionOptions: { level: 9 },
                threshold: 10240,
                minRatio: 0
            }),
        ],
    },
    /* 打包exe防止路径错误，找不到文件 */
    chainWebpack: config => {
        config.module.rule('md')
            .test(/\.md/)
            .use('vue-loader')
            .loader('vue-loader')
            .end()
            .use('vue-markdown-loader')
            .loader('vue-markdown-loader/lib/markdown-compiler')
            .options({
                raw: true
            });
    },
}