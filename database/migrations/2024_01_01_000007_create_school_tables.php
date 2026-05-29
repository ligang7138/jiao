<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 创建学校相关表
     */
    public function up(): void
    {
        // 学校表
        Schema::create('school', function (Blueprint $table) {
            $table->id();
            $table->string('school_sn', 20)->unique()->comment('学校编码');
            $table->string('school_name', 100)->comment('学校名称');
            $table->string('school_district', 50)->nullable()->comment('学区名称');
            $table->string('school_subdistrict', 50)->nullable()->comment('子学区');
            $table->string('school_period', 20)->nullable()->comment('学段');
            $table->string('bank_no', 30)->nullable()->comment('银行账号');
            $table->string('taxpayer_no', 30)->nullable()->comment('纳税人识别号');
            $table->string('invoice_title', 100)->nullable()->comment('发票抬头');
            $table->tinyInteger('account_type')->default(1)->comment('账期类型');
            $table->integer('account_period')->default(30)->comment('账期周期');
            $table->date('account_start_date')->nullable()->comment('账期开始日期');
            $table->date('account_end_date')->nullable()->comment('账期结束日期');
            $table->date('account_execute_date')->nullable()->comment('出账日期');
            $table->tinyInteger('status')->default(1)->comment('状态 0=停用 1=启用');
            $table->unsignedBigInteger('add_time')->comment('创建时间戳');
            $table->timestamps();

            $table->index('status');
        });

        // 学校用户表
        Schema::create('school_user', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id')->comment('学校ID');
            $table->string('username', 50)->comment('登录账号');
            $table->string('password', 100)->comment('密码');
            $table->string('salt', 10)->default('')->comment('盐值');
            $table->string('name', 50)->nullable()->comment('姓名');
            $table->tinyInteger('type')->default(1)->comment('账号类型 1=审核账号 2=下单账号');
            $table->unsignedBigInteger('sso_user_id')->nullable()->comment('SSO用户ID');
            $table->tinyInteger('status')->default(1)->comment('状态');
            $table->unsignedBigInteger('add_time')->comment('创建时间戳');
            $table->timestamps();

            $table->index('school_id');
        });

        // 食堂表
        Schema::create('school_canteen', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id')->comment('学校ID');
            $table->string('name', 100)->comment('食堂名称');
            $table->tinyInteger('canteen_type')->default(1)->comment('食堂类型 1=教师食堂 2=学生食堂');
            $table->string('linkman', 50)->nullable()->comment('联系人');
            $table->string('mobile', 20)->nullable()->comment('联系电话');
            $table->string('address', 200)->nullable()->comment('地址');
            $table->string('receive_start_time', 10)->default('06:00')->comment('收货开始时间');
            $table->string('receive_end_time', 10)->default('08:00')->comment('收货结束时间');
            $table->decimal('monthly_purchase_amount', 10, 2)->default(0)->comment('月计划采购额');
            $table->decimal('current_month_purchase', 10, 2)->default(0)->comment('当月采购额');
            $table->unsignedBigInteger('group_id')->nullable()->comment('分组ID');
            $table->tinyInteger('is_audit')->default(0)->comment('是否主账号');
            $table->string('bank_no', 30)->nullable()->comment('银行账号');
            $table->string('taxpayer_no', 30)->nullable()->comment('纳税人识别号');
            $table->string('invoice_title', 100)->nullable()->comment('发票抬头');
            $table->tinyInteger('account_type')->default(1)->comment('账期类型');
            $table->integer('account_period')->default(30)->comment('账期周期');
            $table->date('account_start_date')->nullable()->comment('账期开始日期');
            $table->date('account_end_date')->nullable()->comment('账期结束日期');
            $table->date('account_execute_date')->nullable()->comment('出账日期');
            $table->unsignedBigInteger('sso_user_id')->nullable()->comment('SSO用户ID');
            $table->tinyInteger('status')->default(1)->comment('状态');
            $table->unsignedBigInteger('add_time')->comment('创建时间戳');
            $table->timestamps();

            $table->index('school_id');
            $table->index('group_id');
            $table->index('status');
        });

        // 分组表
        Schema::create('group', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->comment('分组名称');
            $table->unsignedBigInteger('pid')->default(0)->comment('父分组ID');
            $table->string('code', 20)->nullable()->comment('分组编码');
            $table->tinyInteger('status')->default(1)->comment('状态');
            $table->string('add_user', 50)->nullable()->comment('创建人');
            $table->unsignedBigInteger('add_time')->comment('创建时间戳');
            $table->string('update_user', 50)->nullable()->comment('修改人');
            $table->unsignedBigInteger('update_time')->nullable()->comment('修改时间戳');
            $table->timestamps();

            $table->index('pid');
        });

        // MySQL only
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE school COMMENT '学校表'");
        }
        // MySQL only
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE school_user COMMENT '学校用户表'");
        }
        // MySQL only
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE school_canteen COMMENT '食堂表'");
        }
        // MySQL only
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE `group` COMMENT '分组表'");
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('group');
        Schema::dropIfExists('school_canteen');
        Schema::dropIfExists('school_user');
        Schema::dropIfExists('school');
    }
};