<?php

declare(strict_types=1);
/**
 * 菜品 / 规格校验，字段对齐前端 saveFood / flattenSpecs.
 */

namespace App\Http\Mini\Validate;

class FoodValidate extends AbstractValidate
{
    protected function createRules(): array
    {
        return $this->foodRules(true);
    }

    protected function updateRules(): array
    {
        return $this->foodRules(false);
    }

    protected function createSpecRules(): array
    {
        return [
            'spec_name' => 'required|string|max:30',
            'spec_value' => 'required|string|max:100',
        ];
    }

    protected function updateSpecRules(): array
    {
        return $this->createSpecRules();
    }

    /**
     * @return array<string, string>
     */
    private function foodRules(bool $creating): array
    {
        return [
            'family_id' => 'nullable|integer|min:1',
            'food_name' => ($creating ? 'required' : 'sometimes|required') . '|string|min:1|max:50',
            'food_img' => 'nullable|string|max:255',
            'category' => 'nullable|string|max:20',
            'cook_uids' => 'nullable',
            'specs' => 'nullable|array',
            'specs.*.spec_name' => 'required_with:specs|string|max:30',
            'specs.*.spec_value' => 'nullable|string|max:100',
        ];
    }

    protected function commonMessages(): array
    {
        return [
            'food_name.required' => '请填写菜品名称',
            'food_name.max' => '菜品名称最多 50 个字',
            'category.max' => '分类名称过长',
            'specs.*.spec_name.required_with' => '规格名称不能为空',
            'spec_name.required' => '请填写规格名称',
            'spec_value.required' => '请填写规格选项',
        ];
    }
}
