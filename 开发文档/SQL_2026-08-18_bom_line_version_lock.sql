-- ============================================================
-- SQL_2026-08-18_bom_line_version_lock.sql
-- BOM 多版本：方案B（引用即版本）
-- 库：yixin
-- 说明：bom_lines_all.component_bom_header_id = 行级子件版本绑定（引用即版本）
-- ============================================================

-- 1. 新增字段（已执行）
ALTER TABLE bom_lines_all
  ADD COLUMN component_bom_header_id INT(11) DEFAULT NULL
  COMMENT 'child BOM header lock (NULL=follow is_current default)'
  AFTER component_item;

-- 2. 迁移：子件有 BOM 头但行未绑定的行，按子件当前 is_current 版本补绑（已执行，7 行）
UPDATE bom_lines_all l SET l.component_bom_header_id = (
    SELECT h.bom_header_id FROM bom_headers_all h
    WHERE h.assembly_item_no = l.component_item
    ORDER BY h.is_current DESC, h.bom_header_id DESC LIMIT 1
)
WHERE l.disable_date = 0 AND l.component_bom_header_id IS NULL
  AND EXISTS (SELECT 1 FROM bom_headers_all h3 WHERE h3.assembly_item_no = l.component_item);

-- 3. 可选索引（数据量大时再执行）
-- ALTER TABLE bom_lines_all ADD INDEX idx_comp_bomhdr (component_bom_header_id);

-- ============================================================
-- 回滚
-- ============================================================
-- 恢复到"全部跟随"状态：
-- UPDATE bom_lines_all SET component_bom_header_id = NULL WHERE disable_date=0;
-- 彻底回滚（删除字段）：
-- ALTER TABLE bom_lines_all DROP COLUMN component_bom_header_id;
-- ALTER TABLE bom_lines_all DROP INDEX idx_comp_bomhdr;
