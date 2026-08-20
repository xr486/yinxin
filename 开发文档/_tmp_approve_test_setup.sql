USE yixin;
INSERT INTO bom_routings_all (assembly_item_no, route_status, operation_seq_num, operation_code, creation_date, created_by, last_update_date, last_updated_by, rate, channeng, renli, remarks, approve_date, approved_by)
SELECT item_no, '待签核', 1, (SELECT operation_code FROM bom_parameters ORDER BY operation_id LIMIT 1), UNIX_TIMESTAMP(), 'TESTBOT', UNIX_TIMESTAMP(), 'TESTBOT', 0, '', '', '', 0, ''
FROM sf_item_no
WHERE item_type IN ('F','B') AND item_no NOT IN (SELECT DISTINCT assembly_item_no FROM bom_routings_all WHERE disable_date IS NULL)
LIMIT 1;
SELECT ROW_COUNT() AS inserted, (SELECT assembly_item_no FROM bom_routings_all WHERE created_by='TESTBOT' LIMIT 1) AS test_item;
