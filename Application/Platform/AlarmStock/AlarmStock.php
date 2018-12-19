<?php

function refreshAlarmStock($sid,$db,$operator_id=0)
{
    try{
        /*
         * tmp_alarmstock_day   记录库存里面单品刷新的具体方式
         * tmp_alarmstock_incrate     记录2*cycl_day之前的天数的货品销售数量  和  之前cycle_day天的销售数量 并计算销售增长率
         * tmp_alarmstock_incrate_date  记录动态前面统计增长率的时候的时间点
         * */
//            $warehouse_list = D('Setting/Warehouse')->field('warehouse_id')->where(array('is_disabled'=>0))->order('warehouse_id asc')->select();
        //-------调试注释
        $is_open_alarm = getSysCfg($db,'purchase_alarmstock_open','0');
        $alarm_day = getSysCfg($db,'stock_lastcalc_alarmday','1970-07-01');
        if(strtotime($alarm_day) == strtotime(date('Y-m-d',time())) && $operator_id ==0)
        {
            releaseDb($db);
            logx("已经进行过自动刷新", $sid . "/AlarmStock");
            return array('status'=>1,'info'=>'已经进行过自动刷新');;
        }
        if(!$is_open_alarm){
            releaseDb($db);
            logx("请在系统设置中开启库存预警", $sid . "/AlarmStock");
            return array('status'=>1,'info'=>'请在系统设置中开启库存预警');
        }
        $db->execute("CREATE TEMPORARY TABLE IF NOT EXISTS tmp_alarmstock_day(
                                  spec_id INT(11),
                                  warehouse_id INT(11),
                                  alarm_days INT(11) DEFAULT 0,
                                  sales_rate DECIMAL(19,4) DEFAULT '1.0',
                                  sales_fixrate DECIMAL(19,4) DEFAULT '1.0',
                                  sales_rate_cycle DECIMAL(19,0) DEFAULT '0',
                                  sales_rate_type INT(11) DEFAULT 0,
                                  UNIQUE INDEX `IDX_tmp_stockspec` (`spec_id`,`warehouse_id`),
                                  INDEX `IDX_tmp_sales_rate_type` (`sales_rate_type`)
                            );");
        $db->execute(" DELETE FROM tmp_alarmstock_day;");
        $db->execute("CREATE TEMPORARY TABLE IF NOT EXISTS tmp_alarmstock_incrate(
                              spec_id INT(11),
                              warehouse_id INT(11),
                              count_old DECIMAL(19,4) DEFAULT '0.0000',
                              count_new DECIMAL(19,4) DEFAULT '0.0000',
                              sales_rate DECIMAL(19,4) DEFAULT '0.0000',
                              UNIQUE INDEX `IDX_tmp_stockspec` (`spec_id`,`warehouse_id`)
                        );");
        $db->execute("DELETE FROM tmp_alarmstock_incrate;");
        $db->execute("CREATE TEMPORARY TABLE IF NOT EXISTS tmp_alarmstock_incrate_date(
                             spec_id INT(11),
                             warehouse_id INT(11),
                             sales_rate_cycle DECIMAL(19,4) DEFAULT '0',
                             sales_rate_time1 VARCHAR(10) DEFAULT '0000-00-00',
                             sales_rate_time2 VARCHAR(10) DEFAULT '0000-00-00',
                             UNIQUE INDEX `IDX_tmp_stockspec` (`spec_id`,`warehouse_id`)
                        );");
        $db->execute("DELETE FROM tmp_alarmstock_incrate_date");
        $db->execute("CREATE TEMPORARY TABLE IF NOT EXISTS tmp_stock_spec_log(
                                  rec_id bigint(20) NOT NULL ,
                                  alarm_days INT(11) DEFAULT 0,
                                  operator_id INT(11) DEFAULT 0,
                                  operator_type INT(11) DEFAULT 0,
                                  sales_rate DECIMAL(19,4) DEFAULT '1.0',
                                  stock_num DECIMAL(19,4) DEFAULT '0.0000',
                                  sales_fixrate DECIMAL(19,4) DEFAULT '1.0',
                                  sales_rate_cycle DECIMAL(19,0) DEFAULT '0',
                                  sales_rate_type INT(11) DEFAULT 0,
                                  `message` varchar(1024) NOT NULL DEFAULT '' COMMENT '操作日志',
                                  PRIMARY KEY (`rec_id`),
                                  INDEX `IDX_tmp_sales_rate_type` (`sales_rate_type`)
                        );");
        $db->execute("DELETE FROM tmp_stock_spec_log;");
        $db->execute("INSERT INTO tmp_stock_spec_log(rec_id,operator_id,operator_type,alarm_days,sales_rate_type,sales_fixrate,sales_rate_cycle,sales_rate,stock_num)
                            SELECT rec_id,'{$operator_id}' operator_id,1 operator_type,alarm_days,sales_rate_type,sales_fixrate,sales_rate_cycle,sales_rate,stock_num FROM stock_spec WHERE (alarm_type&1 OR alarm_type&4 OR alarm_type=0) AND alarm_type&2 =0
              ");
        $str_log = '新建临时表';
        $db->execute("BEGIN");
        $last_alarm_date = getSysCfg($db,'stock_lastcalc_alarmtime','1970-07-01 00:00:00');
        $last_alarm_time = date('Y-m-d',strtotime($last_alarm_date));

        $current_date = date('Y-m-d H:i:s',time());
        $current_time = date('Y-m-d',time());
        if(strtotime($current_date) - strtotime($last_alarm_date) <  60*60)
        {
            releaseDb($db);
            logx("当前库存警戒为最新,不需要刷新,请间隔一小时后再刷新", $sid . "/AlarmStock");
            return array('status'=>1,'info'=>'当前库存警戒为最新,不需要刷新,请间隔一小时后再刷新');
        }
        //获取设置表里的该设置值，如果没有则给0 （当前该设置值不会为0）
        $purchase_rate_type = getSysCfg($db,'purchase_rate_type',0);
        if(!$purchase_rate_type)
        {
            releaseDb($db);
            logx("未配置全局销售增长率的计算方式", $sid . "/AlarmStock");
            return array('status'=>1,'info'=>'未配置全局销售增长率的计算方式');
        }
        $fix_inc_rate = 0.0;
        $rate_map = array('1'=>'固定销售增长率','2'=>'动态销售增长计算周期');
        $purchase_fixrate_value = getSysCfg($db,'purchase_fixrate_value',0);
        $purchase_rate_cycle = getSysCfg($db,'purchase_rate_cycle',0);
        switch ($purchase_rate_type)
        {
            case 1://固定销售增长率
            {

                if(!$purchase_rate_type)
                {
                    releaseDb($db);
                    logx("未配置全局固定月销售增长率", $sid . "/AlarmStock");
                    return array('status'=>1,'info'=>'未配置全局固定月销售增长率');
                }
                $fix_inc_rate = pow($purchase_fixrate_value,1/30);
                break;
            }
            case 2:
            {
                //$purchase_rate_cycle = D('Setting/System')->where(array('key'=>'purchase_rate_cycle'))->getField('value');

                if(!$purchase_rate_cycle)
                {
                    releaseDb($db);
                    logx("未配置全局动态销售周期", $sid . "/AlarmStock");
                    return array('status'=>1,'info'=>'未配置全局动态销售周期');
                }
                if($purchase_rate_cycle<1 || $purchase_rate_cycle >360)
                {
                    releaseDb($db);
                    logx("计算周期应该在一年内", $sid . "/AlarmStock");
                    return array('status'=>1,'info'=>'计算周期应该在一年内');
                }
                break;
            }
        }
        $alarm_stock_days = getSysCfg($db,'alarm_stock_days',0);
        if(!$alarm_stock_days)
        {
            releaseDb($db);
            logx("未配置全局警戒天数", $sid . "/AlarmStock");
            return array('status'=>1,'info'=>'未配置全局警戒天数');
        }
        $before_current_time =  date('Y-m-d',strtotime('-1 day',strtotime($current_time)));
        //------------记录库存使用的增长方式
        $str_log = '记录库存使用的增长方式';
        $db->execute("INSERT IGNORE INTO tmp_alarmstock_day(spec_id,warehouse_id,alarm_days,sales_rate_cycle,sales_rate_type,sales_fixrate)
			SELECT spec_id,warehouse_id,alarm_days,sales_rate_cycle,sales_rate_type,sales_fixrate
			FROM stock_spec
			WHERE (alarm_type&4 ) and alarm_type&2 <>1 GROUP by spec_id,warehouse_id");
        $res_max = $db->query_result("SELECT MAX(alarm_days) max_alarm_days FROM tmp_alarmstock_day;");
        $max_alarm_days = $res_max['max_alarm_days'];
        $min_start_date = date('Y-m-d',strtotime("-{$max_alarm_days} day",strtotime($before_current_time)));
        //-----------更新使用全局预警天数的库存
        $db->execute("INSERT IGNORE INTO tmp_alarmstock_day(spec_id,warehouse_id,alarm_days,sales_rate_cycle,sales_rate_type,sales_fixrate)
			SELECT spec_id,warehouse_id,'{$alarm_stock_days}',sales_rate_cycle,sales_rate_type,sales_fixrate
			FROM stock_spec
			WHERE (alarm_type&1 OR alarm_type=0) and alarm_type&2 <>1;");
        //----------更新动态增长率计算方式

        $str_log = '更新动态增长率计算方式';
        $db->execute("	UPDATE  tmp_alarmstock_day SET sales_rate_type = {$purchase_rate_type}, sales_rate_cycle = {$purchase_rate_cycle},sales_fixrate = {$purchase_fixrate_value} WHERE sales_rate_type = 0;");
        // ---移动到使用全局的下面来更新增长率
        $db->execute("UPDATE tmp_alarmstock_day SET sales_rate = POWER(sales_fixrate,1/30) WHERE sales_rate_type = 1;");
        $db->execute("	UPDATE  tmp_alarmstock_day SET alarm_days = {$alarm_stock_days} WHERE alarm_days <=0;");
        $db->execute("INSERT INTO tmp_alarmstock_incrate_date(spec_id,warehouse_id,sales_rate_cycle)
		                    SELECT spec_id,warehouse_id,sales_rate_cycle
		                    FROM tmp_alarmstock_day WHERE sales_rate_type = 2;");
        $db->execute("UPDATE tmp_alarmstock_incrate_date SET sales_rate_time1 = DATE_SUB('{$before_current_time}',INTERVAL sales_rate_cycle DAY),
	                        sales_rate_time2 = DATE_SUB('{$before_current_time}',INTERVAL sales_rate_cycle*2 DAY);");
        $db->execute("UPDATE tmp_alarmstock_incrate_date td
                            LEFT JOIN (SELECT MIN(sales_date) sales_date,spec_id,warehouse_id FROM stat_daily_sales_spec_warehouse  GROUP BY spec_id,warehouse_id ) sspd 
                            ON td.spec_id = sspd.spec_id and td.warehouse_id = sspd.warehouse_id
                            SET td.sales_rate_time1 = IF(td.sales_rate_time1 >= sspd.sales_date,td.sales_rate_time1,sspd.sales_date),
                            td.sales_rate_time2 = IF(td.sales_rate_time2 >= sspd.sales_date,td.sales_rate_time2, sspd.sales_date);
                            ");
        //--------------统计前两个周期内的数量
        $str_log = '统计前两个周期内的数量';
        $db->execute("INSERT INTO tmp_alarmstock_incrate(spec_id,warehouse_id,count_old)
                            SELECT td.spec_id,td.warehouse_id,IFNULL(SUM(num-refund_num),0) AS count_old
                            FROM tmp_alarmstock_incrate_date td
                            LEFT JOIN stat_daily_sales_spec_warehouse sspd ON td.spec_id = sspd.spec_id and td.warehouse_id = sspd.warehouse_id
                            WHERE sspd.sales_date BETWEEN td.sales_rate_time2 AND DATE_ADD(td.sales_rate_time2, INTERVAL td.sales_rate_cycle DAY)
                            GROUP BY td.spec_id,td.warehouse_id
                            ON DUPLICATE KEY UPDATE count_old = VALUES(count_old);");
        $db->execute("INSERT INTO tmp_alarmstock_incrate(spec_id,warehouse_id,count_new)
                            SELECT td.spec_id,td.warehouse_id,IFNULL(SUM(num-refund_num),0) AS count_new
                            FROM tmp_alarmstock_incrate_date td
                            LEFT JOIN stat_daily_sales_spec_warehouse sspd ON td.spec_id = sspd.spec_id and td.warehouse_id = sspd.warehouse_id
                            WHERE sspd.sales_date BETWEEN td.sales_rate_time1 AND '{$before_current_time}'
                            GROUP BY td.spec_id,td.warehouse_id
                            ON DUPLICATE KEY UPDATE count_new = VALUES(count_new);");
        //------------更新这一周期的增长率
        $str_log = '更新这一周期的增长率';
        $db->execute("UPDATE tmp_alarmstock_incrate ti,tmp_alarmstock_incrate_date td 
                            SET ti.sales_rate = POWER((1.0*ti.count_new)/ti.count_old,1.0/td.sales_rate_cycle) 
                            WHERE ti.count_old <> 0 AND ti.spec_id = td.spec_id AND ti.warehouse_id = td.warehouse_id;
                            ");
        //------------- 之后如果最出一段时间的销量为0的话将增长率设为1
        $str_log = '之后如果最出一段时间的销量为0的话将增长率设为1';
        $db->execute("	UPDATE tmp_alarmstock_incrate SET sales_rate = 1 WHERE count_old = 0 AND sales_rate = 0;");
        //--------------- 将增长率更新到tmp_alarmstock_day中
        $str_log = '将增长率更新到tmp_alarmstock_day中';
        $db->execute("UPDATE tmp_alarmstock_day td,tmp_alarmstock_incrate ti
                            SET td.sales_rate = ti.sales_rate
                            WHERE td.spec_id = ti.spec_id AND td.warehouse_id = ti.warehouse_id;");// AND td.warehouse_id = ti.warehouse_id
        //-------------刷新stock_spec
        $str_log = '刷新stock_spec';
        $db->execute("INSERT INTO stock_spec(spec_id,warehouse_id,alarm_days,sales_rate,sales_fixrate,sales_rate_cycle,sales_rate_type,safe_stock)
                            SELECT td.spec_id,td.warehouse_id,td.alarm_days,td.sales_rate,td.sales_fixrate,td.sales_rate_cycle,td.sales_rate_type,
                            CEILING(CAST((IFNULL(SUM(sspd.num-sspd.refund_num),0))*POWER(td.sales_rate,td.alarm_days) AS DECIMAL(19,4))) safe_stock
                            FROM tmp_alarmstock_day td
                            LEFT JOIN stat_daily_sales_spec_warehouse  sspd ON td.spec_id = sspd.spec_id and td.warehouse_id = sspd.warehouse_id
                            AND sspd.sales_date BETWEEN DATE_SUB('{$before_current_time}',INTERVAL td.alarm_days-1 DAY) AND '$before_current_time' 
                            AND sspd.sales_date > '{$min_start_date}'
                            GROUP BY spec_id,warehouse_id
                            ON DUPLICATE KEY UPDATE 
                            stock_spec.alarm_days = VALUES(alarm_days),
                            stock_spec.sales_rate = VALUES(sales_rate),
                            stock_spec.sales_fixrate = VALUES(sales_fixrate),
                            stock_spec.sales_rate_cycle = VALUES(sales_rate_cycle),
                            stock_spec.safe_stock =VALUES(safe_stock);");
        $db->execute("INSERT INTO tmp_stock_spec_log(rec_id,operator_id,operator_type,alarm_days,sales_rate_type,sales_fixrate,sales_rate_cycle,sales_rate)
                            SELECT rec_id,'{$operator_id}' operator_id,1 operator_type,alarm_days,sales_rate_type,sales_fixrate,sales_rate_cycle,sales_rate FROM stock_spec where (alarm_type&1 or alarm_type&4 or alarm_type = 0) and alarm_type&2 =0
                            ON DUPLICATE KEY UPDATE 
                            tmp_stock_spec_log.message = CONCAT(
                                '刷新警戒库存：',
                                IF(
                                    VALUES(sales_rate_type) =0,
                                    CASE {$purchase_rate_type}
                                          WHEN 1 THEN
                                              CONCAT('使用全局【月固定销售增长率】计算方式,月固定增长率改为“',VALUES(sales_fixrate),'”')
                                          WHEN 2 THEN
                                              CONCAT('使用全局【动态销售增长】计算方式,动态计算周期改为“',VALUES(sales_rate_cycle),'”')
                                    END ,
                                    CASE VALUES(sales_rate_type)
                                          WHEN 1 THEN
                                              CONCAT('使用库存单独配置的【月固定销售增长率】计算方式,月固定增长率改为“',VALUES(sales_fixrate),'”')
                                          WHEN 2 THEN
                                              CONCAT('使用库存单独配置的【动态销售增长】计算方式,动态计算周期改为“',VALUES(sales_rate_cycle),'”')
                                          ELSE '' 
                                    END 
                                ),
                                CONCAT('计算出的日增长率为：“',VALUES(sales_rate),'”，'),
                                IF(tmp_stock_spec_log.alarm_days<>VALUES(alarm_days),CONCAT('---警戒库存天数从“',tmp_stock_spec_log.alarm_days,'”到“',VALUES(alarm_days),'”'),'')
                            )");
        $db->execute("insert into stock_spec_log(stock_spec_id,operator_id,operator_type,message)
                            SELECT rec_id stock_spec_id,operator_id,operator_type,message 
                            FROM tmp_stock_spec_log");
        //D('Setting/System')->add(array('key'=>'stock_lastcalc_alarmtime','value'=>array('exp',"CURDATE()")),array(),array('value'=>array('exp','VALUES(value)')));
        setSysCfg($db,'stock_lastcalc_alarmtime',$current_date);
        setSysCfg($db,'stock_lastcalc_alarmday',date('Y-m-d',time()));
        $result = $db->execute("DELETE FROM stock_spec_log  WHERE stock_spec_log.created<date_add('{$current_date}',INTERVAL '-7' DAY ) AND stock_spec_log.operator_type = 1 AND  stock_spec_log.`data` =0");
		$db->execute("COMMIT");
        return array('status'=>0,'info'=>'成功');
    } catch (Exception $e){
        $db->execute("ROLLBACK");
        logx('refreshAlarmStock ERR:'.print_r($e->getMessage(),true),'$sid'.'/AlarmStock');
        return array('status'=>2,'info'=>'refreshAlarmStock ERR:'.print_r($e->getMessage(),true));
    }
}