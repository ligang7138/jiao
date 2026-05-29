<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 创建商品相关表
     */
    public function up(): void
    {
        // 分类表
        Schema::create('category', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pid')->default(0)->comment('父分类ID');
            $table->string('name', 50)->comment('分类名称');
            $table->string('logo', 500)->nullable()->comment('分类图片');
            $table->decimal('float_rate_cap', 5, 4)->default(0)->comment('浮动率上限');
            $table->tinyInteger('allow_report_after_send')->default(0)->comment('发货后补传报告权限');
            $table->integer('sort')->default(0)->comment('排序');
            $table->tinyInteger('status')->default(1)->comment('状态 0=停用 1=启用');
            $table->timestamps();

            $table->index('pid');
            $table->index('status');
        });

        // 商品单位表
        Schema::create('goods_unit', function (Blueprint $table) {
            $table->id();
            $table->string('name', 20)->comment('单位名称');
            $table->tinyInteger('status')->default(1)->comment('状态');
            $table->timestamps();
        });

        // 商品主表
        Schema::create('goods', function (Blueprint $table) {
            $table->id();
            $table->string('goods_sn', 20)->unique()->comment('商品编码');
            $table->string('goods_name', 100)->comment('商品名称');
            $table->string('spec', 50)->comment('规格');
            $table->string('unit', 20)->comment('单位');
            $table->unsignedBigInteger('cate_id')->comment('一级分类ID');
            $table->unsignedBigInteger('scate_id')->comment('二级分类ID');
            $table->tinyInteger('level')->default(1)->comment('等级 1=普通 2=精品');
            $table->tinyInteger('attr')->default(1)->comment('属性 1=非标品 2=标品 3=特种品');
            $table->tinyInteger('goods_type')->default(0)->comment('是否教师专用 0=否 1=是');
            $table->tinyInteger('goods_channel')->default(0)->comment('是否议价商品 0=否 1=是');
            $table->decimal('discount_rate', 5, 4)->default(0)->comment('折扣率');
            $table->string('slogo', 500)->nullable()->comment('商品图片JSON');
            $table->text('image_list')->nullable()->comment('图片列表JSON');
            $table->text('detail_image_list')->nullable()->comment('详情图片JSON');
            $table->text('remark')->nullable()->comment('详细描述');
            $table->string('brand', 50)->nullable()->comment('品牌');
            $table->string('place', 100)->nullable()->comment('产地');
            $table->string('expire_date', 50)->nullable()->comment('保质期');
            $table->tinyInteger('status')->default(0)->comment('状态 0=下架 1=上架');
            $table->unsignedBigInteger('schedule_down_time')->default(0)->comment('预下架时间');
            $table->unsignedBigInteger('add_time')->comment('创建时间戳');
            $table->unsignedBigInteger('update_time')->nullable()->comment('更新时间戳');
            $table->timestamps();

            $table->index(['cate_id', 'scate_id']);
            $table->index('status');
            $table->index('goods_sn');
        });

        // 商品指导价表
        Schema::create('goods_jiagewang', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('goods_id')->comment('商品ID');
            $table->string('goods_sn', 20)->comment('商品编码');
            $table->string('name', 100)->nullable()->comment('商品名称');
            $table->string('cate_name', 50)->nullable()->comment('一级分类名');
            $table->string('scate_name', 50)->nullable()->comment('二级分类名');
            $table->decimal('price', 10, 2)->default(0)->comment('指导价');
            $table->decimal('white_price', 10, 2)->default(0)->comment('白名单价格');
            $table->string('update_date', 10)->nullable()->comment('更新日期');
            $table->string('update_user', 50)->nullable()->comment('更新人');
            $table->unsignedBigInteger('update_time')->nullable()->comment('更新时间戳');
            $table->timestamps();

            $table->unique('goods_id');
            $table->index('goods_sn');
        });

        // 商品附件表（检测报告等）
        Schema::create('goods_attachment', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('goods_id')->comment('商品ID');
            $table->string('logo', 500)->comment('附件路径');
            $table->string('name', 100)->nullable()->comment('附件名称');
            $table->tinyInteger('type')->default(1)->comment('类型 1=检测报告 2=营业执照');
            $table->date('start_date')->nullable()->comment('开始日期');
            $table->date('end_date')->nullable()->comment('结束日期');
            $table->tinyInteger('status')->default(1)->comment('状态');
            $table->timestamps();

            $table->index('goods_id');
        });

        // 商品上下架日志表
        Schema::create('goods_status_log', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('goods_id')->comment('商品ID');
            $table->tinyInteger('operate_type')->comment('操作类型 1=上架 2=下架');
            $table->tinyInteger('old_status')->comment('原状态');
            $table->tinyInteger('new_status')->comment('新状态');
            $table->string('reason', 200)->nullable()->comment('原因');
            $table->string('operator', 50)->nullable()->comment('操作人');
            $table->unsignedBigInteger('operate_time')->comment('操作时间');
            $table->timestamps();

            $table->index('goods_id');
        });

        // MySQL only
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE category COMMENT '分类表'");
        }
        // MySQL only
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE goods_unit COMMENT '商品单位表'");
        }
        // MySQL only
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE goods COMMENT '商品表'");
        }
        // MySQL only
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE goods_jiagewang COMMENT '商品指导价表'");
        }
        // MySQL only
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE goods_attachment COMMENT '商品附件表'");
        }
        // MySQL only
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE goods_status_log COMMENT '商品上下架日志表'");
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('goods_status_log');
        Schema::dropIfExists('goods_attachment');
        Schema::dropIfExists('goods_jiagewang');
        Schema::dropIfExists('goods');
        Schema::dropIfExists('goods_unit');
        Schema::dropIfExists('category');
    }
};