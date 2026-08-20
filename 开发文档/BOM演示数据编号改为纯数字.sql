-- ============================================================
-- 演示物料编号 SW-* 改为纯数字 7800000x（2026-08-11）
-- 数据库：yixin（MySQL 5.6.17）
-- ============================================================

-- 1) sf_item_no：物料主数据编号
UPDATE sf_item_no SET item_no='78000001' WHERE item_no='SW-001';
UPDATE sf_item_no SET item_no='78000002' WHERE item_no='SW-101';
UPDATE sf_item_no SET item_no='78000003' WHERE item_no='SW-102';
UPDATE sf_item_no SET item_no='78000004' WHERE item_no='SW-201';
UPDATE sf_item_no SET item_no='78000005' WHERE item_no='SW-202';
UPDATE sf_item_no SET item_no='78000006' WHERE item_no='SW-203';
UPDATE sf_item_no SET item_no='78000007' WHERE item_no='SW-204';
UPDATE sf_item_no SET item_no='78000008' WHERE item_no='SW-205';
UPDATE sf_item_no SET item_no='78000009' WHERE item_no='SW-206';

-- 2) bom_headers_all：BOM 头母件编号
UPDATE bom_headers_all SET assembly_item_no='78000001' WHERE assembly_item_no='SW-001';
UPDATE bom_headers_all SET assembly_item_no='78000002' WHERE assembly_item_no='SW-101';
UPDATE bom_headers_all SET assembly_item_no='78000003' WHERE assembly_item_no='SW-102';

-- 3) bom_lines_all：行母件编号
UPDATE bom_lines_all SET assembly_item_no='78000001' WHERE assembly_item_no='SW-001';
UPDATE bom_lines_all SET assembly_item_no='78000002' WHERE assembly_item_no='SW-101';
UPDATE bom_lines_all SET assembly_item_no='78000003' WHERE assembly_item_no='SW-102';

-- 4) bom_lines_all：作为子件的编号
UPDATE bom_lines_all SET component_item='78000002' WHERE component_item='SW-101';
UPDATE bom_lines_all SET component_item='78000003' WHERE component_item='SW-102';
UPDATE bom_lines_all SET component_item='78000004' WHERE component_item='SW-201';
UPDATE bom_lines_all SET component_item='78000005' WHERE component_item='SW-202';
UPDATE bom_lines_all SET component_item='78000006' WHERE component_item='SW-203';
UPDATE bom_lines_all SET component_item='78000007' WHERE component_item='SW-204';
UPDATE bom_lines_all SET component_item='78000008' WHERE component_item='SW-205';
UPDATE bom_lines_all SET component_item='78000009' WHERE component_item='SW-206';
