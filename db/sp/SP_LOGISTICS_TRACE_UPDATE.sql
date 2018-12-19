DROP PROCEDURE IF EXISTS `SP_LOGISTICS_TRACE_UPDATE`;
DELIMITER $$
CREATE PROCEDURE `SP_LOGISTICS_TRACE_UPDATE`()
    SQL SECURITY INVOKER
    COMMENT '物流追踪更新'
MAIN_LABEL:BEGIN
	DECLARE V_NOT_FOUND,V_TradeID INT DEFAULT(0);
	DECLARE traces_cusor CURSOR FOR SELECT slt.trade_id FROM tmp_traces_no tmp,sales_logistics_trace slt WHERE tmp.error_info='' AND 
						tmp.logistics_type=slt.logistics_type AND tmp.logistics_no=slt.logistics_no AND slt.`logistics_status`=5;
	DECLARE CONTINUE HANDLER FOR NOT FOUND SET V_NOT_FOUND = 1;
	DECLARE EXIT HANDLER FOR SQLEXCEPTION BEGIN 
		ROLLBACK;
		SELECT '发生未知错误' error_info;
		/*RESIGNAL;*/
	END ;
	
	START TRANSACTION;
		-- 状态检查
		UPDATE tmp_traces_no SET error_info ='状态不正确' WHERE STATUS NOT IN(2,3,4) AND error_info='';
		
		-- 处理状态
		UPDATE tmp_traces_no tmp,sales_logistics_trace slt SET slt.logistics_status=CASE tmp.status WHEN 2 THEN 3 WHEN 3 THEN 5 WHEN 4 THEN IF(slt.logistics_status=7,7,6) ELSE slt.logistics_status END 
			WHERE tmp.error_info='' AND tmp.logistics_no=slt.logistics_no;
		
		-- 检查是否存在
		UPDATE tmp_traces_no tmp LEFT JOIN sales_logistics_trace slt ON (tmp.logistics_no=slt.logistics_no) SET tmp.error_info='物流单号不存在'
			WHERE tmp.error_info='' AND slt.logistics_no IS NULL;
		
		-- 插入明细
		INSERT IGNORE INTO sales_logistics_trace_detail(trace_id,accept_time,accept_station) SELECT slt.rec_id,ttd.accept_time,ttd.accept_station FROM tmp_traces_no tmp,sales_logistics_trace slt,tmp_traces_detail ttd 
			WHERE tmp.error_info='' AND tmp.logistics_no=slt.logistics_no AND slt.logistics_no=ttd.logistics_no;
		
		
		-- 更新最后状态变更时间
		UPDATE tmp_traces_no tmp,sales_logistics_trace slt,tmp_traces_detail ttd SET slt.logistics_time = ttd.accept_time 
			WHERE tmp.error_info='' AND tmp.logistics_no=slt.logistics_no AND slt.logistics_no=ttd.logistics_no AND ttd.accept_time>slt.logistics_time;
		
		-- 拒签又签收的，更新为已签收
		UPDATE tmp_traces_no tmp,sales_logistics_trace slt,sales_logistics_trace_detail sltd 
			SET slt.`logistics_status`=5,slt.logistics_time = IF(slt.logistics_time<sltd.accept_time,sltd.accept_time,slt.logistics_time) 
			WHERE tmp.error_info='' AND tmp.logistics_no=slt.logistics_no AND slt.`logistics_status`=6 
			AND slt.`rec_id`=sltd.`trace_id` AND sltd.`accept_station` LIKE '%已签收%';
		
		-- 更新揽件时间
		UPDATE tmp_traces_no tmp,sales_logistics_trace slt,sales_logistics_trace_detail sltd 
			SET slt.get_time = sltd.accept_time 
			WHERE tmp.error_info='' AND tmp.logistics_no=slt.logistics_no AND slt.get_time = '0000-00-00 00:00:00'
			AND slt.`rec_id`=sltd.`trace_id` AND sltd.`accept_station` LIKE '%揽件%';
		
	COMMIT;
	
	SELECT IFNULL(error_info,'') error_info FROM tmp_traces_no WHERE error_info<>'' LIMIT 1;
END$$

DELIMITER ;