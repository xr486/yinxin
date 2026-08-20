-- BOM 菜单改名后补充 user_power 权限
-- 背景：菜单 Caption 从 "BOM建立" 改为 "BOM管理"，
-- 但三级菜单显示需要 user_power.function_name = 菜单 Caption 且 use_flag=1，
-- 因此给原本拥有 "BOM建立" 权限的用户同步补一条 "BOM管理"。

-- 正向：给所有原本有 BOM建立 权限的用户追加 BOM管理
INSERT INTO user_power (function_name, user_id, use_flag)
SELECT 'BOM管理', user_id, 1
FROM user_power
WHERE function_name='BOM建立' AND use_flag=1
  AND user_id NOT IN (
    SELECT user_id FROM user_power WHERE function_name='BOM管理' AND use_flag=1
  );

-- 回滚：删除刚加的 BOM管理 权限
-- DELETE FROM user_power WHERE function_name='BOM管理' AND use_flag=1;
