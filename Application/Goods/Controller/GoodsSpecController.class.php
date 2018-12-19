<?php

namespace Goods\Controller;

use Common\Common\DatagridExtention;
use Common\Controller\BaseController;
use Think\Log;
use Think\Exception\BusinessLogicException;
//use Common\Common\Factory;
use Common\Common\UtilDB;
use Common\Common\Factory;


class GoodsSpecController extends BaseController {
    public function getGoodsSpec() {
        $id_list                          = DatagridExtention::getIdList(array('more_button', 'more_content', 'hidden_flag', 'form', 'tab_container', 'tool_bar', 'datagrid', 'delete','set_hotcake'));
        $fields                           = get_field('Goods/GoodsSpec', 'goods_spec');
        propFildConv($fields,'prop','goods_spec');
        $datagrid                         = array(
            'id'      => $id_list['datagrid'],
            'options' => array(
                'title'        => '',
                'url'          => U("GoodsSpec/loadDataByCondition"),
                'toolbar'      => "#{$id_list['tool_bar']}",
                'fitColumns'   => false,
                'singleSelect' => false,
                'ctrlSelect'   => true
            ),
            'fields'  => $fields,
            'class'   => 'easyui-datagrid',
            'style'   => "overflow:scroll",
        );
        $checkbox=array('field' => 'ck','checkbox' => true);
        array_unshift($datagrid['fields'],$checkbox);
        $arr_tabs                         = array(
            array('url' => U('Goods/GoodsCommon/getTabsView', array('tabs' => "spec_platform_goods")) . '?prefix=goodsspec&tab=spec_platform_goods', "id" => $id_list['tab_container'], "title" => "平台货品"),
            array('url' => U('Goods/GoodsCommon/getTabsView', array('tabs' => "spec_set_out_warehouse")) . '?prefix=goodsspec&tab=spec_set_out_warehouse', "id" => $id_list['tab_container'], "title" => "出库仓库"),
            array('url' => U('Goods/GoodsCommon/getTabsView', array('tabs' => "spec_set_out_logistics")) . '?prefix=goodsspec&tab=spec_set_out_logistics', "id" => $id_list['tab_container'], "title" => "出库物流"),
            array('url' => U('Goods/GoodsCommon/getTabsView', array('tabs' => "goods_spec_log")) . '?prefix=goodsspec&tab=goods_spec_log', "id" => $id_list['tab_container'], "title" => "日志"),
        );
        $params['datagrid']               = array();
        $params['datagrid']['url']        = U("GoodsSpec/loadDataByCondition");
        $params['datagrid']['id']         = $id_list['datagrid'];
        $params['search']['more_button']  = $id_list['more_button'];  //更多按钮的button
        $params['search']['more_content'] = $id_list['more_content']; //更多的层id
        $params['search']['hidden_flag']  = $id_list['hidden_flag'];  //更多的隐藏值，用来判断是否是展开
        $params['search']['form_id']      = $id_list['form'];   //用作获取form的作用的，用来提交查询信息
        $params['tabs']['id']             = $id_list['tab_container'];
        $params['tabs']['url']            = U('GoodsCommon/updateTabsData');

        $params['delete']['id']  = $id_list['delete'];
        $params['delete']['url'] = U('GoodsSpec/delGoodsSpec') . '?type=2';

        $list = UtilDB::getCfgList(array('brand', 'goods_class'), array("brand" => array("is_disabled" => 0)));
        $this->assign("goods_brand", $list['brand']);
        $this->assign('map_brand', json_encode($list['brand']));
        $this->assign('map_class', json_encode($list['goods_class']));
        
        $this->assign("params", json_encode($params));
        $this->assign('arr_tabs', json_encode($arr_tabs));
        $this->assign('tool_bar', $id_list['tool_bar']);
        $this->assign('datagrid', $datagrid);
        $this->assign("id_list", $id_list);
        
        $this->display('show');

        
    }

    public function loadDataByCondition($page = 1, $rows = 20, $search = array(), $sort = 'id', $order = 'desc') {
        try {
            $data = D('Goods/GoodsSpec')->loadDataByCondition($page, $rows, $search, $sort, $order);
        } catch (\Exception $e) {
            $msg = $e->getMessage();
            Log::write($msg);
            $data['total'] = 0;
            $data['rows']  = '';
        };
        $this->ajaxReturn($data);
    }

    /**
     * 选取货品单品
     * author:luyanfeng
     * table:goods_spec
     */
    public function selectGoodsSpec($page = 1, $rows = 10, $search = array(), $sort = 'gs.spec_id', $order = 'desc') {
        if (IS_POST) {
			$sys_available_stock =  get_config_value('sys_available_stock',640);
			$available_str = D('Stock/StockSpec')->getAvailableStrBySetting($sys_available_stock);

            $goods_spec_db = M('goods_spec');
            $where         = 'WHERE gs.deleted=0 ';
			$model         = I("get.model");
            $model = addslashes($model);
			$warehouse_id  = I("get.warehouse_id");
			$warehouse_id  =intval($warehouse_id);
			if($model == 'purchase' || $model == 'purchase_return'){
				$provider_id  =  I("get.provider_id");
				$provider_id = intval($provider_id);
			}
            $warehouse_id  = $warehouse_id>0?$warehouse_id:'all';
           
            $page = intval($page);
            $rows = intval($rows);
            foreach ($search as $k => $v) {
                if ($v === "") continue;
                switch ($k) {
                    case 'brand_id':
                        set_search_form_value($where, 'brand_id', $v, 'gb', 2, 'AND');
                        break;
                    case 'goods_no':
                        set_search_form_value($where, 'goods_no', $v, 'gg', 1, 'AND');
                        break;
                    case 'goods_name':
                        set_search_form_value($where, 'goods_name', $v, 'gg', 10, 'AND');
                        break;
                    case 'spec_no':
                        set_search_form_value($where, 'spec_no', $v, 'gs', 10, 'AND');
                        break;
                    case 'spec_name':
                        set_search_form_value($where, 'spec_name', $v, 'gs', 6, 'AND');
                        break;
                    case 'barcode':
                        if($model == 'goodsSpecBarcode'){
                            set_search_form_value($where, 'barcode', $v, 'gbc', 1, 'AND');
                        }else{
                            set_search_form_value($where, 'barcode', $v, 'gs', 1, 'AND');
                        }

                        break;
                    case "class_id":
                        $left_join_goods_class_str = set_search_form_value($where, 'class_id', $v, 'gg', 7, 'AND');
                        break;
                    case "is_show_zero_stock":
                        $is_show_zero_stock =intval($v);
                        break;
                    case "warehouse_id":
                        $warehouse_id = intval($v);
                        break;
                    case "model":
                        $model = addslashes($v);
                        break;
                    case "is_allow_neg_stock":
                        set_search_form_value($where,'is_allow_neg_stock', $v, 'gs', 8,'AND');
                        break;
                    default:
                        continue;
                }
            }
            $limit = ($page - 1) * $rows . "," . $rows;
            $sort  = $sort . " " . $order;
            $sort  = addslashes($sort);
			$field_content = array(
				'cost_price'=>'CAST(IFNULL(ss.cost_price,0) AS DECIMAL(19,4)) as cost_price',
			);
			$field_right = D('Setting/EmployeeRights')->getFieldsRight('',$field_content);
			$point_number=get_config_value('point_number',0);
			$expect_num = "CAST(0 AS DECIMAL(19,".$point_number.")) expect_num";
			$num = "CAST(1 AS DECIMAL(19,".$point_number.")) AS num";
			$stock_num = "CAST(IFNULL(ss.stock_num,0) AS DECIMAL(19,".$point_number.")) stock_num";
			$orderable_num = "CAST(IFNULL(".$available_str.",0) AS DECIMAL(19,".$point_number.")) orderable_num";
            if ($warehouse_id == "all" && $model == "") {
                $sql       = "SELECT gs.spec_id as id,gs.img_url,gs.spec_id,gs.spec_name,gg.short_name,gs.spec_no,gg.spec_count,gg.goods_no,gg.goods_name,gs.market_price,
                        gs.spec_code,gb.brand_name,gs.barcode,cgu.name as unit_name,gg.unit as base_unit_id,gs.lowest_price,gg.goods_id,
                        0 as src_price,gs.wholesale_price,gs.member_price,gs.tax_rate,gg.brand_id,gs.barcode,gs.retail_price,
                        gs.weight,gs.retail_price
                        FROM goods_spec gs
                        LEFT JOIN goods_goods gg ON(gs.goods_id=gg.goods_id)
                        LEFT JOIN goods_brand gb ON(gg.brand_id=gb.brand_id)
                        LEFT JOIN cfg_goods_unit cgu ON(gg.unit = cgu.rec_id)
                        $where";//                         LEFT JOIN cfg_goods_unit cgu ON(gg.unit = cgu.rec_id)
                $sql_count = "SELECT COUNT(1) AS total FROM goods_spec gs
                        LEFT JOIN goods_goods gg ON(gs.goods_id=gg.goods_id)
                        LEFT JOIN goods_brand gb ON(gg.brand_id=gb.brand_id)
                        LEFT JOIN cfg_goods_unit cgu ON(gg.unit = cgu.rec_id)
                        $where";//                         LEFT JOIN cfg_goods_unit cgu ON(gg.unit = cgu.rec_id)
            } else if (($warehouse_id != "all" && $model == "") ) {//出库开单时，需要从相应的仓库中出库
                /*$where     = $is_show_zero_stock == 0?$where . "AND IFNULL(ss.stock_num-ss.order_num-ss.sending_num, 0)>0 AND ss.warehouse_id={$warehouse_id}"
                             :$where . "AND IFNULL(ss.stock_num-ss.order_num-ss.sending_num, 0)>=0 AND ss.warehouse_id={$warehouse_id}";*/
                $where = $where."AND (IFNULL(".$available_str.", 0)>0 OR gs.is_allow_neg_stock=1) AND ss.warehouse_id={$warehouse_id} ";
                $sql       = "SELECT gs.spec_id as id,gs.img_url,gs.spec_id,gs.is_allow_neg_stock,gs.spec_name,gs.spec_no,gs.market_price,gs.spec_code,gs.barcode,gs.lowest_price,gs.wholesale_price,gs.tax_rate,gs.barcode,gs.retail_price,gs.weight,gs.unit as base_unit_id,
                            gg.short_name,gg.spec_count,gg.goods_no,gg.goods_name,gg.goods_id,gg.brand_id,
                            ".$stock_num.",ss.lock_num,ss.subscribe_num,ss.order_num,ss.sending_num,ss.purchase_arrive_num,ss.transfer_num,ss.unpay_num,".$orderable_num.",ss.status,CAST(IFNULL(ss.cost_price,0) AS DECIMAL(19,4)) AS price,CAST((1*IFNULL(ss.cost_price,0)) AS DECIMAL(19,4)) AS total_amount,
                            gb.brand_name,
                            IFNULL(cwp.position_no,cwp2.position_no) position_no,
							IFNULL(cwp.rec_id,cwp2.rec_id) position_id,
                            cgu.name as unit_name,
                            CAST(0 AS DECIMAL(19,4)) as src_price,
                            ".$num.",
                            '' AS remark
                        FROM goods_spec gs
                        LEFT JOIN goods_goods gg ON(gs.goods_id=gg.goods_id)
                        LEFT JOIN goods_brand gb ON(gg.brand_id=gb.brand_id)
                        ".$left_join_goods_class_str."
                        LEFT JOIN stock_spec ss ON(gs.spec_id=ss.spec_id)
                        LEFT JOIN cfg_goods_unit cgu ON(gs.unit = cgu.rec_id)
                        LEFT JOIN stock_spec_position ssp ON(ssp.spec_id = gs.spec_id and ssp.warehouse_id = {$warehouse_id})
                        LEFT JOIN cfg_warehouse_position cwp ON(ssp.position_id= cwp.rec_id)
                        LEFT JOIN cfg_warehouse_position cwp2 ON(cwp2.rec_id = -ss.warehouse_id)

                        $where";//                         LEFT JOIN cfg_goods_unit cgu ON(gg.unit = cgu.rec_id)
                $sql_count = "SELECT COUNT(1) AS total FROM goods_spec gs
                        LEFT JOIN goods_goods gg ON(gs.goods_id=gg.goods_id)
                        ".$left_join_goods_class_str."
                        LEFT JOIN goods_brand gb ON(gg.brand_id=gb.brand_id)
                        LEFT JOIN stock_spec ss ON(gs.spec_id=ss.spec_id)
                        LEFT JOIN cfg_goods_unit cgu ON(gs.unit = cgu.rec_id)
                        $where";//                         LEFT JOIN cfg_goods_unit cgu ON(gg.unit = cgu.rec_id)
            } else if ($warehouse_id != "all" && $model == "transfer") {//出库开单时，需要从相应的仓库中出库
                $to_warehouse_id  = intval(I("get.to_warehouse_id"));
                /*$where     = $is_show_zero_stock == 0?$where . "AND IFNULL(ss.stock_num-ss.order_num-ss.sending_num, 0)>0 AND ss.warehouse_id={$warehouse_id}"
                             :$where . "AND IFNULL(ss.stock_num-ss.order_num-ss.sending_num, 0) AND ss.warehouse_id={$warehouse_id}";*/
                $where = $where."AND (IFNULL(".$available_str.", 0)>0 OR gs.is_allow_neg_stock=1) AND ss.warehouse_id={$warehouse_id} ";
                $sql       = "SELECT gs.spec_id as id,gs.img_url,gs.spec_id,gs.spec_name,gs.spec_no,gs.market_price,gs.spec_code,gs.barcode,gs.lowest_price,gs.wholesale_price,gs.tax_rate,gs.barcode,gs.retail_price,gs.weight,gs.unit as base_unit_id,
                            gg.short_name,gg.spec_count,gg.goods_no,gg.goods_name,gg.goods_id,gg.brand_id,
                            ".$stock_num.",ss.lock_num,ss.subscribe_num,ss.order_num,ss.sending_num,ss.purchase_arrive_num,ss.transfer_num,ss.unpay_num,".$orderable_num.",ss.status,CAST(IFNULL(ss.cost_price,0) AS DECIMAL(19,4)) AS price,CAST((1*IFNULL(ss.cost_price,0)) AS DECIMAL(19,4)) AS total_amount,
                            gb.brand_name,
                            IFNULL(cwp.position_no,cwp2.position_no) from_position_no,
							IFNULL(cwp.rec_id,cwp2.rec_id) from_position,
							(CASE  WHEN ss_t.last_position_id THEN ss_t.last_position_id ELSE -{$to_warehouse_id} END) AS to_position,
                        (CASE  WHEN ss_t.last_position_id THEN cwp_t.position_no  ELSE cwp2_t.position_no END) AS to_position_no,
                        IF(ss_t.last_position_id,1,IF(ss_t.spec_id IS NOT NULL AND (ss_t.order_num <> 0 OR ss_t.sending_num <> 0),1,0)) AS is_allocated,
                            cgu.name as unit_name,
                            CAST(0 AS DECIMAL(19,4)) as src_price,
                            ".$num.",
                            '' AS remark
                        FROM goods_spec gs
                        LEFT JOIN goods_goods gg ON(gs.goods_id=gg.goods_id)
                        LEFT JOIN goods_brand gb ON(gg.brand_id=gb.brand_id)
                        ".$left_join_goods_class_str."
                        LEFT JOIN stock_spec ss ON(gs.spec_id=ss.spec_id)
                        LEFT JOIN stock_spec ss_t ON(gs.spec_id=ss_t.spec_id AND ss_t.warehouse_id={$to_warehouse_id})
                        LEFT JOIN cfg_goods_unit cgu ON(gs.unit = cgu.rec_id)
                        LEFT JOIN stock_spec_position ssp ON(ssp.spec_id = gs.spec_id and ssp.warehouse_id = {$warehouse_id})
                        LEFT JOIN cfg_warehouse_position cwp ON(ssp.position_id= cwp.rec_id)
                        LEFT JOIN cfg_warehouse_position cwp2 ON(cwp2.rec_id = -ss.warehouse_id)
                        LEFT JOIN stock_spec_position ssp_t ON(ssp_t.spec_id = gs.spec_id and ssp_t.warehouse_id = {$to_warehouse_id})
                        LEFT JOIN cfg_warehouse_position cwp_t ON(ssp_t.position_id= cwp_t.rec_id)
                        LEFT JOIN cfg_warehouse_position cwp2_t ON(cwp2_t.rec_id = -{$to_warehouse_id})

                        $where";//                         LEFT JOIN cfg_goods_unit cgu ON(gg.unit = cgu.rec_id)
                $sql_count = "SELECT COUNT(1) AS total FROM goods_spec gs
                        LEFT JOIN goods_goods gg ON(gs.goods_id=gg.goods_id)
                        ".$left_join_goods_class_str."
                        LEFT JOIN goods_brand gb ON(gg.brand_id=gb.brand_id)
                        LEFT JOIN stock_spec ss ON(gs.spec_id=ss.spec_id)
                        LEFT JOIN cfg_goods_unit cgu ON(gs.unit = cgu.rec_id)
                        $where";//                         LEFT JOIN cfg_goods_unit cgu ON(gg.unit = cgu.rec_id)
            } else if ($warehouse_id != "all" && $model == "stock") {//入库开单的时候虽然查找的是全部的单品信息，但是入库价格需要根据入库的仓库的成本价来作为相应的入库价
			   $sql       = "SELECT gs.spec_no,gs.img_url,gs.unit base_unit_id, gs.spec_id as id,gs.spec_id,gs.spec_name, gs.market_price, gs.spec_code, gs.barcode, gs.lowest_price, gs.barcode,gs.retail_price, gs.weight,
                        gg.short_name,gg.spec_count,gg.goods_no,gg.goods_name, gg.goods_id,gg.brand_id, 
                        gb.brand_name,
                        cgu.name as unit_name,
                        ".$stock_num.",".$orderable_num.",
                        CAST(1 as DECIMAL(19,4)) AS rebate, ".$expect_num.", ".$num.",CAST(IFNULL(ss.cost_price,0) AS DECIMAL(19,4)) AS total_cost,CAST(IFNULL(ss.cost_price,0) AS DECIMAL(19,4)) AS tax_price,CAST(IFNULL(ss.cost_price,0) AS DECIMAL(19,4)) AS tax_amount,CAST(0 AS DECIMAL(19,4)) AS tax,CAST(IFNULL(ss.cost_price,0) AS DECIMAL(19,4)) AS src_price,CAST(IFNULL(ss.cost_price,0) AS DECIMAL(19,4)) AS cost_price,
                        (CASE  WHEN ss.last_position_id THEN ss.last_position_id ELSE -{$warehouse_id} END) AS position_id,
                        (CASE  WHEN ss.last_position_id THEN cwp.position_no  ELSE cwp2.position_no END) AS position_no,
                        IF(ss.last_position_id,1,IF(ss.spec_id IS NOT NULL AND (ss.order_num <> 0 OR ss.sending_num <> 0),1,0)) AS is_allocated
                        FROM goods_spec gs
                        LEFT JOIN goods_goods gg ON(gs.goods_id=gg.goods_id)
                        LEFT JOIN goods_brand gb ON(gg.brand_id=gb.brand_id)
                        LEFT JOIN stock_spec ss ON(gs.spec_id=ss.spec_id AND ss.warehouse_id={$warehouse_id})
                        LEFT JOIN stock_spec_position ssp ON(gs.spec_id=ssp.spec_id AND ssp.warehouse_id={$warehouse_id})
                        LEFT JOIN cfg_goods_unit cgu ON(gs.unit = cgu.rec_id)
                        LEFT JOIN cfg_warehouse_position cwp ON(ss.last_position_id = cwp.rec_id)
                        LEFT JOIN cfg_warehouse_position cwp2 ON(cwp2.rec_id = -{$warehouse_id})
                        $where";
                $sql_count = "SELECT COUNT(1) AS total
                        FROM goods_spec gs
                        LEFT JOIN goods_goods gg ON(gs.goods_id=gg.goods_id)
                        LEFT JOIN goods_brand gb ON(gg.brand_id=gb.brand_id)
                        LEFT JOIN stock_spec ss ON(gs.spec_id=ss.spec_id AND ss.warehouse_id={$warehouse_id})
                        LEFT JOIN cfg_goods_unit cgu ON(gs.unit = cgu.rec_id)
                        $where";
            } else if ( $model == "rule") { // 赠品策略接口，初始化的时候warehouse_id =all ,当搜索条件有仓库时，应显示对应仓库的单品
                if($warehouse_id == "all"){
                    $stock_num = "CAST(IFNULL(sum(ss.stock_num),0) AS DECIMAL(19,".$point_number.")) stock_num";
                    $orderable_num = "CAST(IFNULL(sum(".$available_str."),0) AS DECIMAL(19,".$point_number.")) orderable_num";
                    $re =$goods_spec_db->query("SELECT warehouse_id  FROM stock_spec GROUP BY warehouse_id");
                    if(empty($re)){
                        $data = array("total" => 0,"rows" => array());
                        $this->ajaxReturn($data);
                    }
                    $warehouse_id =array();
                    foreach ($re as $v) {
                        $warehouse_id[] = $v['warehouse_id'];
                    }
                    $warehouse_id = join(',',$warehouse_id);
                    $sql       = "SELECT gs.spec_no,gs.img_url,gs.unit base_unit_id, gs.spec_id as id,gs.spec_id,gs.spec_name, gs.market_price, gs.spec_code, gs.barcode, gs.lowest_price, gs.barcode,gs.retail_price, gs.weight,
                        gg.short_name,gg.spec_count,gg.goods_no,gg.goods_name, gg.goods_id,gg.brand_id,
                        gb.brand_name,
                        cgu.name as unit_name,
                        ".$stock_num.",".$orderable_num.",
                        CAST(1 as DECIMAL(19,4)) AS rebate,CAST(0 AS DECIMAL(19,4)) AS expect_num, CAST(1 AS DECIMAL(19,4) ) AS num,CAST(0 AS DECIMAL(19,4)) AS total_cost,CAST(IFNULL(ss.cost_price,0) AS DECIMAL(19,4)) AS tax_price,CAST(IFNULL(ss.cost_price,0) AS DECIMAL(19,4)) AS tax_amount,CAST(0 AS DECIMAL(19,4)) AS tax,CAST(IFNULL(ss.cost_price,0) AS DECIMAL(19,4)) AS src_price,CAST(IFNULL(ss.cost_price,0) AS DECIMAL(19,4)) AS cost_price
                        FROM goods_spec gs
                        LEFT JOIN goods_goods gg ON(gs.goods_id=gg.goods_id)
                        LEFT JOIN goods_brand gb ON(gg.brand_id=gb.brand_id)
                        LEFT JOIN stock_spec ss ON(gs.spec_id=ss.spec_id AND ss.warehouse_id IN ($warehouse_id))
                        LEFT JOIN cfg_goods_unit cgu ON(gs.unit = cgu.rec_id)
                        $where GROUP BY gs.spec_no";
                    $sql_count = "SELECT COUNT(1) AS total FROM (SELECT gs.spec_id FROM goods_spec gs
                        LEFT JOIN goods_goods gg ON(gs.goods_id=gg.goods_id)
                        LEFT JOIN goods_brand gb ON(gg.brand_id=gb.brand_id)
                        LEFT JOIN stock_spec ss ON(gs.spec_id=ss.spec_id AND ss.warehouse_id IN ($warehouse_id))
                        LEFT JOIN cfg_goods_unit cgu ON(gs.unit = cgu.rec_id)
                        $where GROUP BY gs.spec_no)t";
                }else {
                    $where  = $where ."AND ss.warehouse_id = {$warehouse_id}";
                    $sql    = "SELECT gs.spec_id as id,gs.img_url,gs.spec_id,gs.spec_name,gs.spec_no,gs.market_price,gs.spec_code,gs.barcode,gs.lowest_price,gs.wholesale_price,gs.tax_rate,gs.barcode,gs.retail_price,gs.weight,gs.unit as base_unit_id,
                            gg.short_name,gg.spec_count,gg.goods_no,gg.goods_name,gg.goods_id,gg.brand_id,
                            ss.stock_num,ss.lock_num,ss.subscribe_num,ss.order_num,ss.sending_num,ss.purchase_arrive_num,ss.transfer_num,ss.unpay_num,CAST(IFNULL(".$available_str.", 0) AS DECIMAL(19,4)) as orderable_num,ss.status,CAST(IFNULL(ss.cost_price,0) AS DECIMAL(19,4)) AS price,CAST((1*IFNULL(ss.cost_price,0)) AS DECIMAL(19,4)) AS total_amount,
                            gb.brand_name,
                            cgu.name as unit_name,
                            CAST(0 AS DECIMAL(19,4)) as src_price,
                            ".$num.",
                            '' AS remark
                        FROM goods_spec gs
                        LEFT JOIN goods_goods gg ON(gs.goods_id=gg.goods_id)
                        LEFT JOIN goods_brand gb ON(gg.brand_id=gb.brand_id)
                        LEFT JOIN stock_spec ss ON(gs.spec_id=ss.spec_id)
                        LEFT JOIN cfg_goods_unit cgu ON(gs.unit = cgu.rec_id)
                        $where";//                         LEFT JOIN cfg_goods_unit cgu ON(gg.unit = cgu.rec_id)
                    $sql_count = "SELECT COUNT(1) AS total FROM goods_spec gs
                        LEFT JOIN goods_goods gg ON(gs.goods_id=gg.goods_id)
                        LEFT JOIN goods_brand gb ON(gg.brand_id=gb.brand_id)
                        LEFT JOIN stock_spec ss ON(gs.spec_id=ss.spec_id)
                        LEFT JOIN cfg_goods_unit cgu ON(gs.unit = cgu.rec_id)
                        $where";
                }
            }else if($model == 'purchase'){      //采购开单选择货品
				$where = $where.' and ppg.provider_id='.$provider_id;
				$sql = "select gs.spec_id as id,gs.spec_id,gs.spec_name,gs.img_url,gg.short_name,gs.spec_no,gg.spec_count,gg.goods_no,gg.goods_name,gs.market_price,
                        gs.spec_code,gb.brand_name,gs.barcode,cgu.name as unit_name,gg.unit as base_unit_id,gs.lowest_price,gg.goods_id,
                        0 as src_price,gs.wholesale_price,gs.member_price,gs.tax_rate,gg.brand_id,gs.barcode,gs.retail_price,CAST(IFNULL(ss.cost_price,0) AS DECIMAL(19,4)) AS tax_price,CAST(IFNULL(ss.cost_price,0) AS DECIMAL(19,4)) AS tax_amount,CAST(0 AS DECIMAL(19,4)) AS tax,CAST(IFNULL(ss.cost_price,0) AS DECIMAL(19,4)) AS src_price,ppg.price AS cost_price,
						".$stock_num.",".$orderable_num.",".$expect_num.", ".$num.",	
					   gs.weight,gs.retail_price,ppg.rec_id from purchase_provider_goods ppg 
						LEFT JOIN stock_spec ss on (ss.spec_id = ppg.spec_id and ss.warehouse_id ={$warehouse_id})
						LEFT JOIN goods_spec gs on (gs.spec_id = ppg.spec_id)
                        LEFT JOIN goods_goods gg ON(gs.goods_id=gg.goods_id)
                        LEFT JOIN goods_brand gb ON(gg.brand_id=gb.brand_id)
                        LEFT JOIN cfg_goods_unit cgu ON(gg.unit = cgu.rec_id) $where";
				$sql_count = "SELECT COUNT(1) AS total FROM purchase_provider_goods ppg 
				        LEFT JOIN goods_spec gs on (gs.spec_id = ppg.spec_id) 
                        LEFT JOIN goods_goods gg ON(gs.goods_id=gg.goods_id)
                        LEFT JOIN goods_brand gb ON(gg.brand_id=gb.brand_id)
                        LEFT JOIN stock_spec ss ON(gs.spec_id=ss.spec_id and ss.warehouse_id ={$warehouse_id})
                        LEFT JOIN cfg_goods_unit cgu ON(gs.unit = cgu.rec_id)
                        $where";		
			}else if($model == 'goodsSpecBarcode'){      //采购开单选择货品
                $sql          = "SELECT gbc.rec_id AS id, gs.spec_id,gs.img_url,gbc.barcode, gs.spec_no, gg.goods_name, gg.short_name, gg.goods_no, gs.spec_name, gs.spec_code,gs.prop1,gs.prop2,gs.prop3,gs.prop4,gb.brand_name, IF(gbc.is_master,'是','否') is_master, 0 AS is_suite, gbc.type FROM goods_barcode gbc
                                 LEFT JOIN goods_spec gs ON gbc.type=1 AND gbc.target_id=gs.spec_id
                                 LEFT JOIN goods_goods gg ON(gg.goods_id=gs.goods_id)
                                 LEFT JOIN goods_brand gb ON(gg.brand_id=gb.brand_id)".$where ." AND gbc.type=1";

                $sql_count    = "SELECT COUNT(1) AS total  FROM goods_barcode gbc
                                 LEFT JOIN goods_spec gs ON gbc.type=1 AND gbc.target_id=gs.spec_id
                                 LEFT JOIN goods_goods gg ON(gg.goods_id=gs.goods_id)
                                 LEFT JOIN goods_brand gb ON(gg.brand_id=gb.brand_id)".$where ." AND gbc.type=1";

			}else if (($warehouse_id != "all" && $model == "pd") ) {//盘点开单时，需要从相应的仓库中出库，但是允许0库存
                $where = $where." AND ss.warehouse_id={$warehouse_id} ";
                $sql       = "SELECT gs.spec_id as id,gs.img_url,gs.spec_id,gs.spec_name,gs.spec_no,gs.market_price,gs.spec_code,gs.barcode,gs.lowest_price,gs.wholesale_price,gs.tax_rate,gs.barcode,gs.retail_price,gs.weight,gs.unit as base_unit_id,
                            gg.short_name,gg.spec_count,gg.goods_no,gg.goods_name,gg.goods_id,gg.brand_id,
                            ".$stock_num.",ss.lock_num,ss.subscribe_num,ss.order_num,ss.sending_num,ss.purchase_arrive_num,ss.transfer_num,ss.unpay_num,".$orderable_num.",ss.status,CAST(IFNULL(ss.cost_price,0) AS DECIMAL(19,4)) AS price,CAST((1*IFNULL(ss.cost_price,0)) AS DECIMAL(19,4)) AS total_amount,
                            gb.brand_name,
                            IFNULL(cwp.position_no,cwp2.position_no) position_no,
							IFNULL(cwp.rec_id,cwp2.rec_id) position_id,
                            cgu.name as unit_name,
                            CAST(0 AS DECIMAL(19,4)) as src_price,
                            ".$num.",
                            '' AS remark
                        FROM goods_spec gs
                        LEFT JOIN goods_goods gg ON(gs.goods_id=gg.goods_id)
                        LEFT JOIN goods_brand gb ON(gg.brand_id=gb.brand_id)
                        ".$left_join_goods_class_str."
                        LEFT JOIN stock_spec ss ON(gs.spec_id=ss.spec_id)
                        LEFT JOIN cfg_goods_unit cgu ON(gs.unit = cgu.rec_id)
                        LEFT JOIN stock_spec_position ssp ON(ssp.spec_id = gs.spec_id and ssp.warehouse_id = {$warehouse_id})
                        LEFT JOIN cfg_warehouse_position cwp ON(ssp.position_id= cwp.rec_id)
                        LEFT JOIN cfg_warehouse_position cwp2 ON(cwp2.rec_id = -ss.warehouse_id)

                        $where";//                         LEFT JOIN cfg_goods_unit cgu ON(gg.unit = cgu.rec_id)
                $sql_count = "SELECT COUNT(1) AS total FROM goods_spec gs
                        LEFT JOIN goods_goods gg ON(gs.goods_id=gg.goods_id)
                        ".$left_join_goods_class_str."
                        LEFT JOIN goods_brand gb ON(gg.brand_id=gb.brand_id)
                        LEFT JOIN stock_spec ss ON(gs.spec_id=ss.spec_id)
                        LEFT JOIN cfg_goods_unit cgu ON(gs.unit = cgu.rec_id)
                        $where";//                         LEFT JOIN cfg_goods_unit cgu ON(gg.unit = cgu.rec_id)
            }else if($model == 'purchase_return'){      //采购退货选择货品
				$where = $where.' and ppg.provider_id='.$provider_id;
				$sql = "select gs.spec_id as id,gs.spec_id,gs.img_url,gs.spec_name,gg.short_name,gs.spec_no,gg.spec_count,gg.goods_no,gg.goods_name,gs.market_price,
                        gs.spec_code,gb.brand_name,gs.barcode,cgu.name as unit_name,gg.unit as base_unit_id,gs.lowest_price,gg.goods_id,
                        0 as src_price,gs.wholesale_price,gs.member_price,gs.tax_rate,gg.brand_id,gs.barcode,gs.retail_price,CAST(IFNULL(ss.cost_price,0) AS DECIMAL(19,4)) AS tax_price,CAST(IFNULL(ss.cost_price,0) AS DECIMAL(19,4)) AS tax_amount,CAST(0 AS DECIMAL(19,4)) AS tax,CAST(IFNULL(ss.cost_price,0) AS DECIMAL(19,4)) AS src_price,ppg.price AS cost_price,
						".$stock_num.",".$orderable_num.",".$expect_num.", ".$num.",	
					   gs.weight,gs.retail_price,ppg.rec_id from purchase_provider_goods ppg 
						inner JOIN stock_spec ss on (ss.spec_id = ppg.spec_id and ss.warehouse_id ={$warehouse_id})
						LEFT JOIN goods_spec gs on (gs.spec_id = ppg.spec_id)
                        LEFT JOIN goods_goods gg ON(gs.goods_id=gg.goods_id)
                        LEFT JOIN goods_brand gb ON(gg.brand_id=gb.brand_id)
                        LEFT JOIN cfg_goods_unit cgu ON(gg.unit = cgu.rec_id) $where";
				$sql_count = "SELECT COUNT(1) AS total FROM purchase_provider_goods ppg 
				        LEFT JOIN goods_spec gs on (gs.spec_id = ppg.spec_id) 
                        LEFT JOIN goods_goods gg ON(gs.goods_id=gg.goods_id)
                        LEFT JOIN goods_brand gb ON(gg.brand_id=gb.brand_id)
                        inner JOIN stock_spec ss ON(gs.spec_id=ss.spec_id and ss.warehouse_id ={$warehouse_id})
                        LEFT JOIN cfg_goods_unit cgu ON(gs.unit = cgu.rec_id)
                        $where";	
			}
            try {
                $total         = $goods_spec_db->query($sql_count);
                $data['total'] = $total[0]["total"];
//             $data['total'] = $goods_spec_db->query($sql_count)[0]["total"];
                $data['rows'] = $goods_spec_db->query($sql . " limit $limit");
            } catch (\PDOException $e) {
                Log::write($e->getMessage());
                $data = array("total" => 0,"rows" => array());
            } catch(\Exception $e){
                Log::write($e->getMessage());
                $data = array("total" => 0,"rows" => array());
            }
            $this->ajaxReturn($data);
        } else {
            $params = I("get.");//获取params参数
            $prefix = "goods_spec_select_datagrid";//页面中元素ID的命名前缀
            if (isset($params["prefix"])) {
                $prefix = $params["prefix"];
            }
            $type = true;//是否包含子表
            if (isset($params["type"])  && $params["type"] == "false") {
                $type = "";
            }
            $warehouse_id = "all";//仓库ID
            if (isset($params["warehouse_id"])) {
                $warehouse_id = $params["warehouse_id"];
            }
			$provider_id = "";
			if (isset($params["provider_id"])) { //供应商id
                $provider_id = $params["provider_id"];
            }
            $value = array("warehouse_id" => $warehouse_id);
            $model = "";
            if (isset($params["model"])) {
                $model = $params["model"];
            }
            $value["model"] = $model;
            //根据是否包含仓库ID决定datagrid的fields
            if ($warehouse_id == "all" && $model == "") {
                $fields = D("GoodsSpec")->getSelectStr();
            }else if($model=='goodsSpecBarcode'){
                $fields = D("GoodsSpec")->getGoodsBarcodeDatagridFields();
            }else{
                $fields = D("GoodsSpec")->getStockSelectDatagridFields();
            }
            //根据$type确定页面中datagrid的高度
            if ($type) {
                $style = "height:300px";
            } else {
                $style = "height:430px";
            }
            $url = U("GoodsSpec/selectGoodsSpec");//根据是否存在warehouse_id修改url
			if($warehouse_id !="all" && $model == "purchase_return"){
				 $url = $url . "?warehouse_id=" . $warehouse_id . "&model=" . $model."&provider_id=".$provider_id;
			}else if($warehouse_id !="all" && $model == "purchase"){
				 $url = $url . "?warehouse_id=" . $warehouse_id . "&model=" . $model."&provider_id=".$provider_id;
			}else if ($warehouse_id != "all" && $model == "") {
                $url = $url . "?warehouse_id=" . $warehouse_id;
            }else if ($warehouse_id != "all" && $model == "transfer") {
                $url = $url . "?warehouse_id=" . $warehouse_id . "&model=" . $model."&to_warehouse_id=".$params['to_warehouse_id'];
            }  else if ($warehouse_id != "all" && $model != "") {
                $url = $url . "?warehouse_id=" . $warehouse_id . "&model=" . $model;
            } else if ($warehouse_id == "all" && $model != ""){
                $url = $url . "?warehouse_id=" . $warehouse_id . "&model=" . $model;
            }
            //根据$prefix完成表中各元素的命名
            $id_list = array(
                "datagrid"     => $prefix . "_goods_spec_select_datagrid",
                "sub_datagrid" => $prefix . "_sub_goods_spec_select_datagrid",
                "form"         => $prefix . "_goods_spec_select_form",
                "toolbar"      => $prefix . "_goods_spec_select_toolbar",
                "sub_toolbar"  => $prefix . "_sub_goods_spec_select_toolbar",
                "class_id"     => $prefix . "_goods_spec_class_id",
                "prefix"       => $prefix
            );
            //主表属性
            $datagrid = array(
                "id"      => $id_list["datagrid"],
                "style"   => $style,
                "class"    =>'',
                "options" => array(
                    "toolbar"    => $id_list["toolbar"],
                    "url"        => $url,
                    "fitColumns" => false,
                    "fit"        => true,
                    'singleSelect' => false,
                    'ctrlSelect'   => true
                ),
                "fields"  => $fields
            );
            $checkbox=array('field' => 'ck','checkbox' => true);
            array_unshift($datagrid['fields'],$checkbox);
            //子表属性
            if($model == 'transfer' ){
                $sub_datagrid = array(
                    "id"      => $id_list["sub_datagrid"],
                    "style"   => "height:130px",
                    "options" => array(
                        "border"       => false,
                        "singleSelect" => true,
                        "pagination"   => false,
                        "rownumbers"   => true,
                        "fitColumns"   => true,
                        "fit"          => true,
                    ),
                    "fields"  => D("GoodsSpec")->getSubStockSelectDatagridFields()
                );
            }else if($model == 'goodsSpecBarcode' ){
                $sub_datagrid = array(
                "id"      => $id_list["sub_datagrid"],
                "style"   => "height:130px",
                    "options" => array(
                        "border"       => false,
                        "singleSelect" => true,
                        "pagination"   => false,
                        "rownumbers"   => true,
                        "fitColumns"   => false,
                        "fit"          => true,
                    ),
                "fields"  => D("GoodsSpec")->getSubGoodsBarcodeDatagridFields()
                );
            }else{
                $sub_datagrid = array(
                "id"      => $id_list["sub_datagrid"],
                "style"   => "height:130px",
                    "options" => array(
                        "border"       => false,
                        "singleSelect" => true,
                        "pagination"   => false,
                        "rownumbers"   => true,
                        "fitColumns"   => false,
                        "fit"          => true,
                    ),
                "fields"  => D("GoodsSpec")->getSubSelectStr()
                );
            }

            //主表参数
            $params = array(
                "controller" => strtolower(CONTROLLER_NAME),
                "datagrid"   => array("id" => $id_list["datagrid"]),
                "search"     => array("form_id" => $id_list["form"])
            );
            $list   = UtilDB::getCfgList(array("brand","warehouse"), array("brand" => array("is_disabled" => 0)));
            //向页面发送参数
            $this->assign("warehouse",$list["warehouse"]);
            $this->assign('model',$model);//用来判断是哪个模块下显示的单品列表，显示的页面样式跟数据有所区别
            $this->assign("goods_brand", $list["brand"]);
            $this->assign("id_list", $id_list);
            $this->assign("params", json_encode($params));
            $this->assign("datagrid", $datagrid);
            $this->assign("type", $type);
            $this->assign("prefix", $prefix);
            $this->assign("value", $value);
            //如果$type为真，则发送子表的属性
            if ($type) {
                $this->assign("sub_datagrid", $sub_datagrid);
            }
            $model=='transfer'?$this->display('transfer_select'):$this->display("select");
        }
    }
    
    public function delGoodsSpec($id, $type) 
    {
        $model_db       = M();
        $is_rollback    = false;
        $sql_error_info = '';
        $goods_id = 0;
        $result=array('status'=>0,'info'=>'');
        $list=array();
        $tmp_arr=array();
        $check_spec_count = array();
        try {

            foreach ($id as $v){
                if($type==1 && $v == ''){
                    $result = array('status'=>0,'info'=>'');
                    break;
                }
                $sql_error_info     = 'delGoodsSpec-get_goods_spec';

                //取出所选行对应goods_spec表的单品信息
                $res_goods_spec_arr = $model_db->table('goods_spec')->alias('gs')->field('gs.spec_id, gs.spec_no,gs.spec_name,gs.goods_id')->where(array('spec_id' => array('eq', $v), 'deleted' => array('eq', 0)))->find();

                if(empty($res_goods_spec_arr['goods_id'])) {
                    $list[]=array('spec_no'=>$res_goods_spec_arr['spec_no'],'info'=>'该单品已被删除，请重新打开本界面');
                    continue;
                }

                $res_spec_count     = $model_db->query("SELECT spec_count,goods_id FROM goods_goods WHERE goods_id=%d AND deleted=0",$res_goods_spec_arr['goods_id']);

                if(!array_key_exists($res_goods_spec_arr['goods_id'], $check_spec_count)){
                    $check_spec_count[$res_goods_spec_arr['goods_id']] = $res_spec_count[0]['spec_count'];
                }
                if ($type == 2 && $check_spec_count[$res_goods_spec_arr['goods_id']] < 2) {
                    $goods_id = $res_goods_spec_arr['goods_id'];
                }
                $tmp_arr[]=$res_goods_spec_arr;

                $check_spec_count[$res_goods_spec_arr['goods_id']] -= 1;
            }
            $goods_goods_db = Factory::getModel('GoodsGoods');
            $goods_goods_db->delSpec($model_db, $tmp_arr, $is_rollback, $sql_error_info, $list,$goods_id);

            if (count($list)>0){
                $result=$type==1?array('status'=>1,'info'=>$list[0]['info']):array('status'=>2,'info'=>array('total'=>count($list),'rows'=>$list));
            }
        } catch (\PDOException $e) {
            if ($is_rollback) {
                $model_db->rollback();
            }
            Log::write($sql_error_info . ':' . $e->getMessage());
            $result=array('status'=>1,'info'=>'未知错误，请联系管理员');
        } catch (\Exception $e) {
            if ($is_rollback) {
                $model_db->rollback();
            }
            $result=array('status'=>1,'info'=> $e->getMessage());
        }
        $this->ajaxReturn($result);
    }

    public function showHotCakeDialog()
    {
        $spec_id = I("get.ids");
        $params = array(
            'rec_id'      => $spec_id,
            'chg_dialog'  => 'batchEditHotCake',
            'id_datagrid' => strtolower(CONTROLLER_NAME . '_' . "edit_hotcake" . '_datagrid'),
            'form_id'     => 'goods_spec_hotcake_form',
            'form_url'    => U('GoodsSpec/chgHotCake'),
        );

        $this->assign("params", $params);
        $this->display('dialog_hotcake');
    }

    public function chgHotCake()
    {
        $rec_id = I('post.rec_id');
        $hotcake = I('post.is_hotcake');
        $result = array('status'=>0,'msg'=>'操作成功');
        try{
            D('GoodsSpec')->chgHotCake($rec_id,$hotcake,$result);
        } catch (BusinessLogicException $e){
            \Think\Log::write('-chgHotCake-' . $e->getMessage());
            $result=array('status'=>1,'msg'=> $e->getMessage());
        } catch (\Exception $e){
            \Think\Log::write('-chgHotCake-' . $e->getMessage());
            $result=array('status'=>1,'msg'=> parent::UNKNOWN_ERROR);
        }
        $this->ajaxReturn($result);
    }
}