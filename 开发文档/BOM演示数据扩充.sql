-- ============================================================
-- BOM 演示数据扩充（2026-08-11）：新增蓝牙耳机、智能手环两条产品线
-- 数据库：yixin（MySQL 5.6.17）
-- ============================================================

-- 1) 新增物料（sf_item_no）
INSERT INTO sf_item_no (item_no, item_name, item_desc, units, item_category1, item_type, item_use, project_name, inspect_flag, disable_flag, item_status, creation_date, created_by, last_update_date, last_updated_by) VALUES
('78000010', '蓝牙耳机', 'TWS 真无线 入耳式', '台', '成品', 'F', 'S', '常规', 'Y', 'Y', '已签核', UNIX_TIMESTAMP(), 'admin', UNIX_TIMESTAMP(), 'admin'),
('78000011', '耳机左耳组件', '左耳 主从一体', '套', '半成品', 'B', 'S', '常规', 'Y', 'Y', '已签核', UNIX_TIMESTAMP(), 'admin', UNIX_TIMESTAMP(), 'admin'),
('78000012', '耳机右耳组件', '右耳 主从一体', '套', '半成品', 'B', 'S', '常规', 'Y', 'Y', '已签核', UNIX_TIMESTAMP(), 'admin', UNIX_TIMESTAMP(), 'admin'),
('78000013', '充电仓组件', '磁吸充电仓 总成', '套', '半成品', 'B', 'S', '常规', 'Y', 'Y', '已签核', UNIX_TIMESTAMP(), 'admin', UNIX_TIMESTAMP(), 'admin'),
('78000014', '喇叭单元', '13mm 动圈单元', '个', '电子类', 'M', 'S', '常规', 'Y', 'Y', '已签核', UNIX_TIMESTAMP(), 'admin', UNIX_TIMESTAMP(), 'admin'),
('78000015', '耳机锂电池', '45mAh 扣式电池', '块', '电子类', 'M', 'S', '常规', 'Y', 'Y', '已签核', UNIX_TIMESTAMP(), 'admin', UNIX_TIMESTAMP(), 'admin'),
('78000016', '充电仓外壳', 'ABS 磨砂白', '个', '机加件', 'M', 'S', '常规', 'Y', 'Y', '已签核', UNIX_TIMESTAMP(), 'admin', UNIX_TIMESTAMP(), 'admin'),
('78000017', '充电接口板', 'Type-C 充电小板', '块', '电子类', 'M', 'S', '常规', 'Y', 'Y', '已签核', UNIX_TIMESTAMP(), 'admin', UNIX_TIMESTAMP(), 'admin'),
('78000018', '硅胶耳帽', 'S/M/L 三码', '对', '标准件', 'M', 'S', '常规', 'Y', 'Y', '已签核', UNIX_TIMESTAMP(), 'admin', UNIX_TIMESTAMP(), 'admin'),
('78000020', '智能手环', '1.1寸 AMOLED', '台', '成品', 'F', 'S', '常规', 'Y', 'Y', '已签核', UNIX_TIMESTAMP(), 'admin', UNIX_TIMESTAMP(), 'admin'),
('78000021', '腕带组件', 'TPU 腕带 快拆', '套', '半成品', 'B', 'S', '常规', 'Y', 'Y', '已签核', UNIX_TIMESTAMP(), 'admin', UNIX_TIMESTAMP(), 'admin'),
('78000022', '主机组件', '手环主机 总成', '套', '半成品', 'B', 'S', '常规', 'Y', 'Y', '已签核', UNIX_TIMESTAMP(), 'admin', UNIX_TIMESTAMP(), 'admin'),
('78000023', '腕带主体', 'TPU 黑色 16mm', '条', '标准件', 'M', 'S', '常规', 'Y', 'Y', '已签核', UNIX_TIMESTAMP(), 'admin', UNIX_TIMESTAMP(), 'admin'),
('78000024', '心率传感器', 'PPG 光学模组', '颗', '电子类', 'M', 'S', '常规', 'Y', 'Y', '已签核', UNIX_TIMESTAMP(), 'admin', UNIX_TIMESTAMP(), 'admin'),
('78000025', '触摸屏模组', '1.1寸 AMOLED 触摸', '块', '电子类', 'M', 'S', '常规', 'Y', 'Y', '已签核', UNIX_TIMESTAMP(), 'admin', UNIX_TIMESTAMP(), 'admin'),
('78000026', '手环锂电池', '120mAh 聚合物', '块', '电子类', 'M', 'S', '常规', 'Y', 'Y', '已签核', UNIX_TIMESTAMP(), 'admin', UNIX_TIMESTAMP(), 'admin');

-- 2) 新增 BOM 头
INSERT INTO bom_headers_all (assembly_item_no, version, status, approve_by, approve_date, approve_remark, creation_date, created_by, last_update_date, last_updated_by) VALUES
('78000010', '1', '已签核', 'admin', UNIX_TIMESTAMP(), '演示数据', UNIX_TIMESTAMP(), 'admin', UNIX_TIMESTAMP(), 'admin'),
('78000011', '1', '已签核', 'admin', UNIX_TIMESTAMP(), '演示数据', UNIX_TIMESTAMP(), 'admin', UNIX_TIMESTAMP(), 'admin'),
('78000012', '1', '已签核', 'admin', UNIX_TIMESTAMP(), '演示数据', UNIX_TIMESTAMP(), 'admin', UNIX_TIMESTAMP(), 'admin'),
('78000013', '1', '已签核', 'admin', UNIX_TIMESTAMP(), '演示数据', UNIX_TIMESTAMP(), 'admin', UNIX_TIMESTAMP(), 'admin'),
('78000020', '1', '已签核', 'admin', UNIX_TIMESTAMP(), '演示数据', UNIX_TIMESTAMP(), 'admin', UNIX_TIMESTAMP(), 'admin'),
('78000021', '1', '已签核', 'admin', UNIX_TIMESTAMP(), '演示数据', UNIX_TIMESTAMP(), 'admin', UNIX_TIMESTAMP(), 'admin'),
('78000022', '1', '已签核', 'admin', UNIX_TIMESTAMP(), '演示数据', UNIX_TIMESTAMP(), 'admin', UNIX_TIMESTAMP(), 'admin');

-- 3) 新增 BOM 行
INSERT INTO bom_lines_all (bom_header_id, assembly_item_no, item_num, operation_seq_num, component_item, weizhi, component_quantity, sunhao_rate, component_remarks, effectivity_date, creation_date, created_by, last_update_date, last_updated_by) VALUES
-- 蓝牙耳机：左耳 + 右耳 + 充电仓 + 耳帽×4
((SELECT bom_header_id FROM bom_headers_all WHERE assembly_item_no='78000010' AND version='1'), '78000010', 1, 10, '78000011', 'A1', 1, 0, '左耳', UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 'admin', UNIX_TIMESTAMP(), 'admin'),
((SELECT bom_header_id FROM bom_headers_all WHERE assembly_item_no='78000010' AND version='1'), '78000010', 2, 20, '78000012', 'A2', 1, 0, '右耳', UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 'admin', UNIX_TIMESTAMP(), 'admin'),
((SELECT bom_header_id FROM bom_headers_all WHERE assembly_item_no='78000010' AND version='1'), '78000010', 3, 30, '78000013', 'A3', 1, 0, '充电仓', UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 'admin', UNIX_TIMESTAMP(), 'admin'),
((SELECT bom_header_id FROM bom_headers_all WHERE assembly_item_no='78000010' AND version='1'), '78000010', 4, 40, '78000018', 'A4', 4, 0, '耳帽备品', UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 'admin', UNIX_TIMESTAMP(), 'admin'),
-- 左耳组件：喇叭 + 电池
((SELECT bom_header_id FROM bom_headers_all WHERE assembly_item_no='78000011' AND version='1'), '78000011', 1, 10, '78000014', 'B1', 1, 0.01, '动圈单元', UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 'admin', UNIX_TIMESTAMP(), 'admin'),
((SELECT bom_header_id FROM bom_headers_all WHERE assembly_item_no='78000011' AND version='1'), '78000011', 2, 20, '78000015', 'B2', 1, 0, '扣式电池', UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 'admin', UNIX_TIMESTAMP(), 'admin'),
-- 右耳组件：喇叭 + 电池
((SELECT bom_header_id FROM bom_headers_all WHERE assembly_item_no='78000012' AND version='1'), '78000012', 1, 10, '78000014', 'B1', 1, 0.01, '动圈单元', UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 'admin', UNIX_TIMESTAMP(), 'admin'),
((SELECT bom_header_id FROM bom_headers_all WHERE assembly_item_no='78000012' AND version='1'), '78000012', 2, 20, '78000015', 'B2', 1, 0, '扣式电池', UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 'admin', UNIX_TIMESTAMP(), 'admin'),
-- 充电仓组件：外壳 + 接口板 + 电池
((SELECT bom_header_id FROM bom_headers_all WHERE assembly_item_no='78000013' AND version='1'), '78000013', 1, 10, '78000016', 'C1', 1, 0, '外壳', UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 'admin', UNIX_TIMESTAMP(), 'admin'),
((SELECT bom_header_id FROM bom_headers_all WHERE assembly_item_no='78000013' AND version='1'), '78000013', 2, 20, '78000017', 'C2', 1, 0, 'Type-C 小板', UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 'admin', UNIX_TIMESTAMP(), 'admin'),
((SELECT bom_header_id FROM bom_headers_all WHERE assembly_item_no='78000013' AND version='1'), '78000013', 3, 30, '78000015', 'C3', 1, 0, '充电仓电池', UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 'admin', UNIX_TIMESTAMP(), 'admin'),
-- 智能手环：腕带 + 主机 + 电池
((SELECT bom_header_id FROM bom_headers_all WHERE assembly_item_no='78000020' AND version='1'), '78000020', 1, 10, '78000021', 'D1', 1, 0, '腕带', UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 'admin', UNIX_TIMESTAMP(), 'admin'),
((SELECT bom_header_id FROM bom_headers_all WHERE assembly_item_no='78000020' AND version='1'), '78000020', 2, 20, '78000022', 'D2', 1, 0, '主机', UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 'admin', UNIX_TIMESTAMP(), 'admin'),
((SELECT bom_header_id FROM bom_headers_all WHERE assembly_item_no='78000020' AND version='1'), '78000020', 3, 30, '78000026', 'D3', 1, 0, '手环电池', UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 'admin', UNIX_TIMESTAMP(), 'admin'),
-- 腕带组件：腕带主体 + 表扣（复用 78000007）×2
((SELECT bom_header_id FROM bom_headers_all WHERE assembly_item_no='78000021' AND version='1'), '78000021', 1, 10, '78000023', 'E1', 1, 0, 'TPU 带体', UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 'admin', UNIX_TIMESTAMP(), 'admin'),
((SELECT bom_header_id FROM bom_headers_all WHERE assembly_item_no='78000021' AND version='1'), '78000021', 2, 20, '78000007', 'E2', 2, 0, '表扣', UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 'admin', UNIX_TIMESTAMP(), 'admin'),
-- 主机组件：心率传感器 + 触摸屏 + 主控芯片（复用 78000005）
((SELECT bom_header_id FROM bom_headers_all WHERE assembly_item_no='78000022' AND version='1'), '78000022', 1, 10, '78000024', 'F1', 1, 0, '心率模组', UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 'admin', UNIX_TIMESTAMP(), 'admin'),
((SELECT bom_header_id FROM bom_headers_all WHERE assembly_item_no='78000022' AND version='1'), '78000022', 2, 20, '78000025', 'F2', 1, 0.02, '触摸屏', UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 'admin', UNIX_TIMESTAMP(), 'admin'),
((SELECT bom_header_id FROM bom_headers_all WHERE assembly_item_no='78000022' AND version='1'), '78000022', 3, 30, '78000005', 'F3', 1, 0, '主控芯片', UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 'admin', UNIX_TIMESTAMP(), 'admin');
