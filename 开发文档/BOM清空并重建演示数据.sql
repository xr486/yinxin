-- ============================================================
-- 清空 BOM 全部数据 + 重新插入演示数据（2026-08-11）
-- 数据库：yixin（MySQL 5.6.17）
-- ============================================================

-- 1) 清空 BOM 三张表全部数据
DELETE FROM bom_substitutes_all;
DELETE FROM bom_lines_all;
DELETE FROM bom_headers_all;

-- 2) 插入 BOM 头（顶层/半成品，物料主数据 SW-* 已在 sf_item_no）
INSERT INTO bom_headers_all (assembly_item_no, version, status, approve_by, approve_date, approve_remark, creation_date, created_by, last_update_date, last_updated_by) VALUES
('SW-001', '1', '已签核', 'admin', UNIX_TIMESTAMP(), '演示数据', UNIX_TIMESTAMP(), 'admin', UNIX_TIMESTAMP(), 'admin'),
('SW-101', '1', '已签核', 'admin', UNIX_TIMESTAMP(), '演示数据', UNIX_TIMESTAMP(), 'admin', UNIX_TIMESTAMP(), 'admin'),
('SW-102', '1', '已签核', 'admin', UNIX_TIMESTAMP(), '演示数据', UNIX_TIMESTAMP(), 'admin', UNIX_TIMESTAMP(), 'admin');

-- 3) 插入 BOM 行
INSERT INTO bom_lines_all (bom_header_id, assembly_item_no, item_num, operation_seq_num, component_item, weizhi, component_quantity, sunhao_rate, component_remarks, effectivity_date, creation_date, created_by, last_update_date, last_updated_by) VALUES
((SELECT bom_header_id FROM bom_headers_all WHERE assembly_item_no='SW-001' AND version='1'), 'SW-001', 1, 10, 'SW-101', 'A1', 1, 0, '表盘总成', UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 'admin', UNIX_TIMESTAMP(), 'admin'),
((SELECT bom_header_id FROM bom_headers_all WHERE assembly_item_no='SW-001' AND version='1'), 'SW-001', 2, 20, 'SW-102', 'A2', 2, 0, '左右各一', UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 'admin', UNIX_TIMESTAMP(), 'admin'),
((SELECT bom_header_id FROM bom_headers_all WHERE assembly_item_no='SW-001' AND version='1'), 'SW-001', 3, 30, 'SW-205', 'B1', 1, 0, '内置电池', UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 'admin', UNIX_TIMESTAMP(), 'admin'),
((SELECT bom_header_id FROM bom_headers_all WHERE assembly_item_no='SW-001' AND version='1'), 'SW-001', 4, 40, 'SW-206', 'B2', 1, 0, '', UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 'admin', UNIX_TIMESTAMP(), 'admin'),
((SELECT bom_header_id FROM bom_headers_all WHERE assembly_item_no='SW-101' AND version='1'), 'SW-101', 1, 10, 'SW-201', 'C1', 1, 0.02, '贴合工艺', UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 'admin', UNIX_TIMESTAMP(), 'admin'),
((SELECT bom_header_id FROM bom_headers_all WHERE assembly_item_no='SW-101' AND version='1'), 'SW-101', 2, 20, 'SW-202', 'C2', 1, 0, '主控', UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 'admin', UNIX_TIMESTAMP(), 'admin'),
((SELECT bom_header_id FROM bom_headers_all WHERE assembly_item_no='SW-102' AND version='1'), 'SW-102', 1, 10, 'SW-203', 'D1', 1, 0, '硅胶带体', UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 'admin', UNIX_TIMESTAMP(), 'admin'),
((SELECT bom_header_id FROM bom_headers_all WHERE assembly_item_no='SW-102' AND version='1'), 'SW-102', 2, 20, 'SW-204', 'D2', 2, 0, '表扣', UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 'admin', UNIX_TIMESTAMP(), 'admin');
