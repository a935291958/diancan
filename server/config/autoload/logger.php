<?php

declare(strict_types=1);
/**
 * This file is part of MineAdmin.
 *
 * @link     https://www.mineadmin.com
 * @document https://doc.mineadmin.com
 * @contact  root@imoi.cn
 * @license  https://github.com/mineadmin/MineAdmin/blob/master/LICENSE
 */
use Mine\Support\Logger\UuidRequestIdProcessor;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Level;

$formatter = [
    'class' => LineFormatter::class,
    'constructor' => [
        'format' => null,
        'dateFormat' => 'Y-m-d H:i:s',
        'allowInlineLineBreaks' => true,
    ],
];

$processor = [
    'class' => UuidRequestIdProcessor::class,
];

return [
    'default' => [
        'handler' => [
            'class' => RotatingFileHandler::class,
            'constructor' => [
                'filename' => BASE_PATH . '/runtime/logs/debug.log',
                'level' => Level::Debug,
            ],
        ],
        'formatter' => $formatter,
        'processor' => $processor,
    ],
    'sql' => [
        'handler' => [
            'class' => RotatingFileHandler::class,
            'constructor' => [
                'filename' => BASE_PATH . '/runtime/logs/sql.log',
                'level' => Level::Debug,
            ],
        ],
        'formatter' => $formatter,
        'processor' => $processor,
    ],
    // AbstractHandler::report() 使用该通道
    'error' => [
        'handler' => [
            'class' => RotatingFileHandler::class,
            'constructor' => [
                'filename' => BASE_PATH . '/runtime/logs/error.log',
                'level' => Level::Error,
            ],
        ],
        'formatter' => $formatter,
        'processor' => $processor,
    ],
    'api' => [
        'handler' => [
            'class' => RotatingFileHandler::class,
            'constructor' => [
                'filename' => BASE_PATH . '/runtime/logs/api.log',
                'level' => Level::Info,
            ],
        ],
        'formatter' => $formatter,
        'processor' => $processor,
    ],
];
