<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 创建岗位表
     */
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->unique()->comment('岗位名称');
            $table->string('code', 50)->nullable()->comment('岗位编码');
            $table->unsignedBigInteger('department_id')->nullable()->comment('所属部门');
            $table->text('remark')->nullable()->comment('备注');
            $table->text('privilege')->nullable()->comment('权限列表JSON');
            $table->tinyInteger('status')->default(1)->comment('状态 0=停用 1=启用');
            $table->timestamps();

            $table->index('department_id');
            $table->index('status');
        });

        // MySQL only
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE posts COMMENT '岗位表'");
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};