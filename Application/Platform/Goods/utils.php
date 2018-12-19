<?php

$GLOBALS['update_goods_sql']= 'ON DUPLICATE KEY UPDATE ' .
    'status=IF(@chg_goods_count:=@chg_goods_count+1,VALUES(status),0),' .
    'modify_flag=IF(outer_id=VALUES(outer_id) AND spec_outer_id=VALUES(spec_outer_id),modify_flag,1),' .
    'goods_name=VALUES(goods_name),spec_name=VALUES(spec_name),spec_code=VALUES(spec_code),spec_sku_properties=VALUES(spec_sku_properties),' .
    'outer_id=VALUES(outer_id),spec_outer_id=VALUES(spec_outer_id),pic_url=VALUES(pic_url),' .
    'stock_num=VALUES(stock_num),price=VALUES(price),is_deleted=VALUES(is_deleted),cid=VALUES(cid),'.
    'stock_change_count=stock_change_count+((VALUES(stock_num)>last_syn_num)=is_stock_changed),' .
    'is_stock_changed=IF(VALUES(stock_num)>last_syn_num,1,is_stock_changed)';

$GLOBALS['update_goods_sql_new']= 'ON DUPLICATE KEY UPDATE ' .
    'status=IF(@chg_goods_count:=@chg_goods_count+1,VALUES(status),0),' .
    'modify_flag=IF(outer_id=VALUES(outer_id) AND spec_outer_id=VALUES(spec_outer_id),modify_flag,1),' .
    'is_name_changed=IF(goods_name=VALUES(goods_name),is_name_changed,1),'.
    'goods_name=VALUES(goods_name),spec_name=VALUES(spec_name),spec_code=VALUES(spec_code),spec_sku_properties=VALUES(spec_sku_properties),' .
    'outer_id=VALUES(outer_id),spec_outer_id=VALUES(spec_outer_id),pic_url=VALUES(pic_url),' .
    'stock_num=VALUES(stock_num),price=VALUES(price),is_deleted=VALUES(is_deleted),cid=VALUES(cid),'.
    'stock_change_count=stock_change_count+((VALUES(stock_num)>last_syn_num)=is_stock_changed),' .
    'is_stock_changed=IF(VALUES(stock_num)>last_syn_num,1,is_stock_changed),' .
    'list_time=VALUES(list_time),hold_stock_type=VALUES(hold_stock_type),delist_time=VALUES(delist_time)';
/*
	写货品到数据库
*/
function putGoodsToDb($sid, &$db, &$spec_list, &$new_count, &$chg_count, &$error_msg) {
    //global $update_goods_sql;
    global $update_goods_sql_new; //增加货品上下架时间
    //logx(print_r($spec_list,true));

    //$db_version = (int)getSysCfg($db, 'sys_db_version', 0); //版本判断
    /*if ($db_version > 2150) {*/
        if ($db->execute('SET @chg_goods_count = 0') !== false) {
            if (putDataToTable($db, 'api_goods_spec', $spec_list, $update_goods_sql_new) !== false) {
                $chg_goods_count = (int)$db->query_result_single('select @chg_goods_count', 0);

                $new_count += count($spec_list) - intval($chg_goods_count);
                $chg_count += $chg_goods_count;

                $spec_list = array();

                /*if (!$db->multi_query("call SP_API_GOODS_SPEC_AUTO_MATCH_BY_MODIFY(0)")) {
                    logx("call SP_API_GOODS_SPEC_AUTO_MATCH_BY_MODIFY failed!", $sid);
                    //兼容以前版本
                    if (!goods_match($sid, $db)) {
                        $error_msg = 'goods match failed!';
                    }
                }*/
                if($sid == 'moyanjuke'){
                    return true;
                }
                if (!goods_match($sid, $db)) {
                    $error_msg["status"] = 0;
                    $error_msg["info"] = 'goods match failed!';
                }
                return true;
            }
        }
    /*} else {
        if ($db->execute('SET @chg_goods_count = 0') !== false) {
            if (putDataToTable($db, 'api_goods_spec', $spec_list, $update_goods_sql) !== false) {
                $chg_goods_count = (int)$db->query_result_single('select @chg_goods_count', 0);

                $new_count += count($spec_list) - intval($chg_goods_count);
                $chg_count += $chg_goods_count;

                $spec_list = array();*/

                /*if (!$db->multi_query("call SP_API_GOODS_SPEC_AUTO_MATCH_BY_MODIFY(0)")) {
                    logx("call SP_API_GOODS_SPEC_AUTO_MATCH_BY_MODIFY failed!", $sid);
                    //兼容以前版本
                    if (!goods_match($sid, $db)) {
                        $error_msg = 'goods match failed!';
                    }
                }*/
                /*if (!goods_match($sid, $db)) {
                    $error_msg["status"] = 0;
                    $error_msg["info"] = 'goods match failed!';
                }
                return true;
            }
        }
    }*/

    $error_msg["status"] = 0;
    $error_msg["info"] = '数据库错误:' . $db->error_msg();
    logx("$sid ERROR putTradesToDb $error_msg", $sid . "/Goods",'error');

    return false;
}

function goods_match($sid, &$db) {
    if ($db->multi_query("call SP_UTILS_GET_CFG_CHAR('sys_goods_auto_bchg_del_match', @V_AutoDelMatchGoods, 0)")) {
        $result = $db->query_result("select @V_AutoDelMatchGoods");
        if ($result['@V_AutoDelMatchGoods'] && $db->execute('BEGIN') !== false) {
            if ($db->execute("UPDATE api_goods_spec gs " .
                             " SET gs.match_target_type=0, gs.match_target_id=0,gs.match_code='',gs.is_deleted=0,gs.modify_flag=0 " .
                             " WHERE gs.modify_flag>0 AND (gs.modify_flag & 1) AND " .
                             " gs.is_manual_match=0 AND " .
                             " gs.status>0 ")
            ) {
                $db->execute('COMMIT');
                logx("GoodsMatch cancel ok!", $sid . "/Goods");
                return true;
            }
            $db->execute('ROLLBACK');
            logx("$sid GoodsMatch cancel failed!", $sid . "/Goods",'error');
            return false;
        }
    }

    if ($db->multi_query("call SP_UTILS_GET_CFG_CHAR('sys_goods_match_concat_code', @V_ConcatGoodsNO, 0)") &&
        $db->multi_query("call SP_UTILS_GET_CFG_CHAR('apigoods_auto_match', @V_AutoMatchGoods, 1)")
    ) {
        $result = $db->query_result("select @V_AutoMatchGoods, @V_ConcatGoodsNO");
        if ($result['@V_AutoMatchGoods'] && $db->execute('BEGIN') !== false) {
            if ($db->execute("UPDATE api_goods_spec gs, goods_merchant_no mn " .
                             " SET gs.match_target_type=mn.type, gs.match_target_id=mn.target_id,gs.match_code=mn.merchant_no,gs.is_deleted=0,gs.is_stock_changed=1 " .
                             " WHERE gs.modify_flag>0 AND (gs.modify_flag & 1) AND " .
                             " gs.is_manual_match=0 AND " .
                             " gs.status>0 AND " .
                             " IF({$result['@V_ConcatGoodsNO']},CONCAT(gs.outer_id,gs.spec_outer_id),IF(gs.spec_outer_id='',gs.outer_id,gs.spec_outer_id))=mn.merchant_no AND mn.merchant_no<>'' ")
                &&
                $db->execute("UPDATE api_goods_spec ag,goods_spec gs,goods_goods gg,goods_class gc " .
                             " SET ag.brand_id=gg.brand_id,ag.class_id_path=gc.path " .
                             " WHERE ag.modify_flag>0 AND (ag.modify_flag & 1) AND ag.status>0 AND ag.match_target_type=1 AND gs.spec_id=ag.match_target_id AND gg.goods_id=gs.goods_id AND gc.class_id=gg.class_id ")
                &&
                $db->execute("UPDATE api_goods_spec ag,goods_suite gs,goods_class gc " .
                             " SET ag.brand_id=gs.brand_id,ag.class_id_path=gc.path " .
                             " WHERE ag.modify_flag>0 AND (ag.modify_flag & 1) AND ag.status>0 AND ag.match_target_type=2 AND gs.suite_id=ag.match_target_id AND gc.class_id=gs.class_id ")
                &&
                $db->execute("UPDATE api_trade_order ato,api_goods_spec ag,api_trade ax " .
                             " SET ato.is_invalid_goods=0,ax.bad_reason=0 " .
                             " WHERE ato.is_invalid_goods=1 AND ag.`platform_id`=ato.`platform_id` AND ag.`goods_id`=ato.`goods_id` AND " .
                             " ag.`spec_id`=ato.`spec_id` AND ax.platform_id=ato.`platform_id` AND ax.tid=ato.tid AND ax.trade_status<40 AND " .
                             " ag.match_target_type>0 AND ag.modify_flag>0 AND (ag.modify_flag & 1) ")
                &&
                $db->execute("call I_DL_INIT_REFRESH_STOCK_SYNC")
            ) {
                $db->execute('COMMIT');
                logx("goods match ok!", $sid . "/Goods");
                return true;
            }
            $db->execute('ROLLBACK');
            logx("$sid goods match failed!", $sid . "/Goods",'error');
            return false;
        }
    }

    return true;
}

?>