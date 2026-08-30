<?php

declare(strict_types=1);
/**
 * 模块：菜品 — 迁移 jt_jiating_food / food_spec
 */
use App\Http\Mini\Support\MiniTable;
use Hyperf\Database\Migrations\Migration;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Schema\Schema;

class CreateMiniFoodModuleTables extends Migration
{
    public function up(): void
    {
        $food = MiniTable::name('food');
        if (! Schema::hasTable($food)) {
            Schema::create($food, static function (Blueprint $table): void {
                $table->increments('id')->comment('菜品ID');
                $table->unsignedInteger('family_id')->default(0)->comment('所属家庭ID');
                $table->string('food_name', 50)->default('')->comment('菜品名称');
                $table->string('food_img', 255)->default('')->comment('菜品图片地址');
                $table->string('category', 20)->default('')->comment('菜品分类');
                $table->string('cook_uids', 100)->default('')->comment('可烹饪成员ID，逗号分隔');
                $table->unsignedInteger('create_uid')->default(0)->comment('创建人ID');
                $table->unsignedInteger('create_time')->default(0)->comment('创建时间');
                $table->unsignedInteger('update_time')->default(0)->comment('更新时间');
                $table->index('family_id', 'idx_family_id');
                $table->comment('菜品表');
            });
        }

        $spec = MiniTable::name('food_spec');
        if (! Schema::hasTable($spec)) {
            Schema::create($spec, static function (Blueprint $table): void {
                $table->increments('id')->comment('规格ID');
                $table->unsignedInteger('food_id')->default(0)->comment('关联菜品ID');
                $table->string('spec_name', 30)->default('')->comment('规格名称');
                $table->string('spec_value', 100)->default('')->comment('规格选项值');
                $table->unsignedInteger('create_time')->default(0)->comment('创建时间');
                $table->index('food_id', 'idx_food_id');
                $table->comment('菜品规格表');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists(MiniTable::name('food_spec'));
        Schema::dropIfExists(MiniTable::name('food'));
    }
}
