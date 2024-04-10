/*
 * @Author: 18855190718 1491579574@qq.com
 * @Date: 2023-08-07 18:43:57
 * @LastEditors: wmzn-ltpp 1491579574@qq.com
 * @LastEditTime: 2024-01-07 23:14:07
 * @FilePath: \LTPP-CODE\Frontend\src\plugins\SqsGlobal.js
 * @Description: Email:1491579574@qq.com
 * QQ:1491579574
 * Copyright (c) 2023 by SQS, All Rights Reserved. 
 */
/* 全局变量 */
import c_tips from './code/c';
import cpp_tips from './code/cpp';
import java_tips from './code/java';
import go_tips from './code/golang';
import javascript_tips from './code/javascript';
import rust_tips from './code/rust';
import php_tips from './code/php';
import typescript_tips from './code/typescript';
import ruby_tips from './code/ruby';
import csharp_tips from './code/csharp';
import python_tips from './code/python';


const cpp = `#include<bits/stdc++.h>\n#pragma GCC optimize(2)\n#pragma GCC optimize(3)\n#define PF first\n#define PS second\n#define UM unordered_map\n#define US unordered_set\n#define MS mutiset\n#define F(i, start, end, offset) for(int i = start; i < end; i += offset)\n#define F_(i, start, end, offset) for(int i = start; i >= end; i-= offset)\n#define fastcpp ios::sync_with_stdio(false), cin.tie(0), cout.tie(0)\n#define random(a, b) (rand() % (b - a) + a)\nusing namespace std;\nusing LL = long long int;\nusing PII = pair<int, int>;\nusing STR = string;\n\nint main(){\n    fastcpp;\n    \n    \n    return 0;\n}`;
const c = `#include<stdio.h>\n#pragma GCC optimize(2)\n#pragma GCC optimize(3)\n#define F(i, start, end, offset) for(int i = start; i < end; i += offset)\n#define F_(i, start, end, offset) for(int i = start; i >= end; i-= offset)\n#define random(a, b) (rand() % (b - a) + a)\ntypedef long long int LL;\n\nint main(){\n    \n    \n    return 0;\n}`;
const java = `public class Main{\n    //请勿更改类名，否则运行失败!\n    public static void main(String[] args){\n        \n        \n    }\n}`;
const python = `def main():\n    res = ''\n    \n    \n    return res\n\nprint(main())`;
const go = `package main\n//请勿更改包名，否则运行失败!\nimport (\n    "fmt"\n    "bufio"\n    "os"\n    "strconv"\n    "strings"\n)\n\nfunc read() []int {\n    var arr = make([]int, 0);\n    inputs := bufio.NewScanner(os.Stdin)\n    for inputs.Scan() {\n        data := strings.Split(inputs.Text(), " ")\n        for i := range data {\n            val, _ := strconv.Atoi(data[i])\n            arr = append(arr, val)\n        }\n    }\n    return arr;\n}\n\nfunc print(arr []int){\n    for i := 0; i < len(arr); i++ {\n        fmt.Print(arr[i]," ")\n    }\n}\n\nfunc main(){\n    var arr = read()\n    \n    \n    print(arr)\n}`;
const php = `<?php\n\n/**\n * 输入\n * @param [fn] function(cin_line = '', cin_all = []) 每行读取结束后的回调函数(cin_line => 读取的当前行, cin_all => 所有读入)\n * @param [$is_slpit] 是否需要对读入的字符串切割\n * @param [$split_char] 按照什么字符切割\n * @return array\n */\nfunction cin($fn = null, $is_slpit = true, $split_char = ' '){\n    $cin_all = [];\n    if(!$fn){\n        $fn = function(){};\n    }\n    while (!feof(STDIN)){\n        $cin_line = [];\n        $line = trim(fgets(STDIN));\n        if(empty($line)){\n            continue;\n        }\n        $cin_line = $is_slpit ? explode($split_char, $line) : $line;\n        $cin_all[] = $cin_line;\n        $fn($cin_line, $cin_all);\n    }\n    return $cin_all;\n}\n\n(function (){\n    $res = null;\n    $arr = cin(function($read_line = [], $out = []){\n        // 每行读取完成的回调函数\n        \n    }, true);\n    \n    var_dump($res);\n})();`;
const javascript = `/**\n * 输入\n * @param [fn] Function(cin_line = '', cin_all = []) 每行读取结束后的回调函数(cin_line => 读取的当前行, cin_all => 所有读入)\n * @param [is_split] Boolean 是否需要对读入的字符串切割\n * @param [split_char] String 按照什么字符切割\n * @return void\n */\nasync function cinLine(fn = () => {}, is_split = true, split_char = ' '){\n    const cin_all = [];\n    const readline = require('readline');\n    const rl = readline.createInterface({\n        input: process.stdin,\n        output: process.stdout\n    });\n    return new Promise((resolve, reject) => {\n        try{\n            rl.on('line', function(line){\n                const cin_line = is_split ? line.split(split_char) : line;\n                cin_all.push(cin_line);\n                fn.call(this, cin_line, cin_all);\n            });\n            rl.on('close',() => {\n                resolve();\n            });\n        }catch(e){\n            reject(e);\n        }\n    });\n}\n\n(async() => {\n    let res = null;\n    \n    await cinLine((cin_line = '', cin_all = []) => {\n        // 每行读取完成的回调函数\n        \n    });\n\n    console.log(res);\n})();`;
const rust = `#![allow(warnings)]\nuse std::io;\nuse std::error::Error;\nuse std::boxed::Box;\nuse std::convert::TryInto;\nuse std::cmp::Ordering;\nuse std::cmp::min;\nuse std::cmp::max;\n\nfn getAns() -> i64 {\n    let res: i64 = 0;\n    \n    \n    return res;\n}\n\nfn main() -> Result<(), Box<dyn Error>> {\n    \n    \n    print!("{}", getAns());\n    Ok(())\n}`;
const typescript = `/**\n * 输入\n * @param [fn] Function(cin_line = '', cin_all = []) 每行读取结束后的回调函数(cin_line => 读取的当前行, cin_all => 所有读入)\n * @param [is_split] Boolean 是否需要对读入的字符串切割\n * @param [split_char] String 按照什么字符切割\n * @return void\n */\n// @ts-ignore\nasync function cinLine(fn = () => {}, is_split = true, split_char = ' '){\n    const cin_all = [];\n    // @ts-ignore\n    const readline = require('readline');\n    const rl = readline.createInterface({\n        // @ts-ignore\n        input: process.stdin,\n        // @ts-ignore\n        output: process.stdout\n    });\n    return new Promise<void>((resolve, reject) => {\n        try{\n            rl.on('line', function(line: string){\n                const cin_line = is_split ? line.split(split_char) : line;\n                cin_all.push(cin_line);\n                fn.call(this, cin_line, cin_all);\n            });\n            rl.on('close',() => {\n                resolve();\n            });\n        }catch(e){\n            reject(e);\n        }\n    });\n}\n\n(async() => {\n    let res = null;\n    \n    await cinLine((cin_line = '', cin_all = []) => {\n        // 每行读取完成的回调函数\n        \n    });\n\n    console.log(res);\n})();`;
const ruby = `def main()\n    res = '';\n    input = gets.chomp\n    \n    \n    return res\nend\n\n\nputs main()`;
const csharp = `using System;\n\nnamespace Main\n{\n    class Program\n    {\n        static void Main(string[] args)\n        {\n            string input = Console.ReadLine();\n            \n            \n            Console.WriteLine(input);\n        }\n    }\n}`;
const websocket_connect_str = '@ltpp@';
const loading_tips = '加载中';
const code_light_css = '--mtk1:#7c4dff !important;--mtk5:#3dc9b0 !important;--mtk6:#fa278e !important;--mtk7:#437aed !important;--mtk8:#d365e5 !important;--mtk9:#437aed !important;--mtk14:#ff0000 !important;--mtk20:#3dc9b0 !important;--mtk22:#fa278e !important;--mtk23:#3dc9b0 !important;';
const code_dark_css = '--mtk1:#21e016 !important;--mtk5:#3dc9b0 !important;--mtk6:#ff9070 !important;--mtk7:#ffdd00 !important;--mtk8:#fa278e !important;--mtk9:#ff9900 !important;--mtk14:#ff0000 !important;--mtk20:#3dc9b0 !important;--mtk22:#00d7ff !important;--mtk23:#00bdff !important;';

c_tips.key.unshift(c);
cpp_tips.key.unshift(cpp);
rust_tips.key.unshift(rust);
javascript_tips.key.unshift(javascript);
typescript_tips.key.unshift(typescript);
php_tips.key.unshift(php);
go_tips.key.unshift(go);
java_tips.key.unshift(java);
csharp_tips.key.unshift(csharp);
ruby_tips.key.unshift(ruby);
python_tips.key.unshift(python);

// 编辑器主题
const themelist = [
    {
        value: "vs-dark",
        label: "暗黑主题",
        css: code_dark_css
    },
    {
        value: "vs",
        label: "亮色主题",
        css: code_light_css
    },
    {
        value: "hc-black",
        label: "暗黑高亮",
        css: code_dark_css
    },
    {
        value: "hc-light",
        label: "亮色高亮",
        css: code_light_css
    }
];

const language_map = {
    c: 'C',
    cpp: 'C++',
    java: 'Java',
    python: 'Python3',
    go: 'Go',
    php: 'PHP',
    javascript: 'JavaScript',
    rust: 'Rust',
    typescript: 'TypeScript',
    csharp: 'C#',
    ruby: 'Ruby'
};

const map_language = {
    C: 'c',
    'C++': 'cpp',
    Java: 'java',
    Python3: 'python',
    Go: 'go',
    PHP: 'php',
    JavaScript: 'javascript',
    Rust: 'rust',
    TypeScript: 'typescript',
    'C#': 'csharp',
    Ruby: 'ruby'
};

const language_tips = {
    c: c_tips.key,
    cpp: cpp_tips.key,
    java: java_tips.key,
    python: python_tips.key,
    go: go_tips.key,
    php: php_tips.key,
    javascript: javascript_tips.key,
    rust: rust_tips.key,
    typescript: typescript_tips.key,
    csharp: csharp_tips.key,
    ruby: ruby_tips.key
};

const map_language_file = {
    c: 'c',
    cc: 'c',
    cpp: 'cpp',
    java: 'java',
    py: 'python',
    go: 'go',
    php: 'php',
    js: 'javascript',
    rs: 'rust',
    ts: 'typescript',
    cs: 'csharp',
    rb: 'ruby'
};

// 编辑器语言
const options = [
    {
        value: "c",
        label: language_map.c,
    },
    {
        value: "cpp",
        label: language_map.cpp
    },
    {
        value: "java",
        label: language_map.java
    },
    {
        value: "python",
        label: language_map.python
    },
    {
        value: "go",
        label: language_map.go
    },
    {
        value: "php",
        label: language_map.php
    },
    {
        value: "javascript",
        label: language_map.javascript
    },
    {
        value: "rust",
        label: language_map.rust
    },
    {
        value: "typescript",
        label: language_map.typescript
    },
    {
        value: "csharp",
        label: language_map.csharp
    },
    {
        value: "ruby",
        label: language_map.ruby
    },
];

/**
 * 文章列表数据模板
 * @var object $article_list_data
 */
const article_list_data = {
    id: "",
    name: loading_tips,
    article: loading_tips,
    image: "",
    writerid: "",
    writer: loading_tips,
    fabulous: loading_tips,
    collection: loading_tips,
    releasetime: loading_tips,
    lastchangetime: loading_tips
};

const question_list_data = {
    "id": "",
    "name": loading_tips,
    "userid": "",
    "time": loading_tips,
    "answer_num": 0,
    "writer": loading_tips,
    "headimage": ""
};
const monitor_list_data = {
    "id": "",
    "time": loading_tips,
    "path": loading_tips,
    "function": loading_tips,
    "userid": loading_tips,
    "name": loading_tips,
    "grade": loading_tips,
    "user_aid": loading_tips
};
// OJ题库列表数据模板
const oj_problem_list_data = {
    id: "",
    problemName: loading_tips,
    problemLabe: loading_tips,
    ACNum: loading_tips,
    ALLSubmitNum: loading_tips,
    Time: loading_tips,
    Memory: loading_tips,
    problemFrom: loading_tips,
    ACpoint: 1,
    hassolve: 0,
    time: '加载中'
};

// OJ竞赛列表数据模板
const oj_contest_list_data = {
    id: "",
    name: loading_tips,
    content: loading_tips,
    begin: loading_tips,
    end: loading_tips,
    creater: loading_tips,
    allpeople: loading_tips,
    type: loading_tips,
    createrid: "",
    password: true
};

const video_list_data = {
    fabulous: 0,
    id: "",
    love: 0,
    name: '加载中',
    tag: '加载中',
    url: '加载中',
    time: '加载中'
}

// 商品列表数据模板
const goods_list_data = {
    "id": "",
    "name": loading_tips,
    "money": loading_tips,
    "path": loading_tips,
    "type": loading_tips,
    "size": loading_tips,
    "times": loading_tips,
    "blurb": loading_tips,
    "time": loading_tips,
    "has_buy": true
};

// 公告数据模板
const notice_list_data = {
    "id": "",
    "content": loading_tips,
    "time": loading_tips
};

// 用户列表数据模板
const user_list_data =
{
    id: "",
    name: loading_tips,
    registertime: loading_tips,
    lastlogin: loading_tips,
    sex: loading_tips,
    email: loading_tips,
    headimage: '',
    fans: loading_tips,
    follow: loading_tips,
    online: loading_tips,
    acnum: loading_tips,
    mysay: loading_tips,
    bkimage: '',
};
// 用户排名界面数据模板
const user_rank_list_data = {
    index: loading_tips,
    id: loading_tips,
    name: loading_tips,
    acnum: loading_tips,
    registertime: loading_tips,
    lastlogin: loading_tips,
    online: 0,
    sex: loading_tips,
    fans: loading_tips,
    follow: loading_tips,
};
// 提交记录数据模板
const codehistory_data = {
    id: 0,
    userid: "",
    language: loading_tips,
    status: loading_tips,
    time: loading_tips,
    usetime: 0,
    usememory: 0,
    code: loading_tips,
    user: loading_tips,
};
// 短句数据模板
const short_sentence_list = {
    id: "",
    hitokoto: loading_tips,
    from: loading_tips,
};

const max_video_retry_times = 16;

// H5属性
const attribute = [
    // HTML 标签属性
    "accept", "data-original-format", "", "accept-charset", "accesskey", "action", "align", "alt", "async", "autocomplete", "autofocus",
    "autoplay", "bgcolor", "border", "charset", "checked", "cite", "class", "color", "cols", "colspan",
    "content", "contenteditable", "controls", "coords", "data", "datetime", "default", "defer", "dir", "dirname",
    "disabled", "download", "draggable", "dropzone", "enctype", "for", "form", "formaction", "headers", "height",
    "hidden", "high", "href", "hreflang", "http-equiv", "id", "ismap", "kind", "label", "lang", "list", "loop",
    "low", "max", "maxlength", "media", "method", "min", "multiple", "muted", "name", "novalidate", "open",
    "optimum", "pattern", "placeholder", "poster", "preload", "radiogroup", "readonly", "rel", "required", "reversed",
    "rows", "rowspan", "sandbox", "scope", "scoped", "selected", "shape", "size", "sizes", "span", "spellcheck",
    "src", "srcdoc", "srclang", "srcset", "start", "step", "style", "tabindex", "target", "title", "translate",
    "type", "usemap", "value", "width", "wrap",
    // SVG 标签属性
    "accent-height", "accumulate", "additive", "data-view-component", "aria-hidden", "alignment-baseline", "allowReorder", "alphabetic", "amplitude",
    "arabic-form", "ascent", "attributeName", "attributeType", "azimuth", "baseFrequency", "baseline-shift",
    "bbox", "begin", "bias", "by", "calcMode", "cap-height", "clip", "clip-path", "clip-rule", "color",
    "color-interpolation", "color-interpolation-filters", "color-profile", "color-rendering", "contentScriptType",
    "contentStyleType", "crossorigin", "cursor", "cx", "cy", "d", "decelerate", "descent", "diffuseConstant",
    "direction", "display", "divisor", "dominant-baseline", "dur", "dx", "dy", "edgeMode", "elevation", "enable-background",
    "end", "exponent", "externalResourcesRequired", "fill", "fill-opacity", "fill-rule", "filter", "filterRes",
    "filterUnits", "flood-color", "flood-opacity", "font-family", "font-size", "font-size-adjust", "font-stretch",
    "font-style", "font-variant", "font-weight", "format", "from", "fr", "fx", "fy", "g1", "g2", "glyph-name",
    "glyph-orientation-horizontal", "glyph-orientation-vertical", "glyphRef", "gradientTransform", "gradientUnits",
    "hanging", "horiz-adv-x", "horiz-origin-x", "ideographic", "image-rendering", "in", "in2", "intercept", "k",
    "k1", "k2", "k3", "k4", "kernelMatrix", "kernelUnitLength", "kerning", "keyPoints", "keySplines", "keyTimes",
    "lengthAdjust", "letter-spacing", "lighting-color", "limitingConeAngle", "local", "marker-end", "marker-mid",
    "marker-start", "markerHeight", "markerUnits", "markerWidth", "mask", "maskContentUnits", "maskUnits",
    "mathematical", "max", "media", "method", "min", "mode", "name", "numOctaves", "offset", "onabort", "onactivate",
    "onbegin", "onclick", "onend", "onerror", "onfocusin", "onfocusout", "onload", "onmousedown", "onmousemove",
    "onmouseout", "onmouseover", "onmouseup", "onrepeat", "onresize", "onscroll", "onunload", "operator", "order",
    "orient", "orientation", "origin", "overflow", "overline-position", "overline-thickness", "panose-1", "paint-order",
    "path", "pathLength", "patternContentUnits", "patternTransform", "patternUnits", "pointer-events", "points",
    "pointsAtX", "pointsAtY", "pointsAtZ", "preserveAlpha", "preserveAspectRatio", "primitiveUnits", "r",
    "radius", "referrerPolicy", "refX", "refY", "rel", "repeatCount", "repeatDur", "requiredExtensions",
    "requiredFeatures", "restart", "result", "rotate", "rx", "ry", "scale", "seed", "shape-rendering", "slope",
    "spacing", "specularConstant", "specularExponent", "speed", "spreadMethod", "startOffset", "stdDeviation",
    "stemh", "stemv", "stitchTiles", "stop-color", "stop-opacity", "strikethrough-position", "strikethrough-thickness",
    "string", "stroke", "stroke-dasharray", "stroke-dashoffset", "stroke-linecap", "stroke-linejoin", "stroke-miterlimit",
    "stroke-opacity", "stroke-width", "surfaceScale", "systemLanguage", "tabindex", "tableValues", "targetX",
    "targetY", "text-anchor", "text-decoration", "text-rendering", "textLength", "to", "transform", "type",
    "u1", "u2", "underline-position", "underline-thickness", "unicode", "unicode-bidi", "unicode-range", "units-per-em",
    "v-alphabetic", "v-hanging", "v-ideographic", "v-mathematical", "values", "vector-effect", "version",
    "vert-adv-y", "vert-origin-x", "vert-origin-y", "viewBox", "viewTarget", "visibility", "widths", "word-spacing",
    "writing-mode", "x", "x-height", "x1", "x2", "xChannelSelector", "xlink:actuate", "xlink:arcrole", "xlink:href",
    "xlink:role", "xlink:show", "xlink:title", "xlink:type", "xml:base", "xml:lang", "xml:space", "y", "y1", "y2",
    "yChannelSelector", "z", "zoomAndPan", "xmlns",
    // WebView 和 iFrame 属性
    "allow", "allowfullscreen", "allowpaymentrequest", "allowusermedia", "csp", "height", "loading", "name", "referrerpolicy",
    "sandbox", "src", "srcdoc", "width"
];

// 标签白名单，均会转小写，所以只需要配置小写规则
const white_list = {
    a: attribute,
    div: attribute,
    img: attribute,
    p: attribute,
    span: attribute,
    h1: attribute,
    h2: attribute,
    h3: attribute,
    h4: attribute,
    h5: attribute,
    h6: attribute,
    code: attribute,
    blockquote: attribute,
    button: attribute,
    hr: attribute,
    ul: attribute,
    ol: attribute,
    li: attribute,
    table: attribute,
    tr: attribute,
    td: attribute,
    th: attribute,
    form: attribute,
    input: attribute,
    textarea: attribute,
    label: attribute,
    select: attribute,
    option: attribute,
    iframe: attribute,
    canvas: attribute,
    audio: attribute,
    video: attribute,
    abbr: attribute,
    address: attribute,
    b: attribute,
    bdi: attribute,
    bdo: attribute,
    big: attribute,
    center: attribute,
    cite: attribute,
    del: attribute,
    dfn: attribute,
    em: attribute,
    font: attribute,
    i: attribute,
    ins: attribute,
    kbd: attribute,
    mark: attribute,
    meter: attribute,
    output: attribute,
    progress: attribute,
    q: attribute,
    rp: attribute,
    rt: attribute,
    ruby: attribute,
    s: attribute,
    samp: attribute,
    small: attribute,
    strike: attribute,
    strong: attribute,
    sub: attribute,
    sup: attribute,
    template: attribute,
    time: attribute,
    tt: attribute,
    u: attribute,
    var: attribute,
    wbr: attribute,
    svg: attribute,
    body: attribute,
    html: attribute,
    path: attribute,
    meta: attribute,
    title: attribute,
    '!doctype html': attribute,
    '!doctype': attribute,
    source: attribute,
    pre: attribute,
    linearGradient: attribute,
    stop: attribute,
    br: attribute,
    figure: attribute,
    head: attribute,
    ifream: attribute,
    webview: attribute
};

// xss配置
const xss_options = {
    whiteList: white_list
};

// 去掉不在白名单上的标签
const strip_ignore_tag_body = true;

export default {
    strip_ignore_tag_body,
    xss_options,
    loading_tips,
    websocket_connect_str,
    language_map,
    map_language,
    map_language_file,
    language_tips,
    cpp,
    c,
    java,
    python,
    go,
    php,
    javascript,
    rust,
    csharp,
    ruby,
    typescript,
    // 编辑器主题
    themelist,
    // 编辑器语言
    options,
    /**
     * 文章列表数据模板
     * @var object $article_list_data
     */
    article_list_data,
    // OJ题库列表数据模板
    oj_problem_list_data,
    // OJ竞赛列表数据模板
    oj_contest_list_data,
    // 用户列表数据模板
    user_list_data,
    // 用户排名界面数据模板
    user_rank_list_data,
    // 提交记录数据模板
    codehistory_data,
    // 短句数据模板
    short_sentence_list,
    // 商品数据模板
    goods_list_data,
    max_video_retry_times,
    video_list_data,
    question_list_data,
    monitor_list_data,
    notice_list_data
};