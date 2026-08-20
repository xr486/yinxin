-- ====================================================================
-- 工艺管理模块 - 权限配置 SQL（2026-08-18 v1）
-- 作用：
--   1) scripts 表新增 4 个页面条目
--   2) user_power 给核心用户开 4 个菜单授权
--   3) www_users.modulesallowed 扩展为 14 位，第 6 位=1（Tech）
-- 注意：执行后所有用户必须重新登录才能看到新菜单和权限
-- ====================================================================

-- --------------------------------------------------------------------
-- 1) scripts 表：注册 4 个 PHP 页面（已执行，重复运行会报主键冲突）
-- --------------------------------------------------------------------
-- INSERT INTO `scripts` (`script`, `pagesecurity`, `description`, `function_name`) VALUES
-- ('ProcessCategory.php',          1, '工艺类型',     '工艺类型'),
-- ('Process.php',                  1, '工序',         '工序'),
-- ('CraftRoute.php',               1, '工艺路线',     '工艺路线'),
-- ('CraftRouteTemplate.php',       1, '工艺模板',     '工艺模板');

-- --------------------------------------------------------------------
-- 2) www_users.modulesallowed：扩展为 14 位，第 6 位=1（Tech 模块可见）
--    现状：13 位，'1,1,1,1,1,1,1,1,1,1,1,1,1,'  (26 字符)
--    加 Tech 在第 6 位（Doc 与 WIP 之间，即 5 个逗号后）
--    每个元素占 2 字符 '1,'，前 5 元素共 10 字符
-- --------------------------------------------------------------------
UPDATE `www_users`
   SET `modulesallowed` = CONCAT(
       SUBSTRING(`modulesallowed`, 1, 10),   -- 前 5 元素 "1,1,1,1,1,"
       '1,',                                 -- 第 6 元素 Tech=1
       SUBSTRING(`modulesallowed`, 11)        -- 后续 8 元素
   )
 WHERE LENGTH(`modulesallowed`) = 26;        -- 只处理 13 位的旧用户

-- 兜底：把 modulesallowed 长度仍为 26（说明未成功扩展）的再处理一次
-- （极端情况：旧值已含异常字符时跳过）

-- --------------------------------------------------------------------
-- 3) user_power：给所有现有 user_id 开工艺模块 4 项菜单授权
--    www_users 主键列名是 userid（非 user_id）
-- --------------------------------------------------------------------
INSERT INTO `user_power` (`user_id`, `function_name`, `model_name`, `use_flag`, `created_by`, `creation_date`)
SELECT DISTINCT u.userid, '工艺类型', '工艺', '1', 'admin', UNIX_TIMESTAMP()
  FROM `www_users` u
 WHERE u.userid <> ''
   AND NOT EXISTS (SELECT 1 FROM `user_power` p
                    WHERE p.user_id = u.userid AND p.function_name = '工艺类型');

INSERT INTO `user_power` (`user_id`, `function_name`, `model_name`, `use_flag`, `created_by`, `creation_date`)
SELECT DISTINCT u.userid, '工序', '工艺', '1', 'admin', UNIX_TIMESTAMP()
  FROM `www_users` u
 WHERE u.userid <> ''
   AND NOT EXISTS (SELECT 1 FROM `user_power` p
                    WHERE p.user_id = u.userid AND p.function_name = '工序');

INSERT INTO `user_power` (`user_id`, `function_name`, `model_name`, `use_flag`, `created_by`, `creation_date`)
SELECT DISTINCT u.userid, '工艺路线', '工艺', '1', 'admin', UNIX_TIMESTAMP()
  FROM `www_users` u
 WHERE u.userid <> ''
   AND NOT EXISTS (SELECT 1 FROM `user_power` p
                    WHERE p.user_id = u.userid AND p.function_name = '工艺路线');

INSERT INTO `user_power` (`user_id`, `function_name`, `model_name`, `use_flag`, `created_by`, `creation_date`)
SELECT DISTINCT u.userid, '工艺模板', '工艺', '1', 'admin', UNIX_TIMESTAMP()
  FROM `www_users` u
 WHERE u.userid <> ''
   AND NOT EXISTS (SELECT 1 FROM `user_power` p
                    WHERE p.user_id = u.userid AND p.function_name = '工艺模板');
