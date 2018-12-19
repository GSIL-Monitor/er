<?php

namespace Goods\Controller;

use Common\Controller\BaseController;
use Common\Common\UtilTool;
use Common\Common\UtilDB;
use Common\Common\Factory;
use Common\Common\ExcelTool;
use Think\Exception\BusinessLogicException;
use Think\Log;

class GoodsGoodsController extends BaseController {

    public function getGoodsList($page = 1, $rows = 20, $search = array(), $sort = 'goods_id', $order = 'desc') {
        if (IS_POST) {
            //先查询，后连接数据库
            $goods_goods_db = M('goods_goods');
            $page = intval($page);
            $rows = intval($rows);
            //搜索表单-数据处理
            $where_goods_goods_str = '';
            set_search_form_value($where_goods_goods_str, 'deleted', 0, 'gg_1', 2, ' AND ');
            $where_goods_spec_str        = '';
            $where_left_join_goods_class = '';
            foreach ($search as $k => $v) {
                if ($v === "") continue;
                switch ($k) {   //set_search_form_value->Common/Common/function.php
                    case 'goods_no'://goods_goods
                        set_search_form_value($where_goods_goods_str, $k, $v, 'gg_1', 1, ' AND ');
                        break;
                    case 'goods_name':
                        set_search_form_value($where_goods_goods_str, $k, $v, 'gg_1', 10, ' AND ');
                        break;
                    case 'goods_type':
                        set_search_form_value($where_goods_goods_str, $k, $v, 'gg_1', 2, ' AND ');
                        break;
                    case 'short_name':
                        set_search_form_value($where_goods_goods_str, $k, $v, 'gg_1', 6, ' AND ');
                        break;
                    case 'brand_id':
                        set_search_form_value($where_goods_goods_str, $k, $v, 'gg_1', 2, ' AND ');
                        break;
                    case 'flag_id':
                        set_search_form_value($where_goods_goods_str, $k, $v, 'gg_1', 2, ' AND ');
                        break;
                    case 'class_id'://分类         goods_gooods
                        $where_left_join_goods_class = set_search_form_value($where_goods_goods_str, $k, $v, 'gg_1', 7, ' AND ');
                        break;
                    case 'spec_no'://goods_spec
                        set_search_form_value($where_goods_spec_str, $k, $v, 'gs', 10, ' AND ');
                        break;
                    case 'spec_name':
                        set_search_form_value($where_goods_spec_str, $k, $v, 'gs', 6, ' AND ');
                        break;
                    case 'barcode':
                        set_search_form_value($where_goods_spec_str, $k, $v, 'gs', 1, ' AND ');
                        break;
                    case 'created'://处理
                        break;
                    case 'large_type':
                        break;
                }
            }
            //拼接货品分类的搜索条件
            /* if (isset($search["class_id"]) && $search["class_id"] != "") {
                $goods_class_db        = M("goods_class");
                $arr_goods_class       = $goods_class_db->field("class_id,path")->select();
                $where_goods_class_str = "(";
                foreach ($arr_goods_class as $k => $v) {
                    $arr_class_path = explode(",", $v["path"]);
                    foreach ($arr_class_path as $value) {
                        if ($value == $search["class_id"]) {
                            $where_goods_class_str .= $v["class_id"] . ",";
                            break;
                        }
                    }
                }
                if ($where_goods_class_str != "(") {
                    $len                               = strlen($where_goods_class_str);
                    $where_goods_class_str[ $len - 1 ] = ")";
                    $where_goods_class_str .= " ";
                } else {
                    $where_goods_class_str = "";
                }
                $where_goods_class_str = " AND gc.class_id in " . $where_goods_class_str;
            } */
            $limit         = ($page - 1) * $rows . "," . $rows;//分页
            $order         = 'gg_1.' . $sort . ' ' . $order;//排序
            $order         = addslashes($order);
            $sql_sel_limit = 'SELECT gg_1.goods_id FROM goods_goods gg_1 ';
            $sql_total     = 'SELECT COUNT(1) AS total FROM goods_goods gg_1 ';
            $sql_where     = '';
            $flag          = false;
            $sql_limit     = ' ORDER BY ' . $order . ' LIMIT ' . $limit;
            if (!empty($where_goods_spec_str)) {
                $sql_where .= ' LEFT JOIN goods_spec gs ON gg_1.goods_id=gs.goods_id AND gs.deleted=0 ';
                $sql_limit = ' GROUP BY gg_1.goods_id ORDER BY ' . $order . ' LIMIT ' . $limit;
                $sql_total = 'SELECT COUNT(DISTINCT gg_1.goods_id) AS total FROM goods_goods gg_1 ';
            }
            $sql_where .= $where_left_join_goods_class;
            /* if (isset($where_goods_class_str) && $where_goods_class_str != "") {
                $sql_where .= 'LEFT JOIN goods_class gc ON gg_1.class_id=gc.class_id ';
            } */
            $flag = false;
            connect_where_str($sql_where, $where_goods_goods_str, $flag);
            connect_where_str($sql_where, $where_goods_spec_str, $flag);
            //connect_where_str($sql_where, $where_goods_class_str, $flag);
            $sql_sel_limit .= $sql_where;
            $sql_total .= $sql_where;
            //得到该条件下的分页的sql
            $sql_sel_limit .= $sql_limit;
            //查询表字段整理-得到查询的字段字符串
            $sql_fields_str    = 'SELECT gg.goods_id AS id,gg.goods_type,gg.goods_no,gg.goods_name,gg.short_name,gg.alias,gg.spec_count,gg.origin,gg.flag_id,gb.brand_name AS brand_id,gc.class_name AS class_id,cgu.name AS unit FROM goods_goods gg ';
            $sql_left_join_str = 'LEFT JOIN goods_brand gb ON gg.brand_id=gb.brand_id LEFT JOIN cfg_goods_unit cgu ON gg.unit=cgu.rec_id LEFT JOIN goods_class gc ON gg.class_id=gc.class_id';
            //得到执行的sql语句
            $sql = $sql_fields_str . 'INNER JOIN(' . $sql_sel_limit . ')gg_2 ON gg.goods_id=gg_2.goods_id ' . $sql_left_join_str;
            try {
                //得到查询的数据
                $res_total = $goods_goods_db->query($sql_total);
                $total     = intval($res_total[0]['total']);
                $list      = $total ? $goods_goods_db->query($sql) : array();
                $data      = array('total' => $total, 'rows' => $list);//datagrid需要的json下标totle和rows
            } catch (\PDOException $e) {
                \Think\Log::write($e->getMessage());
                $data = array('total' => 0, 'rows' => array());
            } catch (\Exception $e) {
                \Think\Log::write($e->getMessage());
                $data = array("total" => 0, "rows" => array());
            }
            $this->ajaxReturn($data);
        } else {
            $id_list  = array(
                'toolbar'       => 'goods_goods_toobbar',
                'tab_container' => 'goods_goods_tab_container',
                'id_datagrid'   => strtolower(CONTROLLER_NAME . '_' . ACTION_NAME . '_datagrid'),
                'edit'          => 'goods_goods_edit_dialog',
                //'more_button'   => 'goods_goods_more_button',
                'more_content'  => 'goods_goods_more_content',
                'hidden_flag'   => 'goods_goods_hidden_flag',
                'set_flag'      => 'goods_goods_set_flag',
                'search_flag'   => 'goods_goods_search_flag',//标记作为搜索条件,不作为搜索条件不写
                'form'          => 'goods_goods_search_form',
                'fileForm'      => 'goods_goods_file_form',
                'fileDialog'    => 'goods_goods_file_dialog'

            );
            $datagrid = array(
                'id'      => $id_list['id_datagrid'],
                'style'   => '',
                'class'   => '',
                'options' => array(
                    'title'        => '',
                        'url'          => U('GoodsGoods/getGoodsList', array('grid' => 'datagrid')),
                    'toolbar'      => "#{$id_list['toolbar']}",
                    'fitColumns'   => true,
                    'singleSelect' => false,

                    'ctrlSelect'   => true
                ),
                'fields'  => get_field("GoodsGoods", "goods_goods")
            );
            $checkbox=array('field' => 'ck','checkbox' => true);
            array_unshift($datagrid['fields'],$checkbox);
            $arr_tabs = array(
                array('id' => $id_list['tab_container'], 'url' => U('Goods/GoodsCommon/getTabsView') . '?tab=spec_list&prefix=goodsGoods', 'title' => '单品列表'),
                array('id' => $id_list['tab_container'], 'url' => U('Goods/GoodsCommon/getTabsView') . '?tab=goods_set_out_warehouse&prefix=goodsGoods', 'title' => '出库仓库'),
                array('id' => $id_list['tab_container'], 'url' => U('Goods/GoodsCommon/getTabsView') . '?tab=goods_set_out_logistics&prefix=goodsGoods', 'title' => '出库物流'),
                array('id' => $id_list['tab_container'], 'url' => U('Goods/GoodsCommon/getTabsView') . '?tab=goods_log&prefix=goodsGoods', 'title' => '日志'),
            );
            $arr_flag = Factory::getModel('Setting/Flag')->getFlagData(2);
            $params   = array(
                'datagrid' => array('id' => $id_list['id_datagrid']),
                'search'   => array('more_content' => $id_list['more_content'], 'hidden_flag' => $id_list['hidden_flag'], 'form_id' => $id_list['form']),
                'tabs'     => array('id' => $id_list['tab_container'], 'url' => U('GoodsCommon/updateTabsData')),
                'edit'     => array('id' => $id_list['edit'], 'url' => U('GoodsGoods/updateGoods'), 'title' => '货品编辑'),
                'add'      => array('id' => $id_list['edit'], 'url' => U('GoodsGoods/addGoods'), 'title' => '新建货品'),
                'delete'   => array('url' => U('Goods/GoodsGoods/delGoods')),
                'flag'     => array(
                    'set_flag'    => $id_list['set_flag'],
                    'url'         => U('Setting/Flag/flag') . '?flagClass=2',
                    'json_flag'   => $arr_flag['json'],
                    'list_flag'   => $arr_flag['list'],
                    'dialog'      => array('id' => 'flag_set_dialog', 'url' => U('Setting/Flag/setFlag') . '?flagClass=2', 'title' => '颜色标记'),
                    'search_flag' => $id_list['search_flag']
                ),
            );

            $list_form            = UtilDB::getCfgList(array('brand'), array("brand" => array("is_disabled" => 0)));
            $list_form['created'] = array(0 => array('id' => 'all', 'name' => '不限'), 1 => array('id' => '2', 'name' => '今天'), 2 => array('id' => '3', 'name' => '昨天'), 3 => array('id' => '4', 'name' => '本月'));
            $faq_url=C('faq_url');
            $this->assign('faq_url_goods_interpretation',$faq_url['goods_interpretation']);//货品名词解释
            $this->assign('faq_url_goods_question',$faq_url['goods_interpretation']);//货品常见问题
            $this->assign("list", $list_form);
            $this->assign("params", json_encode($params));
            $this->assign('arr_tabs', json_encode($arr_tabs));
            $this->assign("id_list", $id_list);
            $this->assign('datagrid', $datagrid);
            $this->display('show');
        }

    }

    public function addGoods() {
        if (IS_POST) {
            //arr_前缀代表数组(如果返回的值是数组则res_做前缀，_arr为后缀),_data后缀代表从页面传输过来的数据,res_前缀代表执行完sql的返回结果
            $arr_form_data               = I('post.info', '', C('JSON_FILTER'));
            $arr_goods_spec_data         = I('post.spec_datagrid', '', C('JSON_FILTER'));
            $spec_count                  = count($arr_goods_spec_data);
            $arr_form_data['goods_no']   = trim_all($arr_form_data['goods_no']);
            $arr_form_data['goods_name'] = trim($arr_form_data['goods_name']);
            $arr_form_data['spec_count'] = $spec_count;
            $arr_form_data['modified']   = array('exp', 'NOW()');
            $arr_form_data['created']    = array('exp', 'NOW()');
            $goods_goods_db              = Factory::getModel('GoodsGoods');
            $model                       = M();
            $is_rollback                 = false;
            $sql_error_info              = '';
            $userId                      = get_operator_id();
            try {
                if (!($goods_goods_db->create($arr_form_data))) {
                    $this->error($goods_goods_db->getError());
                }
                unset($goods_goods_db);//释放内存GoodsGoodsModel类->对象$goods_goods_db
                $is_rollback = true;
                $model->startTrans();
                //插入货品
                $sql_error_info = 'add_goods_goods';
                $res_goods_id   = $model->table('goods_goods')->add($arr_form_data);
                //插入货品日志
                $arr_goods_log    = array(
                    'goods_type'   => 1,//1-货品 2-组合装
                    'goods_id'     => $res_goods_id,
                    'spec_id'      => 0,
                    'operator_id'  => $userId,//操作者暂时默认为user_id=1
                    'operate_type' => 11,
                    'message'      => '新建货品--' . $arr_form_data['goods_name'],//"CONCAT('新建货品','--','')",
                    'created'      => array('exp', 'NOW()')
                );
                $sql_error_info   = 'add_goods_goods_log';
                $res_goods_log_id = $model->table('goods_log')->add($arr_goods_log);
                //插入单品
                $goods_goods_db = Factory::getModel('GoodsGoods');
                $list_unit      = UtilDB::getCfgList(array('unit'));
                $unit_dict      = UtilTool::array2dict($list_unit['unit']);
                for ($i = 0; $i < $spec_count; $i++) {
                    $arr_goods_spec_data[ $i ]['spec_no'] = trim_all($arr_goods_spec_data[ $i ]['spec_no']);
                    if (!isset($arr_goods_spec_data[ $i ]['spec_no'])) {
                        E('商家编码不能为空');
                    }
                    if (!isset($unit_dict[ $arr_goods_spec_data[ $i ]['unit'] ])) {
                        E('单品存在非法单位');
                    }
                    $goods_goods_db->checkSpecNo($model, $arr_goods_spec_data[ $i ]['spec_no'], $sql_error_info);
                    $arr_goods_spec_data[ $i ]['goods_id']  = $res_goods_id;
                    $arr_goods_spec_data[ $i ]['barcode']  = trim_all($arr_goods_spec_data[ $i ]['barcode']);
                    $arr_goods_spec_data[ $i ]['spec_code']  = trim_all($arr_goods_spec_data[ $i ]['spec_code']);
                    $arr_goods_spec_data[ $i ]['spec_name'] = trim_all(set_default_value($arr_goods_spec_data[ $i ]['spec_name'], '默认规格'));
                    $arr_goods_spec_data[ $i ]['modified']  = array('exp', 'NOW()');
                    $arr_goods_spec_data[ $i ]['created']   = array('exp', 'NOW()');
                }
                $goods_goods_db->addSpec($model, $arr_goods_spec_data, $is_rollback, $sql_error_info, $res_goods_id, $userId, 'insert');
                $model->commit();
            } catch (\PDOException $e) {
                if ($is_rollback) {
                    $model->rollback();
                }
                    \Think\Log::write($sql_error_info . ':' . $e->getMessage());
                $this->error('添加失败');
            } catch (\Exception $e) {
                if ($is_rollback) {
                    $model->rollback();
                }
               // \Think\Log::write($sql_error_info . ':' . $e->getMessage());
                $this->error('添加失败-' . $e->getMessage());
            }
            $this->success('添加成功');
        } else {
            //$datagrid_addGoods = Factory::getModel('GoodsGoods')->getDatagridStr();
            $list_form         = UtilDB::getCfgList(array('brand', 'unit'), array("brand" => array("is_disabled" => 0)));
            $id_list           = array(
                'datagrid_id' => 'add_goods_datagrid',
                'form'        => 'add_goods_form',
                'toolbar'     => 'add_goods_toolbar',
            );
            $fields = get_field("GoodsGoods", "add_goods");
            propFildConv($fields,'prop','goods_spec');
            $datagrid = [
                "id"      => $id_list["datagrid_id"],
                "options" => [
                    "toolbar" => $id_list["toolbar"],
                    'fitColumns'   => false,
                ],
                "fields"  => $fields
            ];
            $this->assign("list", $list_form);
            $this->assign("id_list", $id_list);
            $this->assign("datagrid", $datagrid);
            //$this->assign('datagrid_addGoods', $datagrid_addGoods);
            $this->display('add');
        }
    }

    public function updateGoods($id) {
        if (IS_POST) {
            $arr_form_data               = I('post.info', '', C('JSON_FILTER'));
            $arr_add_spec                = I('post.add_spec', '', C('JSON_FILTER'));
            $arr_update_spec             = I('post.update_spec', '', C('JSON_FILTER'));
            $arr_form_data['goods_no']   = trim_all($arr_form_data['goods_no'],1);
            $arr_form_data['goods_name'] = trim($arr_form_data['goods_name']);
            $spec_count                  = I('post.spec_count');
            $arr_form_data['spec_count'] = $spec_count;
            $arr_form_data['goods_id']   = $id;//
            $model_db                    = M();
            $is_rollback                 = false;
            $sql_error_info              = '';
            $userId                      = get_operator_id();
            //一下两个字段用来比较货品档案的分类和品牌是否改变，如果改变需要刷新所有单品的库存同步规则
            $is_changed_brand = 0;
            $is_changed_class = 0;
            try {
                $goods_goods_db = Factory::getModel('GoodsGoods');
                if (!($goods_goods_db->create($arr_form_data))) {
                    $this->error($goods_goods_db->getError());
                } else {
                    unset($goods_goods_db);//释放内存GoodsGoodsModel类->对象$goods_goods_db
                    unset($arr_form_data['goods_id']);//便于save判断数据->更新数据的时候，如果更新的值和原来的值没有任何不同,返回false
                    $goods_goods_db = Factory::getModel('GoodsGoods');
                    $is_rollback    = true;
                    $model_db->startTrans();
                    /* 更新货品信息 */
                    $sql_error_info      = 'updateGoods-save_goods_goods';
                    $res_goods_goods_arr = M('goods_goods')->alias('gg')->field('gg.goods_id,gg.goods_no,gg.goods_name,gg.short_name,gg.alias,gg.origin,gg.class_id,gg.brand_id,gg.goods_type,gg.unit,gg.flag_id,gg.remark')->where(array('goods_id' => array('eq', $id)))->find();
                    $res_update_goods    = $model_db->table('goods_goods')->where(array('goods_id' => array('eq', $id), 'deleted' => array('eq', 0)))->save($arr_form_data);
                    if ($res_update_goods) {
                        $sql_error_info = 'updateGoods-goods_goods';
                        //修改货品编码
                        if ($arr_form_data['goods_no'] !== $res_goods_goods_arr['goods_no']) {
                            $arr_goods_log    = array(
                                'goods_type'   => 1,//1-货品 2-组合装
                                'goods_id'     => $id,
                                'spec_id'      => 0,
                                'operator_id'  => $userId,
                                'operate_type' => 51,//更新货品编号
                                'message'      => '修改货品货品编码：从 ' . $res_goods_goods_arr['goods_no'] . ' 到 ' . $arr_form_data['goods_no'],
                                'created'      => array('exp', 'NOW()')
                            );
                            $sql_error_info   = 'updateGoods-goods_no';
                            $res_goods_log_id = $model_db->table('goods_log')->add($arr_goods_log);
                        }
                        //货品名称
                        if ($arr_form_data['goods_name'] !== $res_goods_goods_arr['goods_name']) {
                            $arr_goods_log    = array(
                                'goods_type'   => 1,//1-货品 2-组合装
                                'goods_id'     => $id,
                                'spec_id'      => 0,
                                'operator_id'  => $userId,
                                'operate_type' => 51,
                                'message'      => '修改货品名称：从 ' . $res_goods_goods_arr['goods_name'] . ' 到 ' . $arr_form_data['goods_name'],
                                'created'      => array('exp', 'NOW()')
                            );
                            $sql_error_info   = 'updateGoods-goods_name';
                            $res_goods_log_id = $model_db->table('goods_log')->add($arr_goods_log);
                        }
                        //修改货品分类
                        $arr_spec_id    = array();
                        $sql_error_info = 'updateGoods-get_goods_spec_ids';
                        $arr_tmp        = $model_db->table('goods_spec')->field('spec_id')->where('goods_id=' . $id)->select();
                        foreach ($arr_tmp as $v) {
                            $arr_spec_id[] = $v['spec_id'];
                        }
                        if ($arr_form_data['class_id'] != $res_goods_goods_arr['class_id']) {
                            //标记货品分类改变
                            $is_changed_class = 1;
                            $sql_error_info   = 'updateGoods-get_goods_class_path';
                            $res_tmp_arr      = $model_db->table('goods_class')->field('path,class_name')->where('class_id=' . $arr_form_data['class_id'])->find();

                            $sql_error_info = 'updateGoods-api_goods_spec-path';
                            $model_db->table('api_goods_spec')->where(array('is_deleted' => array('eq', 0), 'match_target_type' => array('eq', 1), 'match_target_id' => array('in', $arr_spec_id)))->save(array('class_id_path' => $res_tmp_arr['path']));
                            //$model_db->execute("UPDATE api_goods_spec SET class_id_path=".$res_tmp['path']." WHERE is_deleted=0 AND match_target_type=1 AND match_target_id IN ()");

                            $sql_error_info   = 'updateGoods-get_goods_class_name';
                            $arr_tmp          = $model_db->table('goods_class')->field('class_name')->where('class_id=' . $res_goods_goods_arr['class_id'])->find();
                            $arr_goods_log    = array(
                                'goods_type'   => 1,//1-货品 2-组合装
                                'goods_id'     => $id,
                                'spec_id'      => 0,
                                'operator_id'  => $userId,
                                'operate_type' => 51,
                                'message'      => '修改货品分类：从 ' . $arr_tmp['class_name'] . ' 到 ' . $res_tmp_arr['class_name'],
                                'created'      => array('exp', 'NOW()')
                            );
                            $sql_error_info   = 'updateGoods-goods_class';
                            $res_goods_log_id = $model_db->table('goods_log')->add($arr_goods_log);
                        }
                        //修改货品品牌
                        if ($arr_form_data['brand_id'] != $res_goods_goods_arr['brand_id']) {
                            //标记货品品牌改变
                            $is_changed_brand = 1;
                            $sql_error_info   = 'updateGoods-api_goods_spec-brand_id';
                            $model_db->table('api_goods_spec')->where(array('is_deleted' => array('eq', 0), 'match_target_type' => array('eq', 1), 'match_target_id' => array('in', $arr_spec_id)))->save(array('brand_id' => $arr_form_data['brand_id']));
                            $sql_error_info   = 'updateGoods-get_brand_name';
                            $res_tmp_arr      = $model_db->table('goods_brand')->field('brand_name')->where('brand_id=' . $arr_form_data['brand_id'])->find();
                            $arr_tmp          = $model_db->table('goods_brand')->field('brand_name')->where('brand_id=' . $res_goods_goods_arr['brand_id'])->find();
                            $arr_goods_log    = array(
                                'goods_type'   => 1,//1-货品 2-组合装
                                'goods_id'     => $id,
                                'spec_id'      => 0,
                                'operator_id'  => $userId,
                                'operate_type' => 51,
                                'message'      => '修改货品品牌：从 ' . $arr_tmp['brand_name'] . ' 到 ' . $res_tmp_arr['brand_name'],
                                'created'      => array('exp', 'NOW()')
                            );
                            $sql_error_info   = 'updateGoods-goods_brand';
                            $res_goods_log_id = $model_db->table('goods_log')->add($arr_goods_log);
                        }
                        if ($arr_form_data['class_id'] != $res_goods_goods_arr['class_id'] || $arr_form_data['brand_id'] != $res_goods_goods_arr['brand_id']) {//如果修改了货品的分类 或者 品牌的话刷新相应关联平台货品的同步规则
                            $sql_error_info = 'updateGoods-get_api_goods_spec_info';
                            $res_tmp_arr    = $model_db->query("SELECT * FROM ( SELECT ag.rec_id,rule.rec_id rule_id,rule.priority,rule.rule_no,rule.warehouse_list,rule.stock_flag, rule.percent,rule.plus_value,rule.min_stock,rule.is_auto_listing,rule.is_auto_delisting,rule.is_disable_syn FROM api_goods_spec ag LEFT JOIN cfg_stock_sync_rule rule ON ( rule.is_disabled=0 AND FIND_IN_SET(rule.class_id,ag.class_id_path) AND FIND_IN_SET(ag.shop_id, rule.shop_list)AND ag.brand_id=IF(rule.brand_id=-1,ag.`brand_id`,rule.`brand_id`)) INNER JOIN goods_spec tgs ON (tgs.spec_id=ag.match_target_id AND ag.match_target_type=1 AND ag.is_deleted=0) WHERE tgs.goods_id=%d AND ag.stock_syn_rule_id<>0  ORDER BY rule.priority DESC ) _ALIAS_ GROUP BY rec_id ", $id);
                            $goods_goods_db->addApiSpec($model_db, $res_tmp_arr, $sql_error_info);
                        }
                    }
                    /* 添加单品 */
                    $list_unit = UtilDB::getCfgList(array('unit'));
                    $unit_dict = UtilTool::array2dict($list_unit['unit']);
                    if (!empty($arr_add_spec)) {
                        $spec_num = count($arr_add_spec);
                        for ($i = 0; $i < $spec_num; $i++) {
                            $arr_add_spec[ $i ]['spec_no'] = trim_all($arr_add_spec[ $i ]['spec_no'],1);
                            if (!isset($arr_add_spec[ $i ]['spec_no'])) {
                                E('商家编码不能为空');
                            }
                            if (!isset($unit_dict[ $arr_add_spec[ $i ]['unit'] ])) {
                                E('单品存在非法单位');
                            }
                            $goods_goods_db->checkSpecNo($model_db, $arr_add_spec[ $i ]['spec_no'], $sql_error_info);
                            $arr_add_spec[ $i ]['goods_id']  = $id;
                            $arr_add_spec[ $i ]['spec_name'] = trim_all(set_default_value($arr_add_spec[ $i ]['spec_name'], '默认规格'),1);
                            $arr_add_spec[ $i ]['spec_code'] = trim_all($arr_add_spec[ $i ]['spec_code']);
                            $arr_add_spec[ $i ]['barcode'] = trim_all($arr_add_spec[ $i ]['barcode']);
                            $arr_add_spec[ $i ]['modified']  = array('exp', 'NOW()');
                            $arr_add_spec[ $i ]['created']   = array('exp', 'NOW()');
                            $arr_add_spec[ $i ]['barcode'] = trim_all($arr_add_spec[ $i ]['barcode']);
                            unset($arr_add_spec[ $i ]['spec_id']);
                        }
                        $goods_goods_db->addSpec($model_db, $arr_add_spec, $is_rollback, $sql_error_info, $id, $userId, 'update');
                    }
                    /* 更新单品 */
                    if (!empty($arr_update_spec)) {
                        $spec_num = count($arr_update_spec);
                        $spec_log = array();//日志
                        for ($i = 0; $i < $spec_num; $i++) {
                            $str_update_info                  = '';
                            $arr_update_spec[ $i ]['spec_no'] = trim_all($arr_update_spec[ $i ]['spec_no'],1);
                            if (!isset($arr_update_spec[ $i ]['spec_no'])) {
                                E('商家编码不能为空');
                            }
                            if (!isset($unit_dict[ $arr_update_spec[ $i ]['unit'] ])) {
                                E('单品存在非法单位');
                            }
                            //查询加锁--防止未更新完成被请求数据
                            $sql_error_info = 'updateGoods-get_goods_spec';
                            $res_spec_arr   = $model_db->table('goods_spec')->lock(true)->alias('gs')->field('gs.spec_id, gs.img_url, gs.spec_no, gs.spec_name, gs.spec_code, gs.barcode, gs.retail_price, gs.wholesale_price, gs.member_price, gs.market_price, gs.lowest_price, gs.validity_days, gs.length, gs.width, gs.height, gs.unit,gs.sale_score, gs.pack_score, gs.pick_score, gs.weight, gs.tax_rate, gs.is_allow_neg_stock, gs.large_type')->where(array('spec_id' => array('eq', $arr_update_spec[ $i ]['spec_id']), 'deleted' => array('eq', 0)))->find();
                            //商家编码
                            if ($arr_update_spec[ $i ]['spec_no'] !== $res_spec_arr['spec_no']) {
                                $goods_goods_db->checkSpecNo($model_db, $arr_update_spec[ $i ]['spec_no'], $sql_error_info);
                                $sql_error_info = 'updateGoods-goods_merchant_no';
                                $model_db->table('goods_merchant_no')->where('type=1 AND target_id=' . $arr_update_spec[ $i ]['spec_id'])->save(array('merchant_no' => $arr_update_spec[ $i ]['spec_no']));
                                $str_update_info .= ' 商家编码：从' . $res_spec_arr['spec_no'] . ' 到 ' . $arr_update_spec[ $i ]['spec_no'];
                            }
                            //主条码
                            $arr_update_spec[$i]['barcode'] = trim_all($arr_update_spec[$i]['barcode']);
                            if ($arr_update_spec[ $i ]['barcode'] !== $res_spec_arr['barcode']) {
                                $sql_error_info   = 'updateGoods-goods_barcode-check';
                                $goods_barcode_db = M("goods_barcode");
                                if($res_spec_arr['barcode'] != ''){
                                    $res_del_sql = $goods_barcode_db->execute("DELETE FROM goods_barcode WHERE barcode='%s' AND type=1 AND target_id=%d", array($res_spec_arr['barcode'], $res_spec_arr['spec_id']));
                                    if($res_del_sql===false || $res_del_sql==0){
                                        \Think\Log::write(' 主条码：从' . $res_spec_arr[ $i ]['barcode'] .'单品ID：'. $res_spec_arr['spec_id'].'  sql 删除语句未成功','WARN');
                                    }
                                }
                                if ($arr_update_spec[ $i ]['barcode'] != '') {
                                    $arr_goods_barcode = array(
                                      'barcode'     =>  $arr_update_spec[ $i ]['barcode'],
                                        'type'      => 1,
                                        'target_id' => $arr_update_spec[ $i ]['spec_id'],
                                    );
                                    $sql_error_info = 'updateGoods-goods_barcode_select';
                                    $count = $goods_barcode_db->where($arr_goods_barcode)->count();
                                    if($count){
                                        $sql_error_info = 'updateGoods-goods_barcode_update';
                                        $goods_barcode_db->where($arr_goods_barcode)->save(array('is_master'=>1,"modified"=>date("Y-m-d H:i:s", time())));
                                    }else{
                                        $sql_error_info    = 'updateGoods-goods_barcode-add';
                                        $arr_goods_barcode['tag'] = get_seq("goods_barcode");
                                        $arr_goods_barcode['is_master'] = 1;
                                        $arr_goods_barcode['created'] = array('exp', 'NOW()');
                                        $goods_barcode_db->data($arr_goods_barcode)->add();
                                    }
                                }
                                $str_update_info .= ' 主条码：从' . $res_spec_arr['barcode'] . ' 到 ' . $arr_update_spec[ $i ]['barcode'];
                            }
                            $sql_error_info    = 'updateGoods-barcode_count';
                            $res_barcode_count = $model_db->query("SELECT COUNT(1) AS barcode_count FROM goods_barcode WHERE `type` = 1 AND target_id =%d", $arr_update_spec[ $i ]['spec_id']);

                            $arr_update_spec[ $i ]['barcode_count'] = $res_barcode_count[0]['barcode_count'];

                            $sql_error_info  = 'updateGoods-goods_spec_update';
                            $res_update_spec = $model_db->table('goods_spec')->where('spec_id=' . $arr_update_spec[ $i ]['spec_id'])->save($arr_update_spec[ $i ]);
                            if ($res_update_spec !== false) {
                                $goods_goods_db->matchApiGoodsSpecByTargetId($model_db, 1, $arr_update_spec[ $i ]['spec_id'], $sql_error_info);
                                $goods_goods_db->matchApiGoodsSpecByMerchantNo($model_db, $arr_update_spec[ $i ]['spec_no'], $sql_error_info);
                                switch($arr_update_spec[$i]['is_allow_neg_stock']){
                                    case 0: $arr_update_spec[$i]['is_allow_neg_stock']='否';
                                        break;
                                    case 1: $arr_update_spec[$i]['is_allow_neg_stock']='是';
                                        break;
                                };
                                switch($res_spec_arr['is_allow_neg_stock']){
                                    case 0: $res_spec_arr['is_allow_neg_stock']='否';
                                        break;
                                    case 1: $res_spec_arr['is_allow_neg_stock']='是';
                                        break;
                                };
								switch($arr_update_spec[$i]['large_type']){
                                    case 0: $arr_update_spec[$i]['large_type']='非大件';
                                        break;
                                    case 1: $arr_update_spec[$i]['large_type']='普通大件';
                                        break;
                                    case 2: $arr_update_spec[$i]['large_type']='独立大件';
                                        break;
                                };
                                switch($res_spec_arr['large_type']){
                                    case 0: $res_spec_arr['large_type']='非大件';
                                        break;
                                    case 1: $res_spec_arr['large_type']='普通大件';
                                        break;
                                    case 2: $res_spec_arr['large_type']='独立大件';
                                        break;
                                };
                                switch($arr_update_spec[$i]['is_not_need_examine']){
                                    case 0: $arr_update_spec[$i]['is_not_need_examine']='否';
                                        break;
                                    case 1: $arr_update_spec[$i]['is_not_need_examine']='是';
                                        break;
                                };
                                switch($res_spec_arr['is_not_need_examine']){
                                    case 0: $res_spec_arr['is_not_need_examine']='否';
                                        break;
                                    case 1: $res_spec_arr['is_not_need_examine']='是';
                                        break;
                                };
                                if ($arr_update_spec[ $i ]['spec_name'] != $res_spec_arr['spec_name']) {
                                    $str_update_info .= ' 名称：从' . $res_spec_arr['spec_name'] . ' 到 ' . $arr_update_spec[ $i ]['spec_name'];
                                }
                                if ($arr_update_spec[ $i ]['retail_price'] != $res_spec_arr['retail_price']) {
                                    $str_update_info .= ' 零售价：从' . $res_spec_arr['retail_price'] . ' 到 ' . $arr_update_spec[ $i ]['retail_price'];
                                }
                                /*if ($arr_update_spec[ $i ]['wholesale_price'] != $res_spec_arr['wholesale_price']) {
                                    $str_update_info .= ' 批发价：从' . $res_spec_arr['wholesale_price'] . ' 到 ' . $arr_update_spec[ $i ]['wholesale_price'];
                                }
                                if ($arr_update_spec[ $i ]['member_price'] != $res_spec_arr['member_price']) {
                                    $str_update_info .= ' 会员价：从' . $res_spec_arr['member_price'] . ' 到 ' . $arr_update_spec[ $i ]['member_price'];
                                }*/
                                if ($arr_update_spec[ $i ]['market_price'] != $res_spec_arr['market_price']) {
                                    $str_update_info .= ' 市场价：从' . $res_spec_arr['market_price'] . ' 到 ' . $arr_update_spec[ $i ]['market_price'];
                                }
                                if ($arr_update_spec[ $i ]['is_allow_neg_stock'] != $res_spec_arr['is_allow_neg_stock']) {
                                    $str_update_info .= ' 是否允许负库存出库：从' . $res_spec_arr['is_allow_neg_stock'] . ' 到 ' . $arr_update_spec[ $i ]['is_allow_neg_stock'];
                                }
                                if ($arr_update_spec[ $i ]['lowest_price'] != $res_spec_arr['lowest_price']) {
                                    $str_update_info .= ' 最低价：从' . $res_spec_arr['lowest_price'] . ' 到 ' . $arr_update_spec[ $i ]['lowest_price'];
                                }
                                /*if ($arr_update_spec[ $i ]['sale_score'] != $res_spec_arr['sale_score']) {
                                    $str_update_info .= ' 销售积分：从' . $res_spec_arr['sale_score'] . ' 到 ' . $arr_update_spec[ $i ]['sale_score'];
                                }
                                if ($arr_update_spec[ $i ]['pack_score'] != $res_spec_arr['pack_score']) {
                                    $str_update_info .= ' 打包积分：从' . $res_spec_arr['pack_score'] . ' 到 ' . $arr_update_spec[ $i ]['pack_score'];
                                }
                                if ($arr_update_spec[ $i ]['pick_score'] != $res_spec_arr['pick_score']) {
                                    $str_update_info .= ' 拣货积分：从' . $res_spec_arr['pick_score'] . ' 到 ' . $arr_update_spec[ $i ]['pick_score'];
                                }*/
                                if ($arr_update_spec[ $i ]['weight'] != $res_spec_arr['weight']) {
                                    $str_update_info .= ' 重量：从' . $res_spec_arr['weight'] . ' 到 ' . $arr_update_spec[ $i ]['weight'];
                                }
                                if ($arr_update_spec[ $i ]['length'] != $res_spec_arr['length']) {
                                    $str_update_info .= ' 长：从' . $res_spec_arr['length'] . ' 到 ' . $arr_update_spec[ $i ]['length'];
                                }
                                if ($arr_update_spec[ $i ]['width'] != $res_spec_arr['width']) {
                                    $str_update_info .= ' 宽：从' . $res_spec_arr['width'] . ' 到 ' . $arr_update_spec[ $i ]['width'];
                                }
                                if ($arr_update_spec[ $i ]['height'] != $res_spec_arr['height']) {
                                    $str_update_info .= ' 高：从' . $res_spec_arr['height'] . ' 到 ' . $arr_update_spec[ $i ]['height'];
                                }
                                if ($arr_update_spec[ $i ]['unit'] != $res_spec_arr['unit']) {
                                    $str_update_info .= ' 单位：从' . $unit_dict[ $res_spec_arr['unit'] ] . ' 到 ' . $unit_dict[ $arr_update_spec[ $i ]['unit'] ];
                                }
                                if ($arr_update_spec[ $i ]['prop1'] != $res_spec_arr['prop1']) {
                                    $str_update_info .= ' 自定义1：从' . $res_spec_arr['prop1'] . ' 到 ' . $arr_update_spec[ $i ]['prop1'];
                                }
                                if ($arr_update_spec[ $i ]['prop2'] != $res_spec_arr['prop2']) {
                                    $str_update_info .= ' 自定义2：从' . $res_spec_arr['prop2'] . ' 到 ' . $arr_update_spec[ $i ]['prop2'];
                                }
                                if ($arr_update_spec[ $i ]['prop3'] != $res_spec_arr['prop3']) {
                                    $str_update_info .= ' 自定义3：从' . $res_spec_arr['prop3'] . ' 到 ' . $arr_update_spec[ $i ]['prop3'];
                                }
                                if ($arr_update_spec[ $i ]['prop4'] != $res_spec_arr['prop4']) {
                                    $str_update_info .= ' 自定义4：从' . $res_spec_arr['prop4'] . ' 到 ' . $arr_update_spec[ $i ]['prop4'];
                                } 
								if ($arr_update_spec[ $i ]['large_type'] != $res_spec_arr['large_type']) {
                                    $str_update_info .= ' 大件类别：从' . $res_spec_arr['large_type'] . ' 到 ' . $arr_update_spec[ $i ]['large_type'];
                                }
                                if ($arr_update_spec[ $i ]['is_not_need_examine'] != $res_spec_arr['is_not_need_examine']) {
                                    $str_update_info .= ' 无需验货：从' . $res_spec_arr['is_not_need_examine'] . ' 到 ' . $arr_update_spec[ $i ]['is_not_need_examine'];
                                }
                            }
                            $spec_log[ $i ] = array(
                                'goods_type'   => 1,//1-货品 2-组合装
                                'goods_id'     => $id,
                                'spec_id'      => $arr_update_spec[ $i ]['spec_id'],
                                'operator_id'  => $userId,//操作者暂时默认为user_id=1
                                'operate_type' => 52,
                                'message'      => '修改单品--' . $str_update_info,
                                'created'      => array('exp', 'NOW()')
                            );
                        }
                        $sql_error_info = 'updateGoods-goods_spec_log';
                        $model_db->table('goods_log')->addAll($spec_log);
                    }
                    //如果货品的品牌或者分类改变，则刷新所有单品的库存同步策略
                    if ($is_changed_brand == 1 || $is_changed_class == 1) {
                        $goods_goods_db->refreshStockSyncRule($id);
                    }
                    $model_db->commit();
                }
            } catch (\PDOException $e) {
                if ($is_rollback) {
                    $model_db->rollback();
                }
                \Think\Log::write($sql_error_info . ':' . $e->getMessage());
                $this->error('保存失败');
            } catch (\Exception $e) {
                if ($is_rollback) {
                    $model_db->rollback();
                }
               // \Think\Log::write($sql_error_info . ':' . $e->getMessage());
                $this->error('保存失败-' . $e->getMessage());
            }
            $this->success('保存成功');
        } else {
            //$datagrid_editGoods  = Factory::getModel('GoodsGoods')->getDatagridStr();
            $res_goods_goods_arr = M('goods_goods')->alias('gg_1')->field('gg_1.goods_id,gg_1.goods_no,gg_1.goods_name,gg_1.short_name,gg_1.alias,gg_1.origin,gg_1.class_id,gg_1.brand_id,gg_1.goods_type,gg_1.unit,gg_1.flag_id,gg_1.remark')->where(array('goods_id' => array('eq', $id)))->find();
            $res_goods_spec_arr  = M('goods_spec')->alias('gs_1')->field('gs_1.spec_id, gs_1.img_url, gs_1.spec_no, gs_1.spec_name, gs_1.spec_code, gs_1.barcode, gs_1.retail_price, gs_1.wholesale_price, gs_1.member_price, gs_1.market_price, gs_1.lowest_price, gs_1.validity_days, gs_1.length, gs_1.width, gs_1.height,gs_1.unit, gs_1.sale_score, gs_1.pack_score, gs_1.pick_score, gs_1.weight, gs_1.tax_rate,gs_1.remark,gs_1.is_allow_neg_stock, gs_1.prop1, gs_1.prop2, gs_1.prop3, gs_1.prop4, gs_1.large_type, gs_1.is_not_need_examine,gs_1.is_hotcake')->where(array('goods_id' => array('eq', $id), 'deleted' => array('eq', 0)))->select();
            $list_form           = UtilDB::getCfgList(array('brand', 'unit'), array("brand" => array("is_disabled" => 0)));
            $id_list             = array(
                'datagrid_id' => 'edit_goods_datagrid',
                'form'        => 'edit_goods_form',
                'toolbar'     => 'edit_goods_toolbar',
            );
            $fields = get_field("GoodsGoods", "add_goods");
            propFildConv($fields,'prop','goods_spec');

            $datagrid = [
                "id"      => $id_list["datagrid_id"],
                "options" => [
                    "toolbar" => $id_list["toolbar"],
                    'fitColumns'   => false,
                ],
                "fields"  => $fields
            ];
            $this->assign("list", $list_form);
            $this->assign("id_list", $id_list);
            $this->assign('info', $res_goods_goods_arr);
            $this->assign('goods_spec_list', json_encode($res_goods_spec_arr));
            $this->assign('datagrid',$datagrid);
            //$this->assign('datagrid_editGoods', $datagrid_editGoods);
            $this->display('edit');
        }
    }

    public function delGoods($id) {
        $model_db       = M();
        $is_rollback    = false;
        $sql_error_info = '';
        $result=array('status'=>0,'info'=>'');
        $list=array();
        try {
        	$goods_goods_db     = Factory::getModel('GoodsGoods');
        	foreach ($id as $v){
        		$is_rollback    = false;
        		$sql_error_info     = 'delGoods-get_goods_spec';
        		$res_goods_spec_arr = $model_db->table('goods_spec')->alias('gs')->field('gs.spec_id, gs.spec_no,gs.spec_name,gs.goods_id,gg.goods_no')->join('LEFT JOIN goods_goods gg ON gg.goods_id = gs.goods_id')->where(array('gs.goods_id' => array('eq', $v), 'gs.deleted' => array('eq', 0)))->select();
        		$goods_goods_db->delSpec($model_db, $res_goods_spec_arr, $is_rollback, $sql_error_info, $list,$v);
        	}
        	if (count($list)>0){
        		$result=array('status'=>2,'info'=>array('total'=>count($list),'rows'=>$list));
        	}
        } catch (\PDOException $e) {
            if ($is_rollback) {
                $model_db->rollback();
            }
            \Think\Log::write($sql_error_info . ':' . $e->getMessage());
            $result=array('status'=>1,'info'=>'未知错误，请联系管理员');
        } catch (\Exception $e) {
            if ($is_rollback) {
                $model_db->rollback();
            }
            $result=array('status'=>1,'info'=> $e->getMessage());
        }
        $this->ajaxReturn($result);
    }
    public function uploadExcel(){
        if(!self::ALLOW_EXPORT){
            $res["status"] = 1;
            $res["info"]   = self::EXPORT_MSG;
            $this->ajaxReturn(json_encode($res), "EVAL");
        }
        $type = I('get.type');
        //获取表格相关信息
        $file = $_FILES["file"]["tmp_name"];
        $name = $_FILES["file"]["name"];
        try{
            $M = M();
            $excelClass = new ExcelTool();
            $excelClass->checkExcelFile($name,$file);
            $excelClass->uploadFile($file,"GoodsGoodsImport");
            $count = $excelClass->getExcelCount();
            if(workTimeUploadNum()<$count){
                SE(UtilTool::UPPER_UPLOAD);
            }
            //建立临时表，存储数据处理的结果
            $excelData = $excelClass->Excel2Arr($count);
            //如果tmp_import_detail表已存在，就删除并重新创建
            $res = array();
            //处理数据，将数据插入数据库并返回信息
            $goodsDB = D("GoodsGoods");
            $total_array = array();
            $error_list = array();



            $enable_check_by_template = true;//是否通过模板比对上传文件
            $template_file_name = "单品导入模板.xls";
            $template_file_sub_path = APP_PATH."Runtime/File/";                      //linux
            $template_file_path = $template_file_sub_path.$template_file_name;                  //linux
//            $template_file_sub_path = "C:\\www\\weberp\\branches\\dev\\source\\Application\\Runtime\\File\\";   //Windows
//            $template_file_path = iconv('UTF-8','GB2312',$template_file_sub_path.$template_file_name);      //windows

            if(file_exists($template_file_path)){
                $template_excelClass = new ExcelTool();
                $template_file_path = $template_excelClass->setFilePath($template_file_name,$template_file_sub_path);
                $template_count = $template_excelClass->getExcelCount();
                $template_excelData = $template_excelClass->Excel2Arr($template_count);
            }else{
                //模板路径有问题，关闭校验，发送邮件
                $enable_check_by_template = false;
                \Think\Log::write ( '货品导入模板比对失败，单品导入模板路径有误，模板路径：'.$template_file_path ,\Think\Log::ERR);
            }

            //记录sheet数值索引
            $sheet_index = 0;
            foreach ($excelData as $sheet) {

               //表头校验
                if( $enable_check_by_template==true ){
                    //若第一个sheet表头信息不一致，则返回错误信息，若第二个及以后的表头不一致则跳过该sheet
                    if($sheet_index==0){
                        for ($t=0;$t<count($template_excelData['Sheet1'][0]);$t++){
                            if(!(trim($template_excelData['Sheet1'][0][$t]) == trim($sheet[0][$t]))){
                        $res['status'] = 1;
                        $res['info']   = '文件第一行数据有误，请参照模板文件';
                        $this->ajaxReturn(json_encode($res), "EVAL");
                           }
                        }
                    }else{
                        $sheet_index++;
                        continue;
                    }
                }

                //数据填充
                for ($k = 1; $k < count($sheet); $k++) {
                    $row = $sheet[$k];
                    if (UtilTool::checkArrValue($row)) continue;
                    //分类存储数据
                    $i = 0;
                    if(mb_strlen($row[$i])>40){
                        $error_list[] = array('id'=>$k+1,'result'=>'数据过长','message'=>'商家编码过长，系统截取前40个字符');
                    }
                    $data["spec"]["spec_no"] = mb_substr(trim_all($row[$i],1),0,40);//商家编码
                    $data["merchant_no"]["merchant_no"] = mb_substr(trim_all($row[$i++],1),0,40);
                    $data["goods"]["goods_no"] = mb_substr($row[$i++],0,40);//货品编号
                    $data["goods"]["goods_name"] = $row[$i++];//货品名称
                    $data["goods"]["short_name"] = $row[$i++];//货品简称
                    $data["goods"]["alias"] = $row[$i++];//货品别名
                    $data["class"]["class_name"] = $row[$i++];//分类
                    $data["brand"]["brand_name"] = $row[$i++];//货品品牌
                    $data["goods"]["goods_type"] = $row[$i++];//货品类别
                    $data["spec"]["is_hotcake"] = $row[$i++];//是否爆款
                    $data["unit"]["name"] = $row[$i++];//单位
                    $data["flag"]["flag_name"] = $row[$i++];//标记
                    $data["goods"]["origin"] = $row[$i++];//产地
                    $data["spec"]["img_url"] = $row[$i++];//图片链接
                    $data["spec"]["spec_name"] = mb_substr(trim_all($row[$i++],1),0,100);//规格名称
                    $data["spec"]["spec_code"] = mb_substr(trim_all($row[$i++]),0,40);//规格码
                    $data["spec"]["barcode"] = trim_all($row[$i]);//条码
                    $data["barcode"]["barcode"] = $row[$i++];
                    $data["spec"]["lowest_price"] = floatval($row[$i++]);//最低售价
                    $data["spec"]["retail_price"] = floatval($row[$i++]);//零售价
                    $data["spec"]["market_price"] = floatval($row[$i++]);//市场价
                    $data["spec"]["validity_days"] = floatval($row[$i++]);//有效期
                    $data["spec"]["length"] = floatval($row[$i++]);//长
                    $data["spec"]["width"] = floatval($row[$i++]);//宽
                    $data["spec"]["height"] = floatval($row[$i++]);//高
                    $data["spec"]["weight"] = floatval($row[$i++]);//重量
                    $data["spec"]["remark"] = floatval($row[$i++]);//备注
                    $data["spec"]["prop1"] = $row[$i++];//自定义1
                    $data["spec"]["prop2"] = $row[$i++];//自定义2
                    $data["spec"]["prop3"] = $row[$i++];//自定义3
                    $data["spec"]["prop4"] = $row[$i++];//自定义4
					$data['spec']["is_allow_neg_stock"] = $row[$i++]; //允许负库存
                    $data['spec']['large_type'] = $row[$i++]; //大件类别
                    $total_array[] = $data;
                }
                $sheet_index++;
            }
            $res = array('status'=>0,'info'=>'');
            if(count($total_array)>0)
            {
                $i = 0;
                while(count($total_array)>0)
                {
                    $line = $i*100;
                    $arr = array_splice($total_array,0,100);
                    $M->startTrans();
                    if($type == 'import'){
                        $goodsDB->importSpec($arr,$error_list,$line);
                    }else{
                        $goodsDB->updateSpec($arr,$error_list,$line);
                    }
                    $M->commit();
                    $i++;
                }

            }
            if(count($error_list)>0){
                $res['status'] = 2;
                $res['info'] = $error_list;
            }

        }catch (BusinessLogicException $e){
            $res['status'] = 1;
            $res['info']   = $e->getMessage();
        }catch (\Exception $e){
            Log::write($e->getMessage());
            $res["status"] = 1;
            $res["info"]   = parent::UNKNOWN_ERROR;
            $this->ajaxReturn(json_encode($res), "EVAL");
        }
        unset($data);
        
        $this->ajaxReturn(json_encode($res), "EVAL");
    }

    //下载单品导入模板
    public function downloadTemplet(){
        $file_name = "单品导入模板.xls";
        $file_sub_path = APP_PATH."Runtime/File/";
        try{
            ExcelTool::downloadTemplet($file_name,$file_sub_path);
        } catch (BusinessLogicException $e){
            Log::write($e->getMessage());
            echo '对不起，模板不存在，下载失败！';
        } catch (\Exception $e) {
            Log::write($e->getMessage());
            echo parent::UNKNOWN_ERROR;
        }
    }



    /**
     * 导出到Excel文件
     */
    public function exportToExcel(){
        if(!self::ALLOW_EXPORT){
            echo self::EXPORT_MSG;
            return false;
        }
        $id_list = I('get.id_list');
        $type = I('get.type');
        $result = array('status'=>0,'info'=>'');
        $goods_goods_db = D('GoodsGoods');
        try{
            if($id_list==''){
                $search = I('get.search','',C('JSON_FILTER'));
                foreach ($search as $k => $v) {
                    $key=substr($k,7,strlen($k)-8);
                    $search[$key]=$v;
                    unset($search[$k]);
                }
                $goods_goods_db->exportToExcel('',$search, $type);
            }
            else{
                $goods_goods_db->exportToExcel($id_list, null, $type);
            }
        }
        catch (BusinessLogicException $e){
            $result = array('status'=>1,'info'=> $e->getMessage());
        }catch (\Exception $e) {
            $result=array('status'=>1,'info'=>parent::UNKNOWN_ERROR);
        }
        //$this->ajaxReturn($result);
        echo $result['info'];
    }




    /*
     * 初始化单品库存
     * */
    public function importGoodsStock(){
        $warehouse_id = I('post.warehouse_id');
        $result=array('status'=>0,'data'=>'');
        try{
            $import_stock_arr = D('GoodsGoods')->getImportStockGoods($warehouse_id,$result);
            if($import_stock_arr){
                $sql_drop = "CREATE TEMPORARY TABLE IF NOT EXISTS  `tmp_import_detail` (`rec_id` INT(11) NOT NULL AUTO_INCREMENT,`spec_name` VARCHAR(100),`spec_no` varchar(40),`position_no` varchar(40) DEFAULT '',`position_id` int(11),`warehouse_name` varchar(64) ,`stock_num` decimal(19,4),`num` decimal(19,4),`price` decimal(19,4),`warehouse_id` smallint(6),`spec_id` int(11),`cost_price` decimal(19,4),`status` TINYINT,`line` SMALLINT,`message` VARCHAR(60),`result` VARCHAR(30),PRIMARY KEY(`rec_id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
                M()->execute($sql_drop);
                D('Stock/StockManagement')->importStockSpec($import_stock_arr,$result);
                M()->execute("DELETE FROM tmp_import_detail");
            }
        } catch (BusinessLogicException $e){
            \Think\Log::write('-importGoodsStock-' . $e->getMessage());
            $result=array('status'=>1,'msg'=> $e->getMessage());
        } catch (\Exception $e){
            \Think\Log::write('-importGoodsStock-' . $e->getMessage());
            $result=array('status'=>1,'msg'=> parent::UNKNOWN_ERROR);
        }
        $this->ajaxReturn($result);
    }

    public function showClassDialog(){
        $goods_id = I("get.id");
        $type = I('get.type');
        if($type == 'class'){
            $params = array(
                'rec_id'      => $goods_id,
                'chg_dialog'  => 'batchEditClass',
                'id_datagrid' => strtolower(CONTROLLER_NAME . '_' . "edit_Class" . '_datagrid'),
                'form_id'     => 'goods_goods_class_form',
                'form_url'    => U('GoodsGoods/chgClass'),
                'type'        => $type
            );
        }elseif($type == 'brand'){
            $params = array(
                'rec_id'      => $goods_id,
                'chg_dialog'  => 'batchEditBrand',
                'id_datagrid' => strtolower(CONTROLLER_NAME . '_' . "edit_Brand" . '_datagrid'),
                'form_id'     => 'goods_goods_brand_form',
                'form_url'    => U('GoodsGoods/chgBrand'),
                'type'        => $type
            );
        }
        $list_form = UtilDB::getCfgList(array('brand'), array("brand" => array("is_disabled" => 0)));
        $this->assign("params", $params);
        $this->assign('list', $list_form['brand']);
        $this->display('dialog_chg');

    }
    public function chgClass(){
        $rec_id = I('post.rec_id');
        $class_id = I('post.class_id');
        $result = array('status'=>0,'msg'=>'操作成功');
        try{
            D('GoodsGoods')->batchChg($rec_id,$class_id,$result,'class');
        } catch (BusinessLogicException $e){
            \Think\Log::write('-chgClass-' . $e->getMessage());
            $result=array('status'=>1,'msg'=> $e->getMessage());
        } catch (\Exception $e){
            \Think\Log::write('-chgClass-' . $e->getMessage());
            $result=array('status'=>1,'msg'=> parent::UNKNOWN_ERROR);
        }
        $this->ajaxReturn($result);
    }

    public function chgBrand(){
        $rec_id = I('post.rec_id');
        $brand_id = I('post.brand_id');
        $result = array('status'=>0,'msg'=>'操作成功');
        try{
            D('GoodsGoods')->batchChg($rec_id,$brand_id,$result,'brand');
        } catch (BusinessLogicException $e){
            \Think\Log::write('-chgBrand-' . $e->getMessage());
            $result=array('status'=>1,'msg'=> $e->getMessage());
        } catch (\Exception $e){
            \Think\Log::write('-importGoodsStock-' . $e->getMessage());
            $result=array('status'=>1,'msg'=> parent::UNKNOWN_ERROR);
        }
        $this->ajaxReturn($result);

    }
    //设置出库仓库
    public function setOutWarehouse(){
        if(IS_POST)
         {
            $out_warehouse=I('post.out_warehouse','',C('JSON_FILTER'));
            $shop_id=I('post.shop_id');
            $goods_id=I('post.goods_id');
            $type = I('post.type');
            try
            {
                D('GoodsGoods')->setOutWarehouse($out_warehouse,$goods_id,$shop_id,$type);
            }catch(BusinessLogicException $e)
            {
                $this->error($e->getMessage());
            }
            $this->success();
         }else{
            $goods_id = I("get.id");
            $type = I("get.type");
            $id_list = array(
                        'form'=>$type.'_set_out_warehouse_form',
                        'toolbar'=> $type.'_set_out_warehouse_datagrid_toolbar',
                        'id_datagrid' => $type.'_set_out_warehouse_datagrid',
                );
            $datagrid=array(
                    'id'=>$id_list['id_datagrid'],
                    'goods_id'=> $goods_id,
                    'type'=>$type,
                    'style'=>'',
                    'class'=>'easyui-datagrid',
                    'options'=> array(
                            'title'=>'',
                            'style'=>'',
                            'toolbar' => "#{$id_list['toolbar']}",
                            'url'   => U('GoodsGoods/getWarehouseList', array('goods_id'=>$goods_id,'type'=>$type)),
                            'singleSelect'=>false,
                            'pagination'=>false,
                            'fitColumns'=>true,
                            'checkOnSelect'=>false,
                            'rownumbers'=>false
                    ),
                    'fields' => get_field('GoodsCommon','out_warehouse_dialog')
            );
            $shop_list[] = array("id" => "all", "name" => "全部");
            $list_form=UtilDB::getCfgList(array('shop'));
            $list_form   = array_merge($shop_list, $list_form["shop"]);
            $faq_url=C('faq_url');
            $this->assign('faq_url',$faq_url['warehouse_rule']);//设置出库仓库常见问题
            $this->assign('type',$type);
            $this->assign('list',$list_form);
            $this->assign('id_list', $id_list);
            $this->assign('datagrid',$datagrid);
            $this->display('set_warehouse_dialog');
        }
    }
    public function getWarehouseList($goods_id=0,$shop_id=0,$type='spec')
    {
        $warehouses=array();
        $warehouse_list=array();
        try
        {
            $shop_id=intval($shop_id);
            $warehouse_db=M('cfg_warehouse');
            switch ($type) {
                case 'spec':
                    $spec_id=$goods_id;                       
                    break;
                case 'goods':                    
                    $spec_id = "SELECT spec_id from goods_spec WHERE goods_id in (".$goods_id.")";
                    break;
                default:
                    $spec_id = 0;
                    break;
            }
            $res_out_warehouse=$warehouse_db->alias('w')
                                                 ->field('w.warehouse_id AS id,IF(cgw.shop_id='.$shop_id.' and cgw.spec_id in ('.$spec_id.'),cgw.rec_id,0) AS rec_id,IF(IF(cgw.shop_id='.$shop_id.' and cgw.spec_id in ('.$spec_id.'),cgw.warehouse_id,0),1,0) AS is_select,IF(cgw.shop_id='.$shop_id.' and cgw.spec_id in ('.$spec_id.'),cgw.priority,\'\') AS priority,w.name')
                                                 ->join('LEFT JOIN cfg_goods_warehouse cgw ON w.warehouse_id=cgw.warehouse_id')
                                                 ->where(array('w.is_disabled'=>array('eq',0)))
                                                 ->order('priority DESC,id ASC')                                         
                                                 ->select();  
            $map=array();
            foreach ($res_out_warehouse as $ow)
            {
                if (isset($map[strval($ow['id'])]))
                {
                    $map[strval($ow['id'])]=($ow['is_select']!=0&&$ow['priority']>$map[strval($ow['id'])]['priority'])?$ow:$map[strval($ow['id'])];
                }else
                {
                    $map[strval($ow['id'])]=$ow;
                }
            }
            foreach ($map as $ow) 
            {
                $warehouses[]=$ow;
            }
            $warehouse_list=array('total'=>count($warehouses),'rows'=>$warehouses);
        }catch(\PDOException $e)
        {
            \Think\Log::write('-getWarehouseList-' . $e->getMessage());
            $warehouse_list=array('total'=>0,'rows'=>array());
        }catch(\Exception $e)
        {
            \Think\Log::write('-getWarehouseList-' . $e->getMessage());
            $warehouse_list=array('total'=>0,'rows'=>array());
        }
        $this->ajaxReturn($warehouse_list);
    }
    //设置出库物流
    public function setOutLogistics(){
        if(IS_POST)
        {
            $out_logistics=I('post.out_logistics','',C('JSON_FILTER'));
            $shop_id=I('post.shop_id');
            $warehouse_id=I('post.warehouse_id');
            $goods_id=I('post.goods_id');
            $type = I('post.type');
            try
            {
                D('GoodsGoods')->setOutLogistics($out_logistics,$goods_id,$shop_id,$warehouse_id,$type);
            }catch(BusinessLogicException $e)
            {
                $this->error($e->getMessage());
            }
            $this->success();
        }else{
            $goods_id = I("get.id");
            $type = I("get.type");
            $id_list = array(
                'form'=>$type.'_set_out_logistics_form',
                'toolbar'=> $type.'_set_out_logistics_datagrid_toolbar',
                'id_datagrid' => $type.'_set_out_logistics_datagrid',
            );
            $datagrid=array(
                'id'=>$id_list['id_datagrid'],
                'goods_id'=> $goods_id,
                'type'=>$type,
                'style'=>'',
                'class'=>'easyui-datagrid',
                'options'=> array(
                    'title'=>'',
                    'style'=>'',
                    'toolbar' => "#{$id_list['toolbar']}",
                    'url'   => U('GoodsGoods/getLogisticsList', array('goods_id'=>$goods_id,'type'=>$type)),
                    'singleSelect'=>false,
                    'pagination'=>false,
                    'fitColumns'=>true,
                    'checkOnSelect'=>false,
                    'rownumbers'=>false
                ),
                'fields' => get_field('GoodsCommon','out_logistics_dialog')
            );
            $shop_list[] = array("id" => "all", "name" => "全部");
            $list_form=UtilDB::getCfgRightList(array('shop','warehouse'),array('warehouse'=>array('is_disabled'=>array('eq',0))));
//            $faq_url=C('faq_url');
//            $this->assign('faq_url',$faq_url['warehouse_rule']);
            $this->assign('type',$type);
            $this->assign('list',$list_form);
            $this->assign('id_list', $id_list);
            $this->assign('datagrid',$datagrid);
            $this->display('set_logistics_dialog');
        }
    }
    public function getLogisticsList($goods_id=0,$shop_id=0,$warehouse_id=0,$type='spec')
    {
        $logistics=array();
        $logistics_list=array();
        try
        {
            $shop_id=intval($shop_id);
            $logistics_db=M('cfg_logistics');
            $is_suite=0;
            switch ($type) {
                case 'spec':
                    $spec_id=$goods_id;
                    break;
                case 'goods':
                    $spec_id = "SELECT spec_id from goods_spec WHERE goods_id in (".$goods_id.")";
                    break;
                case 'suite':
                    $spec_id=$goods_id;
                    $is_suite=1;
                    break;
                default:
                    $spec_id = 0;
                    break;
            }
            $res_out_logistics=$logistics_db->alias('cl')
                ->field('cl.logistics_id AS id,IF(cgl.shop_id='.$shop_id.' and cgl.warehouse_id='.$warehouse_id.'  and cgl.spec_id in ('.$spec_id.'),cgl.rec_id,0) AS rec_id,IF(IF(cgl.shop_id='.$shop_id.' and cgl.warehouse_id='.$warehouse_id.' and cgl.spec_id in ('.$spec_id.'),cgl.logistics_id,0),1,0) AS is_select,IF(cgl.shop_id='.$shop_id.' and cgl.warehouse_id='.$warehouse_id.' and cgl.spec_id in ('.$spec_id.'),cgl.priority,\'\') AS priority,cl.logistics_name')
                ->join('LEFT JOIN cfg_goods_logistics cgl ON cgl.logistics_id=cl.logistics_id AND cgl.type='.$is_suite)
                ->where(array('cl.is_disabled'=>array('eq',0)))
                ->order('priority DESC,id ASC')
                ->select();
            $map=array();
            foreach ($res_out_logistics as $ow)
            {
                if (isset($map[strval($ow['id'])]))
                {
                    $map[strval($ow['id'])]=($ow['is_select']!=0&&$ow['priority']>$map[strval($ow['id'])]['priority'])?$ow:$map[strval($ow['id'])];
                }else
                {
                    $map[strval($ow['id'])]=$ow;
                }
            }
            foreach ($map as $ow)
            {
                $logistics[]=$ow;
            }
            $logistics_list=array('total'=>count($logistics),'rows'=>$logistics);
        }catch(\PDOException $e)
        {
            \Think\Log::write('-getWarehouseList-' . $e->getMessage());
            $warehouse_list=array('total'=>0,'rows'=>array());
        }catch(\Exception $e)
        {
            \Think\Log::write('-getWarehouseList-' . $e->getMessage());
            $warehouse_list=array('total'=>0,'rows'=>array());
        }
        $this->ajaxReturn($logistics_list);
    }


}