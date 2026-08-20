# PLM 文档管理系统设计（参考版）

- 日期：2026-08-14
- 参考：用户提供的 PLM 系统截图（文件工作区 + 文档模板）
- 定位：独立模块「图文档」（现有 DocFileCenter 物料图档视图保留，PLM 管项目/目录文档）
- 原则：**新增表**（与现有表共存），复用上传类（upload2.class.php）、BOM 树样式、三级菜单权限

---

## 1. 系统定位

| 维度 | 现有 DocFileCenter（物料图档） | 新增 PLM 模块 |
|---|---|---|
| 视角 | 物料为中心（图档挂物料） | **目录/项目为中心**（文档存目录树） |
| 组织 | 物料分类 → 物料 | 主目录 → 项目/文件夹（多级树） |
| 版本 | 无 | ✅ 多版本（v1/v2...） |
| 状态 | 无（只有正常） | ✅ 正常 / 已归档 / 已废止 / 检出中 |
| 日志 | 无 | ✅ 操作日志（谁/何时/做了什么） |
| 关联 | 物料（天然） | ✅ 可关联物料/文档/工艺 |
| 协同 | 无 | ✅ 检出/借入（二期） |

两个模块**共存**：物料图档中心管"物料自身的图纸"，PLM 管"按项目/目录组织的文档"。

---

## 2. 核心业务模型

```
┌─────────────────────────────────────────────────┐
│                目录树 doc_folder                 │
│   主目录 ── WHTEST(项目) ── 20260804(文件夹)     │
│              └─ 软件开发(项目) ── 车间 ── 1产线  │
└───────────────────┬─────────────────────────────┘
                    │ 包含
┌───────────────────▼─────────────────────────────┐
│            文档主记录 doc_master                 │
│   doc_code(编码) / doc_name(名称) / status(状态) │
│   current_version(v2) / item_no(关联物料)        │
└───────────────────┬─────────────────────────────┘
                    │ 版本（1:N）
┌───────────────────▼─────────────────────────────┐
│            文档版本 doc_file                     │
│   v1(旧) ── is_current=N ── 可下载不可改         │
│   v2(新) ── is_current=Y ── 当前版本             │
└───────────────────┬─────────────────────────────┘
                    │ 记录
        ┌───────────┼───────────┐
        ▼           ▼           ▼
   doc_log       doc_relation  doc_checkout
   (操作日志)     (关联物料/文档)  (检出/借用)
```

---

## 3. 数据库设计（7 张新表）

### 3.1 doc_folder 目录树（左侧树）

```sql
CREATE TABLE doc_folder (
  folder_id       INT AUTO_INCREMENT PRIMARY KEY,
  parent_id       INT DEFAULT 0 COMMENT '父目录，0=根（主目录）',
  folder_name     VARCHAR(100) NOT NULL COMMENT '目录/项目名称',
  folder_code     VARCHAR(50) DEFAULT '' COMMENT '目录编码（可选）',
  folder_type     VARCHAR(20) DEFAULT 'folder' COMMENT 'folder文件夹 / project项目 / stage阶段',
  sort_order      INT DEFAULT 0 COMMENT '排序',
  disable_flag    VARCHAR(1) DEFAULT 'N' COMMENT 'Y停用/N启用',
  created_by      VARCHAR(30),
  creation_date   INT(11),
  last_update_by  VARCHAR(30),
  last_update_date INT(11)
);
```
- 树结构：`parent_id` 自关联，支持任意层级（主目录→项目→阶段→文件夹）
- 参照截图：主目录 > WHTEST > 20260804 > 车间 > 1产线

### 3.2 doc_master 文档主记录（一个文档一行，跨版本稳定）

```sql
CREATE TABLE doc_master (
  doc_id          INT AUTO_INCREMENT PRIMARY KEY,
  doc_code        VARCHAR(50) DEFAULT '' COMMENT '文档编码/编号（可自动建议）',
  doc_name        VARCHAR(200) NOT NULL COMMENT '文档名称',
  doc_type        VARCHAR(50) DEFAULT '' COMMENT '文档类型（图纸/表单/报告...）',
  folder_id       INT DEFAULT 0 COMMENT '所属目录 doc_folder.folder_id',
  item_no         VARCHAR(40) DEFAULT '' COMMENT '关联物料 sf_item_no.item_no（可空）',
  status          VARCHAR(20) DEFAULT '正常' COMMENT '状态：正常/已归档/已废止',
  check_status    VARCHAR(10) DEFAULT '在库' COMMENT '在库/检出中',
  current_version VARCHAR(10) DEFAULT 'v1' COMMENT '当前版本号',
  is_template     VARCHAR(1) DEFAULT 'N' COMMENT '是否模板（Y=模板库）',
  remark          VARCHAR(300) DEFAULT '',
  created_by      VARCHAR(30),
  creation_date   INT(11),
  last_update_by  VARCHAR(30),
  last_update_date INT(11)
);
```

### 3.3 doc_file 文档版本（实体文件，每版本一行）

```sql
CREATE TABLE doc_file (
  file_id      INT AUTO_INCREMENT PRIMARY KEY,
  doc_id       INT NOT NULL COMMENT '所属文档 doc_master.doc_id',
  version_no   VARCHAR(10) DEFAULT 'v1' COMMENT '版本号',
  file_name    VARCHAR(200) DEFAULT '' COMMENT '原始文件名',
  file_patch   VARCHAR(200) DEFAULT '' COMMENT '存储路径 SO/...',
  file_ext     VARCHAR(10) DEFAULT '' COMMENT '后缀',
  file_size    INT DEFAULT 0 COMMENT '大小(字节)',
  is_current   VARCHAR(1) DEFAULT 'Y' COMMENT 'Y当前版本/N历史版本',
  uploader     VARCHAR(30),
  upload_date  INT(11),
  remark       VARCHAR(200) DEFAULT ''
);
```
- **版本管理核心**：升版 = 新插入一行，旧行 is_current=N

### 3.4 doc_log 操作日志（审计）

```sql
CREATE TABLE doc_log (
  log_id      INT AUTO_INCREMENT PRIMARY KEY,
  doc_id      INT NOT NULL,
  action      VARCHAR(50) COMMENT '导入/升版/归档/反归档/废止/检入/检出/删除/修改属性',
  operator    VARCHAR(30),
  action_date INT(11),
  remark      VARCHAR(200) DEFAULT ''
);
```
- 截图表格有"操作日志"列 → 每个文档显示最近操作

### 3.5 doc_relation 文档关联

```sql
CREATE TABLE doc_relation (
  relation_id INT AUTO_INCREMENT PRIMARY KEY,
  doc_id      INT NOT NULL COMMENT '文档',
  ref_type    VARCHAR(20) COMMENT 'ITEM物料 / DOC文档 / ROUTE工艺路线',
  ref_id      VARCHAR(50) COMMENT '物料编码/文档ID/工艺路线ID',
  remark      VARCHAR(200) DEFAULT ''
);
```
- 对应截图右侧"对应物料 / 相关文档 / 相关流程"

### 3.6 doc_checkout 检出/借用记录（二期）

```sql
CREATE TABLE doc_checkout (
  checkout_id   INT AUTO_INCREMENT PRIMARY KEY,
  doc_id        INT NOT NULL,
  user_id       VARCHAR(30),
  co_type       VARCHAR(10) DEFAULT '检出' COMMENT '检出/借用',
  checkout_date INT(11),
  return_date   INT(11) DEFAULT 0,
  status        VARCHAR(10) DEFAULT '进行中' COMMENT '进行中/已归还',
  remark        VARCHAR(200) DEFAULT ''
);
```

### 3.7 doc_approval 审批/签发记录（三期）

```sql
CREATE TABLE doc_approval (
  approval_id   INT AUTO_INCREMENT PRIMARY KEY,
  doc_id        INT NOT NULL,
  doc_file_id   INT DEFAULT 0 COMMENT '审批针对的版本',
  approval_type VARCHAR(20) COMMENT '签发/变更/废止',
  status        VARCHAR(20) DEFAULT '待审批' COMMENT '待审批/已通过/已驳回',
  applicant     VARCHAR(30),
  apply_date    INT(11),
  approver      VARCHAR(30),
  approve_date  INT(11),
  approve_remark VARCHAR(300) DEFAULT ''
);
```

> **模板库不单独建表**：复用 doc_master（is_template='Y'）+ doc_folder（APQP 阶段目录）

### 与现有表的关系

```
doc_master.item_no ──────────── sf_item_no.item_no（物料）
doc_relation.ref_id(ROUTE) ──── bom_routings_all.route_id（工艺路线）
doc_file.file_patch ─────────── SO/ 目录（与现有 *_file 表同存储机制）
scripts / user_power / www_users ─── 三级菜单权限（复用）
```

---

## 4. 核心业务流程

### 4.1 导入文档（新建）
```
选目录 → 点「导入」→ 填文档名称/编码(自动建议) → 上传文件(多文件)
  → 创建 doc_master(状态=正常, v1) + doc_file(v1, is_current=Y)
  → doc_log 记「导入」
```

### 4.2 升版（版本管理）
```
选中文档 → 点「升版」→ 上传新文件 → 填版本备注
  → 旧 doc_file.is_current=N（保留可下载）
  → 新 doc_file(v2, is_current=Y)，doc_master.current_version=v2
  → doc_log 记「升版」
```

### 4.3 归档 / 反归档
```
选中 → 点「归档」→ doc_master.status=已归档（表格状态变灰标签）
  → 归档后只读：可下载/查看，不可升版/修改
反归档 → status=正常（恢复）
  → doc_log 记录
```

### 4.4 废止
```
选中 → 点「废止」→ status=已废止（置灰，禁止使用，仅日志可查）
  → 比归档更严格：废止 = 作废（防误用）
```

### 4.5 检出 / 检入（二期，协同锁）
```
检出：doc_master.check_status=检出中 + doc_checkout 记录（锁定，别人不能改）
  → 下载本地修改
检入：解锁 + 可选升版（常见流程：检出→改→升版→检入）
```

### 4.6 借用（二期）
```
doc_checkout 记录（type=借用）→ 下载副本，不影响原文件/不锁定
```

### 4.7 文档属性 / 历史版本 / 关联 / 日志
```
文档属性：弹窗查看/编辑 doc_master 字段（名称/编码/类型/目录/关联物料/备注）
历史版本：弹窗列出 doc_file 全部版本，可下载任意旧版
关联：doc_relation 维护（对应物料/相关文档）
日志：doc_log 列表（谁在何时做了什么）
```

### 4.8 删除
- 仅「正常」状态且无检出记录可删除（软删除：doc_master.disable 或移回收站）
- 已归档/已废止 → 禁止直接删除（需先反归档/改状态）

---

## 5. 页面设计（参考截图）

### 5.1 DocPLM.php 文档工作区（主页面）
```
[搜索: 文件名称 / 后缀 / 编码 | 不显示已归档(可选)]
┌────────────────────────┬──────────────────────────────┐
│ 左侧目录树 doc-folder  │  [工具栏: 导入|属性|删除|升版|  │
│  ├ 主目录              │   浏览|下载|归档|废止|检出|借入] │
│  │ ├ WHTEST(项目)      │  [文件表格]                    │
│  │ │ └ 20260804        │  ☑序号 图标 状态 文件名称 后缀  │
│  │ └ 软件开发           │  编码 大小 所在目录 创建者 日志  │
│  │   └ 车间→1产线       │  操作                         │
│  └ [+新建目录][展开][折叠]│  [右侧竖向操作栏]             │
│                         │  属性/历史版本/批注/借用关系/  │
│                         │  变更记录/对应物料/相关文档     │
└────────────────────────┴──────────────────────────────┘
```
- 左侧树：复用 BOM 树样式（文件夹图标，新建目录/重命名/删除）
- 工具栏按钮：按分期显示（一期核心按钮，其余灰显）
- 文件表格列：**与截图一致**（checkbox/序号/图标/状态/文件名称/后缀/文件编码/大小/所在目录/创建者/备注/操作日志/操作）
- 右侧竖向操作栏：图标按钮（属性/历史版本/批注/借用关系/变更记录/对应物料/相关文档）

### 5.2 弹窗（lhgdialog，参考 BOMSetup 弹窗风格）
- 文档属性：字段编辑表单
- 历史版本：版本列表 + 下载
- 导入：上传表单（复用 5 行上传模式）
- 升版：新文件 + 备注

### 5.3 文档模板页（三期）
- 左侧：模板目录树（主目录 > APQP类表单模板 > 第一阶段~第五阶段）
- 右侧：模板列表 + 导入/下载
- 复用 doc_folder + doc_master(is_template=Y)

---

## 6. 与现有系统集成

| 复用点 | 说明 |
|---|---|
| upload2.class.php | 文件上传（SO/ 存储，扩展类型支持） |
| BOM 树 CSS/JS | 左侧目录树（.bom-tree 系列 + 虚线指引） |
| 三级菜单权限 | scripts + user_power + modulesallowed（新增 DocPLM.php 注册 + 「图文档」模块下加菜单项） |
| lhgdialog | 弹窗（属性/历史版本/导入） |
| prnMsg | 操作反馈 |
| DocFileCenter | 保留（物料图档），PLM 独立页面，入口都在「图文档」模块 |

---

## 7. 分期实施计划

### 一期（核心，建议先做）
| 功能 | 说明 |
|---|---|
| 目录树 | 新建/重命名/删除目录 + 展开折叠（复用 BOM 树） |
| 文档导入 | 选择目录 + 上传（多文件） |
| 文件列表 | 表格展示（checkbox/图标/状态/名称/后缀/编码/大小/目录/创建者/操作） |
| 下载 / 预览 | 图片灯箱 / PDF 新窗口（复用现有） |
| 删除 | 软删除（正常状态） |
| 版本管理 | 升版 + 历史版本弹窗 |
| 状态管理 | 归档 / 反归档 / 废止 |
| 操作日志 | doc_log 记录 + 展示 |
| 文档属性 | 弹窗查看/编辑 |
| 搜索 | 名称/后缀/编码过滤 |

### 二期（协同）
| 功能 | 说明 |
|---|---|
| 检出/检入 | 锁定 + 归还 |
| 借用 | 借阅记录 |
| 关联 | 文档↔物料、文档↔文档（对应物料/相关文档） |
| 回收站 | 删除的文件可恢复 |
| 变更记录 | 关联变更列表 |

### 三期（模板与高级）
| 功能 | 说明 |
|---|---|
| 模板库 | APQP 阶段树 + 模板管理（doc_master.is_template） |
| 审批/签发 | doc_approval（签发/变更审批流） |
| 批注 | 文档批注 |
| Excel/CAD 辅助 | excel字段导入 / CAD转PDF / CAD对比（需外部服务） |

---

## 8. 风险与注意点

1. **存储**：文件继续存 SO/ 目录（与现有附件一致），doc_file 只记路径
2. **软删除 vs 物理删除**：建议软删除（doc_master 状态标记），物理文件保留
3. **版本文件命名**：升版上传的文件同样 md5 命名，避免重名覆盖
4. **权限**：所有用户默认可访问（function_name='共用'），如需控制再按 user_power 细分
5. **scripts 表新增**：加新页面后**用户需重新登录**（PageSecurityArray 缓存，项目已知约定）
6. **与 DocFileCenter 关系**：两套并存，避免混淆（PLM 管目录文档，DocFileCenter 管物料图档）
7. **SQL 文档**：所有表创建 SQL + 回滚 SQL 写入开发文档（按项目约定）

---

## 9. 待确认决策（请用户确认）

- [ ] A. 与现有 DocFileCenter 的关系：**并存**（推荐）/ 替换 / 物料图档并入 PLM
- [ ] B. 一期范围：上面一期的功能是否够？（目录+导入+列表+下载+版本+归档+废止+日志+属性+搜索）
- [ ] C. 目录树：**纯文件夹树**（灵活，任意层级）（推荐）/ 固定"项目→阶段"结构
- [ ] D. 文档编码：手动填 + 自动建议（推荐）/ 纯手动 / 纯自动生成
- [ ] E. 是否要先做一期并尽快上线（推荐），二期三期按需追加
