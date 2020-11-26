<?php

namespace Hyperf\EricTool\Annotation;

use Hyperf\EricTool\Traits\ValidationRule;
use Hyperf\Apidog\Annotation\Body;
use Hyperf\Di\Annotation\AnnotationCollector;

/**
 * User: eric
 * Date: 2020/11/25
 * Time: 下午3:02
 *
 * @Annotation
 * @Target("METHOD")
 */
class BodyValidation extends Body
{
    use ValidationRule;

    /**
     * 验证器
     *
     * @var string
     */
    public $validate = '';
    /**
     * 场景
     *
     * @var string
     */
    public $scene = '';

    public $template = "";

    public function __construct($value = null)
    {
        parent::__construct($value);
        if (is_array($value)) {
            foreach ($value as $key => $val) {
                if ($key == 'validate') {
                    $this->rules = $this->getValidation($this->validate, $this->scene, $this->template);
                }
            }
        }
    }

    public function collectMethod(string $className, ?string $target): void
    {
        AnnotationCollector::collectMethod($className, $target, Body::class, $this);
    }
}
