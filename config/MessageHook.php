<?php
return [
    'env' => [
        'develop' => [
            'symbol' => 'α',
            'name'   => '測試一站'
        ],
        'staging' => [
            'symbol' => 'β',
            'name'   => '前測'
        ],
        'cron'    => [
            'symbol' => 'γ',
            'name'   => '測試二站'
        ],
        'master'  => [
            'symbol' => '',
            'name'   => '正式環境'
        ],
    ],
    'url' => env('UPGRADE_HOOK_URL', 'https://chat.googleapis.com/v1/spaces/AAAA9Z4vUcQ/messages?key=AIzaSyDdI0hCZtE6vySjMm-WEfRq3CPzqKqqsHI&token=ig3eNg9t1T2uET0t_1xPijQZTASiFq4bPzMMHL6ZrWY&messageReplyOption=REPLY_MESSAGE_FALLBACK_TO_NEW_THREAD'),
    'thread_key' => env('WEB_HOOK_THREAD_KEY', '1'),
    'should_thread' => env('USE_THREAD_ON_WEB_HOOK', false),
];
