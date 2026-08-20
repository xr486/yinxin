# BOMSetup.php 加载性能优化方案

## 一、问题现象

BOM 建立页面（`BOMSetup.php`）在左侧 BOM 结构树中点击任意节点切换右侧详情时，响应时间过长，肉眼可见卡顿。

## 二、根因分析

### 2.1 数据库层面：索引缺失导致全表扫描

`bom_lines_all` 表是 BOM 行表，数据量相对较大（约 1339 行），且页面中频繁执行以下查询：

1. **取某 BOM 头下所有有效子件**：
   ```sql
   SELECT ... FROM bom_lines_all
   WHERE bom_header_id = ? AND disable_date = 0
   ORDER BY item_num
   ```
2. **判断某物料是否为顶层母件（NOT IN 子查询）**：
   ```sql
   SELECT assembly_item_no FROM bom_headers_all
   WHERE assembly_item_no NOT IN (
       SELECT DISTINCT component_item FROM bom_lines_all WHERE disable_date = 0
   )
   ```
3. **原材料点击时查父 BOM 单层用量**：
   ```sql
   SELECT ... FROM bom_lines_all l
   JOIN bom_headers_all h ON h.bom_header_id = l.bom_header_id
   WHERE l.component_item = ? AND l.disable_date = 0
   ```

在优化前，`bom_lines_all` 表只有主键索引，没有 `bom_header_id`、`component_item`、`disable_date` 等字段的索引。通过 `EXPLAIN` 验证，上述查询均走全表扫描（`ALL`），每次都要扫描 1339 行。

### 2.2 页面层面：每次点击都整页重载

早期实现中，左侧 BOM 节点点击后跳转到带 `?view=...` 参数的完整 URL，浏览器会重新发起整页请求，服务端每次都执行：

- `include('includes/header.inc')`
- 顶层母件 `NOT IN` 子查询（全表扫描）
- 递归 `renderForest()` 生成整棵左侧 BOM 结构树
- 递归中大量 `latestHeader()`、`getItemInfo()`、`isRaw()` 等小查询

索引优化只能加速数据库查询，但无法避免“整页重载 + 重建左树”的冗余开销，因此用户反馈加索引后仍然卡顿。

## 三、优化手段一：数据库索引

### 3.1 改动内容

给 `bom_lines_all` 表新增 3 个索引，用于覆盖上述高频查询场景：

| 索引名 | 字段 | 适用场景 |
|--------|------|----------|
| `idx_header_active` | `(bom_header_id, disable_date, item_num)` | 按 BOM 头取子件行，并直接支持 `ORDER BY item_num` |
| `idx_component_active` | `(component_item, disable_date)` | 按子件编码反查父 BOM 头（原材料单层数据） |
| `idx_disable` | `(disable_date)` | 顶层母件 `NOT IN` 子查询过滤 `disable_date = 0` |

### 3.2 SQL 语句

```sql
-- 索引 1：按 bom_header_id 取子件行（覆盖 WHERE + ORDER BY）
ALTER TABLE `bom_lines_all`
  ADD INDEX `idx_header_active` (`bom_header_id`, `disable_date`, `item_num`);

-- 索引 2：按 component_item 反查父 BOM（覆盖原材料单层用量）
ALTER TABLE `bom_lines_all`
  ADD INDEX `idx_component_active` (`component_item`, `disable_date`);

-- 索引 3：按 disable_date 过滤（覆盖顶层母件 NOT IN 子查询）
ALTER TABLE `bom_lines_all`
  ADD INDEX `idx_disable` (`disable_date`);
```

### 3.3 验证

执行上述 SQL 后，用 `EXPLAIN` 检查前述三类查询，确认不再出现 `type = ALL`，扫描行数显著下降。

### 3.4 回滚

如需回滚索引，执行：

```sql
ALTER TABLE `bom_lines_all`
  DROP INDEX `idx_header_active`,
  DROP INDEX `idx_component_active`,
  DROP INDEX `idx_disable`;
```

## 四、优化手段二：AJAX 局部刷新

### 4.1 思路

将右侧面板（物料属性 + BOM 层级）抽成独立的渲染函数 `renderRightPanel($db, $view, $filterParent)`，并支持 AJAX 请求：当请求带 `?ajax=1` 时，只输出右侧 HTML 片段，不执行 `header.inc`、不生成左侧树、不走整页逻辑。

### 4.2 关键实现

**PHP 端（`BOMSetup.php`）**：

```php
$isAjax = (isset($_GET['ajax']) && $_GET['ajax'] === '1');

// ... 复用函数 renderRightPanel($db, $view, $filterParent)

if ($isAjax) {
    ob_end_clean();
    echo renderRightPanel($db, $view, $filterParent);
    exit;
}

// 非 AJAX 继续走完整页面，include header.inc + 渲染左侧树 + 右侧初始面板
include('includes/header.inc');
// ...
```

**JS 端（`BOMSetup.php` 内嵌脚本）**：

```javascript
$(document).on('click', '.bom-row', function(e){
    // 跳过折叠框、按钮、链接、选中复选框等
    if ($(e.target).closest('.tw, a, button, input, label').length) return;

    var a = $(this).data('assembly');
    var leaf = $(this).closest('.bom-node').hasClass('leaf-node');
    var p = leaf ? $(this).data('parent-assembly') || '' : '';

    sessionStorage.setItem('bom_active', a);
    sessionStorage.setItem('bom_scrollTop', $('#bomLeftBody').scrollTop());

    $('#bomRight').addClass('loading');
    $.get('?ajax=1&view=' + encodeURIComponent(a) + (p ? '&parent=' + encodeURIComponent(p) : ''), function(html){
        $('#bomRight').removeClass('loading').html(html);
        rebuildHierTr();
        applyColSettings();
        activateTab(activeTabTarget);
        history.replaceState({}, '', '?view=' + encodeURIComponent(a) + (p ? '&parent=' + encodeURIComponent(p) : ''));
    }).fail(function(){
        $('#bomRight').removeClass('loading');
        location.href = '?view=' + encodeURIComponent(a) + (p ? '&parent=' + encodeURIComponent(p) : '');
    });
});
```

**事件委托**：右侧按钮、Tab、展开/折叠、列设置、导出等事件全部改用 `$(document).on('click', ...)` 委托，AJAX 替换 `#bomRight` 内容后无需重新绑定。

### 4.3 效果

- 点击左侧节点时，仅刷新右侧 `#bomRight`，不重建左侧树，响应从“整页重载”变为“局部替换”。
- 地址栏通过 `history.replaceState` 同步更新，便于刷新后仍定位到当前节点。
- 列设置、展开/折叠等前端状态在替换后自动重建。

## 五、已废弃的无效尝试

- **全局预加载 `preloadBOMHeaders()`**：曾尝试页面启动时一次性查出所有母件最新 BOM 头缓存，但实际瓶颈在 `bom_lines_all`（1339 行），而 `bom_headers_all` 仅 102 行，预加载收益极小，已删除。

## 六、涉及文件

| 文件 | 改动 |
|------|------|
| `BOMSetup.php` | 新增 `renderRightPanel()`、AJAX 分支、事件委托、左侧节点点击改 `$.get` |
| 数据库 `bom_lines_all` | 新增 `idx_header_active`、`idx_component_active`、`idx_disable` 三个索引 |

## 七、后续约定

凡涉及数据库结构改动（索引、字段、表、外键等），必须在本项目文档中明确说明改动对象，并给出可执行 SQL 语句；便于版本回滚、迁移和多人协作。
