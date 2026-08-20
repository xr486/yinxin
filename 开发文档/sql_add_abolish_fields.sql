ALTER TABLE doc_master ADD COLUMN abolish_date int(11) DEFAULT NULL AFTER last_update_date;
ALTER TABLE doc_master ADD COLUMN abolish_remark varchar(300) DEFAULT NULL AFTER abolish_date;
