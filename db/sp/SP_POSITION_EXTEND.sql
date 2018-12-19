DROP PROCEDURE IF EXISTS `SP_POSITION_EXTEND`;
DELIMITER //
CREATE PROCEDURE `SP_POSITION_EXTEND`()
    SQL SECURITY INVOKER
    COMMENT '多货位添加,升级'
BEGIN
	-- 初始化仓库新建是应该添加的默认货位

	INSERT INTO cfg_warehouse_zone(`type`,`warehouse_id`,`zone_no`,`name`,`is_disabled` ,`created` ) 
		SELECT 1,warehouse_id,'ZC','暂存',0,NOW() FROM cfg_warehouse cw
		ON DUPLICATE KEY UPDATE `name` = VALUES(`name`),is_disabled=VALUES(is_disabled);
	
	INSERT INTO cfg_warehouse_position(`rec_id`,`warehouse_id`,`zone_id`,`position_no`,`is_disabled`,`created`)
		SELECT -cw.warehouse_id,cw.warehouse_id,cwz.zone_id,'ZANCUN',0,NOW() FROM cfg_warehouse cw LEFT JOIN cfg_warehouse_zone cwz ON cw.warehouse_id = cwz.warehouse_id
		ON DUPLICATE KEY UPDATE is_disabled=VALUES(is_disabled);
  
  -- 升级库存中的入库默认货位
	UPDATE stock_spec ss SET ss.last_position_id = -ss.warehouse_id ;
  
  -- 升级库存表信息中的库存到默认的库存货位表里面  stock_spec_position
	INSERT INTO stock_spec_position(`warehouse_id` ,`spec_id` ,`position_id` ,`last_inout_time` ,`last_pd_time` ,`stock_num`)
		SELECT ss.warehouse_id ,ss.spec_id ,-ss.warehouse_id ,NOW(),NOW(),ss.stock_num  FROM stock_spec ss 
		ON DUPLICATE KEY UPDATE last_inout_time=VALUES(last_inout_time ), last_pd_time=VALUES(last_pd_time ),stock_num =VALUES(stock_num) ;
END//
DELIMITER ;