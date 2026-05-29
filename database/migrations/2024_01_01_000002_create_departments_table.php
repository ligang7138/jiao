<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 创建部门表
     */
    public function up(): void
    {
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->comment('部门名称');
            $table->unsignedBigInteger('parent_id')->default(0)->comment('父部门ID');
            $table->integer('sort')->default(0)->comment('排序');
            $table->tinyInteger('status')->default(1)->comment('状态 0=停用 1=启用');
            $table->timestamps();

            $table->index('parent_id');
            $table->index('status');
        });

        // MySQL only
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE departments COMMENT '部门表'");
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('departments');
    }
};