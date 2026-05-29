<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 创建应急、投诉、消息等表
     */
    public function up(): void
    {
        // 应急事件表
        Schema::create('emergency', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('canteen_id')->comment('食堂ID');
            $table->unsignedBigInteger('type_id')->nullable()->comment('应急类型ID');
            $table->string('type_name', 50)->nullable()->comment('应急类型名称');
            $table->string('linkman', 50)->nullable()->comment('联系人');
            $table->string('mobile', 20)->nullable()->comment('联系电话');
            $table->text('content')->nullable()->comment('应急内容');
            $table->string('logo', 500)->nullable()->comment('图片附件JSON');
            $table->tinyInteger('process_status')->default(0)->comment('处理状态 0=未处理 1=已处理');
            $table->text('process_remark')->nullable()->comment('处理方案');
            $table->string('process_user', 50)->nullable()->comment('处理人');
            $table->unsignedBigInteger('process_time')->nullable()->comment('处理时间');
            $table->unsignedBigInteger('add_time')->comment('提交时间戳');
            $table->timestamps();

            $table->index('canteen_id');
            $table->index('process_status');
        });

        // 应急类型表
        Schema::create('emergency_type', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->comment('类型名称');
            $table->tinyInteger('home')->default(0)->comment('前台显示');
            $table->integer('sort')->default(0)->comment('排序');
            $table->tinyInteger('status')->default(1)->comment('状态');
            $table->timestamps();
        });

        // 投诉表
        Schema::create('complaint', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id')->comment('订单ID');
            $table->unsignedBigInteger('type_id')->nullable()->comment('投诉类型ID');
            $table->text('content')->nullable()->comment('投诉内容');
            $table->string('logo', 500)->nullable()->comment('图片附件JSON');
            $table->tinyInteger('process_status')->default(0)->comment('处理状态');
            $table->text('process_remark')->nullable()->comment('处理方案');
            $table->string('process_user', 50)->nullable()->comment('处理人');
            $table->unsignedBigInteger('process_time')->nullable()->comment('处理时间');
            $table->tinyInteger('supp_process_status')->default(0)->comment('供应商处理状态');
            $table->tinyInteger('review_status')->default(0)->comment('审阅状态');
            $table->unsignedBigInteger('review_time')->nullable()->comment('审阅时间');
            $table->unsignedBigInteger('review_user_id')->nullable()->comment('审阅人ID');
            $table->unsignedBigInteger('add_time')->comment('提交时间戳');
            $table->timestamps();

            $table->index('order_id');
            $table->index('process_status');
        });

        // 投诉类型表
        Schema::create('complaint_type', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->comment('类型名称');
            $table->tinyInteger('home')->default(0)->comment('前台显示');
            $table->integer('sort')->default(0)->comment('排序');
            $table->tinyInteger('status')->default(1)->comment('状态');
            $table->timestamps();
        });

        // 评论表
        Schema::create('comment', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id')->comment('订单ID');
            $table->unsignedBigInteger('canteen_id')->comment('食堂ID');
            $table->unsignedBigInteger('supp_id')->comment('供应商ID');
            $table->text('content')->nullable()->comment('评价内容');
            $table->string('logo', 500)->nullable()->comment('图片JSON');
            $table->tinyInteger('service_score')->default(5)->comment('售后服务评分');
            $table->tinyInteger('delivery_score')->default(5)->comment('配送能力评分');
            $table->tinyInteger('quality_score')->default(5)->comment('质量因素评分');
            $table->tinyInteger('price_score')->default(5)->comment('价格因素评分');
            $table->tinyInteger('review_status')->default(0)->comment('审阅状态');
            $table->unsignedBigInteger('review_time')->nullable()->comment('审阅时间');
            $table->unsignedBigInteger('review_user_id')->nullable()->comment('审阅人ID');
            $table->unsignedBigInteger('add_time')->comment('提交时间戳');
            $table->timestamps();

            $table->index('order_id');
            $table->index('review_status');
        });

        // 站内信表
        Schema::create('message', function (Blueprint $table) {
            $table->id();
            $table->string('title', 100)->comment('标题');
            $table->text('content')->comment('内容');
            $table->tinyInteger('sender_type')->default(3)->comment('发送方类型');
            $table->unsignedBigInteger('sender_id')->nullable()->comment('发送方ID');
            $table->tinyInteger('category')->default(1)->comment('消息分类');
            $table->unsignedBigInteger('add_time')->comment('创建时间戳');
            $table->timestamps();
        });

        // 用户消息关联表
        Schema::create('user_message', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('message_id')->comment('消息ID');
            $table->tinyInteger('receiver_type')->default(1)->comment('接收方类型');
            $table->unsignedBigInteger('receiver_id')->comment('接收方ID');
            $table->tinyInteger('is_read')->default(0)->comment('是否已读');
            $table->unsignedBigInteger('read_time')->nullable()->comment('阅读时间');
            $table->timestamps();

            $table->index(['receiver_type', 'receiver_id']);
            $table->index('message_id');
        });

        // SSO统一用户表
        Schema::create('sso_user', function (Blueprint $table) {
            $table->id();
            $table->string('username', 50)->unique()->comment('用户名');
            $table->string('password', 100)->comment('密码');
            $table->string('salt', 10)->default('')->comment('盐值');
            $table->string('name', 50)->nullable()->comment('姓名');
            $table->string('token', 100)->nullable()->comment('登录Token');
            $table->tinyInteger('identity_type')->default(1)->comment('身份类型 1=管理员 2=供应商 3=食堂');
            $table->unsignedBigInteger('logic_id')->nullable()->comment('关联业务ID');
            $table->tinyInteger('status')->default(1)->comment('状态');
            $table->unsignedBigInteger('last_login_time')->nullable()->comment('最后登录时间');
            $table->string('last_login_ip', 45)->nullable()->comment('最后登录IP');
            $table->timestamps();

            $table->index('identity_type');
            $table->index('logic_id');
        });

        // MySQL only
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE emergency COMMENT '应急事件表'");
        }
        // MySQL only
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE emergency_type COMMENT '应急类型表'");
        }
        // MySQL only
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE complaint COMMENT '投诉表'");
        }
        // MySQL only
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE complaint_type COMMENT '投诉类型表'");
        }
        // MySQL only
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE comment COMMENT '评论表'");
        }
        // MySQL only
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE message COMMENT '站内信表'");
        }
        // MySQL only
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE user_message COMMENT '用户消息关联表'");
        }
        // MySQL only
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE sso_user COMMENT 'SSO统一用户表'");
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('sso_user');
        Schema::dropIfExists('user_message');
        Schema::dropIfExists('message');
        Schema::dropIfExists('comment');
        Schema::dropIfExists('complaint_type');
        Schema::dropIfExists('complaint');
        Schema::dropIfExists('emergency_type');
        Schema::dropIfExists('emergency');
    }
};