<?php

declare(strict_types=1);

/**
 * Amqp automatic retry configuration
 */
return [
    'sum_times'           => 5,//单日发送总次数
    'code_length'         => 6,//验证码长度
    'is_ip_limit'         => true,//是否对IP限制
    'ip_sum_times'        => 10,//ip限制次数
    'every_time_interval' => 60,//发送频率限制
    'code_cache_time'     => 300,//验证码缓存时间
    'path'                => '/tmp'//excel临时存储位置
];
