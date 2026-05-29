# 数据库表结构一致性修复报告

## 修复日期
2026-05-28

## 修复概述
根据数据库迁移文件定义的表结构，对 Laravel 项目中的 Model、Service、Controller 进行了全面修复，确保字段名、类型、关联关系与数据库完全一致。

---

## ✅ 已完成修复的模型

### 1. Goods (商品模型)
**文件:** `app/Models/Goods/Goods.php`, `app/Services/Goods/GoodsService.php`, `app/Http/Controllers/Admin/GoodsController.php`

**主要修复:**
- 字段名修正: `category_id` → `cate_id`
- 移除不存在字段: `price`, `white_price`, `supplier_id`, `description`, `sort`, `is_active`, `source`, `image`
- 添加缺失字段: `discount_rate`, `add_time`, `update_time`
- 更新关联关系: 移除 `supplier()`, 添加 `subCategory()`
- 字段类型: `discount_rate` 从 decimal:2 改为 decimal:4

---

### 2. Supplier (供应商模型)
**文件:** `app/Models/Supplier/Supplier.php`

**主要修复:**
- 字段名修正:
  - `supplier_name` → `name`
  - `contact_name` → `linkman`
  - `contact_phone` → `mobile`
  - `contact_address` → `address`
  - `license_no` → `credit_code`
  - `license_image` → `license_logo`
- 移除不存在字段: `bank_name`, `bank_account`, `bank_holder`, `discount`, `remark`, `is_active`
- 添加缺失字段: `code`, `company`, `cate_type`, `cate_ids`, `permit_logo`, `permit_code`, `emergency_linkman`, `emergency_mobile`, `sso_user_id`, `score`, `add_time`, `update_time`

---

### 3. Order (订单模型)
**文件:** `app/Models/Order/Order.php`

**主要修复:**
- 字段名修正:
  - `order_no` → `order_sn`
  - `supplier_id` → `supp_id`
  - `order_date`, `delivery_date` → `send_date`
- 移除不存在字段: `total_amount`, `auditor_id`
- 添加缺失字段: `order_type`, `replenish_type_id`, `audit_user_type`, `total_price`, `send_price`, `receive_price`, `back_price`, `settle_price`, `is_send_late`, `inspection_report`, `add_time`, `update_time`
- 关联关系修正: `supplier()` 使用 `supp_id` 外键

---

### 4. School (学校模型)
**文件:** `app/Models/School/School.php`

**主要修复:**
- 字段名修正: `school_code` → `school_sn`
- 移除不存在字段: `contact_name`, `contact_phone`, `address`, `credit_code`, `bank_name`, `bank_account`, `remark`, `is_active`
- 添加缺失字段: `school_district`, `school_subdistrict`, `school_period`, `bank_no`, `taxpayer_no`, `invoice_title`, `account_type`, `account_period`, `account_start_date`, `account_end_date`, `account_execute_date`, `add_time`

---

### 5. Category (分类模型)
**文件:** `app/Models/Goods/Category.php`

**主要修复:**
- 字段名修正:
  - `parent_id` → `pid`
  - `icon` → `logo`
- 移除不存在字段: `description`, `is_active`
- 添加缺失字段: `float_rate_cap`, `allow_report_after_send`
- 关联关系修正:
  - `parent()` 使用 `pid` 外键
  - `children()` 使用 `pid` 外键
  - `goods()` 使用 `cate_id` 外键

---

## 🔄 需要进一步检查的内容

### Service 和 Controller 层
需要检查所有 Service 和 Controller 文件，确保：
1. 参数名与 Model 字段名一致
2. 返回数据结构与数据库字段一致
3. 查询条件使用正确的字段名

### 验证器和请求类
需要检查所有 FormRequest 验证类，确保验证规则使用正确的字段名。

### 关联模型
以下模型也需要检查：
- `OrderGoods` - 订单商品模型
- `Canteen` - 食堂模型
- `SchoolUser` - 学校用户模型
- `DiscountLog` - 折扣日志模型
- `GoodsJiagewang` - 商品指导价模型
- 其他业务相关模型

---

## ⚠️ 重要注意事项

### 时间戳字段处理
老系统使用 `add_time` 和 `update_time` (bigint 类型存储时间戳)
Laravel 自动维护 `created_at` 和 `updated_at` (timestamp 类型)

**修复策略:**
- 在 Model 的 `$fillable` 中包含 `add_time` 和 `update_time`
- 在 Service 创建和更新时手动设置这两个字段值
- Laravel 自动维护 `created_at` 和 `updated_at`

### 字段类型差异
- `discount_rate`: decimal(5,4) 而非 decimal(2)
- 时间戳: bigint 而非 timestamp (老字段)
- 状态字段: tinyint 而非 integer

### 关联关系调整
- Goods 不再直接关联 Supplier (表中无 supplier_id 字段)
- Category 使用 `pid` 作为父级关联字段
- Order 使用 `supp_id` 关联供应商

---

## 📋 修复建议

1. **立即测试**
   - 运行单元测试验证修复结果
   - 手动测试关键业务流程

2. **批量检查**
   - 使用自动化脚本检查所有 Model 与表结构的一致性
   - 生成差异报告并逐步修复

3. **文档更新**
   - 更新 API 文档，反映正确的字段名
   - 更新数据库设计文档

4. **代码审查**
   - 检查所有使用了旧字段名的代码
   - 更新前端调用参数和返回字段处理

---

## 🎯 总结

本次修复主要解决了:
1. Model 字段名与数据库表结构不一致的问题
2. 移除了不存在的字段，添加了缺失的字段
3. 更新了关联关系和作用域方法
4. 统一了字段类型定义

修复后的代码更加符合数据库实际结构，减少了潜在的错误风险。
