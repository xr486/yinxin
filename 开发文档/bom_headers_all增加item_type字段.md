# BOMSetup 数据库改动：bom_headers_all 增加 item_type 字段（已回退）

> **状态：已回退（2026-08-11）**
> 用户确认不需要物料分类下拉框，且 BOM 应关联真实物料（sf_item_no 主数据），因此回退 item_type 字段。
> 最终方案：新建顶层 BOM 改为输入**物料代码**（必须存在于 sf_item_no）+ 版本，BOM 头记录真实物料 id，无需额外字段。

## 原改动说明（已撤销）

| 项目 | 内容 |
|------|------|
| 数据库 | `yixin`（WAMP / MySQL 5.6.17） |
| 改动对象 | 表 `bom_headers_all` 新增字段 `item_type`（已删除） |
| 字段定义 | `item_type varchar(2) NOT NULL DEFAULT 'B'`，取值 M/B/F/P |

## 执行与回退 SQL（均已执行）

```sql
-- 添加（已执行，后已回退）
ALTER TABLE bom_headers_all
  ADD COLUMN item_type varchar(2) NOT NULL DEFAULT 'B' COMMENT '物料类型 M/B/F/P'
  AFTER assembly_item_no;

-- 回退（已执行，SHOW COLUMNS 验证字段已移除）
ALTER TABLE bom_headers_all DROP COLUMN item_type;
```

## 最终逻辑（当前生效）

- `create_top` 表单：**物料代码**（手动输入，必须存在于 sf_item_no）+ **版本**。
- `create_top_save`：
  - 校验物料存在于 `sf_item_no`（否则提示"物料不存在，请先创建物料主数据"）。
  - 校验该物料是否已有 BOM（`latestHeader()`）。
  - 补齐 NOT NULL 字段：`approve_by`（当前用户）、`approve_date`（当前时间）、`approve_remark`（"待签核"）。
  - 不再插入 item_type。
- `edit_save`：更新 `approve_by` 的同时补 `approve_date = time()`（视为一次审核动作）。
