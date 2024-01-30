<?php
/*
 * @Author: SQS 1491579574@qq.com
 * @Date: 2023-06-01 09:11:34
 * @LastEditors: SQS 1491579574@qq.com
 * @LastEditTime: 2023-06-02 17:22:11
 * @FilePath: \LTPP-CODE\config\plugin\tinywan\jwt\app.php
 * @Description: Email:1491579574@qq.com
 * QQ:1491579574
 * Copyright (c) 2023 by ${git_name_email}, All Rights Reserved. 
 */

return [
        'enable' => true,
        'jwt' => [
                // 算法类型 HS256、HS384、HS512、RS256、RS384、RS512、ES256、ES384、Ed25519
                'algorithms' => 'HS256',
                // access令牌秘钥
                'access_secret_key' => 'SQS@ltpp.vip@ROOT',
                // access令牌过期时间，单位：秒。30 天
                'access_exp' => 2592000,
                // refresh令牌秘钥
                'refresh_secret_key' => 'SQS@666@sqs@ltpp.vip@sqs@666@SQS',
                // refresh令牌过期时间，单位：秒。30 天
                'refresh_exp' => 2592000,
                // refresh 令牌是否禁用，默认不禁用 false
                'refresh_disable' => false,
                // 令牌签发者
                'iss' => 'ltpp.vip',
                // 时钟偏差冗余时间，单位秒。建议这个余地应该不大于几分钟。
                'leeway' => 60,
                // 单设备登录
                'is_single_device' => false,
                // 缓存令牌时间，单位：秒。30 天
                'cache_token_ttl' => 2592000,
                // 缓存令牌前缀
                'cache_token_pre' => 'JWT:TOKEN:',
                // 用户信息模型
                'user_model' => function ($uid) {
                        return [];
                },
                /**
                 * access令牌私钥
                 */
                'access_private_key' => <<<EOD
-----BEGIN RSA PRIVATE KEY-----
...
-----END RSA PRIVATE KEY-----
EOD,

                /**
                 * access令牌公钥
                 */
                'access_public_key' => <<<EOD
-----BEGIN PUBLIC KEY-----
...
-----END PUBLIC KEY-----
EOD,

                /**
                 * refresh令牌私钥
                 */
                'refresh_private_key' => <<<EOD
-----BEGIN RSA PRIVATE KEY-----
...
-----END RSA PRIVATE KEY-----
EOD,

                /**
                 * refresh令牌公钥
                 */
                'refresh_public_key' => <<<EOD
-----BEGIN PUBLIC KEY-----
...
-----END PUBLIC KEY-----
EOD,
        ],
];
