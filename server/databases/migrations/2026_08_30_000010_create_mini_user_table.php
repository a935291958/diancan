<?php

declare(strict_types=1);
/**
 * 模块：用户 — 迁移 jt_jiating_user
 */
use App\Http\Mini\Support\MiniTable;
use Hyperf\Database\Migrations\Migration;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Schema\Schema;

class CreateMiniUserTable extends Migration
{
    public function up(): void
    {
        $table = MiniTable::name('user');
        if (Schema::hasTable($table)) {
            return;
        }
        Schema::create($table, static function (Blueprint $blueprint): void {
            $blueprint->increments('id')->comment('用户ID');
            $blueprint->string('openid', 100)->default('')->comment('微信唯一openid');
            $blueprint->string('nickname', 50)->default('')->comment('用户昵称');
            $blueprint->string('avatar', 255)->default('')->comment('用户头像地址');
            $blueprint->string('token', 255)->default('')->comment('登录令牌');
            $blueprint->unsignedInteger('create_time')->default(0)->comment('创建时间');
            $blueprint->unsignedInteger('update_time')->default(0)->comment('更新时间');
            $blueprint->unique('openid', 'idx_openid');
            $blueprint->index('token', 'idx_token');
            $blueprint->comment('用户表');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(MiniTable::name('user'));
    }
}
