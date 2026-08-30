<?php

declare(strict_types=1);
/**
 * 校验器基类（对应 ThinkPHP validate 层）.
 *
 * 按控制器方法名自动合并规则：commonRules() + {action}Rules()。
 * 例：FamilyController::create -> createRules()。
 */

namespace App\Http\Mini\Validate;

use App\Http\Common\Request\Traits\ActionRulesTrait;
use App\Http\Common\Request\Traits\NoAuthorizeTrait;
use Hyperf\Validation\Request\FormRequest;

abstract class AbstractValidate extends FormRequest
{
    use NoAuthorizeTrait;
    use ActionRulesTrait;
}
