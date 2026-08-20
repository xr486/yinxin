-- 图文档中心演示数据（多分类多物料多图档）
-- 目的：让左侧树能展示多层级、虚线指引效果更明显
-- 时间：2026-08-12
-- 注意：file_patch 指向 SO/ 目录下已有的真实文件（md5 命名），保证 filesize 显示正常

INSERT INTO sf_item_no_file (item_no, file_name, file_patch, creation_date, created_by) VALUES
-- 成品：智能手表 3D模型/外形图/说明书
('78000001', '3D模型',         'SO/00c827cb4f05c14056d4862b87e8f7b0.pdf', UNIX_TIMESTAMP(), 'admin'),
('78000001', '外形图',         'SO/0124c5ffe48c22dae11bd75e7e88d2e0.jpg', UNIX_TIMESTAMP(), 'admin'),
('78000001', '说明书',         'SO/03f2dd0f4b027ccca0346367ab41ae72.pdf', UNIX_TIMESTAMP(), 'admin'),
-- 成品：蓝牙耳机 装配图/SOP
('78000010', '装配图',         'SO/0293efa0fd1ab9588480273559e075c9.jpg', UNIX_TIMESTAMP(), 'admin'),
('78000010', 'SOP作业指导',    'SO/05f2c3fb76e4545552ec9b28a58894e8.png', UNIX_TIMESTAMP(), 'admin'),
-- 成品：智能手环 3D模型
('78000020', '3D模型',         'SO/00c827cb4f05c14056d4862b87e8f7b0.pdf', UNIX_TIMESTAMP(), 'admin'),
-- 半成品：自行车主体 装配图/爆炸图
('38829222', '装配图',         'SO/0124c5ffe48c22dae11bd75e7e88d2e0.jpg', UNIX_TIMESTAMP(), 'admin'),
('38829222', '爆炸图',         'SO/03f2dd0f4b027ccca0346367ab41ae72.pdf', UNIX_TIMESTAMP(), 'admin'),
-- 半成品：主机组件 3D模型/组装流程
('78000022', '3D模型',         'SO/00c827cb4f05c14056d4862b87e8f7b0.pdf', UNIX_TIMESTAMP(), 'admin'),
('78000022', '组装流程图',     'SO/05f2c3fb76e4545552ec9b28a58894e8.png', UNIX_TIMESTAMP(), 'admin'),
-- 半成品：表盘组件 工艺流程图
('78000002', '工艺流程图',     'SO/0293efa0fd1ab9588480273559e075c9.jpg', UNIX_TIMESTAMP(), 'admin'),
-- 机加件：外壳 3D模型/CAD
('78000009', '3D模型',         'SO/03f2dd0f4b027ccca0346367ab41ae72.pdf', UNIX_TIMESTAMP(), 'admin'),
('78000009', 'CAD工程图',      'SO/00c827cb4f05c14056d4862b87e8f7b0.pdf', UNIX_TIMESTAMP(), 'admin'),
-- 机加件：充电仓外壳 CAD
('78000016', 'CAD工程图',      'SO/03f2dd0f4b027ccca0346367ab41ae72.pdf', UNIX_TIMESTAMP(), 'admin'),
-- 标准件：表带主体 3D模型
('78000006', '3D模型',         'SO/00c827cb4f05c14056d4862b87e8f7b0.pdf', UNIX_TIMESTAMP(), 'admin'),
-- 标准件：硅胶耳帽 规格图
('78000018', '规格图',         'SO/0124c5ffe48c22dae11bd75e7e88d2e0.jpg', UNIX_TIMESTAMP(), 'admin');