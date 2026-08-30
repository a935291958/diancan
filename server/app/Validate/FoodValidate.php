<?php

declare(strict_types=1);
/**
 * 菜品 / 规格校验.
 */

namespace App\Validate;

class FoodValidate extends AbstractValidate
{
    protected function createRules(): array
    {
        return $this->foodRules();
    }

    protected function updateRules(): array
    {
        return $this->foodRules();
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
    private function foodRules(): array
    {
        return [
            'family_id' => 'nullable|integer|min:1',
            'food_name' => 'required|string|max:50',
            'food_img' => 'nullable|string|max:255',
            'category' => 'nullable|string|max:20',
            'cook_uids' => 'nullable',
            'specs' => 'nullable|array',
            'specs.*.spec_name' => 'required_with:specs|string|max:30',
            'specs.*.spec_value' => 'required_with:specs|string|max:100',
        ];
    }

    protected function commonMessages(): array
    {
        return [
            'food_name.required' => '请填写菜品名称',
        ];
    }
}
