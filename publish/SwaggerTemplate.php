<?php

namespace App\Constants;

/**
 * swagger 模板使用
 *
 * @package App\Constants
 */
class SwaggerTemplate
{
    //swagger模板 生成注解
    public const SWAGGER_TEMPLATE = [
        "authorization" => [
            'in'          => 'header',
            "key"         => "Authorization",
            "description" => "接口访问凭证",
            "default"     => "Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJqdGkiOiIxIiwiaWF0IjoxNjA1MTUyNjY1LCJuYmYiOjE2MDUxNTI2NjUsImV4cCI6ODgwNTE1MjY2NSwiaWQiOjEsInBob25lIjoiMTgyMDcxOTkyMzAifQ.anxin",
            "rule"        => "",
        ],
        "id"            => [
            'in'          => 'query',
            "key"         => "id",
            "description" => "ID",
            "default"     => "1",
            "rule"        => "required|integer",
        ],
    ];

    //请求body 模板，做合并
    public const VALIDATION_RULE = [
        'page_search' => [
            "page|页码"       => "integer|1",
            "pageSize|每页条数" => "integer|10",
        ],
    ];
}