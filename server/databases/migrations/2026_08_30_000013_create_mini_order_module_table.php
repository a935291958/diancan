<?php

declare(strict_types=1);
/**
 * 模块：点餐 — 迁移 jt_jiating_order（select_spec 存规格 JSON）
 */
use App\Support\MiniTable;
use Hyperf\Database\Migrations\Migration;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Schema\Schema;

class CreateMiniOrderModuleTable extends Migration
{
    public function up(): void
    {
        $table = MiniTable::name('order');
        if (Schema::hasTable($table)) {
            return;
        }
        Schema::create($table, static function (Blueprint $blueprint): void {
            $blueprint->increments('id')->comment('点餐ID');
            $blueprint->unsignedInteger('family_id')->default(0)->comment('家庭ID');
            $blueprint->unsignedInteger('food_id')->default(0)->comment('菜品ID');
            $blueprint->text('select_spec')->comment('用户选中规格JSON');
            $blueprint->unsignedInteger('order_uid')->default(0)->comment('点餐人ID');
            $blueprint->unsignedInteger('cook_uid')->default(0)->comment('指派烹饪人ID');
            $blueprint->string('meal_type', 10)->default('')->comment('用餐时段：早/中/晚');
            $blueprint->string('order_date', 20)->default('')->comment('点餐日期');
            $blueprint->unsignedTinyInteger('status')->default(1)->comment('1待制作2制作中3已完成4已取消');
            $blueprint->unsignedInteger('create_time')->default(0)->comment('点餐时间');
            $blueprint->index('family_id', 'idx_family_id');
            $blueprint->index('order_date', 'idx_order_date');
            $blueprint->comment('点餐记录表');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(MiniTable::name('order'));
    }
}
