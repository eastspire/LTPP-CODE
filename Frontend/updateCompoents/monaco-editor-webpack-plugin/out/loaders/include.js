/*
 * @Author: 1491579574@qq.com
 * @Date: 2023-08-10 10:28:20
 * @LastEditors: 1491579574@qq.com
 * @LastEditTime: 2023-08-10 11:07:16
 * @FilePath: \LTPP-CODE\Frontend\updateCompoents\monaco-editor-webpack-plugin\out\loaders\include.js
 * @Description: Email:1491579574@qq.com
 * QQ:1491579574
 * Copyright (c) 2023 by SQS, All Rights Reserved.
 */
'use strict';
Object.defineProperty(exports, '__esModule', { value: true });
exports.pitch = void 0;
const loaderUtils = require('../../../loader-utils');
const pitch = function pitch(remainingRequest) {
  const { globals = undefined, pre = [], post = [] } = this.query || {};
  // HACK: NamedModulesPlugin overwrites existing modules when requesting the same module via
  // different loaders, so we need to circumvent this by appending a suffix to make the name unique
  // See https://github.com/webpack/webpack/issues/4613#issuecomment-325178346 for details
  if (this._module && this._module.userRequest) {
    this._module.userRequest = `include-loader!${this._module.userRequest}`;
  }
  const stringifyRequest = (request) => {
    if (this.utils) {
      return JSON.stringify(
        this.utils.contextify(this.context || this.rootContext, request)
      );
    }
    return loaderUtils.stringifyRequest(this, request);
  };
  return [
    ...(globals
      ? Object.keys(globals).map(
          (key) => `self[${JSON.stringify(key)}] = ${globals[key]};`
        )
      : []),
    ...pre.map((include) => `import ${stringifyRequest(include)};`),
    `
import * as monaco from ${stringifyRequest(`!!${remainingRequest}`)};
export * from ${stringifyRequest(`!!${remainingRequest}`)};
export default monaco;
		`,
    ...post.map((include) => `import ${stringifyRequest(include)};`),
  ].join('\n');
};
exports.pitch = pitch;
