-- ============================================================
-- PLM 文档管理系统 一期核心表（2026-08-14）
-- 说明：与现有表共存，不修改任何现有表
-- 一期 4 张：doc_folder / doc_master / doc_file / doc_log
-- ============================================================

-- 1. 目录树（左侧树）
CREATE TABLE IF NOT EXISTS doc_folder (
  folder_id       INT AUTO_INCREMENT PRIMARY KEY,
  parent_id       INT DEFAULT 0 COMMENT '父目录，0=根（主目录）',
  folder_name     VARCHAR(100) NOT NULL COMMENT '目录/项目名称',
  folder_code     VARCHAR(50) DEFAULT '' COMMENT '目录编码（可选）',
  folder_type     VARCHAR(20) DEFAULT 'folder' COMMENT 'folder文件夹 / project项目 / stage阶段',
  sort_order      INT DEFAULT 0 COMMENT '排序',
  disable_flag    VARCHAR(1) DEFAULT 'N' COMMENT 'Y停用/N启用',
  created_by      VARCHAR(30) DEFAULT '',
  creation_date   INT(11) DEFAULT 0,
  last_update_by  VARCHAR(30) DEFAULT '',
  last_update_date INT(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='PLM目录树';

-- 2. 文档主记录（一个文档一行，跨版本稳定）
CREATE TABLE IF NOT EXISTS doc_master (
  doc_id          INT AUTO_INCREMENT PRIMARY KEY,
  doc_code        VARCHAR(50) DEFAULT '' COMMENT '文档编码/编号',
  doc_name        VARCHAR(200) NOT NULL COMMENT '文档名称',
  doc_type        VARCHAR(50) DEFAULT '' COMMENT '文档类型（图纸/表单/报告...）',
  folder_id       INT DEFAULT 0 COMMENT '所属目录 doc_folder.folder_id',
  item_no         VARCHAR(40) DEFAULT '' COMMENT '关联物料 sf_item_no.item_no（可空）',
  status          VARCHAR(20) DEFAULT '正常' COMMENT '状态：正常/已归档/已废止/已删除',
  check_status    VARCHAR(10) DEFAULT '在库' COMMENT '在库/检出中',
  current_version VARCHAR(10) DEFAULT 'v1' COMMENT '当前版本号',
  is_template     VARCHAR(1) DEFAULT 'N' COMMENT 'Y模板/N普通文档',
  remark          VARCHAR(300) DEFAULT '' COMMENT '备注',
  created_by      VARCHAR(30) DEFAULT '',
  creation_date   INT(11) DEFAULT 0,
  last_update_by  VARCHAR(30) DEFAULT '',
  last_update_date INT(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='PLM文档主记录';

-- 3. 文档版本/实体文件（每版本一行）
CREATE TABLE IF NOT EXISTS doc_file (
  file_id      INT AUTO_INCREMENT PRIMARY KEY,
  doc_id       INT NOT NULL COMMENT '所属文档 doc_master.doc_id',
  version_no   VARCHAR(10) DEFAULT 'v1' COMMENT '版本号',
  file_name    VARCHAR(200) DEFAULT '' COMMENT '原始文件名',
  file_patch   VARCHAR(200) DEFAULT '' COMMENT '存储路径 SO/...',
  file_ext     VARCHAR(10) DEFAULT '' COMMENT '后缀',
  file_size    INT DEFAULT 0 COMMENT '大小(字节)',
  is_current   VARCHAR(1) DEFAULT 'Y' COMMENT 'Y当前版本/N历史版本',
  uploader     VARCHAR(30) DEFAULT '',
  upload_date  INT(11) DEFAULT 0,
  remark       VARCHAR(200) DEFAULT '' COMMENT '版本备注'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='PLM文档版本/文件';

-- 4. 操作日志（审计）
CREATE TABLE IF NOT EXISTS doc_log (
  log_id      INT AUTO_INCREMENT PRIMARY KEY,
  doc_id      INT NOT NULL COMMENT '文档 doc_master.doc_id',
  action      VARCHAR(50) DEFAULT '' COMMENT '导入/升版/归档/反归档/废止/恢复/删除/修改属性',
  operator    VARCHAR(30) DEFAULT '',
  action_date INT(11) DEFAULT 0,
  remark      VARCHAR(200) DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='PLM文档操作日志';

-- 5. 初始目录：主目录
INSERT INTO doc_folder (parent_id, folder_name, folder_code, folder_type, sort_order, disable_flag, created_by, creation_date)
VALUES (0, '主目录', '', 'folder', 0, 'N', 'admin', UNIX_TIMESTAMP());
-- ============================================================
-- 回滚 SQL（如需删除 PLM 表）
-- ============================================================
DROP TABLE IF EXISTS doc_log;
DROP TABLE IF EXISTS doc_file;
DROP TABLE IF EXISTS doc_master;
DROP TABLE IF EXISTS doc_folder;
