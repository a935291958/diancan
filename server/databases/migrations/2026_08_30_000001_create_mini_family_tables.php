<?php

declare(strict_types=1);
/**
 * 家庭点餐小程序业务表：user / family / family_member / food / food_spec / order.
 * 逻辑全名 jt_jiating_* ；若全局 DB_PREFIX=jt_，则 Schema 表名为 jiating_*。
 */
use Hyperf\Database\Migrations\Migration;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Schema\Schema;

class CreateMiniFamilyTables extends Migration
{
    public function up(): void
    {
        Schema::create($this->table('user'), static function (Blueprint $table): void {
            $table->increments('id')->comment('用户ID');
            $table->string('openid', 100)->default('')->comment('微信唯一openid');
            $table->string('nickname', 50)->default('')->comment('用户昵称');
            $table->string('avatar', 255)->default('')->comment('用户头像地址');
            $table->string('token', 255)->default('')->comment('登录令牌');
            $table->unsignedInteger('create_time')->default(0)->comment('创建时间');
            $table->unsignedInteger('update_time')->default(0)->comment('更新时间');
            $table->unique('openid', 'idx_openid');
            $table->index('token', 'idx_token');
            $table->comment('用户表');
        });

        Schema::create($this->table('family'), static function (Blueprint $table): void {
            $table->increments('id')->comment('家庭ID');
            $table->string('family_name', 50)->default('')->comment('家庭名称');
            $table->string('invite_code', 6)->default('')->comment('6位邀请码');
            $table->unsignedInteger('admin_uid')->default(0)->comment('管理员用户ID');
            $table->unsignedInteger('create_time')->default(0)->comment('创建时间');
            $table->unsignedInteger('update_time')->default(0)->comment('更新时间');
            $table->unique('invite_code', 'idx_invite_code');
            $table->comment('家庭表');
        });

        Schema::create($this->table('family_member'), static function (Blueprint $table): void {
            $table->increments('id')->comment('关联ID');
            $table->unsignedInteger('family_id')->default(0)->comment('家庭ID');
            $table->unsignedInteger('uid')->default(0)->comment('用户ID');
            $table->unsignedInteger('create_time')->default(0)->comment('加入时间');
            $table->index('family_id', 'idx_family_id');
            $table->index('uid', 'idx_uid');
            $table->unique(['family_id', 'uid'], 'uk_family_uid');
            $table->comment('家庭成员关联表');
        });

        Schema::create($this->table('food'), static function (Blueprint $table): void {
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

        Schema::create($this->table('food_spec'), static function (Blueprint $table): void {
            $table->increments('id')->comment('规格ID');
            $table->unsignedInteger('food_id')->default(0)->comment('关联菜品ID');
            $table->string('spec_name', 30)->default('')->comment('规格名称');
            $table->string('spec_value', 100)->default('')->comment('规格选项值');
            $table->unsignedInteger('create_time')->default(0)->comment('创建时间');
            $table->index('food_id', 'idx_food_id');
            $table->comment('菜品规格表');
        });

        Schema::create($this->table('order'), static function (Blueprint $table): void {
            $table->increments('id')->comment('点餐ID');
            $table->unsignedInteger('family_id')->default(0)->comment('家庭ID');
            $table->unsignedInteger('food_id')->default(0)->comment('菜品ID');
            $table->text('select_spec')->comment('用户选中规格JSON');
            $table->unsignedInteger('order_uid')->default(0)->comment('点餐人ID');
            $table->unsignedInteger('cook_uid')->default(0)->comment('指派烹饪人ID');
            $table->string('meal_type', 10)->default('')->comment('用餐时段：早/中/晚');
            $table->string('order_date', 20)->default('')->comment('点餐日期');
            $table->unsignedTinyInteger('status')->default(1)->comment('状态1待制作2制作中3已完成4已取消');
            $table->unsignedInteger('create_time')->default(0)->comment('点餐时间');
            $table->index('family_id', 'idx_family_id');
            $table->index('order_date', 'idx_order_date');
            $table->comment('点餐记录表');
        });
    }

    public function down(): void
    {
        foreach (['order', 'food_spec', 'food', 'family_member', 'family', 'user'] as $name) {
            Schema::dropIfExists($this->table($name));
        }
    }

    /**
     * 相对全局 DB_PREFIX 的表名，避免 jt_ + jt_jiating_user 变成 jt_jt_jiating_user.
     */
    private function table(string $name): string
    {
        $full = (string) env('MINI_TABLE_PREFIX', 'jt_jiating_') . $name;
        $global = (string) env('DB_PREFIX', '');
        if ($global !== '' && str_starts_with($full, $global)) {
            return substr($full, strlen($global));
        }

        return $full;
    }
}
