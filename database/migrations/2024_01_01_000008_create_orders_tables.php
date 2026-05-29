<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 创建订单相关表
     */
    public function up(): void
    {
        // 订单主表
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_sn', 30)->unique()->comment('订单编号');
            $table->unsignedBigInteger('canteen_id')->comment('食堂ID');
            $table->unsignedBigInteger('supp_id')->comment('供应商ID');
            $table->unsignedBigInteger('school_id')->comment('学校ID');
            $table->tinyInteger('status')->default(20)->comment('状态 10=取消 20=已下单 30=已配货 40=已发货 50=已收货');
            $table->tinyInteger('order_type')->default(1)->comment('订单类型 1=正常 2=补单');
            $table->unsignedBigInteger('replenish_type_id')->nullable()->comment('补货原因ID');
            $table->tinyInteger('audit_status')->default(0)->comment('审核状态 0=未审核 1=已审核');
            $table->unsignedBigInteger('audit_time')->nullable()->comment('审核时间');
            $table->tinyInteger('audit_user_type')->nullable()->comment('审核人类型 1=人工 2=系统');
            $table->date('send_date')->comment('送货日期');
            $table->decimal('total_price', 12, 2)->default(0)->comment('下单金额');
            $table->decimal('send_price', 12, 2)->default(0)->comment('发货金额');
            $table->decimal('receive_price', 12, 2)->default(0)->comment('收货金额');
            $table->decimal('back_price', 12, 2)->default(0)->comment('退货金额');
            $table->decimal('settle_price', 12, 2)->default(0)->comment('结算金额');
            $table->tinyInteger('is_send_late')->default(0)->comment('是否迟到');
            $table->text('inspection_report')->nullable()->comment('检测报告JSON');
            $table->string('remark', 500)->nullable()->comment('备注');
            $table->unsignedBigInteger('add_time')->comment('创建时间戳');
            $table->unsignedBigInteger('update_time')->nullable()->comment('更新时间戳');
            $table->timestamps();

            $table->index(['canteen_id', 'send_date']);
            $table->index(['supp_id', 'status']);
            $table->index('order_sn');
            $table->index('send_date');
            $table->index('status');
        });

        // 订单商品表
        Schema::create('orders_goods', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id')->comment('订单ID');
            $table->unsignedBigInteger('goods_id')->comment('商品ID');
            $table->string('goods_sn', 20)->comment('商品编码');
            $table->string('goods_name', 100)->comment('商品名称');
            $table->string('spec', 50)->comment('规格');
            $table->string('unit', 20)->comment('单位');
            $table->decimal('sale_price', 10, 2)->comment('售价');
            $table->decimal('limit_price', 10, 2)->default(0)->comment('限高价');
            $table->decimal('needqty', 10, 2)->comment('需求数量');
            $table->decimal('sendqty', 10, 2)->default(0)->comment('发货数量');
            $table->decimal('receiveqty', 10, 2)->default(0)->comment('收货数量');
            $table->decimal('backqty', 10, 2)->default(0)->comment('退货数量');
            $table->decimal('settleqty', 10, 2)->default(0)->comment('结算数量');
            $table->text('inspection_report')->nullable()->comment('检测报告JSON');
            $table->timestamps();

            $table->index('order_id');
            $table->index('goods_id');
        });

        // 补货原因表
        Schema::create('replenish_type', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->comment('原因名称');
            $table->tinyInteger('home')->default(0)->comment('前台显示');
            $table->integer('sort')->default(0)->comment('排序');
            $table->tinyInteger('status')->default(1)->comment('状态');
            $table->timestamps();
        });

        // 订单商品修复日志表
        Schema::create('orders_goods_fix_log', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('orders_goods_id')->comment('订单商品ID');
            $table->decimal('old_receiveqty', 10, 2)->comment('原收货数量');
            $table->decimal('new_receiveqty', 10, 2)->comment('新收货数量');
            $table->string('supporting_documents_files', 500)->nullable()->comment('业务数据变更单');
            $table->string('operator', 50)->nullable()->comment('操作人');
            $table->unsignedBigInteger('operate_time')->comment('操作时间');
            $table->timestamps();

            $table->index('orders_goods_id');
        });

        // MySQL only
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE orders COMMENT '订单表'");
        }
        // MySQL only
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE orders_goods COMMENT '订单商品表'");
        }
        // MySQL only
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE replenish_type COMMENT '补货原因表'");
        }
        // MySQL only
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE orders_goods_fix_log COMMENT '订单商品修复日志表'");
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('orders_goods_fix_log');
        Schema::dropIfExists('replenish_type');
        Schema::dropIfExists('orders_goods');
        Schema::dropIfExists('orders');
    }
};