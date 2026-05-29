<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 旧库 category 表缺少 float_rate_cap 字段，补齐以兼容商品浮动率上限业务。
     */
    public function up(): void
    {
        if (! Schema::hasTable('category')) {
            return;
        }

        Schema::table('category', function (Blueprint $table) {
            if (! Schema::hasColumn('category', 'float_rate_cap')) {
                $table->decimal('float_rate_cap', 5, 4)
                    ->default(0.1300)
                    ->after('logo')
                    ->comment('浮动率上限');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('category') || ! Schema::hasColumn('category', 'float_rate_cap')) {
            return;
        }

        Schema::table('category', function (Blueprint $table) {
            $table->dropColumn('float_rate_cap');
        });
    }
};
