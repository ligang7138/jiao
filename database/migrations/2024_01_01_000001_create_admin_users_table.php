<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 创建管理员用户表
     */
    public function up(): void
    {
        Schema::create('admin_users', function (Blueprint $table) {
            $table->id();
            $table->string('username', 50)->unique()->comment('用户名');
            $table->string('password', 100)->comment('密码');
            $table->string('salt', 10)->default('')->comment('密码盐值（兼容老系统）');
            $table->string('name', 50)->nullable()->comment('姓名');
            $table->string('email', 100)->nullable()->comment('邮箱');
            $table->string('mobile', 20)->nullable()->comment('手机号');
            $table->string('avatar', 500)->nullable()->comment('头像');
            @$table->unsignedBigInteger('department_id')->nullable()->comment('部门ID');
            $table->boolean('is_super')->default(false)->comment('是否超级管理员');
            $table->tinyInteger('status')->default(1)->comment('状态 0=停用 1=启用');
            $table->unsignedBigInteger('last_login_time')->nullable()->comment('最后登录时间');
            $table->string('last_login_ip', 45)->nullable()->comment('最后登录IP');
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();

            $table->index('department_id');
            $table->index('status');
        });

        // 用户-岗位关联表
        Schema::create('admin_user_posts', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->comment('用户ID');
            $table->unsignedBigInteger('post_id')->comment('岗位ID');
            $table->primary(['user_id', 'post_id']);
        });

        // 添加注释（仅 MySQL）
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE admin_users COMMENT '管理员用户表'");
            DB::statement("ALTER TABLE admin_user_posts COMMENT '用户岗位关联表'");
        }
    }

    /**
     * 回滚
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_user_posts');
        Schema::dropIfExists('admin_users');
    }
};