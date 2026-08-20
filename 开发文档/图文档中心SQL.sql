ALTER TABLE sf_item_no_file MODIFY file_name VARCHAR(100) DEFAULT NULL;
ALTER TABLE bom_routing_all_file MODIFY file_name VARCHAR(100) DEFAULT NULL;
ALTER TABLE bom_routing_public_file MODIFY file_name VARCHAR(100) DEFAULT NULL;

INSERT INTO scripts (script, pagesecurity, description, creation_date, created_by, function_name)
VALUES ('DocFileCenter.php', 1, '图文档中心', UNIX_TIMESTAMP(), 'admin', '共用');