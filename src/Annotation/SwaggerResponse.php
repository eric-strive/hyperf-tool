<?php

namespace Hyperf\EricTool\Annotation;

use Hyperf\EricTool\Traits\ValidationRule;
use Hyperf\Apidog\Annotation\ApiResponse;
use Hyperf\Di\Annotation\AnnotationCollector;

/**
 * User: eric
 * Date: 2020/11/25
 * Time: 下午3:02
 *
 * @Annotation
 * @Target("METHOD")
 */
class SwaggerResponse extends ApiResponse
{
    use ValidationRule;

    /**
     * 响应数据类
     *
     * @var
     */
    public $responseClass;
    /**
     * 场景
     *
     * @var string
     */
    public $scene = '';

    public $code        = 0;
    public $description = '请求成功';

    public function __construct($value = null)
    {
        parent::__construct($value);
        $this->schema = $this->getValidation($this->responseClass, $this->scene);
    }

    public function collectMethod(string $className, ?string $target): void
    {
        AnnotationCollector::collectMethod($className, $target, ApiResponse::class, $this);
    }
}