DROP PROCEDURE IF EXISTS `I_DL_DECIDE_LOGISTICS`;
DELIMITER //
CREATE PROCEDURE `I_DL_DECIDE_LOGISTICS`(OUT `P_LogisticsID` INT, 
		IN `P_LogisticsType` INT,
		IN `P_DeliveryTerm` INT, 
		IN `P_ShopID` INT,
		IN `P_WarehouseID` INT,
		IN `P_Weight` DECIMAL(19,4),
		IN `P_ReceiverCountry` INT, 
		IN `P_ReceiverProvince` INT,
		IN `P_ReceiverCity` INT, 
		IN `P_ReceiverDistrict` INT,
		IN `P_Addr` VARCHAR(256),
		IN `P_Paid` DECIMAL(19,4),
		OUT `P_LogisticsMatchLog` VARCHAR(256))
    SQL SECURITY INVOKER
    COMMENT '选择物流公司'
MAIN_LABEL:BEGIN
	DECLARE V_Tmp1,V_Tmp2,V_Tmp3,V_Tmp4 VARCHAR(40);
	DECLARE V_ExceptWords,V_ExceptWords2,V_ExceptWordsWeight,V_ExceptWordsWeight2,V_ExceptWordsWeight3 VARCHAR(8096);
	DECLARE V_Word VARCHAR(256);
	DECLARE V_ExceptLogisticsID,V_ExceptLogisticsID2,V_LogisticsType,V_I1, V_I2, V_Changed,V_WeightLogisticsId,V_WeightLogisticsId2,V_WeightLogisticsId3,
			V_ExceptWordsFlag,V_IsHadMatch,V_AmountLogisticsId,V_AmountLogisticsId2,V_AmountLogisticsId3 INT DEFAULT(0);
	DECLARE V_Weight1,V_Weight2,V_Weight3,V_PaidAmount,V_PaidAmount2,V_PaidAmount3 DECIMAL(19,4) DEFAULT '0.0000';
	
	SET P_LogisticsID = 0;
	-- 货到付款订单
	IF P_DeliveryTerm=2 THEN
		/*IF @cfg_logistics_match_mode=1 THEN -- 按仓库
			SELECT cod_logistics_id INTO P_LogisticsID FROM sys_warehouse WHERE warehouse_id=P_WarehouseID;
			IF P_LogisticsID THEN
				LEAVE MAIN_LABEL;
			END IF;
	
			SELECT cod_logistics_id INTO P_LogisticsID FROM sys_shop WHERE shop_id=P_ShopID;
			IF P_LogisticsID THEN
				LEAVE MAIN_LABEL;
			END IF;
		ELSE -- 按店铺
			SELECT cod_logistics_id INTO P_LogisticsID FROM sys_shop WHERE shop_id=P_ShopID;
			IF P_LogisticsID THEN
				LEAVE MAIN_LABEL;
			END IF;
			
			SELECT cod_logistics_id INTO P_LogisticsID FROM sys_warehouse WHERE warehouse_id=P_WarehouseID;
			IF P_LogisticsID THEN
				LEAVE MAIN_LABEL;
			END IF;
		END IF;
		
		CALL SP_UTILS_GET_CFG('default_cod_logistics_id', P_LogisticsID, 0); */
		-- IF P_LogisticsID=0  AND @cfg_sales_trade_decide_default_logistics = 0 THEN

		-- 选择
		IF @cfg_logistics_match_mode=1 THEN
			SELECT cod_logistics_id INTO P_LogisticsID FROM cfg_warehouse WHERE warehouse_id=P_WarehouseID;
		ELSE
			SELECT cod_logistics_id INTO P_LogisticsID FROM cfg_shop WHERE shop_id=P_ShopID;
		END IF;       

		IF P_LogisticsID=0 THEN
			SELECT logistics_id INTO P_LogisticsID FROM cfg_logistics 
			WHERE is_disabled=0 AND logistics_id>0 AND is_support_cod LIMIT 1;
		END IF;
		
		LEAVE MAIN_LABEL;
	END IF;
	
	-- 款到发货订单
	-- 看店铺设置
	SELECT logistics_id INTO P_LogisticsID FROM cfg_shop WHERE shop_id=P_ShopID;
	IF P_LogisticsID THEN
		SET P_LogisticsMatchLog="店铺使用物流选择物流公司";
		LEAVE MAIN_LABEL;
	END IF;
	
	-- 如果有按照店铺设置的策略，优先执行该策略
	-- SET @cfg_logistics_match_mode=2;
	SET V_Tmp1 = CONCAT(CASE @cfg_logistics_match_mode WHEN 0 THEN 0 WHEN 1 THEN P_WarehouseID WHEN 2 THEN P_ShopID END,'#');
	SET V_Tmp2 = CONCAT(V_Tmp1,',',P_ReceiverProvince);
	SET V_Tmp3 = CONCAT(V_Tmp2,',',P_ReceiverCity);
	SET V_Tmp4 = CONCAT(V_Tmp3,',',P_ReceiverDistrict);
	
	SELECT logistics_id,except_words,except_logistics_id,except_words2,except_logistics_id2,weight_logistics_id,weight_logistics_id2,weight_logistics_id3,
		paid_amount,paid_amount2,paid_amount3,amount_logistics_id,amount_logistics_id2,amount_logistics_id3,
		weight,weight2,weight3,except_words_weight,except_words_weight2,except_words_weight3
	INTO P_LogisticsID,V_ExceptWords,V_ExceptLogisticsID,V_ExceptWords2,V_ExceptLogisticsID2,V_WeightLogisticsId,V_WeightLogisticsId2,V_WeightLogisticsId3,
		V_PaidAmount,V_PaidAmount2,V_PaidAmount3,V_AmountLogisticsId,V_AmountLogisticsId2,V_AmountLogisticsId3,
		V_Weight1,V_Weight2,V_Weight3,V_ExceptWordsWeight,V_ExceptWordsWeight2,V_ExceptWordsWeight3
	FROM cfg_logistics_match FORCE INDEX(UK_cfg_logistics_match_path) 
	WHERE path in (V_Tmp1,V_Tmp2,V_Tmp3,V_Tmp4) ORDER BY `level` DESC LIMIT 1;
	-- 按照店铺设置全国范围
	IF P_LogisticsID=0 THEN 
		SET V_Tmp1 = CONCAT(CASE @cfg_logistics_match_mode WHEN 0 THEN 0 WHEN 1 THEN P_WarehouseID WHEN 2 THEN P_ShopID END,'#,0');

		SELECT logistics_id,except_words,except_logistics_id,except_words2,except_logistics_id2,weight_logistics_id,weight_logistics_id2,weight_logistics_id3,
			paid_amount,paid_amount2,paid_amount3,amount_logistics_id,amount_logistics_id2,amount_logistics_id3,
			weight,weight2,weight3,except_words_weight,except_words_weight2,except_words_weight3
		INTO P_LogisticsID,V_ExceptWords,V_ExceptLogisticsID,V_ExceptWords2,V_ExceptLogisticsID2,V_WeightLogisticsId,V_WeightLogisticsId2,V_WeightLogisticsId3,
			V_PaidAmount,V_PaidAmount2,V_PaidAmount3,V_AmountLogisticsId,V_AmountLogisticsId2,V_AmountLogisticsId3,
			V_Weight1,V_Weight2,V_Weight3,V_ExceptWordsWeight,V_ExceptWordsWeight2,V_ExceptWordsWeight3
		FROM cfg_logistics_match FORCE INDEX(UK_cfg_logistics_match_path) 
		WHERE path in (V_Tmp1,V_Tmp2,V_Tmp3,V_Tmp4) ORDER BY `level` DESC LIMIT 1;
	END IF;
    
	IF P_LogisticsID=0 THEN 
		SET V_Tmp1 = '0#';
		SET V_Tmp2 = CONCAT(V_Tmp1,',',P_ReceiverProvince);
		SET V_Tmp3 = CONCAT(V_Tmp2,',',P_ReceiverCity);
		SET V_Tmp4 = CONCAT(V_Tmp3,',',P_ReceiverDistrict);
		SET V_Tmp1 = '0#,0';
	
		SELECT logistics_id,except_words,except_logistics_id,except_words2,except_logistics_id2,weight_logistics_id,weight_logistics_id2,weight_logistics_id3,
			paid_amount,paid_amount2,paid_amount3,amount_logistics_id,amount_logistics_id2,amount_logistics_id3,
			weight,weight2,weight3,except_words_weight,except_words_weight2,except_words_weight3
		INTO P_LogisticsID,V_ExceptWords,V_ExceptLogisticsID,V_ExceptWords2,V_ExceptLogisticsID2,V_WeightLogisticsId,V_WeightLogisticsId2,V_WeightLogisticsId3,
			V_PaidAmount,V_PaidAmount2,V_PaidAmount3,V_AmountLogisticsId,V_AmountLogisticsId2,V_AmountLogisticsId3,
			V_Weight1,V_Weight2,V_Weight3,V_ExceptWordsWeight,V_ExceptWordsWeight2,V_ExceptWordsWeight3
		FROM cfg_logistics_match FORCE INDEX(UK_cfg_logistics_match_path) 
		WHERE path in (V_Tmp1,V_Tmp2,V_Tmp3,V_Tmp4) ORDER BY `level` DESC LIMIT 1;

	END IF;
	SET P_LogisticsMatchLog=CONCAT("使用默认物流公司");
	-- 一级不到处理
	IF V_ExceptWords IS NOT NULL AND V_ExceptWords<>'' AND V_ExceptLogisticsID THEN
		SET V_I1 = 1;
		WORD_LABEL:LOOP
			SET V_I2 = LOCATE(',', V_ExceptWords, V_I1);
			IF V_I2 = 0 THEN
			   SET V_Word = TRIM(SUBSTRING(V_ExceptWords, V_I1));
			ELSE
			   SET V_Word = TRIM(SUBSTRING(V_ExceptWords, V_I1, V_I2 - V_I1));
			END IF;
			 
			IF V_Word IS NOT NULL AND V_Word <> '' THEN
				IF LOCATE(V_Word, P_Addr, 1) > 0 THEN
					SET P_LogisticsID = V_ExceptLogisticsID;
					SET V_Changed = 1;
					LEAVE WORD_LABEL;
				END IF;
			END IF;
			 
			IF V_I2 = 0 OR V_I2 IS NULL THEN
			   LEAVE WORD_LABEL;
			END IF;
			
			SET V_I1 = V_I2 + 1;
		END LOOP;
		
	END IF;
	
	-- 二级不到处理
	IF V_Changed AND V_ExceptWords2 IS NOT NULL AND V_ExceptWords2<>'' AND V_ExceptLogisticsID2 THEN
		SET V_I1 = 1;
		WORD_LABEL:LOOP
			SET V_I2 = LOCATE(',', V_ExceptWords2, V_I1);
			IF V_I2 = 0 THEN
			   SET V_Word = TRIM(SUBSTRING(V_ExceptWords2, V_I1));
			ELSE
			   SET V_Word = TRIM(SUBSTRING(V_ExceptWords2, V_I1, V_I2 - V_I1));
			END IF;
			 
			IF V_Word IS NOT NULL AND V_Word <> '' THEN
				IF LOCATE(V_Word, P_Addr, 1) > 0 THEN
					SET P_LogisticsID = V_ExceptLogisticsID2;
					LEAVE WORD_LABEL;
				END IF;
			END IF;
			 
			IF V_I2 = 0 OR V_I2 IS NULL THEN
			   LEAVE WORD_LABEL;
			END IF;
			
			SET V_I1 = V_I2 + 1;
		END LOOP;
	END IF;
	
	SET  V_IsHadMatch =0;
	-- 通过金额来计算物流/重量和金额共用一个配置
	-- IF @cfg_calc_logistics_by_weight=1 AND P_Paid>=0 THEN
	IF P_Paid>=0 THEN
		IF V_PaidAmount3>0.0 AND V_AmountLogisticsId3>0 AND P_Paid >= V_PaidAmount3 THEN
				SET P_LogisticsMatchLog=CONCAT("已付金额满",V_PaidAmount3,"元，确定物流公司");
				SET P_LogisticsID=V_AmountLogisticsId3;
				SET  V_IsHadMatch =1;
		ELSEIF V_PaidAmount2>0.0 AND V_AmountLogisticsId2 >0 AND P_Paid >= V_PaidAmount2 THEN
				SET P_LogisticsMatchLog=CONCAT("已付金额满",V_PaidAmount2,"元，确定物流公司");
				SET P_LogisticsID=V_AmountLogisticsId2;
				SET  V_IsHadMatch =1;
		ELSEIF  V_PaidAmount>0.0 AND V_AmountLogisticsId >0 AND P_Paid >= V_PaidAmount  THEN
				SET P_LogisticsMatchLog=CONCAT("已付金额满",V_PaidAmount,"元，确定物流公司");
				SET P_LogisticsID=V_AmountLogisticsId;
				SET  V_IsHadMatch = 1;
		END IF;
	END IF;
	-- 重量匹配物流
	-- IF @cfg_calc_logistics_by_weight=1 AND V_Weight1>0.0 AND V_WeightLogisticsId AND V_IsHadMatch=0 THEN
	IF V_Weight1>0.0 AND V_WeightLogisticsId AND V_IsHadMatch=0 THEN
		IF P_Weight<=V_Weight1 THEN
		
			SET V_ExceptWordsFlag=1;
			IF V_ExceptWordsWeight IS NOT NULL AND V_ExceptWordsWeight<>''  THEN
				SET V_I1 = 1;
				WORD_LABEL:LOOP
					SET V_I2 = LOCATE(',', V_ExceptWordsWeight, V_I1);
					IF V_I2 = 0 THEN
						SET V_Word = TRIM(SUBSTRING(V_ExceptWordsWeight, V_I1));
					ELSE
						SET V_Word = TRIM(SUBSTRING(V_ExceptWordsWeight, V_I1, V_I2 - V_I1));
					END IF;
					IF V_Word IS NOT NULL AND V_Word <> '' THEN
						IF LOCATE(V_Word, P_Addr, 1) > 0 THEN
							SET V_ExceptWordsFlag =0;
							LEAVE WORD_LABEL;
						END IF;
					END IF;
					IF V_I2 = 0 OR V_I2 IS NULL THEN
						LEAVE WORD_LABEL;
					END IF;	
					SET V_I1 = V_I2 + 1;
				END LOOP;	
			END IF;
			
			IF V_ExceptWordsFlag = 1 THEN
				SET P_LogisticsMatchLog=CONCAT("订单重量不超过",V_Weight1,"千克，确定物流公司");
				SET P_LogisticsID=V_WeightLogisticsId;
			END IF;
		ELSEIF V_Weight2>0.0 AND V_WeightLogisticsId2 THEN
			IF P_Weight<=V_Weight2 THEN
				SET V_ExceptWordsFlag=1;
				IF V_ExceptWordsWeight2 IS NOT NULL AND V_ExceptWordsWeight2<>''  THEN
					SET V_I1 = 1;
					WORD_LABEL:LOOP
						SET V_I2 = LOCATE(',', V_ExceptWordsWeight2, V_I1);
						IF V_I2 = 0 THEN
							SET V_Word = TRIM(SUBSTRING(V_ExceptWordsWeight2, V_I1));
						ELSE
							SET V_Word = TRIM(SUBSTRING(V_ExceptWordsWeight2, V_I1, V_I2 - V_I1));
						END IF;
						IF V_Word IS NOT NULL AND V_Word <> '' THEN
							IF LOCATE(V_Word, P_Addr, 1) > 0 THEN
								SET V_ExceptWordsFlag =0;
								LEAVE WORD_LABEL;
							END IF;
						END IF;
						IF V_I2 = 0 OR V_I2 IS NULL THEN
							LEAVE WORD_LABEL;
						END IF;	
						SET V_I1 = V_I2 + 1;
					END LOOP;	
				END IF;
				
				IF V_ExceptWordsFlag = 1 THEN
					SET P_LogisticsMatchLog=CONCAT("订单重量不超过",V_Weight2,"千克，确定物流公司");
					SET P_LogisticsID=V_WeightLogisticsId2;
				END IF;			
			ELSEIF V_Weight3>0.0 AND V_WeightLogisticsId3 AND P_Weight<=V_Weight3 THEN
				SET V_ExceptWordsFlag=1;
				IF V_ExceptWordsWeight3 IS NOT NULL AND V_ExceptWordsWeight3<>''  THEN
					SET V_I1 = 1;
					WORD_LABEL:LOOP
						SET V_I2 = LOCATE(',', V_ExceptWordsWeight3, V_I1);
						IF V_I2 = 0 THEN
							SET V_Word = TRIM(SUBSTRING(V_ExceptWordsWeight3, V_I1));
						ELSE
							SET V_Word = TRIM(SUBSTRING(V_ExceptWordsWeight3, V_I1, V_I2 - V_I1));
						END IF;
						IF V_Word IS NOT NULL AND V_Word <> '' THEN
							IF LOCATE(V_Word, P_Addr, 1) > 0 THEN
								SET V_ExceptWordsFlag =0;
								LEAVE WORD_LABEL;
							END IF;
						END IF;
						IF V_I2 = 0 OR V_I2 IS NULL THEN
							LEAVE WORD_LABEL;
						END IF;	
						SET V_I1 = V_I2 + 1;
					END LOOP;	
				END IF;
				
				IF V_ExceptWordsFlag = 1 THEN
					SET P_LogisticsMatchLog=CONCAT("订单重量不超过",V_Weight3,"千克，确定物流公司");
					SET P_LogisticsID=V_WeightLogisticsId3;
				END IF;					
			END IF;
		END IF;
	END IF;
	IF P_LogisticsID THEN
		IF P_LogisticsType<>-1 THEN -- 判断物流类别
			SELECT logistics_type INTO V_LogisticsType FROM cfg_logistics WHERE logistics_id=P_LogisticsID;
			IF V_LogisticsType=P_LogisticsType THEN
				LEAVE MAIN_LABEL;
			END IF;
		ELSE
			LEAVE MAIN_LABEL;
		END IF;
	END IF;
	/* IF @cfg_sales_trade_decide_default_logistics AND P_LogisticsType=-1 THEN
		LEAVE MAIN_LABEL;
	END IF; */
	SET P_LogisticsMatchLog=CONCAT("系统默认分配物流公司");
	SELECT logistics_id INTO P_LogisticsID FROM cfg_logistics WHERE is_disabled=0 AND (P_LogisticsType=-1 OR logistics_type=P_LogisticsType) AND logistics_id>0 ORDER BY priority LIMIT 1;
END//
DELIMITER ;