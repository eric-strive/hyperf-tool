<?php

namespace Hyperf\EricTool\Annotation;

use App\Constants\SwaggerTemplate;
use Hyperf\Apidog\Annotation\Body;
use Hyperf\Apidog\Annotation\Header;
use Hyperf\Apidog\Annotation\Param;
use Hyperf\Apidog\Annotation\Query;
use Hyperf\EricTool\Exception\ToolException;
use Hyperf\Di\Annotation\AnnotationCollector;

/**
 * User: junxia
 * Date: 2020/11/25
 * Time: 下午3:02
 *
 * @Annotation
 * @Target("METHOD")
 */
class ValidationRequest extends Param
{
    /**
     * 验证模板
     *
     * @var string
     */
    public $template = '';

    public $key            = 'default|default';
    public $rule           = "";
    public $annotationRule = "";

    public function __construct($value = null)
    {
        parent::__construct($value);
        $this->ruleValidation($this->template);
        $this->setName()->setDescription()->setRequire()->setType();
    }

    public function ruleValidation($template)
    {
        $tmpConf = SwaggerTemplate::SWAGGER_TEMPLATE;
        $rule    = $tmpConf[$template] ?? null;
        if (empty($rule)) {
            throw new ToolException(sprintf('%s值不存在', $template));
        }
        $type = $rule['in'];
        switch ($type) {
            case 'header':
                $this->annotationRule = Header::class;
                break;
            case 'query':
                $this->annotationRule = Query::class;
                break;
            case 'body':
                $this->annotationRule = Body::class;
                break;
        }
        foreach ($rule as $ruleKey => $item) {
            if (property_exists($this, $ruleKey)) {
                $this->{$ruleKey} = $item;
            }
        }
    }

    public function collectMethod(string $className, ?string $target): void
    {
        AnnotationCollector::collectMethod($className, $target, $this->annotationRule, $this);
    }
}
