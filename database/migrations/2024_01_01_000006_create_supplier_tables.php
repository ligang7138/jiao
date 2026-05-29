<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 创建供应商相关表
     */
    public function up(): void
    {
        // 供应商表
        Schema::create('supplier', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique()->comment('供应商编码');
            $table->string('name', 100)->comment('供应商名称');
            $table->string('company', 100)->nullable()->comment('公司名称');
            $table->string('address', 200)->nullable()->comment('公司地址');
            $table->tinyInteger('cate_type')->default(1)->comment('品类类型 1=全品类 2=单品类');
            $table->text('cate_ids')->nullable()->comment('商品分类ID列表');
            $table->string('license_logo', 500)->nullable()->comment('营业执照图片');
            $table->string('credit_code', 50)->nullable()->comment('统一社会信用代码');
            $table->string('permit_logo', 500)->nullable()->comment('食品经营许可证图片');
            $table->string('permit_code', 50)->nullable()->comment('食品经营许可证号');
            $table->string('linkman', 50)->nullable()->comment('联系人');
            $table->string('mobile', 20)->nullable()->comment('联系电话');
            $table->string('emergency_linkman', 50)->nullable()->comment('紧急联系人');
            $table->string('emergency_mobile', 20)->nullable()->comment('紧急联系电话');
            $table->unsignedBigInteger('sso_user_id')->nullable()->comment('SSO用户ID');
            $table->decimal('score', 5, 2)->default(0)->comment('评分');
            $table->tinyInteger('status')->default(1)->comment('状态 0=停用 1=启用');
            $table->unsignedBigInteger('add_time')->comment('创建时间戳');
            $table->unsignedBigInteger('update_time')->nullable()->comment('更新时间戳');
            $table->timestamps();

            $table->index('status');
        });

        // 供应商报价日志表
        Schema::create('discount_log', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('goods_id')->comment('商品ID');
            $table->unsignedBigInteger('supp_id')->comment('供应商ID');
            $table->decimal('quotation_price', 10, 2)->comment('报价');
            $table->decimal('limit_price', 10, 2)->comment('限高价');
            $table->decimal('float_rate', 5, 4)->default(0)->comment('浮动率');
            $table->unsignedBigInteger('add_time')->comment('创建时间');
            $table->timestamps();

            $table->index(['goods_id', 'supp_id']);
        });

        // API接口配置表
        Schema::create('api_urls', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('supplier_id')->comment('供应商ID');
            $table->string('api_name', 50)->comment('API类型名称');
            $table->string('url', 200)->nullable()->comment('接口地址');
            $table->text('headers')->nullable()->comment('请求头JSON');
            $table->text('mapper')->nullable()->comment('字段映射JSON');
            $table->text('formater')->nullable()->comment('格式化函数');
            $table->tinyInteger('status')->default(0)->comment('状态');
            $table->timestamps();

            $table->index('supplier_id');
        });

        // API认证表
        Schema::create('api_auth', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('logic_id')->comment('关联ID');
            $table->string('api_key', 50)->comment('API密钥');
            $table->string('private_key', 100)->comment('私钥');
            $table->timestamps();

            $table->index('logic_id');
        });

        // MySQL only
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE supplier COMMENT '供应商表'");
        }
        // MySQL only
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE discount_log COMMENT '供应商报价日志表'");
        }
        // MySQL only
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE api_urls COMMENT 'API接口配置表'");
        }
        // MySQL only
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE api_auth COMMENT 'API认证表'");
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('api_auth');
        Schema::dropIfExists('api_urls');
        Schema::dropIfExists('discount_log');
        Schema::dropIfExists('supplier');
    }
};