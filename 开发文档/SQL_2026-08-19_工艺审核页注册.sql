USE yixin;
INSERT INTO scripts (script, pagesecurity, description, function_name) VALUES ('CraftRouteApprove.php', 1, '产品工艺审核', '共用');
INSERT INTO user_power (user_id, function_name, use_flag) VALUES ('admin', '产品工艺审核', 1);
