
DROP PROCEDURE IF EXISTS `I_CRM_SMS_RECORD_INSERT_TRIGGER`;
DELIMITER //
CREATE PROCEDURE `I_CRM_SMS_RECORD_INSERT_TRIGGER`(IN P_EventType INT, IN P_OrderId INT, IN P_OperatorId INT)
    SQL SECURITY INVOKER
    COMMENT '事件触发的消息发送'
MAIN_LABEL: BEGIN
	DECLARE V_NotFound,V_SmsNum,V_SmsLength,V_Template,V_DelayTime,V_ShopId,V_IsSplit,V_Exists,V_SplitFromTradeID INT DEFAULT 0;
	DECLARE V_TemplateContent VARCHAR(1024);
	DECLARE V_Mobile,V_OriginalNo,V_RepairCreated,V_RepairNo,V_RepairConclution VARCHAR(256) DEFAULT '';
	DECLARE V_BatchNo,V_Temp,V_NickName,V_Name,V_ShopName,V_LogisticsName,V_LogisticsNO,V_SendTime,
		V_TradeTime,V_PaidTime,V_LogisticsType,V_LimitTime VARCHAR(64) DEFAULT '';
	
	DECLARE V_CodAmount,V_Fee,V_Receivable,V_Discount,V_PreWeight DECIMAL(19,4) DEFAULT 0.0;
	DECLARE V_SmsSendTime DATETIME;
			
	DECLARE sms_rule_cursor CURSOR
		FOR SELECT cssr.template_id,cssr.delay_time 
		FROM cfg_sms_send_rule cssr 
		WHERE cssr.shop_id = V_ShopId AND cssr.event_type = P_EventType AND is_disabled=0;
	
	DECLARE CONTINUE HANDLER FOR NOT FOUND SET V_NotFound = 1;
	-- 触发事件Id(1发货 2退货入库 3换货发出 4催未付款 5保修单审核 6保修单入库 7保修单发货 8签收)
	IF P_EventType<5 OR P_EventType=7 OR P_EventType=8  THEN 
	
		-- 获取客户信息
		SELECT st.buyer_nick,st.receiver_name,TRIM(st.receiver_mobile),st.receivable,ss.shop_name,cl.logistics_name,st.logistics_no,st.trade_time,
			st.discount,st.cod_amount,st.post_amount,st.src_tids,st.weight,st.shop_id,NOW(),st.pay_time,dl.logistics_name,st.split_from_trade_id
		INTO V_NickName,V_Name,V_Mobile,V_Receivable,V_ShopName,V_LogisticsName,V_LogisticsNO,V_TradeTime,V_Discount,V_CodAmount,V_Fee,
			V_OriginalNo,V_PreWeight,V_ShopId,V_SendTime,V_PaidTime,V_LogisticsType,V_SplitFromTradeID
		FROM sales_trade st
			LEFT JOIN crm_customer cc USING (customer_id)
			LEFT JOIN sys_shop ss ON ss.shop_id = st.shop_id
			LEFT JOIN cfg_logistics cl ON cl.logistics_id = st.logistics_id
			LEFT JOIN dict_logistics dl ON dl.logistics_type = cl.logistics_type
			WHERE st.trade_id = P_OrderId;
		
		IF V_Mobile = '' THEN
			LEAVE MAIN_LABEL;
		END IF;
	END IF;
	IF P_EventType=5 OR P_EventType=6 THEN
		-- 获取客户信息
		SELECT nickname,`name`,TRIM(from_mobile),ss.shop_name,sr.created,sr.shop_id,repair_no
			INTO V_NickName,V_Name,V_Mobile,V_ShopName,V_RepairCreated,V_ShopId,V_RepairNo	
			FROM sales_repair sr
			LEFT JOIN sys_shop ss ON ss.shop_id=sr.shop_id
			LEFT JOIN crm_customer cc ON cc.customer_id=sr.customer_id
			WHERE sr.repair_id= P_OrderId;
	END IF;
	IF P_EventType=7 THEN
		SELECT sr.created,repair_no,cor.title
			INTO V_RepairCreated,V_RepairNo,V_RepairConclution
			FROM sales_repair sr LEFT JOIN cfg_oper_reason cor ON cor.reason_id=sr.repair_conclution
			WHERE sr.out_trade_id=P_OrderId;
	END IF;
	
	-- 获取发送短信的配置(一个手机号一段时间发送一次)
	CALL SP_UTILS_GET_CFG2('crm_member_send_sms_limit_time', @cfg_crm_member_send_sms_limit_time, '0');
	SET @cfg_crm_member_send_sms_limit_time=CAST(@cfg_crm_member_send_sms_limit_time AS DECIMAL(19,4));
	
	OPEN sms_rule_cursor;
	SET V_NotFound = 0;
	SMS_RULE_LABEL: LOOP
		SET V_NotFound=0;
		FETCH sms_rule_cursor INTO V_Template,V_DelayTime;
		IF V_NotFound THEN
			LEAVE SMS_RULE_LABEL;
		END IF;
		
		-- get sms content
		SELECT content,is_split INTO V_TemplateContent,V_IsSplit FROM cfg_sms_template WHERE rec_id = V_Template;
		IF V_IsSplit=2 AND V_SplitFromTradeID =0 THEN
			ITERATE SMS_RULE_LABEL;
		END IF;
		
		-- 检查电话号码合法性
		-- SET V_Mobile = FN_CHECK_TEL(V_Mobile);
		
		-- replace the macro of message
		
		SELECT REPLACE(V_TemplateContent,'{.客户网名.}',V_NickName) INTO V_TemplateContent;
		SELECT REPLACE(V_TemplateContent,'{.原始单号.}',V_OriginalNo) INTO V_TemplateContent;
		SELECT REPLACE(V_TemplateContent,'{.客户姓名.}',V_Name) INTO V_TemplateContent;
		SELECT REPLACE(V_TemplateContent,'{.店铺名称.}',V_ShopName) INTO V_TemplateContent;
		SELECT REPLACE(V_TemplateContent,'{.货运方式.}',V_LogisticsName) INTO V_TemplateContent;
		SELECT REPLACE(V_TemplateContent,'{.物流单号.}',V_LogisticsNO) INTO V_TemplateContent;
		SELECT REPLACE(V_TemplateContent,'{.发货时间.}',V_SendTime) INTO V_TemplateContent;
		SELECT REPLACE(V_TemplateContent,'{.下单时间.}',V_TradeTime) INTO V_TemplateContent;
		SELECT REPLACE(V_TemplateContent,'{.应收邮资.}',V_Fee) INTO V_TemplateContent;
		SELECT REPLACE(V_TemplateContent,'{.应收金额.}',V_Receivable) INTO V_TemplateContent;
		SELECT REPLACE(V_TemplateContent,'{.优惠金额.}',V_Discount) INTO V_TemplateContent;
		SELECT REPLACE(V_TemplateContent,'{.COD金额.}',V_CodAmount) INTO V_TemplateContent;
		SELECT REPLACE(V_TemplateContent,'{.收款时间.}',V_PaidTime) INTO V_TemplateContent;
		SELECT REPLACE(V_TemplateContent,'{.预估重量.}',V_PreWeight) INTO V_TemplateContent;
		SELECT REPLACE(V_TemplateContent,'{.物流类型.}',V_LogisticsType) INTO V_TemplateContent;
		SELECT REPLACE(V_TemplateContent,'{.保修单号.}',V_RepairNo) INTO V_TemplateContent;
		SELECT REPLACE(V_TemplateContent,'{.保修单创建时间.}',V_RepairCreated) INTO V_TemplateContent;
		SELECT REPLACE(V_TemplateContent,'{.保修结束语.}',V_RepairConclution) INTO V_TemplateContent;
		-- SELECT REPLACE(V_TemplateContent, '{.称重重量.}', V_Weight) INTO V_TemplateContent;

		SET V_NotFound = 0;
		IF V_TemplateContent IS NULL THEN
			ITERATE SMS_RULE_LABEL;
		END IF;

		-- get sms amount;
		SET V_SmsLength = CHAR_LENGTH(V_TemplateContent);
		SET V_SmsNum = CEIL(V_SmsLength / 66);
		-- 获取短信发送--批次号
		SELECT FN_SYS_NO('SMS') INTO V_BatchNO;
		-- 插入短信内容以及短信相关状态到  ‘短信记录表中’
		
		-- 发送时间确定
		SET V_SmsSendTime = DATE_ADD(NOW(),INTERVAL V_DelayTime MINUTE); 
		
		-- 校验是否满足发短信条件
		IF @cfg_crm_member_send_sms_limit_time >0 THEN
			SET V_LimitTime = DATE_SUB(V_SmsSendTime,INTERVAL @cfg_crm_member_send_sms_limit_time MINUTE); 
			IF EXISTS(SELECT 1 FROM crm_sms_record WHERE (STATUS=0 OR STATUS = 1 OR STATUS = 2) AND 
			(timer_time>=V_LimitTime AND timer_time< V_SmsSendTime) AND phone_num=1 AND phones=V_Mobile) THEN
				ITERATE SMS_RULE_LABEL;
			END IF;
		END IF;

		
		INSERT INTO crm_sms_record(`status`,sms_type,send_type,operator_id,phones,phone_num,message,timer_time,send_time,batch_no,pre_count,try_times,error_msg,created) 
		VALUES(0,1,1,P_OperatorId,V_Mobile,1,V_TemplateContent,V_SmsSendTime,0,V_BatchNo,V_SmsNum,0,'',NOW());
		-- 防止发送短信了的再次发生
		IF P_EventType=8  AND EXISTS(SELECT 1 FROM sales_trade WHERE trade_id= P_OrderId AND NOT(trade_mask&256) ) THEN
			UPDATE sales_trade SET trade_mask = trade_mask|256 WHERE trade_id= P_OrderId;
		END IF;
	END LOOP;
	CLOSE sms_rule_cursor;
END//
DELIMITER ;
