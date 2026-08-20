-- ============================================================
-- 2026-08-17 BOM 子件版本按父BOM行固定（解决切换版本级联影响其他BOM）
-- ⛔ 已回滚：本字段未派上用场，已于 2026-08-17 执行 DROP COLUMN 删除。
--    原因：无 PHP 代码引用；35 行 bom_lines_all 钉定值全为 NULL；设计中的
--    钉定逻辑(getActiveLines/buildMindmapTree)从未实现。备份见 /tmp/bom_lines_all_pre_rollback.sql
-- 目标：在 bom_lines_all 增加 component_version，使父BOM行可单独
--       固定其下子件显示哪个版本；为空时仍跟随子件"当前生效版本"(is_current)，
--       保持历史数据向后兼容。
-- 影响对象：仅 bom_lines_all 增加 1 个字段，不新增表、不改 sf_item_no。
-- ============================================================

-- 正向 SQL
ALTER TABLE bom_lines_all
  ADD COLUMN component_version VARCHAR(20) NULL
  COMMENT 'pinned child version for this parent line; NULL=follow child current(is_current)';

-- 验证
SHOW COLUMNS FROM bom_lines_all LIKE 'component_version';

-- ============================================================
-- 回滚 SQL（如需要撤销本次变更）
-- ALTER TABLE bom_lines_all DROP COLUMN component_version;
-- ============================================================
