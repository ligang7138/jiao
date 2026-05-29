<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 旧库 school 表缺少学段、开票、账期等字段，补齐以兼容学校管理业务。
     */
    public function up(): void
    {
        if (! Schema::hasTable('school')) {
            return;
        }

        Schema::table('school', function (Blueprint $table) {
            if (! Schema::hasColumn('school', 'school_subdistrict')) {
                $table->string('school_subdistrict', 50)->nullable()->after('school_district')->comment('子学区');
            }

            if (! Schema::hasColumn('school', 'school_period')) {
                $after = Schema::hasColumn('school', 'school_subdistrict') ? 'school_subdistrict' : 'school_district';
                $table->string('school_period', 20)->nullable()->after($after)->comment('学段');
            }

            if (! Schema::hasColumn('school', 'bank_no')) {
                $table->string('bank_no', 30)->nullable()->comment('银行账号');
            }

            if (! Schema::hasColumn('school', 'taxpayer_no')) {
                $table->string('taxpayer_no', 30)->nullable()->comment('纳税人识别号');
            }

            if (! Schema::hasColumn('school', 'invoice_title')) {
                $table->string('invoice_title', 100)->nullable()->comment('发票抬头');
            }

            if (! Schema::hasColumn('school', 'invoice_phone')) {
                $table->string('invoice_phone', 30)->nullable()->comment('发票电话');
            }

            if (! Schema::hasColumn('school', 'invoice_address')) {
                $table->string('invoice_address', 200)->nullable()->comment('发票地址');
            }

            if (! Schema::hasColumn('school', 'account_create_type')) {
                $table->tinyInteger('account_create_type')->default(1)->comment('账期创建类型');
            }

            if (! Schema::hasColumn('school', 'account_type')) {
                $table->tinyInteger('account_type')->default(1)->comment('账期类型');
            }

            if (! Schema::hasColumn('school', 'account_period')) {
                $table->integer('account_period')->default(30)->comment('账期周期');
            }

            if (! Schema::hasColumn('school', 'account_time')) {
                $table->integer('account_time')->nullable()->comment('账期时间');
            }

            if (! Schema::hasColumn('school', 'account_start_date')) {
                $table->date('account_start_date')->nullable()->comment('账期开始日期');
            }

            if (! Schema::hasColumn('school', 'account_end_date')) {
                $table->date('account_end_date')->nullable()->comment('账期结束日期');
            }

            if (! Schema::hasColumn('school', 'account_execute_date')) {
                $table->date('account_execute_date')->nullable()->comment('出账日期');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('school')) {
            return;
        }

        $columns = [
            'school_subdistrict',
            'school_period',
            'bank_no',
            'taxpayer_no',
            'invoice_title',
            'invoice_phone',
            'invoice_address',
            'account_create_type',
            'account_type',
            'account_period',
            'account_time',
            'account_start_date',
            'account_end_date',
            'account_execute_date',
        ];

        $existing = array_filter($columns, fn (string $column) => Schema::hasColumn('school', $column));

        if ($existing === []) {
            return;
        }

        Schema::table('school', function (Blueprint $table) use ($existing) {
            $table->dropColumn($existing);
        });
    }
};
