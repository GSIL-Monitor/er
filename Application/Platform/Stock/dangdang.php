<?php
require_once(ROOT_DIR . '/Stock/utils.php');
require_once(ROOT_DIR . '/Manager/utils.php');
require_once(TOP_SDK_DIR . '/dangdang/DangdangClient.php');

function dd_stock_syn(&$db, &$stock, $sid)
{
    getAppSecret($stock,$appkey,$appsecret);
    //API参数

    $dd = new DangdangClient(DD_NEW_API_URL);
    $dd->setAppKey($appkey);
    $dd->setAppSecret($appsecret);
    //$dd->setMethod('dangdang.item.stock.update');
    $dd->setSession($stock->session);

    //获取店铺类型  百货/出版
    $dd_shop_type = $db->query_result_single("select prop1 from cfg_shop where shop_id = {$stock->shop_id}");

    if($dd_shop_type == "")
    {
        $stock->appsercret = $appsecret;
        if(ddGetShopType($db, $stock, $dd,$sid,$type))
        {
            $stock->prop1 = $type;
            if(!$db->execute("update cfg_shop set prop1='".$stock->prop1."' where Shop_id=".$stock->shop_id))
            {
                logx("当当同步库存时获取商家类型保存失败.类型：".$stock->prop1, $sid.'/Stock');
            }
        }
        else
        {
            logx("当当同步库存时获取商家类型失败", $sid.'/Stock');
        }

    }

    $params = array();

    if($dd_shop_type == 2 || $stock->prop1 == 2)  //1百货 2出版
    {
        $dd->setV('3.0');
        $dd->setMethod('dangdang.item.stock.update');
        $params = array(
            'gShopID' => $stock->account_id,
            'item_id' => $stock->goods_id,
            'stock' => $stock->syn_stock,
        );
        logx("当当同步出版类库存 OuterID: {$stock->outer_id}, SynStock: {$stock->syn_stock}, goods_id: {$stock->goods_id}, account_id:{$stock->account_id}", $sid.'/Stock');

    }
    else
    {
        $dd->setV('1.0');
        $dd->setMethod('dangdang.item.stock.update');
        if(!empty($stock->spec_outer_id))
        {
            $params['oit'] = $stock->spec_outer_id;
        }
        else
        {
            $params['oit'] = $stock->outer_id;
        }

        $params['stk'] = $stock->syn_stock;
        logx("当当同步百货类库存 OuterID: {$stock->outer_id}, SynStock: {$stock->syn_stock}, spec_outer_id:{$stock->spec_outer_id}", $sid.'/Stock');
    }

    $retval = $dd->sendByPost('updateItemStock.php', $params, $appsecret);
    if(API_RESULT_OK != ddErrorTest($retval,$db,$stock->shop_id))
    {
        $error_msg = $retval->error_msg;

        if($error_msg == '企业商品标识符错误')
        {
            syn_delete($db, $stock);
            logx("DD同步库存失败, 删除该同步记录: OuterID: {$stock->outer_id}, SynStock: {$stock->syn_stock}", $sid.'/Stock');
        }
        else
        {

            syn_log($db, $stock, 0, $error_msg);
            logx("DD同步库存失败, OuterID: {$stock->outer_id}, SynStock: {$stock->syn_stock} 失败原因: {$error_msg}", $sid.'/Stock');
        }

        return SYNC_FAIL;
    }
    syn_log($db, $stock, 1, "");

    return SYNC_OK;
}
//当当获取商家类型  百货/出版
function ddGetShopType(&$db, &$stock, &$dd,$sid,&$type)
{
    //API参数
    if (empty($stock->session))
        return false;

    $dd->setV('1.0');
    $dd->setMethod('dangdang.shop.getstorecategory.get');

    $retval = $dd->sendByPost('', array(), $stock->appsercret);

    if(API_RESULT_OK != ddErrorTest($retval,$db,$stock->shop_id))
    {
        $error_msg = $retval->error_msg;
        logx("当当获取商家类型失败     NumIID: {$stock->goods_id}, SkuID: {$stock->spec_id}   失败原因: {$error_msg}", $sid.'/Goods');
        return false;
    }
    if(isset($retval->shopCategories->categoryList))
    {
        $category = $retval->shopCategories->categoryList->category[count($retval->shopCategories->categoryList->category)-1];

        if(isset($category->mainCatPathId ))
        {
            $data = @explode('-', $category->mainCatPathId);

            if(intval($data[0]) === 1805)
            {
                $type = 2;
            }else {
                $type = 1;
            }
            return true;
        }
    }
    return false;
}

?>