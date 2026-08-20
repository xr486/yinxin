-- ============================================================
-- BOM 版本切换落库：bom_headers_all 增加 is_current 字段
-- 用途：用户通过页面左上角"版本"下拉切换版本时，标记该版本为
--       "当前生效版本"（is_current=1），latestHeader 优先取该版本。
-- 执行时间：2026-08-13
-- 数据库：yixin (MySQL 5.6)
-- ============================================================

-- 1) 新增字段（可执行；重复执行会报错，用 IF NOT EXISTS 或人工确认）
ALTER TABLE bom_headers_all ADD COLUMN is_current TINYINT NOT NULL DEFAULT 0 COMMENT '是否当前生效版本(1=是,0=历史)' AFTER approve_remark;

-- 2) 初始化：把每个物料 bom_header_id 最大的版本标记为当前（与旧逻辑 latestHeader 一致）
UPDATE bom_headers_all h
JOIN (SELECT assembly_item_no, MAX(bom_header_id) AS m FROM bom_headers_all GROUP BY assembly_item_no) x
  ON x.assembly_item_no = h.assembly_item_no AND x.m = h.bom_header_id
SET h.is_current = 1;

-- ============================================================
-- 回滚 SQL（如需撤销）
-- ALTER TABLE bom_headers_all DROP COLUMN is_current;
-- ============================================================
