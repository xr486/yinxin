# 数据库结构改动 SQL 汇总（改表 DDL）

---

## 1. BOM 版本切换 — `is_current` 字段

- 文件：`开发文档/BOM版本切换is_current字段.sql`
- 涉及表：`bom_headers_all`
- 作用：标记每个物料的"当前生效版本"，页面左上角版本下拉切换时写入 `is_current=1`；`latestHeader` 优先取该版本。

```sql
-- 1) 新增字段
ALTER TABLE bom_headers_all ADD COLUMN is_current TINYINT NOT NULL DEFAULT 0 COMMENT '是否当前生效版本(1=是,0=历史)' AFTER approve_remark;

-- 2) 初始化：把每个物料 bom_header_id 最大的版本标记为当前（与旧 latestHeader 逻辑一致）
UPDATE bom_headers_all h
JOIN (SELECT assembly_item_no, MAX(bom_header_id) AS m FROM bom_headers_all GROUP BY assembly_item_no) x
  ON x.assembly_item_no = h.assembly_item_no AND x.m = h.bom_header_id
SET h.is_current = 1;
```

**回滚**：

```sql
ALTER TABLE bom_headers_all DROP COLUMN is_current;
```

---

## 2. PLM 文档管理一期核心表（新建 4 张表）

- 文件：`开发文档/PLM建表SQL.sql`
- 涉及表（**新建**）：`doc_folder` / `doc_master` / `doc_file` / `doc_log`
- 作用：图文档 / PLM 模块一期，与现有表共存，不修改任何现有表。

```sql
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
```

**回滚**（按依赖逆序删除子表 → 父表）：

```sql
DROP TABLE IF EXISTS doc_log;
DROP TABLE IF EXISTS doc_file;
DROP TABLE IF EXISTS doc_master;
DROP TABLE IF EXISTS doc_folder;
```

---

## 3. `doc_master` 废止字段 — `abolish_date` / `abolish_remark`

- 文件：`开发文档/sql_add_abolish_fields.sql`
- 涉及表：`doc_master`
- 作用：增加文档废止日期与废止说明字段。

```sql
ALTER TABLE doc_master ADD COLUMN abolish_date int(11) DEFAULT NULL AFTER last_update_date;
ALTER TABLE doc_master ADD COLUMN abolish_remark varchar(300) DEFAULT NULL AFTER abolish_date;
```

**回滚**（原文件未含，此处补充；按加字段的逆序删除）：

```sql
ALTER TABLE doc_master DROP COLUMN abolish_remark;
ALTER TABLE doc_master DROP COLUMN abolish_date;
```

---

## 4. 图文档中心 — 文件名字段加长 + 注册页面权限

- 文件：`开发文档/图文档中心SQL.sql`
- 涉及表：`sf_item_no_file` / `bom_routing_all_file` / `bom_routing_public_file`（改 `file_name` 长度）+ `scripts`（插入页面权限）
- 作用：三张文件表 `file_name` 扩到 `VARCHAR(100)`；注册图文档中心页面到 `scripts` 权限表。

```sql
ALTER TABLE sf_item_no_file MODIFY file_name VARCHAR(100) DEFAULT NULL;
ALTER TABLE bom_routing_all_file MODIFY file_name VARCHAR(100) DEFAULT NULL;
ALTER TABLE bom_routing_public_file MODIFY file_name VARCHAR(100) DEFAULT NULL;

INSERT INTO scripts (script, pagesecurity, description, creation_date, created_by, function_name)
VALUES ('DocFileCenter.php', 1, '图文档中心', UNIX_TIMESTAMP(), 'admin', '共用');
```

**回滚**：

```sql
-- file_name 需改回原长度（请先确认原始定义，常见为 VARCHAR(50) 或原值）后执行：
-- ALTER TABLE sf_item_no_file MODIFY file_name VARCHAR(原长度) DEFAULT NULL;
-- ALTER TABLE bom_routing_all_file MODIFY file_name VARCHAR(原长度) DEFAULT NULL;
-- ALTER TABLE bom_routing_public_file MODIFY file_name VARCHAR(原长度) DEFAULT NULL;

-- 撤销页面权限注册：
DELETE FROM scripts WHERE script = 'DocFileCenter.php';
```
