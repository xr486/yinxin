-- 恢复被误删的表盘组件 BOM（78000002 v1）：BOM头 + 子件行（显示屏模组 78000004、主控芯片 78000005）
-- 2026-08-11 14:xx 用户误删（删除保修卡下的引用行时误删了 BOM 头）

INSERT INTO bom_headers_all (assembly_item_no, version, status, approve_by, approve_date, approve_remark, creation_date, created_by, last_update_date, last_updated_by)
VALUES ('78000002', '1', '已签核', 'admin', UNIX_TIMESTAMP(), '恢复误删', UNIX_TIMESTAMP(), 'admin', UNIX_TIMESTAMP(), 'admin');

SET @hid = LAST_INSERT_ID();

INSERT INTO bom_lines_all (bom_header_id, assembly_item_no, item_num, operation_seq_num, component_item, weizhi, component_quantity, sunhao_rate, component_remarks, effectivity_date, creation_date, created_by, last_update_date, last_updated_by) VALUES
(@hid, '78000002', 1, 10, '78000004', 'C1', 1, 0.02, '贴合工艺', UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 'admin', UNIX_TIMESTAMP(), 'admin'),
(@hid, '78000002', 2, 20, '78000005', 'C2', 1, 0, '主控', UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 'admin', UNIX_TIMESTAMP(), 'admin');
