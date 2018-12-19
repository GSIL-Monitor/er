DROP PROCEDURE IF EXISTS `I_DL_DECIDE_WAREHOUSE`;
DELIMITER //
CREATE PROCEDURE `I_DL_DECIDE_WAREHOUSE`(
	OUT `P_WarehouseID` INT,
	INOUT `P_WmsType` INT,
	OUT `P_Locked` INT,
	IN `P_WarehouseNO` VARCHAR(40),
	IN `P_ShopID` INT,
	IN `P_Flag` INT,
	IN `P_ReceiverProvince` INT,
	IN `P_ReceiverCity` INT,
	IN `P_ReceiverDistrict` INT,
	IN `P_CheckStock` INT,
	OUT `P_WareHouseSelectLog` VARCHAR(256))
    SQL SECURITY INVOKER
    COMMENT '选择出货仓库'
MAIN_LABEL:BEGIN
	DECLARE V_NOT_FOUND,V_WarehouseID, V_WarehouseCount,V_WmsType,V_WmsSubType,V_OuterStock,V_OrderCount,V_Count INT DEFAULT(0);
	DECLARE V_LackAmount,V_MinLackAmount DECIMAL(19,4);
	DECLARE V_Path1,V_Path2,V_Path3 VARCHAR(40);
	DECLARE V_WarehouseProvince,V_WarehouseCity,V_WarehouseDistrict VARCHAR(50) DEFAULT'';

	DECLARE warehouse_cursor1 CURSOR FOR SELECT DISTINCT sw.warehouse_id,w.type,w.sub_type,w.is_outer_stock
		FROM cfg_shop_warehouse sw,cfg_warehouse_area wa,cfg_warehouse w
		WHERE sw.shop_id=P_ShopID AND sw.warehouse_id=wa.warehouse_id AND
			IF(P_WmsType>0,w.type=P_WmsType,w.type<(128+P_WmsType)) AND wa.warehouse_id=w.warehouse_id AND w.is_disabled=0 AND
			wa.path IN('',V_Path1,V_Path2,V_Path3)
		ORDER BY sw.priority DESC;

	DECLARE warehouse_cursor2 CURSOR FOR SELECT sw.warehouse_id,w.type,w.is_outer_stock
		FROM cfg_shop_warehouse sw LEFT JOIN cfg_warehouse w ON w.warehouse_id=sw.warehouse_id
		WHERE sw.shop_id=P_ShopID AND w.is_disabled=0 AND IF(P_WmsType>0,w.type=P_WmsType,w.type<(128+P_WmsType))
		ORDER BY sw.priority DESC;

	DECLARE warehouse_cursor3 CURSOR FOR SELECT cgw.warehouse_id,w.type,w.is_outer_stock
		FROM tmp_sales_trade_order tsto,cfg_goods_warehouse cgw,cfg_warehouse w
		WHERE cgw.spec_id=tsto.spec_id AND (cgw.shop_id=0 OR cgw.shop_id= P_ShopID) AND w.warehouse_id=cgw.warehouse_id AND
			w.is_disabled=0 AND IF(P_WmsType>0,w.type=P_WmsType,w.type<(128+P_WmsType)) AND actual_num >0
		ORDER BY cgw.priority DESC;

	DECLARE CONTINUE HANDLER FOR NOT FOUND SET V_NOT_FOUND = 1;

	-- P_WmsType>0, 精确匹配仓库类型
	-- P_WmsType=0, 任意仓库
	-- P_WmsType=-1, 除其它仓库(type<127)

	SET P_WarehouseID = 0;
	SET P_Locked = 0;

	-- 外部仓库
	IF (P_WarehouseNO IS NOT NULL AND P_WarehouseNO <> '') THEN
		SELECT warehouse_id,type,sub_type,is_outer_stock INTO P_WarehouseID,V_WmsType,V_WmsSubType,V_OuterStock
		FROM cfg_warehouse WHERE ext_warehouse_no=P_WarehouseNO AND is_disabled=0 AND
			IF(P_WmsType<=0,type<(128+P_WmsType),type=P_WmsType) AND
			(type<>127 OR sub_type=0) LIMIT 1;

		IF P_WarehouseID THEN
			SET P_WmsType=V_WmsType;
			SET P_Locked = 1;
		END IF;

		LEAVE MAIN_LABEL;
	END IF;

	-- 按货品选仓库
	SET V_MinLackAmount = -999999999;
	IF @cfg_sales_trade_warehouse_bygoods AND (SELECT @tmp_setwarebygoods:=is_setwarebygoods FROM cfg_shop WHERE shop_id = P_ShopID) AND
		EXISTS (SELECT 1 FROM tmp_sales_trade_order tsto,cfg_goods_warehouse cgw WHERE cgw.spec_id=tsto.spec_id AND (cgw.shop_id=0 OR cgw.shop_id = P_ShopID)) THEN

		IF @tmp_setwarebygoods=1 THEN
			OPEN warehouse_cursor3;
			WAREHOUSE_LABEL3: LOOP
				SET V_NOT_FOUND = 0;
				FETCH warehouse_cursor3 INTO V_WarehouseID,V_WmsType,V_OuterStock;
				IF V_NOT_FOUND THEN
					LEAVE WAREHOUSE_LABEL3;
				END IF;

				IF V_OuterStock THEN
					SELECT SUM(LEAST(IFNULL(GREATEST(ss.stock_num+ss.wms_stock_diff-ss.sending_num-ss.order_num,0),0)-tg.actual_num,0)*GREATEST(tg.share_price,0.0001))
						INTO V_LackAmount FROM
					(SELECT spec_id,AVG(share_price) share_price,SUM(actual_num) actual_num FROM tmp_sales_trade_order GROUP BY spec_id) tg
					LEFT JOIN stock_spec ss ON (ss.spec_id=tg.spec_id AND ss.warehouse_id=V_WarehouseID);
				ELSE
					SELECT SUM(LEAST(IFNULL(GREATEST(ss.stock_num-ss.sending_num-ss.order_num,0),0)-tg.actual_num,0)*GREATEST(tg.share_price,0.0001))
						INTO V_LackAmount FROM
					(SELECT spec_id,AVG(share_price) share_price,SUM(actual_num) actual_num FROM tmp_sales_trade_order GROUP BY spec_id) tg
					LEFT JOIN stock_spec ss ON (ss.spec_id=tg.spec_id AND ss.warehouse_id=V_WarehouseID);
				END IF;

				IF V_LackAmount>V_MinLackAmount THEN
					SET P_WmsType=V_WmsType;
					SET P_WarehouseID = V_WarehouseID;
					SET V_MinLackAmount = V_LackAmount;
				END IF;
			END LOOP;
			CLOSE warehouse_cursor3;

			IF P_WarehouseID THEN
				IF @cfg_chg_locked_warehouse_alert THEN
					SET P_Locked = 1;
				END IF;
				SET P_WareHouseSelectLog='按货品选择仓库';
				LEAVE MAIN_LABEL;
			END IF;
		ELSEIF @tmp_setwarebygoods=2 AND (P_Flag&1) THEN
			SELECT COUNT(1) INTO V_OrderCount FROM tmp_sales_trade_order WHERE actual_num>0;
			SELECT name INTO V_WarehouseProvince  FROM dict_province WHERE province_id = P_ReceiverProvince;
			IF V_WarehouseProvince <> ''THEN
				SELECT name INTO V_WarehouseCity FROM dict_city WHERE province_id =P_ReceiverProvince AND city_id = P_ReceiverCity;
				IF V_WarehouseCity<>'' THEN
					SELECT name INTO V_WarehouseDistrict FROM dict_district WHERE city_id = P_ReceiverCity AND district_id=P_ReceiverDistrict;
					IF V_WarehouseDistrict<>''THEN
						SELECT IFNULL(tl.warehouse_id,0),IFNULL(tl.type,0),COUNT(1) INTO P_WarehouseID,P_WmsType,V_Count
						FROM
						(
							SELECT cgw.warehouse_id,w.type
							FROM tmp_sales_trade_order tsto,cfg_goods_warehouse cgw,cfg_warehouse w
							WHERE tsto.actual_num>0 AND cgw.spec_id=tsto.spec_id AND cgw.shop_id IN(0,P_ShopID) AND
							w.is_disabled=0 AND IF(P_WmsType>0,w.type=0,w.type<(128+P_WmsType))  AND cgw.warehouse_id = w.warehouse_id AND
							w.province = V_WarehouseProvince AND w.city = V_WarehouseCity AND w.district = V_WarehouseDistrict
							GROUP BY cgw.warehouse_id
							HAVING COUNT(DISTINCT tsto.rec_id)=V_OrderCount
							ORDER BY cgw.priority DESC
						) tl ;

						IF V_Count >1 THEN
							SET P_WarehouseID = 0;
							SET P_Locked = 2;
							LEAVE MAIN_LABEL;
						ELSEIF V_Count=1 AND P_WarehouseID THEN
							SET P_Locked = 2;
							LEAVE MAIN_LABEL;
						END IF;
					END IF;
				END IF;
			END IF;
		END IF;

	END IF;

	-- 店铺使用仓库没有或者只有一个仓库时特殊处理
    SELECT COUNT(1) INTO V_WarehouseCount FROM cfg_shop_warehouse sw LEFT JOIN cfg_warehouse w ON w.warehouse_id=sw.warehouse_id
	WHERE sw.shop_id=P_ShopID AND w.is_disabled=0 AND IF(P_WmsType>0,w.type=P_WmsType,w.type<(128+P_WmsType));

	IF V_WarehouseCount=0 THEN -- 该店铺一个仓库都没有
		SELECT warehouse_id,type,sub_type,is_outer_stock INTO P_WarehouseID,P_WmsType,V_WmsSubType,V_OuterStock
		FROM cfg_warehouse
		WHERE is_disabled=0 AND IF(P_WmsType>0,type=P_WmsType,type<(128+P_WmsType)) LIMIT 1;
		SET P_WareHouseSelectLog=CONCAT("该店铺没有使用仓库，选择第一个仓库");
		-- 米氏抢单,将有库存的仓库保存到表中
		/*IF P_CheckStock AND P_WmsType=127 AND V_WmsSubType=1 THEN
			SET @tmp_warehouse_enough = P_WarehouseID;
		END IF;*/

		LEAVE MAIN_LABEL;
	ELSEIF V_WarehouseCount=1 THEN -- 只有一个仓库
		SELECT w.type,w.sub_type,w.warehouse_id,w.is_outer_stock INTO P_WmsType,V_WmsSubType,P_WarehouseID,V_OuterStock
		FROM cfg_shop_warehouse sw LEFT JOIN cfg_warehouse w ON w.warehouse_id=sw.warehouse_id
		WHERE sw.shop_id=P_ShopID AND w.is_disabled=0 AND IF(P_WmsType>0,w.type=P_WmsType,w.type<(128+P_WmsType));
		SET P_WareHouseSelectLog=CONCAT("该店铺只有一个仓库，选择此仓库");
		-- 米氏抢单,将有库存的仓库保存到表中
		/*IF P_CheckStock AND P_WmsType=127 AND V_WmsSubType=1 THEN
			SET @tmp_warehouse_enough = P_WarehouseID;
		END IF;*/

		LEAVE MAIN_LABEL;
	END IF;

	-- 按店铺使用仓库和仓库覆盖范围来选择仓库
	SET @tmp_warehouse_enough = NULL;

	SET V_Path1=CONCAT(P_ReceiverProvince,',');
	SET V_Path2=CONCAT(V_Path1,P_ReceiverCity,',');
	SET V_Path3=CONCAT(V_Path2,P_ReceiverDistrict,',');

	-- 在店铺使用的仓库中，存在仓库覆盖收货地址
	OPEN warehouse_cursor1;
	WAREHOUSE_LABEL1: LOOP
		SET V_NOT_FOUND = 0;
		FETCH warehouse_cursor1 INTO V_WarehouseID,V_WmsType,V_WmsSubType,V_OuterStock;
		IF V_NOT_FOUND THEN
			LEAVE WAREHOUSE_LABEL1;
		END IF;

		IF V_OuterStock THEN
			SELECT SUM(LEAST(IFNULL(GREATEST(ss.stock_num+ss.wms_stock_diff-ss.sending_num-ss.order_num,0),0)-tg.actual_num,0)*GREATEST(tg.share_price,0.0001))
				INTO V_LackAmount FROM
			(SELECT spec_id,AVG(share_price) share_price,SUM(actual_num) actual_num FROM tmp_sales_trade_order GROUP BY spec_id) tg
			LEFT JOIN stock_spec ss ON (ss.spec_id=tg.spec_id AND ss.warehouse_id=V_WarehouseID);
		ELSE
			SELECT SUM(LEAST(IFNULL(GREATEST(ss.stock_num-ss.sending_num-ss.order_num,0),0)-tg.actual_num,0)*GREATEST(tg.share_price,0.0001))
				INTO V_LackAmount FROM
			(SELECT spec_id,AVG(share_price) share_price,SUM(actual_num) actual_num FROM tmp_sales_trade_order GROUP BY spec_id) tg
			LEFT JOIN stock_spec ss ON (ss.spec_id=tg.spec_id AND ss.warehouse_id=V_WarehouseID);
		END IF;

		-- 使用外部库存,且要保证库存足够
		/*IF P_CheckStock AND V_LackAmount<0 THEN
			ITERATE WAREHOUSE_LABEL1;
		END IF;*/

		IF V_LackAmount>V_MinLackAmount THEN
			SET P_WmsType=V_WmsType;
			SET P_WarehouseID = V_WarehouseID;
			SET V_MinLackAmount = V_LackAmount;
			SET P_WareHouseSelectLog=CONCAT("该店铺下多个仓库覆盖收货地址，根据计算选择仓库");
		END IF;

		-- 米氏抢单,将有库存的仓库保存到表中
		/*IF P_CheckStock AND V_WmsType=127 AND V_WmsSubType=1 THEN
			SET @tmp_warehouse_enough = CONCAT_WS(',',@tmp_warehouse_enough,V_WarehouseID);
		END IF;*/
	END LOOP;
	CLOSE warehouse_cursor1;

	-- 在店铺使用的仓库中，并且收货地址未在仓库覆盖范围
	IF P_WarehouseID=0 THEN
		SET V_MinLackAmount = -999999999;

		OPEN warehouse_cursor2;
		WAREHOUSE_LABEL2: LOOP
			SET V_NOT_FOUND = 0;
			FETCH warehouse_cursor2 INTO V_WarehouseID,V_WmsType,V_OuterStock;
			IF V_NOT_FOUND THEN
				LEAVE WAREHOUSE_LABEL2;
			END IF;

			IF V_OuterStock THEN
				SELECT SUM(LEAST(IFNULL(GREATEST(ss.stock_num+ss.wms_stock_diff-ss.sending_num-ss.order_num,0),0)-tg.actual_num,0)*GREATEST(tg.share_price,0.0001))
					INTO V_LackAmount FROM
				(SELECT spec_id,AVG(share_price) share_price,SUM(actual_num) actual_num FROM tmp_sales_trade_order GROUP BY spec_id) tg
				LEFT JOIN stock_spec ss ON (ss.spec_id=tg.spec_id AND ss.warehouse_id=V_WarehouseID);
			ELSE
				SELECT SUM(LEAST(IFNULL(GREATEST(ss.stock_num-ss.sending_num-ss.order_num,0),0)-tg.actual_num,0)*GREATEST(tg.share_price,0.0001))
					INTO V_LackAmount FROM
				(SELECT spec_id,AVG(share_price) share_price,SUM(actual_num) actual_num FROM tmp_sales_trade_order GROUP BY spec_id) tg
				LEFT JOIN stock_spec ss ON (ss.spec_id=tg.spec_id AND ss.warehouse_id=V_WarehouseID);
			END IF;

			-- 使用外部库存,且要保证库存足够
			/*IF P_CheckStock AND V_LackAmount<0 THEN
				ITERATE WAREHOUSE_LABEL2;
			END IF;*/

			IF V_LackAmount>V_MinLackAmount THEN
				SET P_WmsType=V_WmsType;
				SET P_WarehouseID = V_WarehouseID;
				SET V_MinLackAmount = V_LackAmount;
				SET P_WareHouseSelectLog=CONCAT("该店铺下所有仓库都不覆盖收货地址，根据计算选择仓库");
			END IF;
		END LOOP;
		CLOSE warehouse_cursor2;
	END IF;

	-- 所有策略都不满足，给默认仓库
	IF P_WarehouseID=0 THEN
		SELECT warehouse_id,type,sub_type,is_outer_stock INTO P_WarehouseID,P_WmsType,V_WmsSubType,V_OuterStock FROM cfg_warehouse
		WHERE is_disabled=0 AND IF(P_WmsType>0,type=P_WmsType,type<(128+P_WmsType))
		ORDER BY is_outer_stock ASC LIMIT 1;
		SET P_WareHouseSelectLog=CONCAT("使用默认仓库");

		-- 米氏抢单,将有库存的仓库保存到表中
		/*IF P_CheckStock AND P_WmsType=127 AND V_WmsSubType=1 THEN
			SET @tmp_warehouse_enough = P_WarehouseID;
		END IF;*/
	END IF;

END//
DELIMITER ;