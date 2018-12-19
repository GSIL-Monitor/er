<?php
require_once(ROOT_DIR . '/Stock/utils.php');
require_once(ROOT_DIR . '/Manager/utils.php');
require_once(TOP_SDK_DIR . '/top/Logger.php');
require_once(TOP_SDK_DIR . '/top/RequestCheckUtil.php');
require_once(TOP_SDK_DIR . '/top/TopClient.php');
require_once(TOP_SDK_DIR . '/top/request/ItemQuantityUpdateRequest.php');
require_once(TOP_SDK_DIR . '/top/request/ItemUpdateListingRequest.php');
require_once(TOP_SDK_DIR . '/top/request/ItemUpdateDelistingRequest.php');
require_once(TOP_SDK_DIR . '/top/request/ItemGetRequest.php');
require_once(ROOT_DIR . '/Common/api_error.php');

function top_stock_syn(&$db, &$stock, $sid,&$fail) {
    getAppSecret($stock, $appkey, $appsecret);
    $session = $stock->session;
    $shopId  = $stock->shop_id;

    $top            = new TopClient();
    $top->format    = 'json';
    $top->appkey    = $appkey;
    $top->secretKey = $appsecret;
    $req            = new ItemQuantityUpdateRequest();
    $req->setNumIid($stock->goods_id);
    if (!empty($stock->spec_id)) {
        $req->setSkuId($stock->spec_id);
    }

    $req->setQuantity($stock->syn_stock);
    $retval = $top->execute($req, $session);
    //同步失败做以下操作:
    if (API_RESULT_OK != topErrorTest($retval, $db, $shopId)) {
        if (@$retval->sub_code == "isv.item-is-delete:invalid-numIid-or-iid"   //商品id对应的商品已经被删除
            || @$retval->sub_code == "isv.item-not-exist:invalid-numIid-or-iid" //商品id对应的商品不存在
            || @$retval->sub_code == "isv.item-is-delete:invalid-numIid-or-iid-tmall"
            || @$retval->sub_code == "isv.item-not-exist:invalid-numIid-or-iid-tmall"
            || @$retval->sub_code == "isv.invalid-parameter:sku-properties"    //传入的sku的属性找不到对应的sku记录
            || strpos(@$retval->error_msg, "没有找到宝贝对应的SKU") !== FALSE
            || strpos(@$retval->error_msg, "SKU不存在") !== FALSE
        ) {
            //对于这些错误, 不需再次同步, 可将其从match表中删掉
            syn_delete($db, $stock);
            logx("同步库存失败, 删除该同步记录: NumIID: {$stock->goods_id}, SkuID: {$stock->spec_id}, SynStock: {$stock->syn_stock} 失败原因: {$retval->sub_code} {$retval->error_msg}", $sid . "/Stock");
            $fail[]   = array("rec_id" => @$stock->rec_id, "goods_id" => @$stock->goods_id, "spec_id" => @$stock->spec_id, "msg" => "{$retval->error_msg}");

        } else if (strpos(@$retval->error_msg, "宝贝正在参与聚划算，禁止卖家编辑、删除、下架以及修改库存") !== FALSE
            || strpos(@$retval->error_msg, "活动商品，当前时间段只能增加库存，不允许做减少商品库存操作") !== FALSE
            || strpos(@$retval->error_msg, "宝贝含有销售属性") !== FALSE
            || strpos(@$retval->error_msg, "天猫世界杯") !== FALSE
            || strpos(@$retval->error_msg, "分销平台代销商品，禁止改库存") !== FALSE
            || strpos(@$retval->error_msg, "预售商品，不能全量更新库存") !== FALSE
            || strpos(@$retval->error_msg, "双十一限定时间内上架状态的互动商品不可修改库存") !== FALSE
        ) {
            syn_disable($db, $stock, @$retval->error_msg);
            logx("同步库存失败, 停止同步: NumIID: {$stock->goods_id}, SkuID: {$stock->spec_id}, SynStock: {$stock->syn_stock} 失败原因: {$retval->sub_code} {$retval->error_msg}", $sid . "/Stock");
            $fail[]   = array("rec_id" => @$stock->rec_id, "goods_id" => @$stock->goods_id, "spec_id" => @$stock->spec_id, "msg" => "{$retval->error_msg}");
        } else if (strpos(@$retval->error_msg, "双12活动") !== FALSE
            || strpos(@$retval->error_msg, "淘宝1212大促") !== FALSE
            || strpos(@$retval->error_msg, "1212全民预售") !== FALSE
            || strpos(@$retval->error_msg, "限流") !== FALSE
            || strpos(@$retval->error_msg, "不支持全量修改") !== FALSE
            || strpos(@$retval->error_msg, "被系统锁定") !== FALSE
            || strpos(@$retval->error_msg, "互动商品") !== FALSE
            || strpos(@$retval->error_msg, "活动商品") !== FALSE
        ) {
            syn_delay($db, $stock, 3600 * 6, @$retval->error_msg);
        } else {
            //否则设置再次同步
            logx("同步库存失败, 下次将继续同步: NumIID: {$stock->goods_id}, SkuID: {$stock->spec_id}, SynStock: {$stock->syn_stock} 失败原因: {@$retval->sub_code} {$retval->error_msg}", $sid . "/Stock");
            $fail[]   = array("rec_id" => @$stock->rec_id, "goods_id" => @$stock->goods_id, "spec_id" => @$stock->spec_id, "msg" => "{$retval->error_msg}");
        }
        //添加同步失败记录
        syn_log($db, $stock, 0, $retval->error_msg);

        if (@$retval->sub_code == 'accesscontrol.limited-by-api-access-count')
            return SYNC_QUIT;

        return SYNC_FAIL;
    }

    //同步成功做以下操作:
    //同步0库存成功:
    if ($stock->syn_stock == 0) {
        top_sale_status($db, $stock->rec_id, 2);
        syn_log($db, $stock, 3, "");
    }else if ($stock->syn_stock >= $stock->stock_syn_min && $stock->stock_syn_min >= 0 && $stock->syn_stock > 0
        && $stock->is_auto_listing == 1 && $stock->status == 2
    ) {
        //超过最小库存且设置了自动上架
        //status状态在platstock.php中维护
        //判断同步货品的上架时间
        $consider_list_time = getSysCfg($db, 'sys_goods_consider_list_time', 0); //上下架配置
        if ($consider_list_time) {
            logx("上架货品为：{$stock->goods_id} 上架时间为：{$stock->list_time} 下架时间：{$stock->delist_time}", $sid . "/Stock");
            if (isset($stock->list_time) && (time() - strtotime($stock->list_time) < 0)) {
                logx("上架失败, 上架货品为：{$stock->goods_id} 上架时间为：{$stock->list_time}", $sid . "/Stock");
                return SYNC_OK;
            }
        }
        //停用接口调用 改为如上配置设置
        /*
        $req = new ItemGetRequest();
        $req->setNumIid($stock->goods_id);
        $req->setFields("approve_status,list_time,delist_time");
        $retval = $top->execute($req, $session);
        if(API_RESULT_OK != topErrorTest($retval,$db,$shopId))
        {
            logx("上架失败, 失败原因: {$retval->sub_code} {$retval->error_msg}", $sid);
            return SYNC_FAIL;
        }
        $list_time = @$retval->item->list_time;		//上架时间
        $delist_time = @$retval->item->delist_time;	//下架时间暂不考虑
        logx("货品上下架时间, 货品ID：{$stock->goods_id} 上架时间为：{$list_time} 下架时间为：{$delist_time}", $sid);
        if(isset($retval->item->list_time) && time()-strtotime($list_time)<0)
        {
            logx("上架失败, 上架货品为：{$stock->goods_id} 上架时间为：{$list_time}", $sid);
            return SYNC_OK;
        }
        */
        //将该货品上架
        $req = new ItemUpdateListingRequest();
        $req->setNumIid($stock->goods_id);
        $req->setNum($stock->syn_stock);
        $retval = $top->execute($req, $session);

        if (API_RESULT_OK != topErrorTest($retval, $db, $shopId)) {
            logx("上架失败, 失败原因: {$retval->sub_code} {$retval->error_msg}", $sid . "/Stock");
            $fail[]   = array("rec_id" => @$stock->rec_id, "goods_id" => @$stock->goods_id, "spec_id" => @$stock->spec_id, "msg" => "{$retval->error_msg}");
            return SYNC_FAIL;
        }
        logx("stock_cmd 上架操作成功，上架数量为{$stock->syn_stock}", $sid . "/Stock");
        top_sale_status($db, $stock->rec_id, 1);
        syn_log($db, $stock, 2, "");
    }else{
        syn_log($db, $stock, 1, "");
    }
    logx("同步库存成功: NumIID: {$stock->goods_id}, SkuID: {$stock->spec_id}, SynStock: {$stock->syn_stock}", $sid . "/Stock");

    return SYNC_OK;
}

?>