<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 创建角色和权限表
     */
    public function up(): void
    {
        // 角色表
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->comment('角色名称');
            $table->string('code', 50)->unique()->comment('角色编码');
            $table->string('description', 200)->nullable()->comment('角色描述');
            $table->tinyInteger('status')->default(1)->comment('状态 0=停用 1=启用');
            $table->timestamps();

            $table->index('status');
        });

        // 权限表（菜单+按钮权限）
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('parent_id')->default(0)->comment('父ID');
            $table->string('name', 50)->comment('权限名称');
            $table->string('code', 100)->unique()->comment('权限编码');
            $table->tinyInteger('type')->default(1)->comment('类型 1=菜单 2=按钮');
            $table->string('path', 200)->nullable()->comment('路由路径');
            $table->string('component', 200)->nullable()->comment('组件路径');
            $table->string('icon', 50)->nullable()->comment('图标');
            $table->integer('sort')->default(0)->comment('排序');
            $table->tinyInteger('status')->default(1)->comment('状态 0=公开 1=需权限');
            $table->timestamps();

            $table->index('parent_id');
            $table->index('type');
            $table->index('status');
        });

        // 岗位-角色关联表
        Schema::create('post_roles', function (Blueprint $table) {
            $table->unsignedBigInteger('post_id')->comment('岗位ID');
            $table->unsignedBigInteger('role_id')->comment('角色ID');
            $table->primary(['post_id', 'role_id']);
        });

        // 角色-权限关联表
        Schema::create('role_permissions', function (Blueprint $table) {
            $table->unsignedBigInteger('role_id')->comment('角色ID');
            $table->unsignedBigInteger('permission_id')->comment('权限ID');
            $table->primary(['role_id', 'permission_id']);
        });

        // MySQL only
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE roles COMMENT '角色表'");
        }
        // MySQL only
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE permissions COMMENT '权限表'");
        }
        // MySQL only
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE post_roles COMMENT '岗位角色关联表'");
        }
        // MySQL only
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE role_permissions COMMENT '角色权限关联表'");
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('role_permissions');
        Schema::dropIfExists('post_roles');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('roles');
    }
};