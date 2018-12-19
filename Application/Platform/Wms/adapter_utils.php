<?php
define('ORDER_TYPE_TRADE',1); //单据类型-订单
define('ORDER_TYPE_PURCHASE',2); //单据类型-采购
define('ORDER_TYPE_REFUND',3); //单据类型-退货
define('ORDER_TYPE_OTHER_STOCKOUT',4); //单据类型-其他出库
define('ORDER_TYPE_OTHER_STOCKIN',5); //单据类型-其他入库
define('ORDER_TYPE_STOCKCHANGE_STOCKOUT',6); //单据类型-其他出库_库存异动
define('ORDER_TYPE_STOCKCHANGE_STOCKIN',7); //单据类型-其他入库_库存异动
define('ORDER_TYPE_TRANSFERIN',8); //单据类型-调拨入库
define('ORDER_TYPE_TRANSFEROUT',9); //单据类型-调拨出库
define('ORDER_TYPE_STOCK_SYNC',10); //库存同步
define('ORDER_TYPE_STOP_WAITING_PO', 11);//采购停止等待
define('ORDER_TYPE_PLAN_PURCHASE', 12);//采购计划开单


define('STATUS_ACCEPT',1); //已接单状态
define('STATUS_FAILED',2); //失败状态
define('STATUS_CANCELED',3); //取消成功(百世的需要)
define('STATUS_CANCELFAIL',4); //取消失败(百世的需要)
define('STATUS_OTHER',5); //其他状态,我们不关心的一些状态(比如订单的已打印,已扫描等)
define('STATUS_FINISH',6); //完成状态


function wms_update_order_status(&$db,$order,$details,&$response)
{
	global $batchFlag;
    $ex_result = $db->execute("CREATE TEMPORARY TABLE IF NOT EXISTS tmp_wms_order(
                          rec_id INT(11) NOT NULL AUTO_INCREMENT,
                          wms_id VARCHAR(50) NOT NULL DEFAULT '' COMMENT 'wms授权标识',
                          wms_no VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'wms仓库编号',
                          owner_no VARCHAR(40) NOT NULL  DEFAULT '' COMMENT '货主编号',
                          order_type TINYINT(2) NOT NULL DEFAULT 0 COMMENT '单据类型 1订单 2采购单 3退货单 4其他出库单 5其他入库单 6异动-其它出库 7异动-其它入库',
                          order_no VARCHAR(50) NOT NULL DEFAULT '' COMMENT '委外单号,一般对应为系统内的outer_no',
                          `status` TINYINT(2) NOT NULL DEFAULT 0 COMMENT '状态值',
                          status_name VARCHAR(50) NOT NULL DEFAULT '' COMMENT '状态描述,给用户展示用,直接把wms反馈的状态描述显示界面上即可',
                          order_plan_flag TINYINT(2) NOT NULL DEFAULT 0 COMMENT '是否为调拨计划单，0 不是，1 是',
                          remark VARCHAR(256) NOT NULL DEFAULT '' COMMENT '备注信息',
                          biz_code VARCHAR(40) NOT NULL DEFAULT '' COMMENT '消息id,去重',
                          logistics_code VARCHAR(30) NOT NULL DEFAULT '' COMMENT '物流公司编号',
                          logistics_no VARCHAR(30) NOT NULL DEFAULT '' COMMENT '物流单号',
						  logistics_list VARCHAR(512) NOT NULL DEFAULT '' COMMENT '多物流公司编号信息',
                          weight DECIMAL(19,4) NOT NULL DEFAULT 0 COMMENT '重量',
						  confirm_flag	TINYINT(2) NOT NULL DEFAULT 1 COMMENT '单据多次出入货标志，0 为最终出入货，1为中间出入货',
                          undefined1 INT(11) NOT NULL DEFAULT 0 COMMENT '未定义1(留作后续扩展)',
                          undefined2 INT(11) NOT NULL DEFAULT 0 COMMENT '未定义2(留作后续扩展)',
                          undefined3 VARCHAR(30) NOT NULL DEFAULT '' COMMENT '未定义3(留作后续扩展)',
                          undefined4 VARCHAR(50) NOT NULL DEFAULT '' COMMENT '未定义4(留作后续扩展)',
                          undefined5 VARCHAR(60) NOT NULL DEFAULT '' COMMENT '未定义5(留作后续扩展)',
                          undefined6 VARCHAR(200) NOT NULL DEFAULT '' COMMENT '未定义6(留作后续扩展)',
                          PRIMARY KEY (rec_id)
                        )ENGINE=MEMORY DEFAULT CHARSET=utf8 comment 'wms单据回传信息表'");
    if(!$ex_result)
    {
		$response['code'] = 99;
		$response['msg'] = '服务器异常';
        logx("创建临时单据表失败");
		return false;
    }
    $ex_result = $db->execute("CREATE TEMPORARY TABLE IF NOT EXISTS tmp_wms_order_detail(
                          rec_id INT(11) NOT NULL AUTO_INCREMENT,
                          order_no VARCHAR(50) NOT NULL DEFAULT '' COMMENT '所属单据的编号,用这个来跟order表建立连接',
                          spec_no VARCHAR(50) NOT NULL DEFAULT '' COMMENT '商家编码',
                          num DECIMAL(19,4) NOT NULL DEFAULT '0' COMMENT '数量',
                          price DECIMAL(19,4) NOT NULL DEFAULT '0' COMMENT '价格',
                          `batch` VARCHAR(40) NOT NULL DEFAULT '' COMMENT '批次',
                          `product_date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '生产日期',
						  `expire_date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '有效期，即过期日期，冗余字段',
                          `inventory_type` TINYINT(2) NOT NULL DEFAULT 0 COMMENT '是否为残次品的标志，0 正品  1 残次',
                          pd_flag TINYINT(1) NOT NULL DEFAULT '0' COMMENT '盘点类型，1 盘盈，2盘亏',
                          remark VARCHAR(200) NOT NULL DEFAULT '' COMMENT '备注',
                          order_detail_id INT(11) NOT NULL DEFAULT 0 COMMENT '单据详细信息id',
						  spec_id INT(11) NOT NULL DEFAULT 0 COMMENT '商品id',
						  undefined1 INT(11) NOT NULL DEFAULT 0 COMMENT '未定义1(留作后续扩展)',
						  undefined2 VARCHAR(30) NOT NULL DEFAULT '' COMMENT '未定义2(留作后续扩展)',
                          PRIMARY KEY (rec_id)
                        )ENGINE=MEMORY DEFAULT CHARSET=utf8 comment 'wms单据回传明细'");
    if(!$ex_result)
    {
		$response['code'] = 99;
		$response['msg'] = '服务器异常';
        logx("创建临时单据明细表失败");
		return false;
    }
	$db->execute('delete from tmp_wms_order');
	$db->execute('delete from tmp_wms_order_detail');
    if($db->execute('BEGIN') !== false)
    {
        if(putTableToDB($db,'tmp_wms_order',$order) !== false)
        {
            if(putTableToDB($db,'tmp_wms_order_detail',$details) !== false)
            {
                if($db->execute('COMMIT') !== false)
                {
					if($batchFlag ==1)//启用批次
					{
						$db->execute("set @batch_flag=1");
					}
	                else//不启用批次
	                {
		                $db->execute("set @batch_flag=0");
	                }
                    $result = $db->query("CALL SP_WMS_ORDER_HANDLE('{$order[0]['order_no']}',@ifcode,@ifmsg)");
                    if(!$result)
                    {
                        logx("CALL SP_WMS_ORDER_HANDLE Failed! error:".$db->error_msg());
                        $response['code'] = 99;
                        $response['msg'] = '服务器处理异常';
                        return false;
                    }
                    $db->free_result($result);
                    $result = $db->query_result("select @ifcode,@ifmsg");

                    $response['code'] = !isset($result['@ifcode']) || is_null($result['@ifcode'])?99:$result['@ifcode'];
                    $response['msg']  = !isset($result['@ifmsg']) || is_null($result['@ifmsg'])?'服务器处理异常':$result['@ifmsg'];
					logx("code:{$response['code']},msg:{$response['msg']}\n");
                    if(intval($result['@ifcode'])!=0)
                    {
                        return false;
                    }
                    return true;
                }
            }
        }
        $db->execute("ROLLBACK");
        logx('putTableToDb error:'.$db->error_msg());
        logx('Order:'.print_r($order,true));
        logx('Detail:'.print_r($details,true));
        $response['code'] = 99;
        $response['msg'] = '系统异常';
        return false;
    }
    //几乎不可能走到这里
	$response['code'] = 99;
	$response['msg'] = '系统未知异常';
	return false;

}
function getTableFileds($db,$table)
{
	$fields = array();
	$result = $db->query("SHOW COLUMNS FROM $table");
	while($row = $db->fetch_array($result))
	{
		$fields[] = $row['Field'];
	}
	return $fields;
}
function rowsToSQL($row,&$db,$keys)
{
	$s = array();
	foreach($row as $k=>$v)
	{
		if(!in_array($k, $keys))
			continue;
		if(is_int($v))
		{
			$s[] = $v;
		}
		else if(is_string($v) && strlen($v)>1 && $v[0]=="\0")
		{
			$s[] = substr($v,1);
		}
		else
		{
			$s[] = "'" . $db->escape_string($v) . "'";
		}
	}
	return '(' . implode(',',$s) . ')';
}
function putTableToDB(&$db,$table,$rows,$update = '')
{
	if(count($rows) == 0)
	{
		return true;
	}
	$fileds = getTableFileds($db,$table);
	$keys = array();
	foreach($rows[0] as $k=>$v)
	{
		if(in_array($k,$fileds))
			$keys[] = $k;
	}
	$data = array();
	for($i=0; $i<count($rows); $i++)
	{
		$data[] = rowsToSQL($rows[$i],$db,$keys);
	}
	$data = implode(',',$data);

	if(!empty($update))
	{
		$sql = "insert into ".$table."(".implode(',',$keys).") values ".$data.' '.$update;
	}
	else
	{
		$sql = "insert ignore into ".$table."(".implode(',',$keys).") values ".$data;
	}
	return $db->execute($sql);
}

function wms_update_stock_pd_sync($db,$order,$details,&$response)
{
	if(empty($db))
	{
		$response['code'] = -1;
		$response['msg'] = '服务器异常';
		return false;
	}

	if(!is_array($order))
	{
		$response['code'] = 1;
		$response['msg'] = '盘点单数据格式异常';
		return false;
	}

	if(empty($order['outer_no']))
	{
		$response['code'] = 1;
		$response['msg'] = '外部单号不能为空';
		return false;
	}

	if(!is_array($details))
	{
		$response['code'] = 1;
		$response['msg'] = '盘点单详情数据格式异常';
		return false;
	}
	//先删除当前连接的tmp_wms_order_detail
	$db->execute("DROP TEMPORARY TABLE IF EXISTS tmp_wms_order_detail");
	$ex_result = $db->execute("CREATE TEMPORARY TABLE IF NOT EXISTS tmp_wms_order_detail(
                          rec_id INT(11) NOT NULL AUTO_INCREMENT,
                          spec_id INT(11) NOT NULL DEFAULT 0,
						  spec_no VARCHAR(50) NOT NULL DEFAULT '' COMMENT '商家编码',
                          position_no VARCHAR(50) NOT NULL DEFAULT '' COMMENT '货位',
                          expire_date DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '有效期',
                          old_num DECIMAL(19,4) NOT NULL DEFAULT 0 COMMENT '盘点前数量',
                          input_num DECIMAL(19,4) NOT NULL DEFAULT 0 COMMENT '盘点数量',
                          surplus DECIMAL(19,4) NOT NULL DEFAULT 0 COMMENT '盘点盈余亏损数量',
						  remark VARCHAR(200) NOT NULL DEFAULT '' COMMENT '备注',
						  undefined1 INT(11) NOT NULL DEFAULT 0 COMMENT '未定义1(留作后续扩展)',
						  undefined2 VARCHAR(30) NOT NULL DEFAULT '' COMMENT '未定义2(留作后续扩展)',
                          PRIMARY KEY (rec_id)
                        )ENGINE=INNODB DEFAULT CHARSET=utf8 comment 'wms单据回传明细'");

	if(!$ex_result)
	{
		$response['code'] = -1;
		$response['msg'] = '服务器异常';
		logx("创建临时单据明细表失败");
		return false;
	}

	$db->execute('delete from tmp_wms_order_detail');

	if($db->execute('BEGIN') === false)	//事务
	{
		$response['code'] = -1;
		$response['msg'] = '系统异常开启处理事务失败';
		return false;
	}

	if(putTableToDB($db,'tmp_wms_order_detail',$details) === false)
	{
		$db->execute("ROLLBACK");
		logx('putTableToDb error:'.$db->error_msg());
		logx('Order:'.print_r($order,true));
		logx('Detail:'.print_r($details,true));
		$response['code'] = '-1';
		$response['msg'] = '系统异常处理详情数据失败';
		return false;
	}
	if($db->execute('COMMIT') === false)
	{
		$response['code'] = -1;
		$response['msg'] = '系统异常提交失败';
		return false;
	}

	$request = to_query_params($order);

	$result = $db->execute("CALL SP_OPENAPI_STOCK_PD_FAST_PD('$request',1)");	//后边的1表示强制审核
	if(!$result)
	{
        $msg = $db->error_msg();
		logx("CALL SP_OPENAPI_STOCK_PD_FAST_PD Failed! error:".$msg);
		$response['code'] = -1;
		$response['msg'] = "服务器处理异常:$msg";
		return false;
	}

	return true;
}

function wms_update_all_stockspec_sync($db,$order,&$response,$wms_warehouse_no,$clear_stock_flag,$ownercode='')
{
	$ex_result = $db->execute("CREATE TEMPORARY TABLE IF NOT EXISTS tmp_wms_stockspec_sync(
		rec_id INT(11) NOT NULL AUTO_INCREMENT,
			   spec_no VARCHAR(50) NOT NULL DEFAULT '' COMMENT '商家编码',
			   wms_sync_stock INT(11) NOT NULL DEFAULT '0' COMMENT '未冻结库存数量',
			   wms_sync_lock_stock INT(11) NOT NULL DEFAULT '0' COMMENT '冻结库存数量',
			   spec_id INT(11) NOT NULL DEFAULT 0 COMMENT '单品id',
               batch VARCHAR(40) NOT NULL DEFAULT '' COMMENT '批次',
               product_date DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '生产日期',
			   expire_date DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '有效期，即过期日期，冗余字段',
               inventory_type TINYINT(2) NOT NULL DEFAULT 0 COMMENT '是否为残次品的标志，0 正品  1 残次',
               inventory_time DATE NOT NULL DEFAULT '0000-00-00' COMMENT  '库存时间',
			   undefined1 INT(11) NOT NULL DEFAULT 0 COMMENT '未定义1(留作后续扩展)',
			   undefined2 VARCHAR(30) NOT NULL DEFAULT '' COMMENT '未定义2(留作后续扩展)',
			   PRIMARY KEY (rec_id),
			   KEY `IX_tmp_wms_stockspec_sync_spec_id` (`spec_id`)
			   )ENGINE=MEMORY DEFAULT CHARSET=utf8 comment 'wms库存同步信息'");

	if(!$ex_result)
	{
		$response['code'] = 99;
		$response['msg'] = '服务器异常';
		logx("创建wms库存同步信息表失败");
		releaseDb($db);
		return ;
	}

	global $batchFlag;
	$db->execute('delete from tmp_wms_stockspec_sync');
	if($db->execute('BEGIN') !== false)
	{
		if(putTableToDB($db,'tmp_wms_stockspec_sync',$order) !== false)
		{

			if($db->execute('COMMIT') !== false)
			{
				$batchFlag = empty($batchFlag)?0:$batchFlag;
				$db->execute("set @batch_flag=$batchFlag");
				$db->execute("set @ownercode='$ownercode'");
				$result = $db->query("CALL SP_WMS_STOCKSPEC_SYNC('$wms_warehouse_no',$clear_stock_flag)");
				if(!$result)
				{
					logx("CALL SP_WMS_STOCKSPEC_SYNC Failed! error:".$db->error_msg());
					$response['code'] = 99;
					$response['msg'] = '服务器处理异常';
					releaseDb($db);
					return false;
				}
				$result = $db->query_result("select @sys_code,@sys_message");
				$response['code'] = $result['@sys_code'];
				$response['msg'] = $result['@sys_message'];
				logx("code:{$response['code']},msg:{$response['msg']}\n");

				releaseDb($db);
				return ;
			}

		}
		$db->execute("ROLLBACK");
		logx('putTableToDb error:'.$db->error_msg());
		logx('Order:'.print_r($order,true));
		$response['code'] = 99;
		$response['msg'] = '系统异常';
		releaseDb($db);
		return ;
	}
	$response['code'] = 99;
	$response['msg'] = '系统未知异常';
	releaseDb($db);
	return ;

}
