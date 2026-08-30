<?php

declare(strict_types=1);
/**
 * 模块：家庭 — 迁移 jt_jiating_family / family_member
 */
use App\Support\MiniTable;
use Hyperf\Database\Migrations\Migration;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Schema\Schema;

class CreateMiniFamilyModuleTables extends Migration
{
    public function up(): void
    {
        $family = MiniTable::name('family');
        if (! Schema::hasTable($family)) {
            Schema::create($family, static function (Blueprint $table): void {
                $table->increments('id')->comment('家庭ID');
                $table->string('family_name', 50)->default('')->comment('家庭名称');
                $table->string('invite_code', 6)->default('')->comment('6位邀请码');
                $table->unsignedInteger('admin_uid')->default(0)->comment('管理员用户ID');
                $table->unsignedInteger('create_time')->default(0)->comment('创建时间');
                $table->unsignedInteger('update_time')->default(0)->comment('更新时间');
                $table->unique('invite_code', 'idx_invite_code');
                $table->comment('家庭表');
            });
        }

        $member = MiniTable::name('family_member');
        if (! Schema::hasTable($member)) {
            Schema::create($member, static function (Blueprint $table): void {
                $table->increments('id')->comment('关联ID');
                $table->unsignedInteger('family_id')->default(0)->comment('家庭ID');
                $table->unsignedInteger('uid')->default(0)->comment('用户ID');
                $table->unsignedInteger('create_time')->default(0)->comment('加入时间');
                $table->index('family_id', 'idx_family_id');
                $table->index('uid', 'idx_uid');
                $table->unique(['family_id', 'uid'], 'uk_family_uid');
                $table->comment('家庭成员关联表');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists(MiniTable::name('family_member'));
        Schema::dropIfExists(MiniTable::name('family'));
    }
}
