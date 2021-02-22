<?php

namespace Hyperf\EricTool\Traits;

use App\Constants\SwaggerTemplate;
use Hyperf\EricTool\Exception\ToolException;

/**
 * User: wangwei
 * Date: 2020/11/25
 * Time: 下午4:04
 */
trait ValidationRule
{
    public function getValidation($validate, $scene, $template = null, $field = false): array
    {
        $validate = strrpos($validate, '\\') ? $validate : sprintf('\App\Request\%s', $validate);
        $rules    = $validate::$scene[$scene] ?? [];
        if (isset($validate::$field_description)) {
            $attributes = $field ? $validate::$field_description : $validate::$field;
        } else {
            $attributes = $validate::$field;
        }
        $validation = $this->validationData($rules, $attributes);
        $templateData = $this->getTemplate($template);
        $validation   = $validation ? array_merge($templateData, $validation) : $templateData;

        return $validation;
    }

    public function validationData($rules, $attributes)
    {
        $validation = [];
        foreach ($rules as $key => $val) {
            $title     = $attributes[$key] ?? null;
            $validaKey = $title ? sprintf('%s|%s', $key, $title) : sprintf('%s|%s', $key, $key);
            if (is_array($val)) {
                $validation[$validaKey] = $this->validationData($val, $attributes);
            } else {
                $validation[$validaKey] = $val;
            }
        }

        return $validation;
    }
    public function getTemplate($template): array
    {
        $templates    = SwaggerTemplate::VALIDATION_RULE;
        $templateData = [];
        if ($template) {
            if (!isset($templates[$template])) {
                throw new ToolException(sprintf('模板规则%s不存在', $template));
            }
            $templateData = $templates[$template];
        }

        return $templateData;
    }
}