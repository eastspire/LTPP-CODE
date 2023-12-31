<?php

/**
 * This file is part of webman.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the MIT-LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @author    walkor<walkor@workerman.net>
 * @copyright walkor<walkor@workerman.net>
 * @link      http://www.workerman.net/
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */

return [
    // File update detection and automatic reload
    // 'monitor' => [
    //     'handler' => process\Monitor::class,
    //     'reloadable' => false,
    //     'constructor' => [
    //         // Monitor these directories
    //         'monitor_dir' => [
    //             app_path(),
    //             config_path(),
    //             base_path() . '/process',
    //             base_path() . '/support',
    //             base_path() . '/resource',
    //             base_path() . '/.env',
    //         ],
    //         // Files with these suffixes will be monitored
    //         'monitor_extensions' => [
    //             'php',
    //             'html',
    //             'htm',
    //             'env'
    //         ]
    //     ]
    // ],
    'CreatContest' => [
        'CreatContest' => process\CreatContest::class,
    ],
    'Chatfile' => [
        'handler' => process\Chatfile::class
    ],
    'Dayproblem' => [
        'handler' => process\Dayproblem::class
    ],
    'Gitcode' => [
        'handler' => process\Gitcode::class
    ],
    'RobotContest' => [
        'handler' => process\RobotContest::class
    ],
    'ContestRank' => [
        'handler' => process\ContestRank::class
    ],
    // 'EchartsRank' => [
    //     'handler' => \Webman\App::class,
    //     'listen' => 'http://0.0.0.0:48788',
    //     'count' => cpu_count() > 8 ? 8 : cpu_count(),
    //     // 进程数
    //     'constructor' => [
    //         'request_class' => \support\Request::class,
    //         // request类设置
    //         'logger' => \support\Log::channel('default'),
    //         // 日志实例
    //         'app_path' => app_path(),
    //         // app目录位置
    //         'public_path' => public_path() // public目录位置
    //     ]
    // ],
    // 'Rank' => [
    //     'handler' => \Webman\App::class,
    //     'listen' => 'http://0.0.0.0:48789',
    //     'count' => cpu_count() > 8 ? 8 : cpu_count(),
    //     // 进程数
    //     'constructor' => [
    //         'request_class' => \support\Request::class,
    //         // request类设置
    //         'logger' => \support\Log::channel('default'),
    //         // 日志实例
    //         'app_path' => app_path(),
    //         // app目录位置
    //         'public_path' => public_path() // public目录位置
    //     ]
    // ],
    // 'OnlineJudge' => [
    //     'handler' => \Webman\App::class,
    //     'listen' => 'http://0.0.0.0:48790',
    //     'count' => cpu_count() > 8 ? 8 : cpu_count(),
    //     // 进程数
    //     'constructor' => [
    //         'request_class' => \support\Request::class,
    //         // request类设置
    //         'logger' => \support\Log::channel('default'),
    //         // 日志实例
    //         'app_path' => app_path(),
    //         // app目录位置
    //         'public_path' => public_path() // public目录位置
    //     ]
    // ],
    // 'OnlineTest' => [
    //     'handler' => \Webman\App::class,
    //     'listen' => 'http://0.0.0.0:48791',
    //     'count' => cpu_count() > 8 ? 8 : cpu_count(),
    //     // 进程数
    //     'constructor' => [
    //         'request_class' => \support\Request::class,
    //         // request类设置
    //         'logger' => \support\Log::channel('default'),
    //         // 日志实例
    //         'app_path' => app_path(),
    //         // app目录位置
    //         'public_path' => public_path() // public目录位置
    //     ]
    // ],
    // 'ArticleAndQuestion' => [
    //     'handler' => \Webman\App::class,
    //     'listen' => 'http://0.0.0.0:48792',
    //     'count' => cpu_count() > 8 ? 8 : cpu_count(),
    //     // 进程数
    //     'constructor' => [
    //         'request_class' => \support\Request::class,
    //         // request类设置
    //         'logger' => \support\Log::channel('default'),
    //         // 日志实例
    //         'app_path' => app_path(),
    //         // app目录位置
    //         'public_path' => public_path() // public目录位置
    //     ]
    // ],
    // 'UserAndQuestion' => [
    //     'handler' => \Webman\App::class,
    //     'listen' => 'http://0.0.0.0:48793',
    //     'count' => cpu_count() > 8 ? 8 : cpu_count(),
    //     // 进程数
    //     'constructor' => [
    //         'request_class' => \support\Request::class,
    //         // request类设置
    //         'logger' => \support\Log::channel('default'),
    //         // 日志实例
    //         'app_path' => app_path(),
    //         // app目录位置
    //         'public_path' => public_path() // public目录位置
    //     ]
    // ],
    // 'Problem' => [
    //     'handler' => \Webman\App::class,
    //     'listen' => 'http://0.0.0.0:48794',
    //     'count' => cpu_count() > 8 ? 8 : cpu_count(),
    //     // 进程数
    //     'constructor' => [
    //         'request_class' => \support\Request::class,
    //         // request类设置
    //         'logger' => \support\Log::channel('default'),
    //         // 日志实例
    //         'app_path' => app_path(),
    //         // app目录位置
    //         'public_path' => public_path() // public目录位置
    //     ]
    // ],
    // 'CodeHistory' => [
    //     'handler' => \Webman\App::class,
    //     'listen' => 'http://0.0.0.0:48795',
    //     'count' => cpu_count() > 8 ? 8 : cpu_count(),
    //     // 进程数
    //     'constructor' => [
    //         'request_class' => \support\Request::class,
    //         // request类设置
    //         'logger' => \support\Log::channel('default'),
    //         // 日志实例
    //         'app_path' => app_path(),
    //         // app目录位置
    //         'public_path' => public_path() // public目录位置
    //     ]
    // ],
    // 'Contest' => [
    //     'handler' => \Webman\App::class,
    //     'listen' => 'http://0.0.0.0:48796',
    //     'count' => cpu_count() > 8 ? 8 : cpu_count(),
    //     // 进程数
    //     'constructor' => [
    //         'request_class' => \support\Request::class,
    //         // request类设置
    //         'logger' => \support\Log::channel('default'),
    //         // 日志实例
    //         'app_path' => app_path(),
    //         // app目录位置
    //         'public_path' => public_path() // public目录位置
    //     ]
    // ],
    // 'Setting' => [
    //     'handler' => \Webman\App::class,
    //     'listen' => 'http://0.0.0.0:48797',
    //     'count' => cpu_count() > 8 ? 8 : cpu_count(),
    //     // 进程数
    //     'constructor' => [
    //         'request_class' => \support\Request::class,
    //         // request类设置
    //         'logger' => \support\Log::channel('default'),
    //         // 日志实例
    //         'app_path' => app_path(),
    //         // app目录位置
    //         'public_path' => public_path() // public目录位置
    //     ]
    // ],
    // 'Comment' => [
    //     'handler' => \Webman\App::class,
    //     'listen' => 'http://0.0.0.0:48798',
    //     'count' => cpu_count() > 8 ? 8 : cpu_count(),
    //     // 进程数
    //     'constructor' => [
    //         'request_class' => \support\Request::class,
    //         // request类设置
    //         'logger' => \support\Log::channel('default'),
    //         // 日志实例
    //         'app_path' => app_path(),
    //         // app目录位置
    //         'public_path' => public_path() // public目录位置
    //     ]
    // ],
    // 'Login' => [
    //     'handler' => \Webman\App::class,
    //     'listen' => 'http://0.0.0.0:48799',
    //     'count' => cpu_count() > 8 ? 8 : cpu_count(),
    //     // 进程数
    //     'constructor' => [
    //         'request_class' => \support\Request::class,
    //         // request类设置
    //         'logger' => \support\Log::channel('default'),
    //         // 日志实例
    //         'app_path' => app_path(),
    //         // app目录位置
    //         'public_path' => public_path() // public目录位置
    //     ]
    // ],
    // 'Register' => [
    //     'handler' => \Webman\App::class,
    //     'listen' => 'http://0.0.0.0:48800',
    //     'count' => cpu_count() > 8 ? 8 : cpu_count(),
    //     // 进程数
    //     'constructor' => [
    //         'request_class' => \support\Request::class,
    //         // request类设置
    //         'logger' => \support\Log::channel('default'),
    //         // 日志实例
    //         'app_path' => app_path(),
    //         // app目录位置
    //         'public_path' => public_path() // public目录位置
    //     ]
    // ],
];
