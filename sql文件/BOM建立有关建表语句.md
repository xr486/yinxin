`-- 表的结构 bom_lines_all`
--

`CREATE TABLE IF NOT EXISTS bom_lines_all (`
  `bom_header_id int(11) DEFAULT NULL,`
  `assembly_item_no varchar(100) NOT NULL,`
  `operation_seq_num varchar(11) DEFAULT '1',`
  `component_item varchar(100) DEFAULT NULL,`
  `item_num int(11) NOT NULL,`
  `component_quantity float NOT NULL,`
  `sunhao_rate double DEFAULT NULL,`
  `component_remarks varchar(480) DEFAULT NULL,`
  `effectivity_date int(11) DEFAULT NULL,`
  `change_notice varchar(20) DEFAULT NULL,`
  `creation_date int(11) DEFAULT NULL,`
  `created_by varchar(20) DEFAULT NULL,`
  `disable_date int(11) DEFAULT '0',`
  `last_update_date int(11) DEFAULT NULL,`
  `last_updated_by varchar(20) DEFAULT NULL,`
  `component_sequence_id int(11) NOT NULL,`
  `weizhi varchar(2000) DEFAULT NULL`
`) ENGINE=MyISAM AUTO_INCREMENT=1694 DEFAULT CHARSET=utf8;`

-- 转存表中的数据 `bom_lines_all`（部分样例数据）
--

INSERT INTO `bom_lines_all` (`bom_header_id`, `assembly_item_no`, `operation_seq_num`, `component_item`, `item_num`, `component_quantity`, `sunhao_rate`, `component_remarks`, `effectivity_date`, `change_notice`, `creation_date`, `created_by`, `disable_date`, `last_update_date`, `last_updated_by`, `component_sequence_id`, `weizhi`) VALUES
(11, '32000400', '1', '20504100', 18, 0.12, 0, '', 1723704085, NULL, 1723704085, 'user15', 0, 1731291901, 'user15', 144, ''),
(11, '32000400', '1', '20502600', 17, 3, 0, '', 1723704085, NULL, 1723704085, 'user15', 0, 1731291901, 'user15', 143, ''),
(11, '32000400', '1', '20502500', 16, 1, 0, '', 1723704085, NULL, 1723704085, 'user15', 0, 1731291901, 'user15', 142, ''),
(11, '32000400', '1', '20502400', 15, 1, 0, '', 1723704085, NULL, 1723704085, 'user15', 0, 1731291901, 'user15', 141, ''),
(11, '32000400', '1', '20502300', 14, 1, 0, '', 1723704085, NULL, 1723704085, 'user15', 0, 1731291901, 'user15', 140, ''),
(11, '32000400', '1', '20501800', 13, 1, 0, '', 1723704085, NULL, 1723704085, 'user15', 0, 1731291901, 'user15', 139, ''),
(11, '32000400', '1', '20501700', 12, 2, 0, '', 1723704085, NULL, 1723704085, 'user15', 0, 1731291901, 'user15', 138, ''),
(11, '32000400', '1', '20501600', 11, 1, 0, '', 1723704085, NULL, 1723704085, 'user15', 0, 1731291901, 'user15', 137, ''),
(11, '32000400', '1', '20501500', 10, 3, 0, '', 1723704085, NULL, 1723704085, 'user15', 0, 1731291901, 'user15', 136, ''),



`-- 表的结构 bom_lines_all`
--

`CREATE TABLE IF NOT EXISTS bom_lines_all (`
  `bom_header_id int(11) DEFAULT NULL,`
  `assembly_item_no varchar(100) NOT NULL,`
  `operation_seq_num varchar(11) DEFAULT '1',`
  `component_item varchar(100) DEFAULT NULL,`
  `item_num int(11) NOT NULL,`
  `component_quantity float NOT NULL,`
  `sunhao_rate double DEFAULT NULL,`
  `component_remarks varchar(480) DEFAULT NULL,`
  `effectivity_date int(11) DEFAULT NULL,`
  `change_notice varchar(20) DEFAULT NULL,`
  `creation_date int(11) DEFAULT NULL,`
  `created_by varchar(20) DEFAULT NULL,`
  `disable_date int(11) DEFAULT '0',`
  `last_update_date int(11) DEFAULT NULL,`
  `last_updated_by varchar(20) DEFAULT NULL,`
  `component_sequence_id int(11) NOT NULL,`
  `weizhi varchar(2000) DEFAULT NULL`
`) ENGINE=MyISAM AUTO_INCREMENT=1694 DEFAULT CHARSET=utf8;`

-- 转存表中的数据 `bom_lines_all`（部分样例数据）
--

INSERT INTO `bom_lines_all` (`bom_header_id`, `assembly_item_no`, `operation_seq_num`, `component_item`, `item_num`, `component_quantity`, `sunhao_rate`, `component_remarks`, `effectivity_date`, `change_notice`, `creation_date`, `created_by`, `disable_date`, `last_update_date`, `last_updated_by`, `component_sequence_id`, `weizhi`) VALUES
(11, '32000400', '1', '20504100', 18, 0.12, 0, '', 1723704085, NULL, 1723704085, 'user15', 0, 1731291901, 'user15', 144, ''),
(11, '32000400', '1', '20502600', 17, 3, 0, '', 1723704085, NULL, 1723704085, 'user15', 0, 1731291901, 'user15', 143, ''),
(11, '32000400', '1', '20502500', 16, 1, 0, '', 1723704085, NULL, 1723704085, 'user15', 0, 1731291901, 'user15', 142, ''),
(11, '32000400', '1', '20502400', 15, 1, 0, '', 1723704085, NULL, 1723704085, 'user15', 0, 1731291901, 'user15', 141, ''),
(11, '32000400', '1', '20502300', 14, 1, 0, '', 1723704085, NULL, 1723704085, 'user15', 0, 1731291901, 'user15', 140, ''),
(11, '32000400', '1', '20501800', 13, 1, 0, '', 1723704085, NULL, 1723704085, 'user15', 0, 1731291901, 'user15', 139, ''),
(11, '32000400', '1', '20501700', 12, 2, 0, '', 1723704085, NULL, 1723704085, 'user15', 0, 1731291901, 'user15', 138, ''),
(11, '32000400', '1', '20501600', 11, 1, 0, '', 1723704085, NULL, 1723704085, 'user15', 0, 1731291901, 'user15', 137, ''),
(11, '32000400', '1', '20501500', 10, 3, 0, '', 1723704085, NULL, 1723704085, 'user15', 0, 1731291901, 'user15', 136, ''),
(11, '32000400', '1', '20501400', 9, 1, 0, '', 1723704085, NULL, 1723704085, 'user15', 0, 1731291901, 'user15', 135, ''),



`-- 表的结构 sf_item_no`
--

`CREATE TABLE IF NOT EXISTS sf_item_no (`
  `item_id int(10) NOT NULL,`
  `item_no varchar(60) NOT NULL DEFAULT '',`
  `item_name varchar(200) DEFAULT NULL,`
  `item_desc varchar(265) DEFAULT NULL,`
  `item_status varchar(20) DEFAULT '已签核' COMMENT '默认已签核，页面太多不确定哪个用0814',`
  `gongyi varchar(200) DEFAULT NULL COMMENT '工艺',`
  `units varchar(10) DEFAULT NULL,`
  `item_category1 varchar(255) DEFAULT NULL,`
  `min_order double(10,2) DEFAULT '0.00',`
  `unit_price float(10,2) DEFAULT NULL,`
  `safe_qty int(10) DEFAULT NULL,`
  `disable_flag varchar(255) DEFAULT 'Y',`
  `pic_path varchar(100) DEFAULT NULL,`
  `effective_date int(11) DEFAULT NULL,`
  `creation_date int(11) DEFAULT NULL,`
  `created_by varchar(30) DEFAULT NULL,`
  `last_update_date int(11) DEFAULT NULL,`
  `last_updated_by varchar(30) DEFAULT NULL,`
  `huohao varchar(200) DEFAULT NULL,`
  `manufacture_time varchar(5) DEFAULT NULL,`
  `lead_time int(5) DEFAULT NULL,`
  `franchise_price float(10,2) DEFAULT NULL,`
  `po_price float(10,2) DEFAULT NULL,`
  `zhidao_price float(20,2) DEFAULT NULL,`
  `item_type varchar(2) DEFAULT NULL,`
  `item_use varchar(20) DEFAULT NULL,`
  `min_qty varchar(30) DEFAULT NULL,`
  `max_qty varchar(30) DEFAULT NULL,`
  `so_flag varchar(2) NOT NULL DEFAULT 'Y',`
  `sub_code varchar(20) DEFAULT NULL,`
  `sub_locator varchar(20) DEFAULT NULL,`
  `conditions varchar(20) DEFAULT 'N' COMMENT '是否启用保存条件',`
  `wendu varchar(255) DEFAULT NULL,`
  `light varchar(10) DEFAULT NULL,`
  `shidu varchar(255) DEFAULT NULL,`
  `suoding_flag varchar(2) DEFAULT 'N' COMMENT '是否锁定',`
  `suoding_remark varchar(100) DEFAULT NULL COMMENT '锁定备注',`
  `item_remark varchar(200) DEFAULT NULL,`
  `youxiaoqi int(20) DEFAULT '0',`
  `project_name varchar(200) NOT NULL COMMENT '项目名称',`
  `inspect_flag varchar(20) NOT NULL DEFAULT 'Y' COMMENT '是否检验'`
`) ENGINE=MyISAM AUTO_INCREMENT=1662 DEFAULT CHARSET=utf8;`

-- 转存表中的数据 `sf_item_no`（部分样例数据）
--

INSERT INTO `sf_item_no` (`item_id`, `item_no`, `item_name`, `item_desc`, `item_status`, `gongyi`, `units`, `item_category1`, `min_order`, `unit_price`, `safe_qty`, `disable_flag`, `pic_path`, `effective_date`, `creation_date`, `created_by`, `last_update_date`, `last_updated_by`, `huohao`, `manufacture_time`, `lead_time`, `franchise_price`, `po_price`, `zhidao_price`, `item_type`, `item_use`, `min_qty`, `max_qty`, `so_flag`, `sub_code`, `sub_locator`, `conditions`, `wendu`, `light`, `shidu`, `suoding_flag`, `suoding_remark`, `item_remark`, `youxiaoqi`, `project_name`, `inspect_flag`) VALUES
(448, '20501600', '料号名称', '料号规格', '已签核', NULL, 'PCS', '标准件', 0.00, NULL, 0, 'Y', '', NULL, 1723700281, 'admin', 1726730327, 'user15', '', '0', 0, NULL, 0.00, NULL, 'M', 'S', NULL, NULL, 'N', '仪器原材料仓', '', 'N', '', '', '', '', '', '', 1825, '', 'Y'),
(447, '20501700', '料号名称', '料号规格', '已签核', NULL, 'PCS', '标准件', 0.00, NULL, 0, 'Y', '', NULL, 1723700281, 'admin', 1726730372, 'user15', '', '0', 0, NULL, 0.00, NULL, 'M', 'S', NULL, NULL, 'N', '仪器原材料仓', '', 'N', '', '', '', '', '', '', 1825, '', 'Y'),
(446, '20501800', '料号名称', '料号规格', '已签核', NULL, 'PCS', '标准件', 0.00, NULL, 0, 'Y', '', NULL, 1723700281, 'admin', 1726730397, 'user15', '', '0', 0, NULL, 0.00, NULL, 'M', 'S', NULL, NULL, 'N', '仪器原材料仓', '', 'N', '', '', '', '', '', '', 1825, '', 'Y'),
(445, '20502300', '料号名称', '料号规格', '已签核', NULL, 'PCS', '标准件', 0.00, NULL, 0, 'Y', '', NULL, 1723700281, 'admin', 1726731240, 'user15', '', '0', 0, NULL, 0.00, NULL, 'M', 'S', NULL, NULL, 'N', '仪器原材料仓', '', 'N', '', '', '', '', '', '', 1825, '', 'Y'),
(444, '20502400', '料号名称', '料号规格', '已签核', NULL, 'PCS', '标准件', 0.00, NULL, 0, 'Y', '', NULL, 1723700281, 'admin', 1726731260, 'user15', '', '0', 0, NULL, 0.00, NULL, 'M', 'S', NULL, NULL, 'N', '仪器原材料仓', '', 'N', '', '', '', '', '', '', 1825, '', 'Y'),
(443, '20502500', '料号名称', '料号规格', '已签核', NULL, 'PCS', '标准件', 0.00, NULL, 0, 'Y', '', NULL, 1723700281, 'admin', 1726731290, 'user15', '', '0', 0, NULL, 0.00, NULL, 'M', 'S', NULL, NULL, 'N', '仪器原材料仓', '', 'N', '', '', '', '', '', '', 1825, '', 'Y'),

![image-20260807104820974](C:\Users\asus\AppData\Roaming\Typora\typora-user-images\image-20260807104820974.png)