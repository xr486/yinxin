-- 回滚：删除 bom_lines_all 的 component_version 字段（2026-08-17 确认未派上用场）
-- 备份已存于 /tmp/bom_lines_all_pre_rollback.sql
ALTER TABLE bom_lines_all DROP COLUMN component_version;
SHOW COLUMNS FROM bom_lines_all LIKE 'component_version';
