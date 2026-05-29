<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 创建退货单、招投标、应收账款等表
     */
    public function up(): void
    {
        // 退单表
        Schema::create('backorder', function (Blueprint $table) {
            $table->id();
            $table->string('backorder_sn', 30)->unique()->comment('退单编号');
            $table->unsignedBigInteger('order_id')->comment('订单ID');
            $table->unsignedBigInteger('order_goods_id')->comment('订单商品ID');
            $table->unsignedBigInteger('goods_id')->comment('商品ID');
            $table->decimal('quantity', 10, 2)->comment('退货数量');
            $table->tinyInteger('type')->default(1)->comment('类型 1=仅退款 2=退货退款');
            $table->unsignedBigInteger('backorder_type_id')->nullable()->comment('退货原因ID');
            $table->string('reason', 200)->nullable()->comment('退货原因');
            $table->string('solution', 200)->nullable()->comment('解决方案');
            $table->tinyInteger('status')->default(3)->comment('状态 1=取消 2=拒绝 3=待审核 4=通过');
            $table->string('operator', 50)->nullable()->comment('操作人');
            $table->unsignedBigInteger('operate_time')->nullable()->comment('操作时间');
            $table->unsignedBigInteger('add_time')->comment('创建时间戳');
            $table->timestamps();

            $table->index('order_id');
            $table->index('status');
        });

        // 退货原因类型表
        Schema::create('backorder_type', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->comment('原因名称');
            $table->tinyInteger('home')->default(0)->comment('前台显示');
            $table->integer('sort')->default(0)->comment('排序');
            $table->tinyInteger('status')->default(1)->comment('状态');
            $table->timestamps();
        });

        // 招投标合作申请历史表
        Schema::create('bidding_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('canteen_id')->comment('食堂ID');
            $table->unsignedBigInteger('supp_id')->comment('供应商ID');
            $table->unsignedBigInteger('school_id')->comment('学校ID');
            $table->tinyInteger('type')->default(1)->comment('类型 1=合作申请 2=终止合作');
            $table->string('attachments', 500)->nullable()->comment('附件JSON');
            $table->date('start_date')->nullable()->comment('合作开始日期');
            $table->date('end_date')->nullable()->comment('合作结束日期');
            $table->string('emergency_linkman', 50)->nullable()->comment('紧急联系人');
            $table->string('emergency_mobile', 20)->nullable()->comment('紧急联系电话');
            $table->tinyInteger('audit_status')->default(1)->comment('审核状态 1=待审核 2=拒绝 3=通过');
            $table->string('audit_reason', 200)->nullable()->comment('审核理由');
            $table->unsignedBigInteger('audit_time')->nullable()->comment('审核时间');
            $table->tinyInteger('review_status')->default(0)->comment('审阅状态');
            $table->unsignedBigInteger('review_time')->nullable()->comment('审阅时间');
            $table->unsignedBigInteger('review_user_id')->nullable()->comment('审阅人ID');
            $table->unsignedBigInteger('add_time')->comment('创建时间戳');
            $table->timestamps();

            $table->index(['canteen_id', 'supp_id']);
            $table->index('audit_status');
        });

        // 招投标合作关系表
        Schema::create('bidding_log', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('canteen_id')->comment('食堂ID');
            $table->unsignedBigInteger('supp_id')->comment('供应商ID');
            $table->unsignedBigInteger('school_id')->comment('学校ID');
            $table->date('start_date')->nullable()->comment('合作开始日期');
            $table->date('end_date')->nullable()->comment('合作结束日期');
            $table->tinyInteger('status')->default(1)->comment('状态 0=终止 1=合作中');
            $table->tinyInteger('effective_status')->default(0)->comment('生效状态 0=未生效 1=已生效');
            $table->unsignedBigInteger('add_time')->comment('创建时间戳');
            $table->timestamps();

            $table->index(['canteen_id', 'supp_id']);
            $table->index('status');
        });

        // 对账单表
        Schema::create('receivable_receipt', function (Blueprint $table) {
            $table->id();
            $table->string('voucher_sn', 30)->unique()->comment('对账单编号');
            $table->unsignedBigInteger('canteen_id')->comment('食堂ID');
            $table->unsignedBigInteger('supp_id')->comment('供应商ID');
            $table->decimal('debit_price', 12, 2)->default(0)->comment('借方金额');
            $table->decimal('credit_price', 12, 2)->default(0)->comment('贷方金额');
            $table->decimal('balance_price', 12, 2)->default(0)->comment('余额');
            $table->tinyInteger('school_confirm_status')->default(0)->comment('学校确认状态');
            $table->tinyInteger('invoice_status')->default(0)->comment('开票状态');
            $table->string('invoice_no', 50)->nullable()->comment('发票号');
            $table->decimal('invoice_price', 12, 2)->default(0)->comment('开票金额');
            $table->tinyInteger('bill_status')->default(0)->comment('收款状态');
            $table->decimal('bill_price', 12, 2)->default(0)->comment('收款金额');
            $table->tinyInteger('inspection_report_status')->default(0)->comment('索票索证状态');
            $table->date('start_date')->nullable()->comment('账期开始日期');
            $table->date('end_date')->nullable()->comment('账期结束日期');
            $table->unsignedBigInteger('add_time')->comment('创建时间戳');
            $table->timestamps();

            $table->index(['canteen_id', 'supp_id']);
        });

        // 账单明细表
        Schema::create('receivable_account', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('receipt_id')->default(0)->comment('对账单ID');
            $table->unsignedBigInteger('order_id')->comment('订单ID');
            $table->unsignedBigInteger('canteen_id')->comment('食堂ID');
            $table->unsignedBigInteger('supp_id')->comment('供应商ID');
            $table->tinyInteger('type')->default(1)->comment('类型 1=订单 2=退单');
            $table->decimal('price', 12, 2)->default(0)->comment('金额');
            $table->decimal('tax_rate', 5, 4)->default(0)->comment('税率');
            $table->decimal('tax_price', 12, 2)->default(0)->comment('税额');
            $table->tinyInteger('status')->default(0)->comment('状态 0=未入账 1=已入账');
            $table->unsignedBigInteger('add_time')->comment('创建时间戳');
            $table->timestamps();

            $table->index('receipt_id');
            $table->index('order_id');
        });

        // MySQL only
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE backorder COMMENT '退单表'");
        }
        // MySQL only
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE backorder_type COMMENT '退货原因类型表'");
        }
        // MySQL only
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE bidding_history COMMENT '招投标合作申请历史表'");
        }
        // MySQL only
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE bidding_log COMMENT '招投标合作关系表'");
        }
        // MySQL only
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE receivable_receipt COMMENT '对账单表'");
        }
        // MySQL only
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE receivable_account COMMENT '账单明细表'");
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('receivable_account');
        Schema::dropIfExists('receivable_receipt');
        Schema::dropIfExists('bidding_log');
        Schema::dropIfExists('bidding_history');
        Schema::dropIfExists('backorder_type');
        Schema::dropIfExists('backorder');
    }
};