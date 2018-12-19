<?php

namespace Goods\Controller;

use Common\Controller\BaseController;
use Think\Exception\BusinessLogicException;

/**
 * Created by PhpStorm.
 * User: yanfeng
 * Date: 2015/8/11
 * Time: 11:25
 */
class GoodsBrandController extends BaseController {
    /**
     * 初始化id_list
     */
    function _initialize() {
        parent::_initialize();
        parent::getIDList($this->id_list, array("form", "toolbar", "datagrid", "add", "add_form", "edit", "edit_form"));
    }

    /**
     * 返回goodsbrand的datagrid
     * author:luyanfeng
     * @param int    $page
     * @param int    $rows
     * @param array  $search
     * @param string $sort
     * @param string $order
     */
    public function getGoodsBrandList($page = 1, $rows = 10, $search = array(), $sort = 'gb.brand_id', $order = 'desc') {
        if (IS_POST) {
            $this->ajaxReturn(D("GoodsBrand")->getGoodsBrandList($page, $rows, $search, $sort, $order));
        } else {
            $id_list  = $this->id_list;
            $datagrid = array(
                "id"      => $id_list["datagrid"],
                "options" => array(
                    "url"        => U("GoodsBrand/getGoodsBrandList"),
                    "toolbar"    => $id_list["toolbar"],
                    "fitColumns" => false,
                    "rownumbers" => true,
                    "pagination" => true,
                    "method"     => "post"
                ),
                "fields"  => get_field("GoodsBrand", "goods_brand")
            );
            $params   = array(
                "controller" => strtolower(CONTROLLER_NAME),
                "datagrid"   => array(
                    "id"  => $id_list["datagrid"],
                    "url" => U("GoodsBrand/getGoodsBrandList")
                ),
                "add"        => array(
                    "id"     => $id_list['add'],
                    "title"  => "新建货品品牌",
                    "url"    => U("GoodsBrand/addGoodsBrand"),
                    "height" => "200",
                    "width"  => "263",
                    'ismax'	 =>  false
                ),
                "edit"       => array(
                    "id"     => $id_list["edit"],
                    "title"  => "编辑货品品牌",
                    "url"    => U("GoodsBrand/editGoodsBrand"),
                    "height" => "200",
                    "width"  => "263",
                    'ismax'	 =>  false
                ),
                "delete"     => array(
                    "url" => U("GoodsBrand/disableGoodsBrand")
                ),
                "search"     => array("form_id" => $id_list["form"])
            );
            $this->assign("id_list", $id_list);
            $this->assign("params", json_encode($params));
            $this->assign("datagrid", $datagrid);
            $this->display("show");
        }
    }

    /**
     * 停用货品品牌
     * author:luyanfeng
     */
    public function disableGoodsBrand() {
        $id = I("post.id");
        $res=array('status'=>0,'info'=>'操作成功');
        try {
            $goodsbrand_db = D('GoodsBrand');
            $goodsbrand_db->disableGoodsBrandById($id);
        } catch (BusinessLogicException $e) {
            $res = array("status" => 1, "info" => $e->getMessage());
        } catch (\Exception $e) {
            \Think\Log::write($e->getMessage());
            $res = array("status" => 1, "info" => parent::UNKNOWN_ERROR);
        }
        $this->ajaxReturn($res);
    }

    /**
     * 返回新建货品品牌页面
     * author:luyanfeng
     */
    public function addGoodsBrand() {
        if (IS_POST) {
            $data = I("post.data");
            $res=array('status'=>0,'info'=>'操作成功');
            try {
                if (!isset($data["is_disabled"])) {
                    $data["is_disabled"] = 0;
                }
                $data["created"]  = date("Y-m-d G:i:s");
                $data["modified"] = date("Y-m-d G:i:s");
                D("GoodsBrand")->updateGoodsBrand($data);
            } catch (BusinessLogicException $e) {
                $res = array("status" => 1, "info" => $e->getMessage());
            } catch (\Exception $e) {
                \Think\Log::write($e->getMessage());
                $res["status"] = 1;
                $res["info"]   = parent::UNKNOWN_ERROR;
            }
            $this->ajaxReturn($res);
        } else {
            //$id_list = array("form" => "goods_brand_add_form");
            /*DatagridExtensionNew::getIDList($id_list, array("add_form"));*/
            $id_list = $this->id_list;
            $this->assign("id_list", $id_list);
            $this->display("add");
        }
    }

    /**
     * 返回编辑货品品牌页面
     * author:luyanfeng
     */
    public function editGoodsBrand() {
        if (IS_POST) {
            $data = I("post.data");
            $res=array('status'=>0,'info'=>'操作成功');
            try {
                if (!isset($data["is_disabled"])) {
                    $data["is_disabled"] = 0;
                }
                $data["modified"] = date("Y-m-d G:i:s");
                D("GoodsBrand")->updateGoodsBrand($data);
            } catch (BusinessLogicException $e) {
                $res = array("status" => 1, "info" => $e->getMessage());
            } catch (\Exception $e) {
                \Think\Log::write($e->getMessage());
                $res["status"] = 1;
                $res["info"]   = parent::UNKNOWN_ERROR;
            }
            $this->ajaxReturn($res);
        } else {
            $id = I("get.id");
            //$id_list = array("form" => "goods_brand_edit_form");
            /*$id_list = self::getIDList($this->id_list, array("edit_form"));*/
            $id_list = $this->id_list;
            self::getIDList($id_list,array("add_dialog"),"add","dialog");
            $this->assign("id_list", $id_list);
            $result = D("GoodsBrand")->getGoodsBrand($id);
            $this->assign("goods_brand", $result[0]);
            $this->display("edit");
        }
    }

}