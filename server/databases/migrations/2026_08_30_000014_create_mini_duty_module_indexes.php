<?php

declare(strict_types=1);
/**
 * 模块：分工 — 复用 order 表，补齐 cook_uid / status 索引。
 */
use App\Http\Mini\Support\MiniTable;
use Hyperf\Database\Migrations\Migration;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Schema\Schema;
use Throwable;

class CreateMiniDutyModuleIndexes extends Migration
{
    public function up(): void
    {
        $table = MiniTable::name('order');
        if (! Schema::hasTable($table)) {
            return;
        }
        try {
            Schema::table($table, static function (Blueprint $blueprint): void {
                $blueprint->index('cook_uid', 'idx_cook_uid');
                $blueprint->index('status', 'idx_status');
            });
        } catch (Throwable) {
            // 索引已存在时忽略
        }
    }

    public function down(): void
    {
        $table = MiniTable::name('order');
        if (! Schema::hasTable($table)) {
            return;
        }
        try {
            Schema::table($table, static function (Blueprint $blueprint): void {
                $blueprint->dropIndex('idx_cook_uid');
                $blueprint->dropIndex('idx_status');
            });
        } catch (Throwable) {
        }
    }
}
