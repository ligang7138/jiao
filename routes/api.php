<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// API v1 路由组
Route::prefix('v1')->group(function () {

    // ==================== 公开接口 ====================

    // 健康检查
    Route::get('/health', function () {
        return response()->json([
            'status' => 'ok',
            'timestamp' => time(),
        ]);
    });

    // 认证相关（无需登录）
    Route::prefix('auth')->group(function () {
        Route::post('/login', [\App\Http\Controllers\Admin\AuthController::class, 'login'])->name('admin.auth.login');
        Route::post('/refresh', [\App\Http\Controllers\Admin\AuthController::class, 'refresh'])->name('admin.auth.refresh');
        Route::get('/captcha', [\App\Http\Controllers\Admin\AuthController::class, 'captcha']);
        Route::post('/forgot-password', [\App\Http\Controllers\Admin\AuthController::class, 'forgotPassword']);
    });

    // ==================== 需要认证的接口 ====================

    Route::middleware(['auth:jwt', 'rbac'])->group(function () {

        // 认证相关（需要登录）
        Route::prefix('auth')->group(function () {
            Route::post('/logout', [\App\Http\Controllers\Admin\AuthController::class, 'logout'])->name('admin.auth.logout');
            Route::get('/me', [\App\Http\Controllers\Admin\AuthController::class, 'me'])->name('admin.auth.me');
            Route::put('/password', [\App\Http\Controllers\Admin\AuthController::class, 'updatePassword']);
            Route::put('/profile', [\App\Http\Controllers\Admin\AuthController::class, 'updateProfile']);
        });

        Route::get('/admin/menus', [\App\Http\Controllers\Admin\MenuController::class, 'index'])->name('admin.menu.index');
        Route::get('/admin/dictionaries', [\App\Http\Controllers\Admin\DictionaryController::class, 'index'])->name('admin.dictionary.index');

        // 价格网管理
        Route::prefix('jiagewang')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\JiagewangController::class, 'index'])->name('admin.jiagewang.index');
            Route::post('/import', [\App\Http\Controllers\Admin\JiagewangController::class, 'import'])->name('admin.jiagewang.import');
            Route::put('/update', [\App\Http\Controllers\Admin\JiagewangController::class, 'update']);
            Route::get('/history', [\App\Http\Controllers\Admin\JiagewangController::class, 'history']);
            Route::get('/match', [\App\Http\Controllers\Admin\JiagewangController::class, 'match'])->name('admin.jiagewang.match');
            Route::get('/no-match', [\App\Http\Controllers\Admin\JiagewangController::class, 'noMatch']);
            Route::get('/export', [\App\Http\Controllers\Admin\JiagewangController::class, 'export']);
            Route::get('/import-errors', [\App\Http\Controllers\Admin\JiagewangController::class, 'importErrors']);
        });

        // 退货单管理
        Route::prefix('backorder')->group(function () {
            // 退货单管理
            Route::get('/', [\App\Http\Controllers\Admin\BackorderController::class, 'index'])->name('admin.backorder.index');
            Route::get('/{id}', [\App\Http\Controllers\Admin\BackorderController::class, 'show'])->where('id', '[0-9]+')->name('admin.backorder.show');
            Route::post('/{id}/audit', [\App\Http\Controllers\Admin\BackorderController::class, 'audit'])->where('id', '[0-9]+')->name('admin.backorder.audit');
            Route::post('/{id}/audit-reject', [\App\Http\Controllers\Admin\BackorderController::class, 'auditReject'])->where('id', '[0-9]+');
            Route::post('/{id}/cancel', [\App\Http\Controllers\Admin\BackorderController::class, 'cancel'])->where('id', '[0-9]+');
            Route::post('/{id}/solution', [\App\Http\Controllers\Admin\BackorderController::class, 'solution'])->where('id', '[0-9]+');

            // 退货原因类型管理
            Route::prefix('type')->group(function () {
                Route::get('/', [\App\Http\Controllers\Admin\BackorderController::class, 'typeIndex'])->name('admin.backorder.type.index');
                Route::get('/options', [\App\Http\Controllers\Admin\BackorderController::class, 'typeOptions']);
                Route::post('/', [\App\Http\Controllers\Admin\BackorderController::class, 'typeStore']);
                Route::put('/{id}', [\App\Http\Controllers\Admin\BackorderController::class, 'typeUpdate'])->where('id', '[0-9]+');
                Route::put('/{id}/status', [\App\Http\Controllers\Admin\BackorderController::class, 'typeStatus'])->where('id', '[0-9]+');
                Route::put('/{id}/home', [\App\Http\Controllers\Admin\BackorderController::class, 'typeHome'])->where('id', '[0-9]+');
                Route::delete('/{id}', [\App\Http\Controllers\Admin\BackorderController::class, 'typeDestroy'])->where('id', '[0-9]+');
            });
        });

        // 招投标管理
        Route::prefix('bidding')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\BiddingController::class, 'index'])->name('admin.bidding.index');
            Route::get('/{id}', [\App\Http\Controllers\Admin\BiddingController::class, 'show'])->where('id', '[0-9]+')->name('admin.bidding.show');
            Route::post('/{id}/audit', [\App\Http\Controllers\Admin\BiddingController::class, 'audit'])->where('id', '[0-9]+');
            Route::get('/logs', [\App\Http\Controllers\Admin\BiddingController::class, 'logList']);
            Route::post('/logs/{id}/terminate', [\App\Http\Controllers\Admin\BiddingController::class, 'terminate'])->where('id', '[0-9]+');
            Route::get('/discount', [\App\Http\Controllers\Admin\BiddingController::class, 'discount']);
            Route::get('/discount/history', [\App\Http\Controllers\Admin\BiddingController::class, 'discountHistory']);
        });

        // 应收账款管理
        Route::prefix('receivable')->group(function () {
            // 已收货订单列表（用于生成对账单）
            Route::get('/orders', [\App\Http\Controllers\Admin\ReceivableController::class, 'index'])->name('admin.receivable.index');

            // 对账单管理
            Route::prefix('receipt')->group(function () {
                Route::get('/', [\App\Http\Controllers\Admin\ReceivableController::class, 'receiptList'])->name('admin.receivable.receipt');
                Route::post('/', [\App\Http\Controllers\Admin\ReceivableController::class, 'receiptStore']);
                Route::get('/{id}', [\App\Http\Controllers\Admin\ReceivableController::class, 'receiptDetail'])->where('id', '[0-9]+');
                Route::put('/{id}', [\App\Http\Controllers\Admin\ReceivableController::class, 'receiptAdjust'])->where('id', '[0-9]+');

                // 开票
                Route::post('/{id}/invoice', [\App\Http\Controllers\Admin\ReceivableController::class, 'invoice'])->where('id', '[0-9]+');
                Route::post('/{id}/invoice-all', [\App\Http\Controllers\Admin\ReceivableController::class, 'invoiceAll'])->where('id', '[0-9]+');

                // 收款
                Route::post('/{id}/bill', [\App\Http\Controllers\Admin\ReceivableController::class, 'bill'])->where('id', '[0-9]+');
            });

            // 账单明细管理
            Route::prefix('account')->group(function () {
                Route::get('/', [\App\Http\Controllers\Admin\ReceivableController::class, 'accountList'])->name('admin.receivable.account');
                Route::post('/', [\App\Http\Controllers\Admin\ReceivableController::class, 'accountStore']);
                Route::delete('/{id}', [\App\Http\Controllers\Admin\ReceivableController::class, 'accountDestroy'])->where('id', '[0-9]+');
            });
        });

        // 审批管理
        Route::prefix('approve')->group(function () {
            // 评论审阅
            Route::prefix('comment')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\ApproveController::class, 'commentIndex'])->name('admin.approve.comment');
                Route::post('/review', [\App\Http\Controllers\Admin\ApproveController::class, 'commentReview']);
            });

            // 投诉审阅
            Route::prefix('complaint')->group(function () {
                Route::get('/', [\App\Http\Controllers\Admin\ApproveController::class, 'complaintIndex'])->name('admin.approve.complaint');
                Route::post('/review', [\App\Http\Controllers\Admin\ApproveController::class, 'complaintReview']);
            });

            // 合作审阅
            Route::prefix('bidding')->group(function () {
                Route::get('/', [\App\Http\Controllers\Admin\ApproveController::class, 'biddingIndex'])->name('admin.approve.bidding');
                Route::post('/review', [\App\Http\Controllers\Admin\ApproveController::class, 'biddingReview']);
            });
        });

        // ==================== 应急管理 ====================
        Route::prefix('emergency')->group(function () {
            // 应急事件列表
            Route::get('/', [\App\Http\Controllers\Admin\EmergencyController::class, 'index'])->name('admin.emergency.index');
            // 应急事件详情
            Route::get('/{id}', [\App\Http\Controllers\Admin\EmergencyController::class, 'show'])->where('id', '[0-9]+')->name('admin.emergency.show');
            // 处理应急事件
            Route::post('/{id}/process', [\App\Http\Controllers\Admin\EmergencyController::class, 'process'])->where('id', '[0-9]+');

            // 应急类型管理
            Route::prefix('type')->group(function () {
                Route::get('/', [\App\Http\Controllers\Admin\EmergencyController::class, 'typeIndex']);
                Route::post('/', [\App\Http\Controllers\Admin\EmergencyController::class, 'typeStore']);
                Route::put('/{id}', [\App\Http\Controllers\Admin\EmergencyController::class, 'typeUpdate'])->where('id', '[0-9]+');
            });
        });

        // ==================== 投诉管理 ====================
        Route::prefix('complaint')->group(function () {
            // 投诉列表
            Route::get('/', [\App\Http\Controllers\Admin\ComplaintController::class, 'index'])->name('admin.complaint.index');
            // 投诉详情
            Route::get('/{id}', [\App\Http\Controllers\Admin\ComplaintController::class, 'show'])->where('id', '[0-9]+')->name('admin.complaint.show');
            // 处理投诉
            Route::post('/{id}/process', [\App\Http\Controllers\Admin\ComplaintController::class, 'process'])->where('id', '[0-9]+');

            // 投诉类型管理
            Route::prefix('type')->group(function () {
                Route::get('/', [\App\Http\Controllers\Admin\ComplaintController::class, 'typeIndex']);
                Route::post('/', [\App\Http\Controllers\Admin\ComplaintController::class, 'typeStore']);
                Route::put('/{id}', [\App\Http\Controllers\Admin\ComplaintController::class, 'typeUpdate'])->where('id', '[0-9]+');
            });
        });

        // ==================== 分组管理 ====================
        Route::prefix('admin/group')->group(function () {
            Route::get('/options', [\App\Http\Controllers\Admin\GroupController::class, 'options'])->name('admin.group.options');
            Route::get('/', [\App\Http\Controllers\Admin\GroupController::class, 'index'])->name('admin.group.index');
            Route::post('/', [\App\Http\Controllers\Admin\GroupController::class, 'store'])->name('admin.group.store');
            Route::get('/{id}', [\App\Http\Controllers\Admin\GroupController::class, 'show'])->where('id', '[0-9]+')->name('admin.group.show');
            Route::put('/{id}', [\App\Http\Controllers\Admin\GroupController::class, 'update'])->where('id', '[0-9]+')->name('admin.group.update');
            Route::delete('/{id}', [\App\Http\Controllers\Admin\GroupController::class, 'destroy'])->where('id', '[0-9]+')->name('admin.group.destroy');

            Route::prefix('/{groupId}/canteens')->group(function () {
                Route::get('/', [\App\Http\Controllers\Admin\GroupController::class, 'canteens'])->where('groupId', '[0-9]+')->name('admin.group.canteens');
                Route::post('/', [\App\Http\Controllers\Admin\GroupController::class, 'addCanteen'])->where('groupId', '[0-9]+')->name('admin.group.canteen.add');
                Route::delete('/{canteenId}', [\App\Http\Controllers\Admin\GroupController::class, 'removeCanteen'])->where('groupId', '[0-9]+')->where('canteenId', '[0-9]+')->name('admin.group.canteen.remove');
                Route::post('/{canteenId}/set-audit', [\App\Http\Controllers\Admin\GroupController::class, 'setAudit'])->where('groupId', '[0-9]+')->where('canteenId', '[0-9]+')->name('admin.group.canteen.set_audit');
                Route::post('/{canteenId}/remove-audit', [\App\Http\Controllers\Admin\GroupController::class, 'removeAudit'])->where('groupId', '[0-9]+')->where('canteenId', '[0-9]+')->name('admin.group.canteen.remove_audit');
            });
        });

        // ==================== 统计分析 ====================
        Route::prefix('stat')->group(function () {
            // 订单统计
            Route::get('/order', [\App\Http\Controllers\Admin\StatController::class, 'order'])->name('admin.stat.order');
            // 商品统计
            Route::get('/goods', [\App\Http\Controllers\Admin\StatController::class, 'goods']);
            // 退货统计
            Route::get('/backorder', [\App\Http\Controllers\Admin\StatController::class, 'backorder']);
            // 退货率统计
            Route::get('/backorder-rate', [\App\Http\Controllers\Admin\StatController::class, 'backorderRate']);
            // 准时率统计
            Route::get('/ontime-rate', [\App\Http\Controllers\Admin\StatController::class, 'ontimeRate']);
            // 补货统计
            Route::get('/replenish', [\App\Http\Controllers\Admin\StatController::class, 'replenish']);
            // 补货率统计
            Route::get('/replenish-rate', [\App\Http\Controllers\Admin\StatController::class, 'replenishRate']);
            // 投诉统计
            Route::get('/complaint', [\App\Http\Controllers\Admin\StatController::class, 'complaint']);
            // 比价统计
            Route::get('/bidding', [\App\Http\Controllers\Admin\StatController::class, 'bidding']);
        });

        // ==================== 商品管理 ====================
        Route::prefix('admin/goods')->group(function () {
            // 商品单位列表（须在 {id} 之前）
            Route::get('/units', [\App\Http\Controllers\Admin\GoodsController::class, 'units'])->name('admin.goods.units');
            // 商品导出
            Route::get('/export', [\App\Http\Controllers\Admin\GoodsController::class, 'export'])->name('admin.goods.export');
            // 商品导入
            Route::post('/import', [\App\Http\Controllers\Admin\GoodsController::class, 'import'])->name('admin.goods.import');
            // 批量删除
            Route::post('/batch-delete', [\App\Http\Controllers\Admin\GoodsController::class, 'batchDestroy'])->name('admin.goods.batch-delete');
            // 批量上架
            Route::post('/batch-publish', [\App\Http\Controllers\Admin\GoodsController::class, 'batchPublish'])->name('admin.goods.batch-publish');
            // 批量下架
            Route::post('/batch-unpublish', [\App\Http\Controllers\Admin\GoodsController::class, 'batchUnpublish'])->name('admin.goods.batch-unpublish');
            // 商品列表
            Route::get('/', [\App\Http\Controllers\Admin\GoodsController::class, 'index'])->name('admin.goods.index');
            // 创建商品
            Route::post('/', [\App\Http\Controllers\Admin\GoodsController::class, 'store'])->name('admin.goods.store');
            // 商品详情
            Route::get('/{id}', [\App\Http\Controllers\Admin\GoodsController::class, 'show'])->where('id', '[0-9]+')->name('admin.goods.show');
            // 更新商品
            Route::put('/{id}', [\App\Http\Controllers\Admin\GoodsController::class, 'update'])->where('id', '[0-9]+')->name('admin.goods.update');
            // 删除商品
            Route::delete('/{id}', [\App\Http\Controllers\Admin\GoodsController::class, 'destroy'])->where('id', '[0-9]+')->name('admin.goods.destroy');
            // 更改状态
            Route::put('/{id}/status', [\App\Http\Controllers\Admin\GoodsController::class, 'changeStatus'])->where('id', '[0-9]+')->name('admin.goods.status');
            // 商品上架
            Route::post('/{id}/publish', [\App\Http\Controllers\Admin\GoodsController::class, 'publish'])->where('id', '[0-9]+');
            // 商品下架
            Route::post('/{id}/unpublish', [\App\Http\Controllers\Admin\GoodsController::class, 'unpublish'])->where('id', '[0-9]+');
            // 上下架记录
            Route::get('/{id}/status-log', [\App\Http\Controllers\Admin\GoodsController::class, 'statusLog'])->where('id', '[0-9]+')->name('admin.goods.status-log');
            // 历史价格
            Route::get('/{id}/history-price', [\App\Http\Controllers\Admin\GoodsController::class, 'historyPrice'])->where('id', '[0-9]+')->name('admin.goods.history-price');
            // 供应商商品列表
            Route::get('/supplier/{supplierId}', [\App\Http\Controllers\Admin\GoodsController::class, 'getSupplierGoods'])->where('supplierId', '[0-9]+')->name('admin.goods.supplier-goods');
        });

        // ==================== 商品单位管理 ====================
        Route::prefix('admin/goods-units')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\GoodsUnitController::class, 'index'])->name('admin.goods.unit.index');
            Route::post('/', [\App\Http\Controllers\Admin\GoodsUnitController::class, 'store'])->name('admin.goods.unit.add');
            Route::put('/{id}', [\App\Http\Controllers\Admin\GoodsUnitController::class, 'update'])->where('id', '[0-9]+')->name('admin.goods.unit.edit');
            Route::put('/{id}/status', [\App\Http\Controllers\Admin\GoodsUnitController::class, 'setStatus'])->where('id', '[0-9]+')->name('admin.goods.unit.status');
        });

        // ==================== 分类管理 ====================
        Route::prefix('admin/categories')->group(function () {
            // 分类树形结构
            Route::get('/tree', [\App\Http\Controllers\Admin\CategoryController::class, 'tree'])->name('admin.category.tree');
            // 顶级分类
            Route::get('/top', [\App\Http\Controllers\Admin\CategoryController::class, 'getTopCategories'])->name('admin.category.top');
            // 分类列表
            Route::get('/', [\App\Http\Controllers\Admin\CategoryController::class, 'index'])->name('admin.category.index');
            // 创建分类
            Route::post('/', [\App\Http\Controllers\Admin\CategoryController::class, 'store'])->name('admin.category.store');
            // 分类详情
            Route::get('/{id}', [\App\Http\Controllers\Admin\CategoryController::class, 'show'])->where('id', '[0-9]+')->name('admin.category.show');
            // 更新分类
            Route::put('/{id}', [\App\Http\Controllers\Admin\CategoryController::class, 'update'])->where('id', '[0-9]+')->name('admin.category.update');
            // 删除分类
            Route::delete('/{id}', [\App\Http\Controllers\Admin\CategoryController::class, 'destroy'])->where('id', '[0-9]+')->name('admin.category.destroy');
            // 子分类
            Route::get('/{parentId}/children', [\App\Http\Controllers\Admin\CategoryController::class, 'getChildren'])->where('parentId', '[0-9]+')->name('admin.category.children');
            // 设置状态
            Route::put('/{id}/status', [\App\Http\Controllers\Admin\CategoryController::class, 'setStatus'])->where('id', '[0-9]+')->name('admin.category.status');
            // 设置浮动率上限
            Route::put('/{id}/float-rate-cap', [\App\Http\Controllers\Admin\CategoryController::class, 'setFloatRateCap'])->where('id', '[0-9]+');
        });

        // ==================== 订单管理 ====================
        Route::prefix('admin/orders')->group(function () {
            // 订单列表
            Route::get('/', [\App\Http\Controllers\Admin\OrderController::class, 'index'])->name('admin.order.index');
            // 创建订单
            Route::post('/', [\App\Http\Controllers\Admin\OrderController::class, 'store']);
            // 订单详情
            Route::get('/{id}', [\App\Http\Controllers\Admin\OrderController::class, 'show'])->where('id', '[0-9]+')->name('admin.order.show');
            // 更新订单
            Route::put('/{id}', [\App\Http\Controllers\Admin\OrderController::class, 'update'])->where('id', '[0-9]+');
            // 删除订单
            Route::delete('/{id}', [\App\Http\Controllers\Admin\OrderController::class, 'destroy'])->where('id', '[0-9]+');
            // 更改状态
            Route::put('/{id}/status', [\App\Http\Controllers\Admin\OrderController::class, 'changeStatus'])->where('id', '[0-9]+');
            // 提交审核
            Route::post('/{id}/submit', [\App\Http\Controllers\Admin\OrderController::class, 'submit'])->where('id', '[0-9]+');
            // 取消订单
            Route::post('/{id}/cancel', [\App\Http\Controllers\Admin\OrderController::class, 'cancel'])->where('id', '[0-9]+');
            // 订单导出
            Route::get('/export', [\App\Http\Controllers\Admin\OrderController::class, 'export'])->name('admin.order.export');
            // 订单明细导出
            Route::get('/export-detail', [\App\Http\Controllers\Admin\OrderController::class, 'exportDetail']);
            // 订单统计
            Route::get('/statistics', [\App\Http\Controllers\Admin\OrderController::class, 'statistics']);
            // 溯源信息
            Route::get('/{id}/trace-source', [\App\Http\Controllers\Admin\OrderController::class, 'traceSource'])->where('id', '[0-9]+')->name('admin.order.trace-source');
        });

        // ==================== 供应商管理 ====================
        Route::prefix('admin/suppliers')->group(function () {
            // 供应商列表
            Route::get('/', [\App\Http\Controllers\Admin\SupplierController::class, 'index'])->name('admin.supplier.index');
            // 创建供应商
            Route::post('/', [\App\Http\Controllers\Admin\SupplierController::class, 'store'])->name('admin.supplier.store');
            // 供应商详情
            Route::get('/{id}', [\App\Http\Controllers\Admin\SupplierController::class, 'show'])->where('id', '[0-9]+')->name('admin.supplier.show');
            // 更新供应商
            Route::put('/{id}', [\App\Http\Controllers\Admin\SupplierController::class, 'update'])->where('id', '[0-9]+')->name('admin.supplier.update');
            // 删除供应商
            Route::delete('/{id}', [\App\Http\Controllers\Admin\SupplierController::class, 'destroy'])->where('id', '[0-9]+');
            // 更改状态
            Route::put('/{id}/status', [\App\Http\Controllers\Admin\SupplierController::class, 'changeStatus'])->where('id', '[0-9]+')->name('admin.supplier.status');
            // 启用的供应商列表
            Route::get('/active', [\App\Http\Controllers\Admin\SupplierController::class, 'getActiveSuppliers'])->name('admin.supplier.active');
            // 折扣变更记录
            Route::get('/{id}/discount-logs', [\App\Http\Controllers\Admin\SupplierController::class, 'getDiscountLogs'])->where('id', '[0-9]+');
        });

        // ==================== 用户管理 ====================
        Route::prefix('admin/roles')->group(function () {
            Route::get('/options', [\App\Http\Controllers\Admin\RoleController::class, 'options'])->name('admin.role.options');
            Route::get('/', [\App\Http\Controllers\Admin\RoleController::class, 'index'])->name('admin.role.index');
            Route::post('/', [\App\Http\Controllers\Admin\RoleController::class, 'store'])->name('admin.role.store');
            Route::get('/{id}', [\App\Http\Controllers\Admin\RoleController::class, 'show'])->where('id', '[0-9]+')->name('admin.role.show');
            Route::put('/{id}', [\App\Http\Controllers\Admin\RoleController::class, 'update'])->where('id', '[0-9]+')->name('admin.role.update');
            Route::put('/{id}/status', [\App\Http\Controllers\Admin\RoleController::class, 'changeStatus'])->where('id', '[0-9]+')->name('admin.role.status');
            Route::get('/{id}/privilege', [\App\Http\Controllers\Admin\RoleController::class, 'privilege'])->where('id', '[0-9]+');
            Route::put('/{id}/privilege', [\App\Http\Controllers\Admin\RoleController::class, 'updatePrivilege'])->where('id', '[0-9]+')->name('admin.role.privilege');
        });

        Route::prefix('admin/permissions')->group(function () {
            Route::get('/tree', [\App\Http\Controllers\Admin\PermissionController::class, 'tree'])->name('admin.permission.tree');
            Route::get('/modules', [\App\Http\Controllers\Admin\PermissionController::class, 'modules']);
            Route::get('/controls', [\App\Http\Controllers\Admin\PermissionController::class, 'controls']);
            Route::get('/', [\App\Http\Controllers\Admin\PermissionController::class, 'index'])->name('admin.permission.index');
            Route::post('/', [\App\Http\Controllers\Admin\PermissionController::class, 'store'])->name('admin.permission.store');
            Route::get('/{id}', [\App\Http\Controllers\Admin\PermissionController::class, 'show'])->where('id', '[0-9]+')->name('admin.permission.show');
            Route::put('/{id}', [\App\Http\Controllers\Admin\PermissionController::class, 'update'])->where('id', '[0-9]+')->name('admin.permission.update');
            Route::put('/{id}/status', [\App\Http\Controllers\Admin\PermissionController::class, 'changeStatus'])->where('id', '[0-9]+')->name('admin.permission.status');
        });

        Route::prefix('admin/logs')->group(function () {
            Route::get('/export', [\App\Http\Controllers\Admin\SystemLogController::class, 'export'])->name('admin.log.export');
            Route::get('/', [\App\Http\Controllers\Admin\SystemLogController::class, 'index'])->name('admin.log.index');
            Route::get('/{id}', [\App\Http\Controllers\Admin\SystemLogController::class, 'show'])->where('id', '[0-9]+')->name('admin.log.show');
        });

        Route::prefix('admin/users')->group(function () {
            Route::get('/options', [\App\Http\Controllers\Admin\UserController::class, 'options'])->name('admin.user.options');
            // 用户列表
            Route::get('/', [\App\Http\Controllers\Admin\UserController::class, 'index'])->name('admin.user.index');
            // 创建用户
            Route::post('/', [\App\Http\Controllers\Admin\UserController::class, 'store'])->name('admin.user.store');
            // 用户详情
            Route::get('/{id}', [\App\Http\Controllers\Admin\UserController::class, 'show'])->where('id', '[0-9]+')->name('admin.user.show');
            // 更新用户
            Route::put('/{id}', [\App\Http\Controllers\Admin\UserController::class, 'update'])->where('id', '[0-9]+')->name('admin.user.update');
            // 删除用户
            Route::delete('/{id}', [\App\Http\Controllers\Admin\UserController::class, 'destroy'])->where('id', '[0-9]+');
            // 批量删除
            Route::post('/batch-delete', [\App\Http\Controllers\Admin\UserController::class, 'batchDestroy']);
            // 更改状态
            Route::put('/{id}/status', [\App\Http\Controllers\Admin\UserController::class, 'changeStatus'])->where('id', '[0-9]+')->name('admin.user.status');
            // 重置密码
            Route::post('/{id}/reset-password', [\App\Http\Controllers\Admin\UserController::class, 'resetPassword'])->where('id', '[0-9]+');
            // 获取用户权限
            Route::get('/{id}/privilege', [\App\Http\Controllers\Admin\UserController::class, 'getPrivilege'])->where('id', '[0-9]+');
            // 更新用户权限
            Route::put('/{id}/privilege', [\App\Http\Controllers\Admin\UserController::class, 'updatePrivilege'])->where('id', '[0-9]+')->name('admin.user.privilege');
        });

        // ==================== 学校管理 ====================
        Route::prefix('admin/schools')->group(function () {
            Route::get('/options', [\App\Http\Controllers\Admin\SchoolController::class, 'options'])->name('admin.school.options');
            Route::get('/active', [\App\Http\Controllers\Admin\SchoolController::class, 'getActiveSchools'])->name('admin.school.active');
            Route::get('/', [\App\Http\Controllers\Admin\SchoolController::class, 'index'])->name('admin.school.index');
            Route::post('/', [\App\Http\Controllers\Admin\SchoolController::class, 'store'])->name('admin.school.store');
            Route::get('/{id}', [\App\Http\Controllers\Admin\SchoolController::class, 'show'])->where('id', '[0-9]+')->name('admin.school.show');
            Route::put('/{id}', [\App\Http\Controllers\Admin\SchoolController::class, 'update'])->where('id', '[0-9]+')->name('admin.school.update');
            Route::put('/{id}/status', [\App\Http\Controllers\Admin\SchoolController::class, 'changeStatus'])->where('id', '[0-9]+')->name('admin.school.status');

            Route::prefix('/{schoolId}/canteens')->group(function () {
                Route::get('/', [\App\Http\Controllers\Admin\CanteenController::class, 'index'])->where('schoolId', '[0-9]+');
                Route::post('/', [\App\Http\Controllers\Admin\CanteenController::class, 'store'])->where('schoolId', '[0-9]+');
                Route::get('/{id}', [\App\Http\Controllers\Admin\CanteenController::class, 'showById'])->where('schoolId', '[0-9]+')->where('id', '[0-9]+');
                Route::put('/{id}', [\App\Http\Controllers\Admin\CanteenController::class, 'updateById'])->where('schoolId', '[0-9]+')->where('id', '[0-9]+');
            });
        });

        // ==================== 食堂管理 ====================
        Route::prefix('admin/canteens')->group(function () {
            Route::get('/active', [\App\Http\Controllers\Admin\CanteenController::class, 'active'])->name('admin.canteen.active');
            Route::get('/', [\App\Http\Controllers\Admin\CanteenController::class, 'index'])->name('admin.canteen.index');
            Route::post('/', [\App\Http\Controllers\Admin\CanteenController::class, 'store'])->name('admin.canteen.store');
            Route::get('/{id}', [\App\Http\Controllers\Admin\CanteenController::class, 'showById'])->where('id', '[0-9]+')->name('admin.canteen.show');
            Route::put('/{id}', [\App\Http\Controllers\Admin\CanteenController::class, 'updateById'])->where('id', '[0-9]+')->name('admin.canteen.update');
            Route::put('/{id}/status', [\App\Http\Controllers\Admin\CanteenController::class, 'changeStatus'])->where('id', '[0-9]+')->name('admin.canteen.status');
        });

    });

});
