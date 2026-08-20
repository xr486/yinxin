-- ============================================================
-- 自行车 BOM 剩余结构补全（2026-08-12）
-- 按图：自行车总成 → 自行车主体 → 车轮/车架/链条/脚踏板/飞轮
--                  车轮 → 前轮/后轮/轴承
--                  前轮 → 内胎/外胎/橡胶
--                  后轮 → 内胎/外胎/气门芯
--                  轴承 → 钢材
--                  车架 → 铝合金/油漆
--                  链条 → 连接扣/套圈
-- 已存在的：自行车总成 0、主体 38829222、车轮 84932035、前轮 86667135
--            脚踏板 92906555、飞轮 70637694、车架 45230687、链条 39580185
--            内胎 23943085（前轮已引用）
-- ============================================================

-- 1) 新增物料（10 个 M 原材料 + 2 个 B 半成品）
INSERT INTO sf_item_no (item_no, item_name, item_desc, units, item_category1, item_type, item_use, project_name, inspect_flag, disable_flag, creation_date, created_by, last_update_date, last_updated_by) VALUES
('35636756', '后轮', 'B 半成品', '个', '机加件', 'B', 'S', '常规', 'Y', 'Y', UNIX_TIMESTAMP(), 'admin', UNIX_TIMESTAMP(), 'admin'),
('89642032', '轴承', 'B 半成品', '个', '标准件', 'B', 'S', '常规', 'Y', 'Y', UNIX_TIMESTAMP(), 'admin', UNIX_TIMESTAMP(), 'admin'),
('40500239', '钢材', 'M 原材料', 'kg', '原材料', 'M', 'S', '常规', 'Y', 'Y', UNIX_TIMESTAMP(), 'admin', UNIX_TIMESTAMP(), 'admin'),
('28332344', '铝合金', 'M 原材料', 'kg', '原材料', 'M', 'S', '常规', 'Y', 'Y', UNIX_TIMESTAMP(), 'admin', UNIX_TIMESTAMP(), 'admin'),
('20122980', '油漆', 'M 原材料', 'kg', '原材料', 'M', 'S', '常规', 'Y', 'Y', UNIX_TIMESTAMP(), 'admin', UNIX_TIMESTAMP(), 'admin'),
('48763664', '连接扣', 'M 原材料', '个', '标准件', 'M', 'S', '常规', 'Y', 'Y', UNIX_TIMESTAMP(), 'admin', UNIX_TIMESTAMP(), 'admin'),
('22303874', '套圈', 'M 原材料', '个', '标准件', 'M', 'S', '常规', 'Y', 'Y', UNIX_TIMESTAMP(), 'admin', UNIX_TIMESTAMP(), 'admin'),
('99157248', '外胎', 'M 原材料', '个', '标准件', 'M', 'S', '常规', 'Y', 'Y', UNIX_TIMESTAMP(), 'admin', UNIX_TIMESTAMP(), 'admin'),
('72901772', '橡胶', 'M 原材料', '个', '原材料', 'M', 'S', '常规', 'Y', 'Y', UNIX_TIMESTAMP(), 'admin', UNIX_TIMESTAMP(), 'admin'),
('78532196', '气门芯', 'M 原材料', '个', '标准件', 'M', 'S', '常规', 'Y', 'Y', UNIX_TIMESTAMP(), 'admin', UNIX_TIMESTAMP(), 'admin');

-- 2) 为 4 个物料创建 BOM 头：后轮 35636756、轴承 89642032、车架 45230687（已有无头）、链条 39580185（已有无头）
INSERT INTO bom_headers_all (assembly_item_no, version, status, approve_by, approve_date, approve_remark, creation_date, created_by, last_update_date, last_updated_by) VALUES
('35636756', '1', '已签核', 'admin', UNIX_TIMESTAMP(), '演示数据', UNIX_TIMESTAMP(), 'admin', UNIX_TIMESTAMP(), 'admin'),
('89642032', '1', '已签核', 'admin', UNIX_TIMESTAMP(), '演示数据', UNIX_TIMESTAMP(), 'admin', UNIX_TIMESTAMP(), 'admin'),
('45230687', '1', '已签核', 'admin', UNIX_TIMESTAMP(), '演示数据', UNIX_TIMESTAMP(), 'admin', UNIX_TIMESTAMP(), 'admin'),
('39580185', '1', '已签核', 'admin', UNIX_TIMESTAMP(), '演示数据', UNIX_TIMESTAMP(), 'admin', UNIX_TIMESTAMP(), 'admin');

-- 3) 补全 bom_lines_all 子件行
-- 车轮 (84932035)：补加 后轮 ×1、轴承 ×1
INSERT INTO bom_lines_all (bom_header_id, assembly_item_no, item_num, operation_seq_num, component_item, weizhi, component_quantity, sunhao_rate, component_remarks, effectivity_date, creation_date, created_by, last_update_date, last_updated_by) VALUES
((SELECT bom_header_id FROM bom_headers_all WHERE assembly_item_no='84932035'), '84932035', 2, 20, '35636756', 'C2', 1, 0, '后轮', UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 'admin', UNIX_TIMESTAMP(), 'admin'),
((SELECT bom_header_id FROM bom_headers_all WHERE assembly_item_no='84932035'), '84932035', 3, 30, '89642032', 'C3', 1, 0, '轴承', UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 'admin', UNIX_TIMESTAMP(), 'admin');

-- 前轮 (86667135)：补加 外胎 ×1、橡胶 ×1（内胎 23943085 已有）
INSERT INTO bom_lines_all (bom_header_id, assembly_item_no, item_num, operation_seq_num, component_item, weizhi, component_quantity, sunhao_rate, component_remarks, effectivity_date, creation_date, created_by, last_update_date, last_updated_by) VALUES
((SELECT bom_header_id FROM bom_headers_all WHERE assembly_item_no='86667135'), '86667135', 2, 20, '99157248', 'D2', 1, 0.01, '外胎', UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 'admin', UNIX_TIMESTAMP(), 'admin'),
((SELECT bom_header_id FROM bom_headers_all WHERE assembly_item_no='86667135'), '86667135', 3, 30, '72901772', 'D3', 1, 0, '橡胶', UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 'admin', UNIX_TIMESTAMP(), 'admin');

-- 后轮 (35636756)：内胎 23943085 ×1、外胎 99157248 ×1、气门芯 78532196 ×1
INSERT INTO bom_lines_all (bom_header_id, assembly_item_no, item_num, operation_seq_num, component_item, weizhi, component_quantity, sunhao_rate, component_remarks, effectivity_date, creation_date, created_by, last_update_date, last_updated_by) VALUES
((SELECT bom_header_id FROM bom_headers_all WHERE assembly_item_no='35636756'), '35636756', 1, 10, '23943085', 'E1', 1, 0, '内胎', UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 'admin', UNIX_TIMESTAMP(), 'admin'),
((SELECT bom_header_id FROM bom_headers_all WHERE assembly_item_no='35636756'), '35636756', 2, 20, '99157248', 'E2', 1, 0.01, '外胎', UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 'admin', UNIX_TIMESTAMP(), 'admin'),
((SELECT bom_header_id FROM bom_headers_all WHERE assembly_item_no='35636756'), '35636756', 3, 30, '78532196', 'E3', 1, 0, '气门芯', UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 'admin', UNIX_TIMESTAMP(), 'admin');

-- 轴承 (89642032)：钢材 40500239 ×1
INSERT INTO bom_lines_all (bom_header_id, assembly_item_no, item_num, operation_seq_num, component_item, weizhi, component_quantity, sunhao_rate, component_remarks, effectivity_date, creation_date, created_by, last_update_date, last_updated_by) VALUES
((SELECT bom_header_id FROM bom_headers_all WHERE assembly_item_no='89642032'), '89642032', 1, 10, '40500239', 'F1', 1, 0.05, '钢材', UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 'admin', UNIX_TIMESTAMP(), 'admin');

-- 车架 (45230687)：铝合金 28332344 ×1、油漆 20122980 ×1
INSERT INTO bom_lines_all (bom_header_id, assembly_item_no, item_num, operation_seq_num, component_item, weizhi, component_quantity, sunhao_rate, component_remarks, effectivity_date, creation_date, created_by, last_update_date, last_updated_by) VALUES
((SELECT bom_header_id FROM bom_headers_all WHERE assembly_item_no='45230687'), '45230687', 1, 10, '28332344', 'G1', 1, 0.02, '铝合金', UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 'admin', UNIX_TIMESTAMP(), 'admin'),
((SELECT bom_header_id FROM bom_headers_all WHERE assembly_item_no='45230687'), '45230687', 2, 20, '20122980', 'G2', 1, 0, '油漆', UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 'admin', UNIX_TIMESTAMP(), 'admin');

-- 链条 (39580185)：连接扣 48763664 ×1、套圈 22303874 ×1
INSERT INTO bom_lines_all (bom_header_id, assembly_item_no, item_num, operation_seq_num, component_item, weizhi, component_quantity, sunhao_rate, component_remarks, effectivity_date, creation_date, created_by, last_update_date, last_updated_by) VALUES
((SELECT bom_header_id FROM bom_headers_all WHERE assembly_item_no='39580185'), '39580185', 1, 10, '48763664', 'H1', 1, 0, '连接扣', UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 'admin', UNIX_TIMESTAMP(), 'admin'),
((SELECT bom_header_id FROM bom_headers_all WHERE assembly_item_no='39580185'), '39580185', 2, 20, '22303874', 'H2', 1, 0, '套圈', UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 'admin', UNIX_TIMESTAMP(), 'admin');
