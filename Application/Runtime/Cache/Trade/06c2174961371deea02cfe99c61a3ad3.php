<?php if (!defined('THINK_PATH')) exit();?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<!-- <link rel="stylesheet" type="text/css" href="/Public/Css/easyui.css">
<link rel="stylesheet" type="text/css" href="/Public/Css/icon.css">
<link rel="stylesheet" type="text/css" href="/Public/Css/table.css">
<script type="text/javascript" src="/Public/Js/jquery.min.js"></script>
<script type="text/javascript" src="/Public/Js/jquery.easyui.min.js"></script>
<script type="text/javascript" src="/Public/Js/easyui-lang-zh_CN.js"></script>
<script type="text/javascript" src="/Public/Js/datagrid.extends.js"></script>
<script type="text/javascript" src="/Public/Js/jquery.extends.js"></script>
<script type="text/javascript" src="/Public/Js/tabs.util.js"></script>
<script type="text/javascript" src="/Public/Js/erp.util.js"></script>
<script type="text/javascript" src="/Public/Js/rich-datagrid.util.js"></script>
<script type="text/javascript" src="/Public/Js/thin-datagrid.util.js"></script>
<script type="text/javascript" src="/Public/Js/datalist.util.js"></script>
<script type="text/javascript" src="/Public/Js/area.js"></script>
-->
</head>
<body>
<!-- layout-datagrid -->
<div class="easyui-layout" data-options="fit:true" style="width:100%;height:100%;overflow:hidden;" id="panel_layout">
<div class="easyui-panel" title="搜索列表" data-options="iconCls:'icon-search',region:'west',split:true,collapsed:false" style="width:230px;background: #eee;" id="panel_search">
	
<div id="trade_check_search" class="easyui-tabs" border="false" >   
	<div title="筛选条件" style="background: #eee;" >
	<form id="<?php echo ($id_list["form"]); ?>">
	<div class="form-div" style="background: #eee;margin-bottom: 2px;margin-left: 100px;">
		<a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-search'" onclick="tradeCheck.searchData(this);">搜索</a>
		<a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-redo'" onclick="tradeCheck.loadData();">重置</a>
	</div>
	<hr style="border:none;border-top:2px dotted #95B8E7;width: 220px;background: #eee;">
	<div  style="position:absolute;height:80%; width: 100%;overflow:auto;background: #eee;">
		<div class="form-div"><label>&nbsp;订单编号：</label><input class="easyui-textbox txt" type="text" name="search[trade_no]" /></div>
		<div class="form-div"><label>&nbsp;原始单号：</label><input class="easyui-textbox txt" type="text" name="search[src_tids]" /></div>
		<input type="hidden" name="search[passel_src_tids]" value="" id="passel_src_tids" />
		<div class="form-div"><label>&nbsp;　　店铺：</label><select class="easyui-combobox sel" name="search[shop_id]"><option value="all">全部</option><?php if(is_array($list["shop"])): $i = 0; $__LIST__ = $list["shop"];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vo["id"]); ?>"><?php echo ($vo["name"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?></select></div>
		<div class="form-div"><label>&nbsp;　　仓库：</label><select class="easyui-combobox sel" name="search[warehouse_id]"><option value="all">全部</option><?php if(is_array($list["warehouse"])): $i = 0; $__LIST__ = $list["warehouse"];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vo["id"]); ?>"><?php echo ($vo["name"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?></select></div>
		<div class="form-div"><label>&nbsp;物流公司：</label><select class="easyui-combobox sel" name="search[logistics_id]"><option value="all">全部</option><?php if(is_array($list["logistics"])): $i = 0; $__LIST__ = $list["logistics"];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vo["id"]); ?>"><?php echo ($vo["name"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?></select></div>
		<div class="form-div"><label>&nbsp;退款状态：</label><input class="easyui-combobox txt" name="search[refund_status]" data-options="panelHeight:'auto',valueField:'id',textField:'name',data:formatter.get_data('refund_status')"/> </div>
		<hr style="border:none;border-top:2px dotted #95B8E7;">		
		<div class="form-div"><label>&nbsp;　　标旗：</label><input class="easyui-combobox txt" name="search[remark_flag]" data-options="panelHeight:'auto',valueField:'id',textField:'name',data:formatter.get_data('remark_flag')"/> </div>
		<div class="form-div"><label>&nbsp;　　备注：</label><input class="easyui-combobox txt" name="search[remark_id]" data-options="panelHeight:'auto',valueField:'id',textField:'name',data:formatter.get_data('remark')"/> </div>
		<div class="form-div"><label>&nbsp;客服备注：</label><input class="easyui-textbox txt" type="text" name="search[cs_remark]" /></div>
		<div class="form-div"><label>&nbsp;买家留言：</label><input class="easyui-textbox txt" type="text" name="search[buyer_message]" /></div>
		<hr style="border:none;border-top:2px dotted #95B8E7;">
		<div class="form-div"><label>&nbsp;客户网名：</label><input class="easyui-textbox txt" type="text" name="search[buyer_nick]" /></div>
		<div class="form-div"><label>&nbsp;　收件人：</label><input class="easyui-textbox txt" type="text" name="search[receiver_name]" /></div>
		<div class="form-div"><label>&nbsp;电话号码：</label><input class="easyui-textbox txt" type="text" name="search[receiver_mobile]" /></div>
		<hr style="border:none;border-top:2px dotted #95B8E7;">
		<div class="form-div"><label>&nbsp;商家编码：</label><input class="easyui-textbox txt" type="text" name="search[spec_no]" /></div>
		<div class="form-div"><label>&nbsp;货品编码：</label><input class="easyui-textbox txt" type="text" name="search[goods_no]" /></div>
		<div class="form-div"><label>&nbsp;货品品牌：</label><select class="easyui-combobox sel" name="search[brand_id]"><option value="all">全部</option><?php if(is_array($list["brand"])): $i = 0; $__LIST__ = $list["brand"];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vo["id"]); ?>"><?php echo ($vo["name"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?></select></div>
		<div class="form-div"><label>&nbsp;货品分类：</label><input class="txt" id="trade_tree_class_show_goods" value="-1" name="search[class_id]" data-options="url:'<?php echo U('Goods/GoodsClass/getTreeClass');?>?type=all',method:'post',required:true"/></div>
		<div class="form-div"><label>&nbsp;品名包含：</label><input class="easyui-textbox txt" type="text" name="search[goods_name_include]"/></div>
		<div class="form-div"><label>&nbsp;品名不含：</label><input class="easyui-textbox txt" type="text" name="search[goods_name_exclude]"/></div>
		<div class="form-div"><label>包含货品：</label><input id="check_include_goods_type_count" class="easyui-textbox txt" type="text" data-options="editable:false,buttonText: '...'" /></div>
		<div class="form-div"><label></label><input id="check_include_goods_type_count_hidden" type="hidden" value="" name="search[include_goods_type_count]" /></div>
		<div class="form-div"><label>不包含货品：</label><input id="not_check_include_goods_type_count" class="easyui-textbox txt" type="text" data-options="editable:false,buttonText: '...'" /></div>
		<div class="form-div"><label></label><input id="not_check_include_goods_type_count_hidden" type="hidden" value="" name="search[not_include_goods_type_count]" /></div>
		<hr style="border:none;border-top:2px dotted #95B8E7;">
		<div class="form-div"><label>&nbsp;订单来源：</label><input class="easyui-combobox txt" name="search[trade_from]" data-options="panelHeight:'auto',valueField:'id',textField:'name',data:formatter.get_data('trade_from')"/> </div>
		<div class="form-div"><label>&nbsp;　　类别：</label><input class="easyui-combobox txt" name="search[trade_type]" data-options="panelHeight:'auto',valueField:'id',textField:'name',data:formatter.get_data('trade_type')"/></div>
		<div class="form-div"><label>&nbsp;　　标记：</label><input id="<?php echo ($id_list["search_flag"]); ?>" class="easyui-combobox txt" name="search[flag_id]"/></div>
		<div class="form-div"><label>&nbsp;驳回原因：</label><select class="easyui-combobox sel" name="search[revert_reason]"><option value="all">全部</option><?php if(is_array($list["reason"])): $i = 0; $__LIST__ = $list["reason"];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vo["id"]); ?>"><?php echo ($vo["name"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?></select></div>
		<div class="form-div"><label>&nbsp;异常原因：</label><input class="easyui-combobox txt" name="search[bad_reason_detail]" data-options="valueField:'id',textField:'name',data:formatter.get_data('bad_reason')"/></div>
		<hr style="border:none;border-top:2px dotted #95B8E7;">
		<div class="form-div"><label>　　　省份：</label><input id="tradeCheckProvince" class="easyui-combobox txt" type="text" name="search[receiver_province]"  data-options="multiple:true,onChange:function(newValue, oldValue){province_select(newValue, oldValue);}"/></div>
		<div class="form-div"><label>　　　城市：</label><input id="tradeCheckCity" class="easyui-combobox txt" type="text" name="search[receiver_city]"/></div>
		<div class="form-div"><label>　　　区县：</label><input id="tradeCheckDistrict" class="easyui-combobox txt" type="text" name="search[receiver_district]"/></div>
		<hr style="border:none;border-top:2px dotted #95B8E7;">			
		<div class="form-div"><label>&nbsp;下单时间：</label><input class="easyui-datetimebox txt" type="text" name="search[trade_start_time]" data-options="editable:false"/></div>
		<div class="form-div"><label>&nbsp;　　　至：</label><input class="easyui-datetimebox txt" type="text" name="search[trade_end_time]" data-options="editable:false"/> </div>
		<div class="form-div"><label>&nbsp;付款时间：</label><input class="easyui-datetimebox txt" type="text" name="search[pay_start_time]" data-options="editable:false"/></div>
		<div class="form-div"><label>&nbsp;　　　至：</label><input class="easyui-datetimebox txt" type="text" name="search[pay_end_time]" data-options="editable:false"/> </div>
		<hr style="border:none;border-top:2px dotted #95B8E7;">
		<div class="form-div"><label>&nbsp;货品数量：</label><input type="text" class="easyui-numberbox" style="width:52px;" name="search[small_number]"/> 到 <input type="text" class="easyui-numberbox" style="width:52px;" name="search[big_number]"/></div>
		<div class="form-div"><label>&nbsp;货品种类：</label><input type="text" class="easyui-numberbox" style="width:52px;" name="search[small_type]"/> 到 <input type="text" class="easyui-numberbox" style="width:52px;" name="search[big_type]"/></div>
		<div class="form-div"><label>&nbsp;订单估重：</label><input type="text" class="easyui-numberbox" data-options="min:0,precision:4" style="width:52px;" name="search[small_weight]"/> 到 <input type="text" class="easyui-numberbox"  data-options="min:0,precision:4" style="width:52px;" name="search[big_weight]"/></div>
		<div class="form-div"><label>&nbsp;实收金额：</label><input type="text" class="easyui-numberbox" data-options="min:0,precision:4" style="width:52px;" name="search[small_paid]"/> 到 <input type="text" class="easyui-numberbox" data-options="min:0,precision:4" style="width:52px;" name="search[big_paid]"/></div>
		<div class="form-div"><label>&nbsp;一单一货：</label><input extend_type="complex-check" type="checkbox" name="search[one_goods_num]"  value="" onclick="$(this).triStateCheckbox('click')"></div>
	</div>
	</form>
	</div>
	<div id="<?php echo ($id_list["fast_div"]); ?>" title="快捷查询" style="background: #eee;" data-options="tools:[{iconCls:'icon-mini-refresh', handler:function(){tradeCheck.loadData(1);}}]">
	<div class="form-div">
		<fieldset style="border:1px solid #95B8E7;padding: 2px;margin-top: 2px;margin-right: 8px;"><legend>特殊标记</legend>
        <a href="javascript:void(0)" data-options="plain:true" onclick="tradeCheck.fastSearch(this,'search[freeze_reason]')">　　冻结订单</a></br>
        <a href="javascript:void(0)" data-options="plain:true" onclick="tradeCheck.fastSearch(this,'search[revert]')">　　驳回订单</a></br>
        <a href="javascript:void(0)" data-options="plain:true" onclick="tradeCheck.fastSearch(this,'search[bad_reason]')">　　异常订单</a></br>
        <a href="javascript:void(0)" data-options="plain:true" onclick="tradeCheck.fastSearch(this,'search[merge]')">　　合并订单</a></br>
        <!-- <a href="javascript:void(0)" data-options="plain:true" onclick="tradeCheck.fastSearch(this,'search[auto_merge]')">　　自动合并订单</a></br> -->
        <a href="javascript:void(0)" data-options="plain:true" onclick="tradeCheck.fastSearch(this,'search[split]')">　　拆分订单</a></br>
        <a href="javascript:void(0)" data-options="plain:true" onclick="tradeCheck.fastSearch(this,'search[refund]')">　　退款订单</a></br>
        <a href="javascript:void(0)" data-options="plain:true" onclick="tradeCheck.fastSearch(this,'search[return]')">　　换货销售订单</a></br>
        </fieldset>
        <fieldset style="border:1px solid #95B8E7;padding: 2px;margin-top: 2px;margin-right: 8px;"><legend>备注</legend>
        <a href="javascript:void(0)" data-options="plain:true" onclick="tradeCheck.fastSearch(this,'search[cs]')">　　有客服备注</a></br>
        <a href="javascript:void(0)" data-options="plain:true" onclick="tradeCheck.fastSearch(this,'search[no_cs]')">　　无客服备注</a></br>
        <a href="javascript:void(0)" data-options="plain:true" onclick="tradeCheck.fastSearch(this,'search[client]')">　　有客户备注</a></br>
        <a href="javascript:void(0)" data-options="plain:true" onclick="tradeCheck.fastSearch(this,'search[no_client]')">　　无客户备注</a></br>
        </fieldset>
        <fieldset style="border:1px solid #95B8E7;padding: 2px;margin-top: 2px;margin-right: 8px;"><legend>时间</legend>
        <a href="javascript:void(0)" data-options="plain:true" onclick="tradeCheck.fastSearch(this,'search[one_day]')">　　一天内</a></br>
        <a href="javascript:void(0)" data-options="plain:true" onclick="tradeCheck.fastSearch(this,'search[tow_day]')">　　两天内</a></br>
        <a href="javascript:void(0)" data-options="plain:true" onclick="tradeCheck.fastSearch(this,'search[one_week]')">　　一周内</a></br>
        <a href="javascript:void(0)" data-options="plain:true" onclick="tradeCheck.fastSearch(this,'search[one_month]')">　　一月内</a></br>
        </fieldset>
	</div>
	</div>
</div>

</div>
<!-- layout-center-datagrid -->
 
<div data-options="region:'center'" style="height:100%;">
<div data-options="region:'center',fit:true" style="width:100%;height:70%;background:#eee;"><table id="<?php echo ($datagrid["id"]); ?>" class="easyui-datagrid" data-options='<?php $dataOptions = array_merge(array ( 'border' => false, 'fit' => true, 'fitColumns' => true, 'rownumbers' => true, 'singleSelect' => true, 'pagination' => true, 'pageList' => array ( 0 => 20, 1 => 50, 2 => 100, ), 'pageSize' => 20, ), $datagrid["options"]);if(isset($dataOptions['toolbar']) && substr($dataOptions['toolbar'],0,1) != '#'): unset($dataOptions['toolbar']); endif;if(isset($dataOptions['methods'])): unset($dataOptions['methods']);endif; echo trim(json_encode($dataOptions), '{}[]').((isset($datagrid["options"]['toolbar']) && substr($datagrid["options"]['toolbar'],0,1) != '#')?',"toolbar":'.$datagrid["options"]['toolbar']:null).(isset($datagrid["options"]['methods'])? ','.$datagrid["options"]['methods']:null); ?>' style="<?php echo ($datagrid["style"]); ?>" ><thead><tr><?php if(is_array($datagrid["fields"])):foreach ($datagrid["fields"] as $key=>$arr):if(isset($arr['formatter'])):unset($arr['formatter']);endif;if(isset($arr['methods'])):unset($arr['methods']);endif;if(isset($arr['editor'])):unset($arr['editor']);endif;echo "<th data-options='".trim(json_encode($arr), '{}[]').(isset($datagrid["fields"][$key]['formatter'])?",\"formatter\":".$datagrid["fields"][$key]['formatter']:null).(isset($datagrid["fields"][$key]['editor'])?",\"editor\":".$datagrid["fields"][$key]['editor']:null).(isset($datagrid["fields"][$key]['methods'])?",".$datagrid["fields"][$key]['methods']:null)."'>".$key."</th>";endforeach;endif; ?></tr></thead></table></div>
<!-- layout-south-tabs -->
<div data-options="region:'south',split:true" style="height:30%;background:#eee;overflow:hidden;">
<?php if($datagrid["setTabs"] == 1): ?><a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-edit'" onclick="<?php echo ($datagrid["setTabsClick"]); ?>" style="position: absolute;margin-left: 650px;z-index:10000;">设置表头</a><?php endif; ?>
 <div class="easyui-tabs" data-options="fit:true,border:false,plain:true" id="<?php echo ($id_list["tab_container"]); ?>"> 
</div> </div> 
</div>
<script type="text/javascript"> 
$(function(){
setTimeout('add_tabs(JSON.parse(\'<?php echo ($arr_tabs); ?>\'))',0);
}); 
/*
$(function(){ add_tabs(JSON.parse('<?php echo ($arr_tabs); ?>')); 
$('body').show();
$('#panel_layout').layout('resize',{height:$('#panel_layout').parent().height()});
}); 
*/
</script>

</div>
<!-- dialog -->

	<div id="<?php echo ($id_list["fileDialog"]); ?>" class="easyui-panel" style="padding:25px 50px 25px 50px">
        <form id="<?php echo ($id_list["fileForm"]); ?>" method="post" enctype="multipart/form-data">
            <div style="margin-bottom:25px">
                <input class="easyui-filebox" name="file" data-options="prompt:'请选择文件...','buttonText':'请选择文件'" style="width:100%;">
            </div>
            <div align="center">
                <a href="javascript:void(0)" class="easyui-linkbutton" style="width:50%" onclick="tradeCheck.upload()">上传</a>
            </div>
        </form>
    </div>
	<div id="<?php echo ($id_list["add"]); ?>"></div><div id="<?php echo ($id_list["edit"]); ?>"></div><div id="<?php echo ($id_list["invalid"]); ?>"></div><div id="<?php echo ($id_list["exchange"]); ?>"></div><div id="passel_split"></div><div id="<?php echo ($id_list["add_goods"]); ?>"></div><div id="<?php echo ($id_list["suite_split"]); ?>"></div>
	<div id="<?php echo ($id_list["sms"]); ?>"></div>
	<div id="<?php echo ($id_list["include_goods"]); ?>"></div><div id="check_include_show_dialog"></div><div id="check_include_add_suite_dialog"></div>

<!-- toolbar -->

<div id="<?php echo ($id_list["toolbar"]); ?>" style="padding:5px;height:auto">
<form id="<?php echo ($id_list["form"]); ?>">
<div id="check_menu" class="easyui-menu" style="width:130px;" noline="true">
	<div data-options="iconCls:'icon-edit'" onclick="tradeCheck.edit(1)">编　　辑</div>
	<div id="copy" data-options="iconCls:'icon-edit'">复　　制</div>
	<div data-options="iconCls:'icon-truck'" >
		<span>修改物流</span>
		<div style="width:100px;height:300px;">
		<?php if(is_array($list["logistics"])): $i = 0; $__LIST__ = $list["logistics"];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><div onclick="tradeCheck.changeTrade(<?php echo ($vo["id"]); ?>,0)"><span style="margin-left: -23px;"><?php echo ($vo["name"]); ?></span></div><?php endforeach; endif; else: echo "" ;endif; ?>
		</div>
	</div>
	<div data-options="iconCls:'icon-warehouse'" >
		<span>修改仓库</span>
		<div style="width:100px;height:300px;">
		<?php if(is_array($list["warehouse"])): $i = 0; $__LIST__ = $list["warehouse"];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><div onclick="tradeCheck.changeTrade(<?php echo ($vo["id"]); ?>,1)"><span style="margin-left: -23px;"><?php echo ($vo["name"]); ?></span></div><?php endforeach; endif; else: echo "" ;endif; ?>
		</div>
	</div>
	<div data-options="iconCls:'icon-edit'" onclick="tradeCheck.editBatchRemark()">修改客服备注</div>
	<div class="menu-sep"></div>
    <div data-options="iconCls:'icon-search'" onclick="checkByType(0)">
    	<span>审　　核</span>
    	<div><div data-options="iconCls:'icon-search'" onclick="checkByType(1)">强制审核</div>
    		<div data-options="iconCls:'icon-search'" onclick="tradeCheck.setExamineConf()">设置自动审核</div>
    	</div>
    </div>
    <div data-options="iconCls:'icon-search'" onclick="quickCheckTrade(0)">
    	<span>快速审核</span>
    	<div style="width:140px;"><div data-options="iconCls:'icon-search'" onclick="quickCheckTrade(1)">快速审核全部订单</div></div>
    </div>
    <div class="menu-sep"></div>
    <div data-options="iconCls:'icon-edit'">
    	<span>货品处理</span>
    	<div>
    		<div data-options="iconCls:'icon-reload'" onclick="passelExchange()">批量换货</div>
    		<div data-options="iconCls:'icon-add'" onclick="passelAddGoods(0)">添加货品</div>
    		<div data-options="iconCls:'icon-add'" onclick="passelAddGoods(1)">添加赠品</div>
			<div data-options="iconCls:'icon-reload'" onclick="recalculationGift()">重算赠品</div>
    	</div> 
    </div>
    <div data-options="iconCls:'icon-search'" onclick="giftNotSentReason()">赠品未赠原因</div>
    <div class="menu-sep"></div>
    <div data-options="iconCls:'icon-merge'" onclick="mergeTrade(0)">合　　并</div>
    <div data-options="iconCls:'icon-split'" onclick="splitTrade(0)">
    	<span>拆　　分</span>
    	<div>
    		<div data-options="iconCls:'icon-split'" onclick="passelSplit(0)">批量拆分</div>
    		<div data-options="iconCls:'icon-split'" onclick="mergeSplit(0)">一键拆分合并单</div>
    		<div data-options="iconCls:'icon-split'" onclick="suiteSplit(0)">按组合装拆分</div>
			<div data-options="iconCls:'icon-split'" onclick="">
				<span>深度拆分</span>
				<div>
					<div data-options="iconCls:'icon-split'" onclick="deepSplit(0)">拆为一单一货（按单品）</div>
					<div data-options="iconCls:'icon-split'" onclick="deepSplitSuite(0)">拆为一单一货（按组合装）</div>
				</div>
			</div>
		</div>
    </div>
    <div class="menu-sep"></div>
    <div data-options="iconCls:'icon-clear'" onclick="tradeCheck.clearRevertTrade()">清除驳回</div>
	<div data-options="iconCls:'icon-clear'" onclick="tradeCheck.clearBadTrade()">清除异常</div>
	<div class="menu-sep"></div>
	<div data-options="iconCls:'icon-lock'" onclick="tradeCheck.freezeTrade()">冻　　结</div>
	<div data-options="iconCls:'icon-unlock'" onclick="tradeCheck.unfreezeTrade()">解　　冻</div>
	<div class="menu-sep"></div>
	<div data-options="iconCls:'icon-redo'" onclick="tradeCheck.cancelTrade()" title="可取消线上未付款订单和线下订单，执行后可在订单管理查看，不可恢复">取　　消</div>
	<div data-options="iconCls:'icon-refund'" onclick="tradeCheck.refundTrade()" title="仅可处理已付款订单，执行后可在订单管理查看">全额退款</div>
	<div data-options="iconCls:'icon-cancel'" onclick="tradeCheck.deleteTrade()" title="仅可删除手工建单，执行后不可恢复" >删　　除</div>
	<div data-options="iconCls:'icon-remove'" onclick="tradeCheck.removeTrade()" title="可清除线上和线下订单，执行后可在订单管理查看，可恢复" >清　　除</div>
	<div class="menu-sep"></div>
	<div data-options="iconCls:'icon-reload'" onclick="uploadRemarkAndFlag()">回传备注和标旗</div>
	<div class="menu-sep"></div>
    <div data-options="iconCls:'icon-search'" onclick="tradeCheck.checkNumber()">查看号码</div>
    <div class="menu-sep"></div>
	<div data-options="iconCls:'icon-back'" onclick="checkTrade(2)" title="直接发货针对于不需要系统打印物流单的订单">直接发货</div>
	<div data-options="iconCls:'icon-sms'" onclick="tradeCheck.sms()" title="主要用于提醒买家确认收货地址">发送短信</div>
</div>
</form>
<input type="hidden" id="<?php echo ($id_list["hidden_flag"]); ?>" value="1">
<a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-edit',plain:true" onclick="tradeCheck.edit(1)">编辑</a>
<a href="javascript:void(0)" class="easyui-menubutton" data-options="iconCls:'icon-search',menu:'#mbut-check-trade'" onclick="checkByType(0)">审核</a>
<a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-search',plain:true" onclick="tradeCheck.searchSrcTids()">根据原始单号筛选订单</a>
<!-- <a href="javascript:void(0)" class="easyui-menubutton" data-options="iconCls:'icon-search',menu:'#mbut-quick-check'" onclick="quickCheckTrade(0)">快速审核</a>
<a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-save',plain:true" onclick="mergeTrade()">合并</a> -->
<a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-split',plain:true" onclick="splitTrade()">拆分</a>
<a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-no_match',plain:true" id="<?php echo ($id_list["invalid_goods"]); ?>" onclick="tradeCheck.invalidGoods()">未匹配货品</a>
<!--<a href="javascript:void(0)" class="easyui-menubutton" data-options="iconCls:'icon-ok',menu:'#mbut-turn-normal'" >转正常单</a> -->
<a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-clear',plain:true" onclick="tradeCheck.clearRevertTrade()">清除驳回</a>
<!-- <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-clear',plain:true" onclick="tradeCheck.clearBadTrade()">清除异常</a>
<a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-lock',plain:true" onclick="tradeCheck.freezeTrade()">冻结</a>
<a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-remove',plain:true" onclick="tradeCheck.unfreezeTrade()">解冻</a>
<a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-redo',plain:true" onclick="tradeCheck.cancelTrade()">取消</a>-->
<!-- <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-back',plain:true" onclick="checkTrade(2)" title="直接发货针对于不需要系统打印物流单的订单">直接发货</a> -->
<!-- <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-refund',plain:true" onclick="tradeCheck.refundTrade()">全额退款</a>  -->
<a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-sign',plain:true" onclick="tradeCheck.setFlag()">标记管理</a>
<a href="javascript:void(0)" class="easyui-menubutton" data-options="iconCls:'icon-sign',plain:true,menu:'#tradeCheck-flag'">标记订单</a>
<a href="javascript:void(0)" class="easyui-menubutton" data-options="iconCls:'icon-truck',plain:true,menu:'#tradeCheck-logistics'">修改物流</a>
<a href="javascript:void(0)" class="easyui-menubutton" data-options="iconCls:'icon-warehouse',plain:true,menu:'#tradeCheck-warehouse'">修改仓库</a>
<!-- <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-search',plain:true" onclick="tradeCheck.checkNumber()">查看号码</a> -->
<a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-edit',plain:true" onclick="setDatagridField('Trade/TradeCheck','trade_check','<?php echo ($datagrid["id"]); ?>',1)">设置表头</a>
<a href="javascript:void(0)" id="tradeCheck_next_link" style="margin-left: 20px;'" class="easyui-linkbutton" data-options="iconCls:'icon-next',plain:true" onClick="open_menu('单据打印', '<?php echo U('Stock/StockSalesPrint/getPrintList');?>')">单据打印</a>
<!-- <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-back',plain:true" onclick="checkTrade(2)" title="直接发货针对于不需要系统打印物流单的订单">直接发货</a>
<a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-remove',plain:true" onclick="tradeCheck.deleteTrade()">删除</a> -->
<div id="mbut-check-trade">
<div data-options="iconCls:'icon-search'" onclick="checkByType(1)">强制审核</div>
<div data-options="iconCls:'icon-search'" onclick="tradeCheck.setExamineConf()">设置自动审核</div>
</div>
<div id="tradeCheck-logistics" style="width:100px;height:300px;" noline="true" >
	<?php if(is_array($list["logistics"])): $i = 0; $__LIST__ = $list["logistics"];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><div onclick="tradeCheck.changeTrade(<?php echo ($vo["id"]); ?>,0)"><span style="margin-left: -25px;"><?php echo ($vo["name"]); ?></span></div><?php endforeach; endif; else: echo "" ;endif; ?>
</div>
<div id="tradeCheck-warehouse" style="width:100px;height:300px;" noline="true" >
	<?php if(is_array($list["warehouse"])): $i = 0; $__LIST__ = $list["warehouse"];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><div onclick="tradeCheck.changeTrade(<?php echo ($vo["id"]); ?>,1)"><span style="margin-left: -25px;"><?php echo ($vo["name"]); ?></span></div><?php endforeach; endif; else: echo "" ;endif; ?>
</div>
<div id="tradeCheck-flag" style="width:100px;height:150px;" noline="true" >
	<?php if(is_array($list["flag"])): $i = 0; $__LIST__ = $list["flag"];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><div onclick="tradeCheck.newSelectFlag(<?php echo ($vo["id"]); ?>)"><span style="margin-left: -25px; background-color:<?php echo ($vo["bg_color"]); ?>; font-family:<?php echo ($vo["family"]); ?>; color: <?php echo ($vo["color"]); ?>;"><?php echo ($vo["name"]); ?></span></div><?php endforeach; endif; else: echo "" ;endif; ?>
</div>
</div>
<script type="text/javascript" >
//加载完调用的方法
$(function(){
	$('#trade_tree_class_show_goods').changStyleTreeCombo('trade_tree_class_show_goods');
	setTimeout(function () { 
	tradeCheck = new RichDatagrid(JSON.parse('<?php echo ($params); ?>')); 
	tradeCheck.setFormData();
	tradeCheckArea = new area("tradeCheckProvince", "tradeCheckCity", "tradeCheckDistrict");
	tradeCheck.setExamineConf=function(){
        open_menu(tradeCheck.params.set_conf.title,tradeCheck.params.set_conf.url);
    }
	tradeCheck.tmp_map_obj = {
		"include" 		: {
			"datagrid" 					: '#check_include_goods_list_datagrid',
			"include_search_data" 		: '#check_include_goods_type_count_hidden',
			"type"						: 'include_goods',
			"tmp_include_relation" 		: '',
			"tmp_include_search_data" 	: '',
		},
		"not_include" 	: {
			"datagrid" 					: '#not_check_include_goods_list_datagrid',
			"include_search_data" 		: '#not_check_include_goods_type_count_hidden',
			"type"						: 'not_include_goods',
			"tmp_include_relation" 		: '',
			"tmp_include_search_data" 	: '',
		}
	};
	$('#check_include_goods_type_count').textbox({onClickButton:function(){
		tradeCheck.include_goods_select($(this),'include');
	}});
	$('#check_include_goods_type_count').textbox({'prompt':'点击右侧按钮',missingMessage:'单击按钮添加货品'});
	$('#check_include_goods_type_count').textbox('textbox').css({'background-color':'#ddd'});
	$('#not_check_include_goods_type_count').textbox({onClickButton:function(){
		tradeCheck.include_goods_select($(this),'not_include');
	}});
	$('#not_check_include_goods_type_count').textbox({'prompt':'点击右侧按钮',missingMessage:'单击按钮添加货品'});
	$('#not_check_include_goods_type_count').textbox('textbox').css({'background-color':'#ddd'});

	tradeCheck.submitEditDialog=function(){submitTradeEditDialog(0);};//保存
	tradeCheck.submitEditCheckDialog=function(){submitTradeEditDialog(1)};//保存并审核
	//未匹配货品->动态数量
	tradeCheck.setInvalidGoodsNum=function(num){
		var invalid_goods='<?php echo ($id_list["invalid_goods"]); ?>';
		if(num>0){$('#'+invalid_goods+' .l-btn-text').text('未匹配货品('+num+')').css('color','red');}
		else if(num==0){$('#'+invalid_goods+' .l-btn-text').text('未匹配货品').css('color','#000000');}
	}
	tradeCheck.params.invalid_num=<?php echo ($id_list["invalid_goods_total"]); ?>;
	tradeCheck.setInvalidGoodsNum(tradeCheck.params.invalid_num);
	tradeCheck.getInvalidGoodsNum=function(){
		var url= "<?php echo U('TradeCheck/getInvalidGoodsNum');?>";
		$.post(url,'',function(res){
			tradeCheck.setInvalidGoodsNum(res);
			return true;
		});		
	}
	$('#'+tradeCheck.params.datagrid.id).datagrid('options').onRowContextMenu =function(e, rowIndex, rowData){
		 // 三个参数：e，rowIndex当前点击时所在行的索引，rowData当前行的数据
            e.preventDefault(); //阻止浏览器捕获右键事件
            var rows=$('#'+tradeCheck.params.datagrid.id).datagrid('getSelections');
            var chose=false;
            for(var i=0;i<rows.length;i++){
            	if($('#'+tradeCheck.params.datagrid.id).datagrid('getRowIndex',rows[i])==rowIndex){chose=true;}
            }
            if(chose==false){
            	$(this).datagrid("clearSelections"); //取消所有选中项
          		$(this).datagrid("selectRow", rowIndex); //根据索引选中该行
       		}
            $(this).datagrid('options').that.click(rowIndex,rowData);
            $('#check_menu').menu('show', {
                hideOnUnhover: false,
                left: e.pageX,//在鼠标点击处显示菜单
                top: e.pageY
            });
            //复制选中方法（该段代码必须放到右键触发事件的最后）
            if(tradeCheck.selectField!=''){
            	var text;
            	if(tradeCheck.selectField=='buyer_nick'&&tradeCheck.selectRows[0]['buyer_nick'].indexOf("http://www.taobao.com/webww")==25){
            		text=$(tradeCheck.selectRows[0]['buyer_nick']).text();
            	}else{
            		text=tradeCheck.selectRows[0][tradeCheck.selectField];
            	}
        		$('#copy').zclip({
        	        path: "/Public/Js/ZeroClipboard.swf",
        	        copy:text,
        	    });
        		}
	};	
	//订单->编辑验证
	tradeCheck.checkEdit=function(row,list){
		if(row==undefined||row==null){row=tradeCheck.selectRows[0];};
		if(row.id!=undefined&&(parseInt(row.id)<=0||parseInt(row.id)=='NaN')){
			if(list==undefined){messager.alert('请选择有效的订单');}
			else{list.push({trade_no:row['trade_no'],result_info:'请选择有效的订单'});}
			return false;}
		if(row.freeze_reason!=undefined&&row.freeze_reason!=0){
			if(list==undefined){messager.alert('订单已冻结,不可编辑');}
			else{list.push({trade_no:row['trade_no'],result_info:'订单已冻结,不可操作'});}
			 return false;}
		if(row.checkouter_id!=undefined&&row.checkouter_id!=0){
			if(list==undefined){messager.alert('订单必须签出才可编辑');}
			else{list.push({trade_no:row['trade_no'],result_info:'订单必须签出才可编辑'});}
			return false;}
		if(list==undefined&&row.trade_status!=undefined&&row.trade_status!=30&&row.trade_status!=25){
			messager.alert('订单非待审核状态，不可编辑');
			return false;}
		return true;
	}
	//订单->未匹配货品
	tradeCheck.invalidGoods=function(){
		var url='<?php echo ($url_list["invalid_goods_url"]); ?>';
		tradeCheck.params.invalid='<?php echo ($id_list["invalid"]); ?>';
		var buttons=[
		         		{text:'确定',handler:function(){$('#'+tradeCheck.params.invalid).dialog('close');}},
		        		{text:'取消',handler:function(){tradeCheck.cancelDialog(tradeCheck.params.invalid);}}
		       		];
		$('#'+tradeCheck.params.invalid).dialog({ title:'未匹配货品', iconCls:'icon-no_match', width:764, height:550, closed:false, inline:true, modal:true, href:url, onBeforeClose:tradeCheck.getInvalidGoodsNum, buttons:buttons });
	}
	// 订单->一键合并且审核
	tradeCheck.mergeAndCheckTrade=function(id){
		var list=[];
		if(id==undefined||id==''){messager.alert('无效订单');return false;}	
		var rows=tradeCheck.selectRows;	
		messager.confirm('确定要一键合并且审核吗？', function(r) {
			if(!r){return false;}
			var url='<?php echo ($url_list["merge_and_check_trade_url"]); ?>';
			Post(url,{'id':id},function(res){
				if(res.status==1){
					messager.alert(res.message);
					return false;
				} else if (res.status == 0) {
					$("#response_dialog").dialog('close');
					tradeCheck.refresh();
				}
 				else{
					tradeCheck.dealDatagridReasonRows(res,undefined);
					tradeCheck.refresh();
				}
			},'JSON');
		});
	}

	tradeCheck.upload = function(){
		var form = $("#<?php echo ($id_list["fileForm"]); ?>");
        var url = "<?php echo U('TradeCheck/importSrcTids');?>";
        var dg = $("#<?php echo ($id_list["datagrid"]); ?>");
        var dialog = $("#<?php echo ($id_list["fileDialog"]); ?>");
        var separator=$("#separator").combobox('getValue');
        $.messager.progress({
            title: "请稍后",
            msg: "该操作可能需要几分钟，请稍等...",
            text: "",
            interval: 100
        });
        form.form("submit", {
            url: url,
            success: function (res) {   
             	$.messager.progress('close');
             	res = JSON.parse(res);
            	if (!res.status) {  
            		var data=res.info;                  
                    switch(separator){
	            		case '0':
	            			data=data.replace(/,/g,' \n ');
	            		break;
	            		case '1':
	            			data=data.replace(/,/g,' ');
	            		break;
	            		case '2':
	            			data=data.replace(/,/g,',');
	            		break;
	            		case '3':
	            			data=data.replace(/,/g,';');
	            		break;
	            	}      
	            	$("#passel_src_tids").val(data);
	                $("#search_src_tids").textbox('setValue',data);
	                dialog.dialog("close");
                } else if (res.status == 1) {
                    messager.alert(res.info);
                }
                form.form("load", {"file": ""});  
            }
        })
	}	
    tradeCheck.uploadDialog = function () {
        var dialog = $("#<?php echo ($id_list["fileDialog"]); ?>")
        dialog.dialog({
            title: "导入原始单号",
            width: "350px",
            height: "160px",
            modal: true,
            closed: false,
            inline: true,
            iconCls: 'icon-save',
        });
    }
    tradeCheck.downloadTemplet = function(){
    	var url = "<?php echo U('TradeCheck/downloadTemplet');?>";
        if (!!window.ActiveXObject || "ActiveXObject" in window){
            messager.confirm('IE浏览器下文件名会中文乱码，确定下载模板吗？',function(r){
                if(!r){return false;}
                window.open(url);
            })
        }else{
            messager.confirm('确定下载模板吗？',function(r){
                if(!r){return false;}
                window.open(url);
            })
        }
    }
	//订单->清除驳回
	tradeCheck.clearRevertTrade=function(id){
		var ids=[];
		var list=[];
		if(id!=undefined&&id!=''){
			ids.push(id);
		}else{
			var rows=tradeCheck.selectRows;
			if(rows==undefined){messager.alert('请选择清除驳回的订单');return false;}
			for(var i in rows){
				if(rows[i]['trade_status']!=undefined&&rows[i]['trade_status']!=30&&rows[i]['trade_status']!=25){
					list.push({trade_no:rows[i]['trade_no'],result_info:'订单状态不正确'});continue;
				}
				if(rows[i]['revert_reason']!=undefined&&rows[i]['revert_reason']==0){
					list.push({trade_no:rows[i]['trade_no'],result_info:'不是驳回订单'});continue;
				}
				if(!tradeCheck.checkEdit(rows[i],list)){continue;}
				ids.push(rows[i]['id']);
			}
		}
		messager.confirm('确定要清除驳回吗？', function(r) {
			if(!r){return false;}
			if(ids.length>0){
				var clear_revert_ids=JSON.stringify(ids);
				var url='<?php echo ($url_list["clear_revert_url"]); ?>';
				Post(url,{'ids':clear_revert_ids},function(res){ 
					tradeCheck.dealDatagridReasonRows(res,list); 
					if(id!=undefined&&id!=''){
						var return_rows= $("#response_dialog_datagrid").datagrid('getRows');
						for (var i=0;i<return_rows.length;i++){
							if(return_rows[i]['trade_id']==res.data.rows[0].id){
								check_rows=[];
								return_rows[i]['solve_way']='<a href="javascript:void(0)" onClick="checkTrade(0,check_rows,'+return_rows[i]['trade_id']+')">再次审核</a>'
								index=$("#response_dialog_datagrid").datagrid('getRowIndex',return_rows[i]); 
								$("#response_dialog_datagrid").datagrid('refreshRow',index); 
							}
						}
					}
				},'JSON');
			}else{
				var res={};res.status=2;res.info={};res.info.rows=list;res.info.total=list.length;
				tradeCheck.dealDatagridReasonRows(res,undefined); 
			}
		});
	}
	//订单->清除异常
	tradeCheck.clearBadTrade=function(id){
		var ids=[];
		var list=[];
		if(id!=undefined&&id!=''){
			ids.push(id);
		}else{
			var rows=tradeCheck.selectRows;
			if(rows==undefined){messager.alert('请选择清除异常的订单');return false;}

			for(var i in rows){
				if(rows[i]['trade_status']==!undefined&&rows[i]['trade_status']!=30&&rows[i]['trade_status']!=25){
					list.push({trade_no:rows[i]['trade_no'],result_info:'订单状态不正确'});continue;
				}
				if((rows[i]['bad_reason']!=undefined&&rows[i]['bad_reason']==0)&&(rows[i]['refund_status']!=1&&rows[i]['refund_status']!=2)){
					list.push({trade_no:rows[i]['trade_no'],result_info:'不是异常订单'});continue;
				}
				if(!tradeCheck.checkEdit(rows[i],list)){continue;}
				ids.push(rows[i]['id']);
			}
		}
		messager.confirm('确定要清除异常吗？',function(r){
			if(!r){return false;}
			if(ids.length>0){
				var clear_bad_ids=JSON.stringify(ids);
				var url='<?php echo ($url_list["clear_bad_url"]); ?>';
				Post(url,{'ids':clear_bad_ids},function(res){
					tradeCheck.dealDatagridReasonRows(res,list);
					if(id!=undefined&&id!=''){
						var return_rows= $("#response_dialog_datagrid").datagrid('getRows');
						for (var i=0;i<return_rows.length;i++){
							if(return_rows[i]['trade_id']==res.data.rows[0].id){
								check_rows=[];
								return_rows[i]['solve_way']='<a href="javascript:void(0)" onClick="checkTrade(0,check_rows,'+return_rows[i]['trade_id']+')">再次审核</a>'
								index=$("#response_dialog_datagrid").datagrid('getRowIndex',return_rows[i]); 
								$("#response_dialog_datagrid").datagrid('refreshRow',index); 
							}
						}
					}
				},'JSON');
			}else{
				var res={};res.status=2;res.info={};res.info.rows=list;res.info.total=list.length;
				tradeCheck.dealDatagridReasonRows(res,undefined); 
			}
		});
	}
	//订单->冻结
	tradeCheck.freezeTrade=function(){
		var rows=tradeCheck.selectRows;
		if(rows==undefined){messager.alert('请选择冻结的订单');return false;}
		var ids=[];
		var list=[];
		for(var i in rows){
			if(rows[i]['id']==undefined||rows[i]['id']<1){
				list.push({trade_no:rows[i]['trade_no'],result_info:'无效订单,请选择有效订单'});continue;
			}
			if(rows[i]['trade_status']!=undefined&&(rows[i]['trade_status']>=55||rows[i]['trade_status']<=5)){
				list.push({trade_no:rows[i]['trade_no'],result_info:'订单状态不正确'});continue;
			}
			if(rows[i]['freeze_reason']!=undefined&&rows[i]['freeze_reason']!=0){
				list.push({trade_no:rows[i]['trade_no'],result_info:'订单已被冻结'});continue;
			}
			ids.push(rows[i]['id']);
		}
		if(ids.length>0){
			var freeze_ids=JSON.stringify(ids);
			tradeCheck.setReason('freeze_reason',freeze_ids,list);
		}else{
			var res={};res.status=2;res.info={};res.info.rows=list;res.info.total=list.length;
			tradeCheck.dealDatagridReasonRows(res,undefined); 
		}
	}
	//订单->解冻
	tradeCheck.unfreezeTrade=function(id){
		var ids=[];
		var list=[];
		if(id==undefined||id==''){
			var rows=tradeCheck.selectRows;
			if(rows==undefined){messager.alert('请选择解冻的订单');return false;}
			for(var i in rows){
				if(rows[i]['id']==undefined||rows[i]['id']<1){
					list.push({trade_no:rows[i]['trade_no'],result_info:'无效订单,请选择有效订单'});continue;
				}
				if(rows[i]['trade_status']!=undefined&&(rows[i]['trade_status']>=55||rows[i]['trade_status']<=5)){
					list.push({trade_no:rows[i]['trade_no'],result_info:'订单状态不正确'});continue;
				}
				if(rows[i]['freeze_reason']!=undefined&&rows[i]['freeze_reason']==0){
					list.push({trade_no:rows[i]['trade_no'],result_info:'订单非冻结状态,不需要解冻'});continue;
				}
				ids.push(rows[i]['id']);
			}
		}else{
			ids.push(id);
		}
		messager.confirm('确定要解冻订单吗？', function(r) {
			if(!r){return false;}
			if(ids.length>0){
				var unfreeze_ids=JSON.stringify(ids);
				var url='<?php echo ($url_list["unfreeze_url"]); ?>';
				Post(url,{'ids':unfreeze_ids},function(res){ 
					tradeCheck.dealDatagridReasonRows(res,list); 
					if(id!=undefined&&id!=''){
						var return_rows= $("#response_dialog_datagrid").datagrid('getRows');
						for (var i=0;i<return_rows.length;i++){
							if(return_rows[i]['trade_id']==res.data.rows[0].id){
								check_rows=[];
								return_rows[i]['solve_way']='<a href="javascript:void(0)" onClick="checkTrade(0,check_rows,'+return_rows[i]['trade_id']+')">再次审核</a>'
								index=$("#response_dialog_datagrid").datagrid('getRowIndex',return_rows[i]); 
								$("#response_dialog_datagrid").datagrid('refreshRow',index); 
							}
						}
					}
				},'JSON');
			}else{
				var res={};res.status=2;res.info={};res.info.rows=list;res.info.total=list.length;
				tradeCheck.dealDatagridReasonRows(res,undefined);
			}
		});
	}
	//订单->取消
	tradeCheck.cancelTrade=function(){
		var rows=tradeCheck.selectRows;
		if(rows==undefined){messager.alert('请选择取消的订单');return false;}
		var ids=[];
		var list=[];
		for(var i in rows){
			if(rows[i]['id']==undefined||rows[i]['id']<1){
				list.push({trade_no:rows[i]['trade_no'],result_info:'无效订单,请选择有效订单'});continue;
			}
			if(rows[i]['trade_status']!=undefined&&(rows[i]['trade_status']!=30&&rows[i]['trade_status']!=25)){
				list.push({trade_no:rows[i]['trade_no'],result_info:'订单状态不正确'});continue;
			}
			if(rows[i]['paid']!=undefined&&rows[i]['paid']!=0){
				list.push({trade_no:rows[i]['trade_no'],result_info:'订单已付款，如果要删除请按退款处理'});continue;
			}
			if(!tradeCheck.checkEdit(rows[i],list)){continue;}
			ids.push(rows[i]['id']);
		}
		messager.confirm('确定要取消订单吗？', function(r) {
			if(!r){return false;}
			if(ids.length>0){
				var cancel_ids=JSON.stringify(ids);
				tradeCheck.setReason('cancel_reason',cancel_ids,list);
			}else{
				var res={};res.status=2;res.info={};res.info.rows=list;res.info.total=list.length;
				tradeCheck.dealDatagridReasonRows(res,undefined);
			}
		});
	}
	tradeCheck.refundTrade=function(){
		var rows=tradeCheck.selectRows;
		if(rows==undefined){messager.alert('请选择退款的订单');return false;}
		var ids=[];
		var list=[];
		for(var i in rows){
			if(rows[i]['trade_status']!=undefined&&rows[i]['trade_status']!=30&&rows[i]['trade_status']!=25){
				list.push({trade_no:rows[i]['trade_no'],result_info:'订单状态不正确'});continue;
			}
			if(rows[i]['trade_status']!=undefined&&rows[i]['warehouse_id']<=0){
				list.push({trade_no:rows[i]['trade_no'],result_info:'订单无效仓库'});continue;
			}
			if(!tradeCheck.checkEdit(rows[i],list)){continue;}
			ids.push(rows[i]['id']);
		}
		messager.confirm('确定要全额退款吗？', function(r) {
			if(!r){return false;}
			if(ids.length>0){
				var refund_ids=JSON.stringify(ids);
				var url='<?php echo ($url_list["refund_url"]); ?>';
				load();
				Post(url,{'ids':refund_ids},function(res){
					disLoad();
					tradeCheck.dealDatagridReasonRows(res,list);
				},'JSON');
			}else{
				var res={};res.status=2;res.info={};res.info.rows=list;res.info.total=list.length;
				tradeCheck.dealDatagridReasonRows(res,undefined);
			}
		});
	}
	//查看号码
	tradeCheck.checkNumber=function(){
		var rows=tradeCheck.selectRows;
		if(rows==undefined){messager.info('请选择操作的行');return false;}
		var ids=[];
		var list=[];
		for(var i in rows){ 
			if(rows[i]['receiver_mobile']==''&&rows[i]['receiver_telno']==''){
				list.push({trade_no:rows[i]['trade_no'],result_info:'手机和固话均为空！'});
				continue;
			}
			ids.push(rows[i]['id']); 
		}
		if(ids.length>0){
			Post('<?php echo U('Trade/TradeCommon/checkNumber');?>',{ids:JSON.stringify(ids),key:'sales_trade'},function(res){
				tradeCheck.dealDatagridReasonRows(res,list);
			},'JSON');
		}else{
			var res={};res.status=2;res.info={};res.info.rows=list;res.info.total=list.length;
			tradeCheck.dealDatagridReasonRows(res,undefined);
		}
	}
	tradeCheck.dealDatagridReasonRows=function(result,list){
		if(result.status==1){ messager.alert(result.message); return;}
		if(list!=undefined&&list.length>0){
			var fail= (typeof result.info.rows=='object')?$.makeArray(result.info.rows):result.info.rows;
			result.info.rows=$.merge(list,fail);
			result.info.total+=list.length;
			result.status=2;
		}
		if(result.status==2&&result.check!=undefined){ $.fn.richDialog("response", result.info, 'checktrade');}
		else if(result.status==2){ $.fn.richDialog("response", result.info, 'tradecheck');}
		if((result.status==0||result.status==2)&&result.data!=undefined){
			if(result.bad_reason!=undefined&&result.status==0){
				messager.alert('清除成功');
			}
			var rows=tradeCheck.selectRows;
			var selectrows_length = 0;
			for(var j=0;j<tradeCheck.selectRows.length;j++){
				if(tradeCheck.selectRows[j] != undefined){selectrows_length += 1;}
			}
			var index;
			var trade_dg=$('#'+tradeCheck.params.datagrid.id);
			for(var i in rows){
				for(var x in result.data.rows){
					if(rows[i].id==result.data.rows[x].id){ 
						index=trade_dg.datagrid('getRowIndex',rows[i]); 
						if(result.freeze_reason!=undefined){rows[i].freeze_reason=result.freeze_reason;if(result.freeze_reason>0){if(rows[i].flag!=undefined&&rows[i].flag!=''){rows[i].flag+=result.flag}else{rows[i].flag=result.flag}}else if(result.freeze_reason==0){rows[i].flag=rows[i].flag.replace(result.flag,"");};trade_dg.datagrid('refreshRow',index);}
						else if(result.cancel_reason!=undefined){rows[i].cancel_reason=result.cancel_reason;rows[i].trade_status=result.trade_status;rows[i].version_id=parseInt(rows[i].version_id)+1;rows[i].flag_id=result.flag_id;tradeCheck.refresh();}
						else if(result.revert_reason!=undefined){rows[i].revert_reason=result.revert_reason;rows[i].version_id=parseInt(rows[i].version_id)+1;rows[i].flag=rows[i].flag.replace(result.flag,"");trade_dg.datagrid('refreshRow',index);}
						else if((result.check!=undefined&&result.check)||(result.del!=undefined&&result.del)){trade_dg.datagrid('deleteRow',index);delete tradeCheck.selectRows[i];selectrows_length -= 1;if(selectrows_length == 0){delete tradeCheck.selectRows;}break;}
						else if(result.refund!=undefined&&result.refund){trade_dg.datagrid('deleteRow',index);tradeCheck.selectRows=undefined;}
						else if(result.check_number){rows[i].receiver_mobile=result.data.rows[x].receiver_mobile;rows[i].receiver_telno=result.data.rows[x].receiver_telno;trade_dg.datagrid('refreshRow',index);}
						else if(result.bad_reason!=undefined){
							rows[i].bad_reason=result.bad_reason;
							if(rows[i].refund_status==1){rows[i].refund_status=0;}
							rows[i].version_id=parseInt(rows[i].version_id)+1;trade_dg.datagrid('refreshRow',index);}
						else if((result.change!=undefined&&result.change)){
							if(result.type==0){rows[i].logistics_id=result.new_name;}else if(result.type==1){rows[i].warehouse_name=result.new_name;}else if(result.type==2){rows[i].cs_remark=result.new_name;}
							rows[i].version_id=parseInt(rows[i].version_id)+1;trade_dg.datagrid('refreshRow',index);
						}
					}
				}
			}
		}
	}
	//删除手工建单
	tradeCheck.deleteTrade =function (){
		var rows=tradeCheck.selectRows;
		if(rows==undefined){messager.alert('请选择删除的订单');return false;}
		var ids=[];
		var list=[];
		var message='订单';
		for(var i in rows){
			if(rows[i]['trade_status']==!undefined&&rows[i]['trade_status']!=30&&rows[i]['trade_status']!=25){
				list.push({trade_no:rows[i]['trade_no'],result_info:'订单状态不正确'});continue;
			}
			if(rows[i]['platform_id']==!undefined&&rows[i]['platform_id']!=0){
				list.push({trade_no:rows[i]['trade_no'],result_info:'线上订单不可删除'});continue;
			}
			if(rows[i]['stockout_no']!=''){
				message+=rows[i]['trade_no']+' ';
			}
			if(!tradeCheck.checkEdit(rows[i],list)){continue;}
			ids.push(rows[i]['id']);
		}
		if(message.length>2){message+='包含出库单，是否删除？'}else{message='确定要删除订单吗？';}
		messager.confirm(message,function(r){
			if(!r){return false;}
			if(ids.length>0){
				var clear_bad_ids=JSON.stringify(ids);
				var url='<?php echo ($url_list["delete_url"]); ?>';
				Post(url,{'ids':clear_bad_ids},function(res){
					tradeCheck.dealDatagridReasonRows(res,list);
				},'JSON');
			}else{
				var res={};res.status=2;res.info={};res.info.rows=list;res.info.total=list.length;
				tradeCheck.dealDatagridReasonRows(res,undefined); 
			}
		});
	}
	//无需系统处理订单
	tradeCheck.removeTrade=function(){
		var rows=tradeCheck.selectRows;
		if(rows==undefined){messager.alert('请选择订单');return false;}
		var ids=[];
		var list=[];
		for(var i in rows){
			if(rows[i]['trade_status']==!undefined&&rows[i]['trade_status']!=30&&rows[i]['trade_status']!=25){
				list.push({trade_no:rows[i]['trade_no'],result_info:'订单状态不正确。'});continue;
			}
//			if(rows[i]['stockout_no']!=''){
//				list.push({trade_no:rows[i]['trade_no'],result_info:'该订单已包含出库单，不可执行此操作，请正常处理。'});continue;
//			}
			if(!tradeCheck.checkEdit(rows[i],list)){continue;}
			ids.push(rows[i]['id']);
		}
		if(ids.length>0){
			tradeCheck.remove_ids=JSON.stringify(ids);
			var url='<?php echo ($url_list["remove_url"]); ?>';
			url += url.indexOf('?') != -1 ? '&ids='+tradeCheck.remove_ids : '?ids='+tradeCheck.remove_ids;
			var buttons=[ {text:'确定',handler:function(){tradeCheck.submitTradeCheckDialog();}}, {text:'取消',handler:function(){tradeCheck.cancelDialog();}} ];
			tradeCheck.showDialog(0,'清除订单',url,400,800,buttons,null,false);
		}else{
			var res={};res.status=2;res.info={};res.info.rows=list;res.info.total=list.length;
			$.fn.richDialog("response", res.info, 'tradecheck');
			$('#'+tradeCheck.params.edit.id).dialog('close');
		}
		
	}
	//修改物流、仓库
	tradeCheck.changeTrade=function(new_id,type){
		var rows=tradeCheck.selectRows;
		if(rows==undefined){messager.alert('请选择订单');return false;}
		var ids=[];
		var list=[];
		var version=[];
		for(var i in rows){
			if(rows[i]['id']==undefined||rows[i]['id']<1){
				list.push({trade_no:rows[i]['trade_no'],result_info:'无效订单,请选择有效订单'});continue;
			}
			if(rows[i]['trade_status']!=undefined&&(rows[i]['trade_status']!=30&&rows[i]['trade_status']!=25)){
				list.push({trade_no:rows[i]['trade_no'],result_info:'订单状态不正确'});continue;
			}
			if(!tradeCheck.checkEdit(rows[i],list)){continue;}
			ids.push(rows[i]['id']);
			version[ids[i]]=rows[i]['version_id'];
		}
		var message=['物流','仓库'];
		messager.confirm('确定要修改'+message[type]+'吗？', function(r) {
			if(!r){return false;}
			if(ids.length>0){
				var data={};
				data.ids=ids;
				data.new_id=new_id;
				data.type=type;
				data.version=version;
				new_data=JSON.stringify(data);
				var url='<?php echo ($url_list["change_url"]); ?>';
				Post("<?php echo U('TradeCheck/changeTrades');?>",{data:new_data},function(res){ 
					tradeCheck.dealDatagridReasonRows(res,list); 
				},'JSON');
			}else{
				var res={};res.status=2;res.info={};res.info.rows=list;res.info.total=list.length;
				tradeCheck.dealDatagridReasonRows(res,undefined);
			}
		});
	}
	//批量修改客服备注
	tradeCheck.editBatchRemark=function(){
		if(tradeCheck.selectRows==undefined){messager.alert('请选择订单！');return false;}
		var url = '<?php echo ($url_list["batch_csremark_url"]); ?>';
		var buttons=[ {text:'确定',handler:function(){tradeCheck.submitTradeCheckDialog();}} ];
		tradeCheck.showDialog(0,'修改客服备注',url,150,350,buttons);
	}
	// 根据原始单号搜索订单
	tradeCheck.searchSrcTids=function(){
		var url = '<?php echo ($url_list["search_src_tids_url"]); ?>';
		var buttons=[ {text:'确定',handler:function(){tradeCheck.submitSearchSrcTidsDialog();}}, {text:'取消',handler:function(){tradeCheck.cancelDialog();}} ];
		tradeCheck.showDialog(0,'根据原始单号搜索订单',url,460,650,buttons);
	}
	//包含不包含货品选择
	tradeCheck.include_goods_select = function(obj,type){
		var that = this;
		var buttons=[ {text:'确定',handler:function(){
			var rows = $(tradeCheck.tmp_map_obj[type].datagrid).datagrid('getRows');
			var include_relation = tradeCheckIncludeGoods.getIncludeRelation();
			var rows_len = rows.length;
			if(rows_len>0){
				var include_val = '';
				var search_data = {};
				obj.textbox('setValue','货品种类:'+rows_len);
				var conditionFieldsMap = {'小于':0,'等于':1,'大于':2};
				for(var i=0; i<rows_len; ++i){
					var row_index = $(tradeCheck.tmp_map_obj[type].datagrid).datagrid('getRowIndex',rows[i]);
					$(tradeCheck.tmp_map_obj[type].datagrid).datagrid('endEdit',row_index);
					include_val += rows[i]['spec_id']+'-'+conditionFieldsMap[rows[i]['condition']]+'-'+rows[i]['num']+'-'+rows[i]['is_suite']+',';
				}
				include_val = include_val.substr(0, include_val.length - 1);
				search_data['include_val'] = include_val;
				search_data['include_relation'] = include_relation;
				$(tradeCheck.tmp_map_obj[type].include_search_data).val(JSON.stringify(search_data));
				tradeCheck.tmp_map_obj[type].tmp_include_relation = include_relation;
				tradeCheck.tmp_map_obj[type].tmp_include_search_data = {'total':rows_len,'rows':rows};
				$('#'+that.params[tradeCheck.tmp_map_obj[type].type].id).dialog('close');
			}else{
				obj.textbox('setValue','');
				$(tradeCheck.tmp_map_obj[type].include_search_data).val('');
				tradeCheck.tmp_map_obj[type].tmp_include_relation = '';
				tradeCheck.tmp_map_obj[type].tmp_include_search_data = '';
				$('#'+that.params[tradeCheck.tmp_map_obj[type].type].id).dialog('close');
			}
		}}, /*{text:'取消',handler:function(){$('#'+that.params.include_goods.id).dialog('close');}} */];
		tradeCheck.showDialog(that.params[tradeCheck.tmp_map_obj[type].type].id,that.params[tradeCheck.tmp_map_obj[type].type].title,that.params[tradeCheck.tmp_map_obj[type].type].url,that.params[tradeCheck.tmp_map_obj[type].type].height,that.params[tradeCheck.tmp_map_obj[type].type].width,buttons,null,that.params[tradeCheck.tmp_map_obj[type].type].ismax);
		if(tradeCheck.tmp_map_obj[type].tmp_include_search_data!=''){
			var interval = setInterval(function(){
				var rows = $(tradeCheck.tmp_map_obj[type].datagrid).length;
				if(rows > 0){
					tradeCheckIncludeGoods.setIncludeRelation(tradeCheck.tmp_map_obj[type].tmp_include_relation);
					$(tradeCheck.tmp_map_obj[type].datagrid).datagrid('loadData',tradeCheck.tmp_map_obj[type].tmp_include_search_data);
					clearInterval(interval);
				}
			}, 300);
		}
	}
	//设置tab表头
	tradeCheck.setTabField=function(){
		var tabIndex=tradeCheck.tabIndex;
		if(tabIndex==1||tabIndex==6||tabIndex==7){messager.alert("该选项卡不支持设置表头。");return false;}
		var tabs=JSON.parse('<?php echo ($arr_tabs); ?>');
		var url=tabs[tabIndex].url;
		var tab_name=(url.split("="))[1].split("&");
		setDatagridField('Trade/TradeCommon',tab_name[0],'tradeCheckdatagrid_'+tab_name[0]);
	}
	//新版搜索
	tradeCheck.searchData=function(){
		resetFast();
		tradeCheck.submitSearchForm(this);
	}
	tradeCheck.loadData=function(set_fast){
		if(set_fast != undefined){
			$('#<?php echo ($id_list["fast_div"]); ?>').find('a').each(function(){
				$(this).css({'color':'#174B73'});
			});
		}
		resetFast();
		$("#<?php echo ($id_list["form"]); ?>").form('reset');
		var dg = $('#'+tradeCheck.params.datagrid.id);
		dg.datagrid('options').pageNumber=1;
		$(dg.datagrid('getPager')).pagination("refresh",{pageNumber:1});
		var id=tradeCheck.params.search.form_id;
		if(tradeCheck.params.search.form_data!=undefined){$('#'+id).form('load',tradeCheck.params.search.form_data);$('#'+id+' :input[extend_type="complex-check"]').each(function(){$(this).triStateCheckbox('init');});tradeCheck.submitSearchForm(this);}
		tradeCheck.tmp_map_obj.include.tmp_include_search_data = '';
		tradeCheck.tmp_map_obj.not_include.tmp_include_search_data = '';
//		tradeCheck.loadFormData();

	}
	tradeCheck.fastSearch=function(that,key){
		setFastClickColor(that);
		resetFast();
		var dg = $('#'+tradeCheck.params.datagrid.id);
		var queryParams ={};
		queryParams[key]=1;
		dg.datagrid('options').queryParams = queryParams;
		dg.datagrid('reload');
	}
	tradeCheck.newSelectFlag=function(id){
		var flag=[];
		flag.id=id;
		tradeCheck.selectFlag(flag);
	}
	tradeCheck.sms = function()
	{
		var that = this;
		var row = tradeCheck.selectRows;
		if(!row){
			messager.alert('请选择要发送短信的订单！', "info");
			return false;
		}
		var buttons = [{
			text: '发送', handler: function () {
				that.submitSMSDialog();
			}
		}, {
			text: '取消', handler: function () {
				that.cancelDialog(that.params.sms.id);
			}
		}];
		this.showDialog(
				that.params.sms.id,
				that.params.sms.title,
				that.params.sms.url,
				that.params.sms.height,
				that.params.sms.width,
				buttons
		);
	}
	function setFastClickColor(that){
		var a_elems = $(that).parent('fieldset').parent('div').find('a');
		a_elems.each(function(i){
			$(this).css({'color':'#174B73'});
		});
		$(that).css({'color':'red'});
	}
	}, 0);
	//清空快捷查询的数据
	function resetFast(){
		var dg = $('#'+tradeCheck.params.datagrid.id);
		var queryParams = dg.datagrid('options').queryParams;
		if(queryParams['search[freeze_reason]']){queryParams['search[freeze_reason]']='';}
		if(queryParams['search[revert]']){queryParams['search[revert]']='';}
		if(queryParams['search[bad_reason]']){queryParams['search[bad_reason]']='';}
		if(queryParams['search[merge]']){queryParams['search[merge]']='';}
		if(queryParams['search[split]']){queryParams['search[split]']='';}
		if(queryParams['search[auto_merge]']){queryParams['search[auto_merge]']='';}
		if(queryParams['search[refund]']){queryParams['search[refund]']='';}
		if(queryParams['search[return]']){queryParams['search[return]']='';}
		if(queryParams['search[cs]']){queryParams['search[cs]']='';}
		if(queryParams['search[no_cs]']){queryParams['search[no_cs]']='';}
		if(queryParams['search[client]']){queryParams['search[client]']='';}
		if(queryParams['search[no_client]']){queryParams['search[no_client]']='';}
		if(queryParams['search[one_day]']){queryParams['search[one_day]']='';}
		if(queryParams['search[tow_day]']){queryParams['search[tow_day]']='';}
		if(queryParams['search[one_week]']){queryParams['search[one_week]']='';}
		if(queryParams['search[one_month]']){queryParams['search[one_month]']='';}
		
	}
});
//订单->快速审核
function quickCheckTrade(checkType){
	$('#check_menu').menu('hide');
	checkType=(checkType==undefined?0:checkType);
	if(checkType==0&&tradeCheck.selectRows==undefined){messager.alert('请选择审核的订单');return false;}
	var url='<?php echo ($url_list["quick_check_url"]); ?>';
	tradeCheck.quickCheck={};
	tradeCheck.quickCheck.type=checkType;
	tradeCheck.quickCheck.url=url;
	//url += url.indexOf('?') != -1 ? '&id='+res.id : '?id='+row.id;
	var buttons=[ {text:'确定',handler:function(){tradeCheck.submitTradeCheckDialog();}}, {text:'取消',handler:function(){tradeCheck.cancelDialog(0);}} ];
	tradeCheck.showDialog(0,'快速审核',url,300,400,buttons,null,false);
}
//订单->订单的审核
var time = null;
function checkByType(checkType,checkRows,id){
	var force_check_pwd_is_open=tradeCheck.params.force_check_pwd_is_open;
	if(checkType==1&&force_check_pwd_is_open==1){
		var url='<?php echo ($url_list["force_check_pwd_url"]); ?>';
		url += url.indexOf('?') != -1 ? '&pwd='+'' : '?pwd='+'';
		var buttons=[ {text:'确定',handler:function(){tradeCheck.submitTradeCheckDialog(id);}}, {text:'取消',handler:function(){tradeCheck.cancelDialog();}} ];
		tradeCheck.showDialog(0,'强制审核',url,150,300,buttons,null,false);
	}else{
		checkTrade(checkType,checkRows,id);
	}
}
function checkTrade(checkType,checkRows,id){
	var ids=[];
	var list=[];
	if(id==undefined||id==''){
		clearTimeout(time);
		time = setTimeout(function(){
			$('#check_menu').menu('hide');
			checkType=(checkType==undefined?0:checkType);
			var rows=((checkRows==undefined||checkRows==''||checkRows==0)?tradeCheck.selectRows:checkRows);
			if(rows==undefined&&checkType!=2){messager.alert('请选择审核的订单');return false;}
			if(rows==undefined&&checkType==2){messager.alert('请选择发货的订单');return false;}
			load();
			var bad_reason=JSON.parse('<?php echo ($bad_reason); ?>');
			for(var i in rows){
				var force_check='否';
				if(rows[i]['id']<1){
					list.push({trade_id:rows[i]['id'],trade_no:rows[i]['trade_no'],result_info:'无效订单,请选择有效订单'});continue;
				}
				if(rows[i]['trade_status']!=undefined&&rows[i]['trade_status']!=30){
					list.push({trade_id:rows[i]['id'],trade_no:rows[i]['trade_no'],result_info:'订单状态不正确',solve_way:'检查订单是否已被其他人审核。',force_check:force_check});continue;
				}
				//检查物流公司是否支持货到付款;无效店铺;无效的物流方式;->前端未验证
				if(rows[i]['shop_id']!=undefined&&rows[i]['shop_id']<1){
					list.push({trade_id:rows[i]['id'],trade_no:rows[i]['trade_no'],result_info:'无效店铺',solve_way:'检查店铺信息是否正确。',force_check:force_check});continue;
				}
				if(rows[i]['warehouse_id']!=undefined&&rows[i]['warehouse_id']<1){
					list.push({trade_id:rows[i]['id'],trade_no:rows[i]['trade_no'],result_info:'无效仓库',solve_way:'检查仓库信息是否正确。',force_check:force_check});continue;
				}
				if(rows[i]['warehouse_type']!=undefined&&rows[i]['warehouse_type']<0){
					list.push({trade_id:rows[i]['id'],trade_no:rows[i]['trade_no'],result_info:'仓库类型不确定',solve_way:'检查仓库信息是否正确。',force_check:force_check});continue;
				}
				if(rows[i]['freeze_reason']!=undefined&&rows[i]['freeze_reason']!=0){
					list.push({trade_id:rows[i]['id'],trade_no:rows[i]['trade_no'],result_info:'订单已被冻结',solve_way:'<a href="javascript:void(0)" onClick="tradeCheck.unfreezeTrade('+rows[i]['id']+')">解冻</a>',force_check:force_check});continue;
				}
				if(rows[i]['refund_status']!=undefined&&rows[i]['refund_status']==1){
					if (rows[i]['goods_count']==1) {
						list.push({trade_id:rows[i]['id'],trade_no:rows[i]['trade_no'],result_info:'申请退款',solve_way:'<a href="javascript:void(0)" onClick="tradeCheck.clearBadTrade('+rows[i]['id']+')">清除退款异常</a>或线上同意退款',force_check:force_check});continue;
					}else{
						list.push({trade_id:rows[i]['id'],trade_no:rows[i]['trade_no'],result_info:'申请退款',solve_way:'<a href="javascript:void(0)" onClick="tradeCheck.clearBadTrade('+rows[i]['id']+')">清除退款异常</a>或<a href="javascript:void(0)" onClick="splitRefundTrade('+rows[i]['id']+')">拆分退款单</a>',force_check:force_check});continue;
					}
					
				}
				if(rows[i]['receiver_name']!=undefined&&rows[i]['receiver_name']==''){
					list.push({trade_id:rows[i]['id'],trade_no:rows[i]['trade_no'],result_info:'收件人为空',solve_way:'编辑收件人',force_check:force_check});continue;
				}
				if(rows[i]['receiver_address']!=undefined&&($.trim(rows[i]['receiver_address'])=='')){
					list.push({trade_id:rows[i]['id'],trade_no:rows[i]['trade_no'],result_info:'收件地址为空',solve_way:'编辑收件地址',force_check:force_check});continue;
				}
				if(rows[i]['revert_reason']!=undefined&&rows[i]['revert_reason']!=0){
					list.push({trade_id:rows[i]['id'],trade_no:rows[i]['trade_no'],result_info:'驳回订单',solve_way:'<a href="javascript:void(0)" onClick="tradeCheck.clearRevertTrade('+rows[i]['id']+')">清除驳回</a>',force_check:force_check});continue;
				}
				//以下判断强制审核跳过
				var force_check='<a href="javascript:void(0)" onClick="checkByType(1,0,'+rows[i]['id']+')">是（点击强审）</a>';
				if(checkType!=1&&rows[i]['logistics_id']!=undefined&&rows[i]['logistics_id']<=0){
					list.push({trade_id:rows[i]['id'],trade_no:rows[i]['trade_no'],result_info:'无效的物流',solve_way:'编辑物流',force_check:force_check});continue;
				}
				if(checkType!=1&&rows[i]['receiver_mobile']!=undefined&&rows[i]['receiver_telno']!=undefined&&rows[i]['receiver_mobile']==''&&rows[i]['receiver_telno']==''){
					list.push({trade_id:rows[i]['id'],trade_no:rows[i]['trade_no'],result_info:'收件人电话不能为空',solve_way:'编辑收件人电话',force_check:force_check});continue;
				}
				if(checkType!=1&&rows[i]['trade_type']!=undefined&&rows[i]['customer_id']!=undefined&&rows[i]['trade_type']==1&&rows[i]['customer_id']<1){
					list.push({trade_id:rows[i]['id'],trade_no:rows[i]['trade_no'],result_info:'无效的客户',solve_way:'检查客户信息是否正确',force_check:force_check});continue;
				}
				if(checkType!=1&&rows[i]['bad_reason']!=undefined&&rows[i]['bad_reason']!=0){
					list.push({trade_id:rows[i]['id'],trade_no:rows[i]['trade_no'],result_info:'异常订单，原因：'+bad_reason[rows[i]['bad_reason']]+'，请先处理。',solve_way:'<a href="javascript:void(0)" onClick="tradeCheck.clearBadTrade('+rows[i]['id']+')">清除异常</a>',force_check:force_check});continue;
				}
				ids.push(rows[i]['id']);
			}
			if(ids.length>0){
				var check_trade_ids=JSON.stringify(ids);
				if(checkType==2){
					disLoad();
					var url='<?php echo ($url_list["direct_consign_url"]); ?>';
					url += url.indexOf('?') != -1 ? '&ids='+check_trade_ids : '?ids='+check_trade_ids;
					var buttons=[ {text:'确定',handler:function(){tradeCheck.submitTradeCheckDialog();}}, {text:'取消',handler:function(){tradeCheck.cancelDialog(0);}} ];
					tradeCheck.showDialog(0,'订单直接发货',url,400,700,buttons,null,false);
					if(list.length>0){
						var result={};
						result.rows=list;
						result.total=list.length;
						$.fn.richDialog("response", result, 'tradecheck');
					}
				}else{
					var url='<?php echo ($url_list["check_url"]); ?>';
					Post(url,{'ids':check_trade_ids,'type':checkType},function(res){ 
						disLoad();
						tradeCheck.dealDatagridReasonRows(res,list); 
					},'JSON');
				}
			}else{
				disLoad();
				var res={};res.status=2;res.info={};res.info.rows=list;res.info.total=list.length;res.check=0;
				tradeCheck.dealDatagridReasonRows(res,undefined);
			}
		},300);
	}else{
		ids.push(id);
		var check_trade_ids=JSON.stringify(ids);
		var url='<?php echo ($url_list["check_url"]); ?>';
		Post(url,{'ids':check_trade_ids,'type':checkType},function(res){ 
			tradeCheck.dealDatagridReasonRows(res,list); 
			disLoad() ;
			if(res.check==true){
				var return_rows= $("#response_dialog_datagrid").datagrid('getRows');
				for (var i=0;i<return_rows.length;i++){
					if(return_rows[i]['trade_id']==res.data.rows[0].id){
						index=$("#response_dialog_datagrid").datagrid('getRowIndex',return_rows[i]); 
						$("#response_dialog_datagrid").datagrid('deleteRow',index); 
						i--;
					}
				}
				return_rows= $("#response_dialog_datagrid").datagrid('getRows');
				if(return_rows.length==undefined||return_rows.length==0){
					 $("#response_dialog").dialog('close');
				}
			}
		},'JSON');
	}
}
//打开新界面时先关闭response_dialog弹窗
function tradeCheckOpenMenuBefore(title,url){
	$("#response_dialog").dialog('close');
	open_menu(title,url);
}
// 赠品未赠原因
function giftNotSentReason($id){
	var rows=tradeCheck.selectRows;
	if(rows==undefined||rows.length>1){messager.alert('请选择单行订单！');return false;}
	if(rows[0].trade_from==2){messager.alert('<label style="align:center;">手工建单的订单不执行赠品策略！</label><br><label style="align:center;color:blue;">#若要添加赠品,请新建添加或编辑添加#</label>');return false;}
	var url = '<?php echo ($url_list["gift_not_send_reason"]); ?>';
	url += url.indexOf('?') != -1 ? '&id='+rows[0].id : '?id='+rows[0].id;
	var buttons=[ {text:'取消',handler:function(){tradeCheck.cancelDialog();}} ];
	tradeCheck.showDialog(0,'赠品未赠原因',url,500,600,buttons);
}
//订单->订单的合并
function mergeTrade(){
	var rows=tradeCheck.selectRows;
	if(rows==undefined||rows.length<2) {messager.alert('合并至少选择两个订单!'); return false;}
	var ids={};
	var version={};
	var trade_status_tmp,warehouse_id_tmp,delivery_term_tmp;
	if(rows[0]['trade_status']!=undefined){trade_status_tmp=rows[0]['trade_status'];}
	if(rows[0]['warehouse_id']!=undefined){warehouse_id_tmp=rows[0]['warehouse_id'];}
	if(rows[0]['delivery_term']!=undefined){delivery_term_tmp=rows[0]['delivery_term'];}
	if(rows[0]['platform_id']!=undefined){platform_id_tmp=rows[0]['platform_id'];}
	for(var i=0;i<rows.length;++i){
		if(rows[i]['is_sealed']!=undefined&&rows[i]['is_sealed']!=0){messager.alert('订单：'+rows[i]['trade_no']+'不可合并的订单');return;}
		if(rows[i]['trade_status']!=undefined&&rows[i]['trade_status']!=trade_status_tmp){messager.alert('订单：'+rows[i]['trade_no']+'状态不正确，检查是否已被其他人审核。');return;}
		if(rows[i]['revert_reason']!=undefined&&rows[i]['revert_reason']!=0){messager.alert('订单：'+rows[i]['trade_no']+'是被驳回订单,请先处理并清除驳回。');return;}
		if(rows[i]['bad_reason']!=undefined&&rows[i]['bad_reason']!=0){messager.alert('订单：'+rows[i]['trade_no']+'是异常订单,请先处理并清除异常');return;}
		if(rows[i]['warehouse_id']!=undefined&&rows[i]['warehouse_id']!=warehouse_id_tmp){messager.alert('订单：'+rows[i]['trade_no']+'仓库不同,无法合并');return;}
		if(rows[i]['delivery_term']!=undefined&&rows[i]['delivery_term']!=delivery_term_tmp){messager.alert('订单：'+rows[i]['trade_no']+'发货方式不同,无法合并');return;}
		if(rows[i]['platform_id']!=undefined&&rows[i]['platform_id']!=platform_id_tmp){messager.alert('订单：'+rows[i]['trade_no']+'来源平台不同,无法合并');return;}
		if(rows[i]['freeze_reason']!=undefined&&rows[i]['freeze_reason']!=0){messager.alert('订单：'+rows[i]['trade_no']+'已冻结，请先解冻订单');return;}
		//if(rows[i]['checkouter_id']!=undefined&&rows[i]['checkouter_id']!=0){messager.alert('订单：'+rows[i]['trade_no']+'必须签出才可编辑');return;}
		ids[i]=rows[i]['id'];
		version[ids[i]]=rows[i]['version_id'];
	}
	var data={};
	data.id=ids;
	data.version=version;
	tradeCheck.merge_trade_ids=JSON.stringify(data);
	var url='<?php echo ($url_list["merge_url"]); ?>';
	url += url.indexOf('?') != -1 ? '&ids='+tradeCheck.merge_trade_ids : '?ids='+tradeCheck.merge_trade_ids;
	var buttons=[ {text:'确定',handler:function(){tradeCheck.submitTradeCheckDialog();}}, {text:'取消',handler:function(){tradeCheck.cancelDialog();}} ];
	tradeCheck.showDialog(0,'订单合并',url,400,400,buttons,null,false);
	
}
//订单->订单的拆分
function splitTrade(){
	$('#check_menu').menu('hide');
	if(tradeCheck.selectRows==undefined) {messager.alert('请选择拆分的订单!'); return false;}
	var row=tradeCheck.selectRows[0];
	if(row.goods_count!=undefined&&row.goods_count<2){messager.alert('订单只有一个货品，不可拆分'); return false;}
	if(row.trade_status!=undefined&&row.trade_status!=30&&row.trade_status!=25){messager.alert('订单状态不正确，检查是否已被其他人审核。'); return false;}
	if(row.freeze_reason!=undefined&&row.freeze_reason!=0){messager.alert('订单已冻结，请先解冻订单。'); return false;}
	if(row.bad_reason!=undefined&&row.bad_reason!=0){messager.alert('订单有异常标记，请先处理'); return false;}
	if(row.is_sealed!=undefined&&row.is_sealed!=0){messager.alert('订单不可拆分'); return false;}
	if(row.delivery_term!=undefined&&row.delivery_term==2){messager.alert('货到付款订单不可拆分'); return false;}
	//订单被其他用户签出
	//if(row.checkouter_id!=undefined&&row.checkouter_id==0){messager.alert('必须签出才可编辑'); return false;}
	var url='<?php echo ($url_list["split_url"]); ?>';
	url += url.indexOf('?') != -1 ? '&id='+row.id : '?id='+row.id;
	var buttons=[ {text:'确定',handler:function(){tradeCheck.submitTradeCheckDialog();}}, {text:'取消',handler:function(){tradeCheck.cancelDialog(0);}} ];
	tradeCheck.showDialog(0,'订单拆分',url,560,764,buttons);
}
// 按组合装拆分
function suiteSplit(){
	if(tradeCheck.selectRows==undefined) {messager.alert('请选择按组合装拆分的订单!'); return false;}
	var row=tradeCheck.selectRows[0];
	if(row.goods_count!=undefined&&row.goods_count<2){messager.alert('订单只有一个货品，不可拆分'); return false;}
	if(row.trade_status!=undefined&&row.trade_status!=30&&row.trade_status!=25){messager.alert('订单状态不正确，检查是否已被其他人审核。'); return false;}
	if(row.freeze_reason!=undefined&&row.freeze_reason!=0){messager.alert('订单已冻结，请先解冻订单。'); return false;}
	if(row.bad_reason!=undefined&&row.bad_reason!=0){messager.alert('订单有异常标记，请先处理'); return false;}
	if(row.is_sealed!=undefined&&row.is_sealed!=0){messager.alert('订单不可拆分'); return false;}
	if(row.delivery_term!=undefined&&row.delivery_term==2){messager.alert('货到付款订单不可拆分'); return false;}
	var url='<?php echo ($url_list["suite_split_url"]); ?>';
	url += url.indexOf('?') != -1 ? '&id='+row.id : '?id='+row.id;
	var buttons=[ {text:'确定',handler:function(){tradeCheck.submitSuiteSplitDialog();}}, {text:'取消',handler:function(){tradeCheck.cancelDialog(0);}} ];
	tradeCheck.showDialog(0,'按组合装拆分',url,400,680,buttons);
}
//订单->批量拆分
function passelSplit(){
	if(tradeCheck.selectRows==undefined) {messager.alert('请选择拆分的订单!'); return false;}
	var rows=tradeCheck.selectRows;
	var ids={};
	for (var i=0;i<rows.length;++i){
		if(rows[i].trade_status!=undefined&&rows[i].trade_status!=30&&rows[i].trade_status!=25){messager.alert('订单'+rows[i].trade_no+'状态不正确，检查是否已被其他人审核。'); return false;}
		if(rows[i].freeze_reason!=undefined&&rows[i].freeze_reason!=0){messager.alert('订单'+rows[i].trade_no+'已冻结，请先解冻订单'); return false;}
		if(rows[i].bad_reason!=undefined&&rows[i].bad_reason!=0){messager.alert('订单'+rows[i].trade_no+'有异常标记，请先处理'); return false;}
		if(rows[i].is_sealed!=undefined&&rows[i].is_sealed!=0){messager.alert('订单'+rows[i].trade_no+'不可拆分'); return false;}
		if(rows[i].delivery_term!=undefined&&rows[i].delivery_term==2){messager.alert(rows[i].trade_no+'为货到付款订单不可拆分'); return false;}
		ids[i]=rows[i]['id'];
	}
	tradeCheck.passel_split_ids=JSON.stringify(ids);
	var url='<?php echo ($url_list["passel_split_url"]); ?>';
	url += url.indexOf('?') != -1 ? '&ids='+tradeCheck.passel_split_ids : '?ids='+tradeCheck.passel_split_ids;
	var buttons=[ {text:'确定',handler:function(){tradeCheck.submitTradeCheckDialog();}}, {text:'取消',handler:function(){tradeCheck.cancelDialog(0);}} ];
	tradeCheck.showDialog(0,'订单批量拆分',url,230,360,buttons);
}
//深度拆分====按单品
function deepSplit(){
	if(tradeCheck.selectRows==undefined) {messager.alert('请选择深度拆分的订单!'); return false;}
	var rows=tradeCheck.selectRows;
	var ids={};
	for (var i=0;i<rows.length;++i){
		if(rows[i].goods_count!=undefined&&rows[i].goods_count<2){messager.alert('订单'+rows[i].trade_no+'只有一个货品，不可拆分'); return false;}
		if(rows[i].trade_status!=undefined&&rows[i].trade_status!=30&&rows[i].trade_status!=25){messager.alert('订单'+rows[i].trade_no+'状态不正确，检查是否已被其他人审核。'); return false;}
		if(rows[i].freeze_reason!=undefined&&rows[i].freeze_reason!=0){messager.alert('订单'+rows[i].trade_no+'已冻结，请先解冻订单'); return false;}
		if(rows[i].bad_reason!=undefined&&rows[i].bad_reason!=0){messager.alert('订单'+rows[i].trade_no+'有异常标记，请先处理'); return false;}
		if(rows[i].is_sealed!=undefined&&rows[i].is_sealed!=0){messager.alert('订单'+rows[i].trade_no+'不可拆分'); return false;}
		if(rows[i].delivery_term!=undefined&&rows[i].delivery_term==2){messager.alert(rows[i].trade_no+'为货到付款订单不可拆分'); return false;}
		ids[i]=rows[i]['id'];
	}
	tradeCheck.deep_split_ids=JSON.stringify(ids);
	var url='<?php echo ($url_list["deep_split_url"]); ?>';
	messager.confirm('确定要将所选订单拆成一单一货吗？',function(r){
		if(!r){return false;}
		Post(url,{'ids':tradeCheck.deep_split_ids},function(res){
			if(res.status==0){
				messager.alert('拆分成功');
				tradeCheck.refresh();
			}else{
				tradeCheck.dealDatagridReasonRows(res,undefined);
				tradeCheck.refresh();
			}
		},'JSON');
	});
}
//深度拆分====按组合装
function deepSplitSuite(){
	if(tradeCheck.selectRows==undefined) {messager.alert('请选择按组合装深度拆分的订单!'); return false;}
	var rows=tradeCheck.selectRows;
	var ids={};
	for (var i=0;i<rows.length;++i){
		if(rows[i].goods_count!=undefined&&rows[i].goods_count<2){messager.alert('订单'+rows[i].trade_no+'只有一个货品，不可拆分'); return false;}
		if(rows[i].trade_status!=undefined&&rows[i].trade_status!=30&&rows[i].trade_status!=25){messager.alert('订单'+rows[i].trade_no+'状态不正确，检查是否已被其他人审核。'); return false;}
		if(rows[i].freeze_reason!=undefined&&rows[i].freeze_reason!=0){messager.alert('订单'+rows[i].trade_no+'已冻结，请先解冻订单'); return false;}
		if(rows[i].bad_reason!=undefined&&rows[i].bad_reason!=0){messager.alert('订单'+rows[i].trade_no+'有异常标记，请先处理'); return false;}
		if(rows[i].is_sealed!=undefined&&rows[i].is_sealed!=0){messager.alert('订单'+rows[i].trade_no+'不可拆分'); return false;}
		if(rows[i].delivery_term!=undefined&&rows[i].delivery_term==2){messager.alert(rows[i].trade_no+'为货到付款订单不可拆分'); return false;}
		ids[i]=rows[i]['id'];
	}
	tradeCheck.deep_split_by_suite_ids=JSON.stringify(ids);
	var url='<?php echo ($url_list["deep_split_by_suite_url"]); ?>';
	messager.confirm('确定要将所选订单拆成一单一货（按组合装）吗？',function(r){
		if(!r){return false;}
		Post(url,{'ids':tradeCheck.deep_split_by_suite_ids},function(res){
			if(res.status==0){
				messager.alert('拆分成功');
				tradeCheck.refresh();
			}else{
				tradeCheck.dealDatagridReasonRows(res,undefined);
				tradeCheck.refresh();
			}
		},'JSON');
	});
}

// 一键拆分合并单
function mergeSplit(){
	if(tradeCheck.selectRows==undefined) {messager.alert('请选择一键拆分的订单!'); return false;}
	var rows=tradeCheck.selectRows;
	var ids={};
	for (var i=0;i<rows.length;++i){
		if(rows[i].goods_count!=undefined&&rows[i].goods_count<2){messager.alert('订单'+rows[i].trade_no+'只有一个货品，不可拆分'); return false;}
		if(rows[i].src_tids!=undefined&&rows[i].src_tids!=''&&rows[i].src_tids.indexOf(',')==-1){messager.alert('订单'+rows[i].trade_no+'非合并单，不可一键拆分'); return false;}
		if(rows[i].trade_status!=undefined&&rows[i].trade_status!=30&&rows[i].trade_status!=25){messager.alert('订单'+rows[i].trade_no+'状态不正确，检查是否已被其他人审核。'); return false;}
		if(rows[i].freeze_reason!=undefined&&rows[i].freeze_reason!=0){messager.alert('订单'+rows[i].trade_no+'已冻结，请先解冻订单'); return false;}
		if(rows[i].bad_reason!=undefined&&rows[i].bad_reason!=0){messager.alert('订单'+rows[i].trade_no+'有异常标记，请先处理'); return false;}
		if(rows[i].is_sealed!=undefined&&rows[i].is_sealed!=0){messager.alert('订单'+rows[i].trade_no+'不可拆分'); return false;}
		if(rows[i].delivery_term!=undefined&&rows[i].delivery_term==2){messager.alert(rows[i].trade_no+'为货到付款订单不可拆分'); return false;}
		ids[i]=rows[i]['id'];
	}
	tradeCheck.merge_split_ids=JSON.stringify(ids);
	var url='<?php echo ($url_list["merge_split_url"]); ?>';
	messager.confirm('确定要一键拆分合并单吗？',function(r){
		if(!r){return false;}
		Post(url,{'ids':tradeCheck.merge_split_ids},function(res){
			if(res.status==0){
				messager.alert('拆分成功');
				tradeCheck.refresh();
			}else{
				tradeCheck.dealDatagridReasonRows(res,undefined);
				tradeCheck.refresh();
			}
		},'JSON');
	});
}

// 
function splitRefundTrade(trade_id){
	var url='<?php echo ($url_list["refund_split_url"]); ?>';
	messager.confirm('确定要拆出申请退款单吗？',function(r){
		if(!r){return false;}
		Post(url,{'trade_id':trade_id},function(res){
			var return_rows= $("#response_dialog_datagrid").datagrid('getRows');
			if(!res.status){
				messager.alert(res.info);
				return false;
			}else{
				messager.alert(res.info);
				tradeCheck.refresh();			
				for (var i=0;i<return_rows.length;i++){
					if(return_rows[i]['trade_id']==trade_id){
						check_rows=[];
						return_rows[i]['solve_way']='<a href="javascript:void(0)" onClick="checkTrade(0,check_rows,'+return_rows[i]['trade_id']+')">再次审核</a>';
						index=$("#response_dialog_datagrid").datagrid('getRowIndex',return_rows[i]); 
						$("#response_dialog_datagrid").datagrid('refreshRow',index); 
					}
				}
			}
		},'JSON');
	});
}
//订单->批量换货
function passelExchange(){
	if(tradeCheck.selectRows==undefined){messager.alert('请选择需要换货的订单！');return false;}
	var rows=tradeCheck.selectRows;
	var version={};
	var ids={};
	for(var i=0;i<rows.length;i++){
		if(i<rows.length-1&&rows[i].warehouse_id!=rows[i+1].warehouse_id){messager.alert('请保持所选订单仓库一致');return false;}
		if(rows[i].trade_status!=undefined&&rows[i].trade_status!=30&&rows[i].trade_status!=25){messager.alert('订单'+rows[i].trade_no+'状态不正确，检查是否已被其他人审核。');return false;}
		if(rows[i].freeze_reason!=undefined&&rows[i].freeze_reason!=0){messager.alert('订单'+rows[i].trade_no+'已冻结，如需操作先解冻');return false;}
		if(rows[i].bad_reason!=undefined&&rows[i].bad_reason!=0){messager.alert('订单'+rows[i].trade_no+'有异常标记，请先处理');return false;}
		ids[i]=rows[i]['id'];
		version[ids[i]]=rows[i]['version_id'];
	}
	var data={};
	data.id=ids;
	data.version=version;
	tradeCheck.passel_exchange_ids=JSON.stringify(data);
	var url='<?php echo ($url_list["passel_exchange_url"]); ?>';
	url+=url.indexOf('?') != -1 ? '&ids='+tradeCheck.passel_exchange_ids : '?ids='+tradeCheck.passel_exchange_ids;
	var buttons=[ {text:'确定',handler:function(){submitPasselExchangeDialog();}}, {text:'取消',handler:function(){tradeCheck.cancelDialog(0);}} ];
	tradeCheck.showDialog(0,'订单批量换货',url,560,764,buttons);
}
// 批量添加货品
function passelAddGoods(is_gift){
	if(tradeCheck.selectRows==undefined){messager.alert('请选择需要添加货品的订单！');return false;}
	var rows=tradeCheck.selectRows;
	var version={};
	var ids={};
	for(var i=0;i<rows.length;i++){
		if(i<rows.length-1&&rows[i].warehouse_id!=rows[i+1].warehouse_id){messager.alert('请保持所选订单仓库一致');return false;}
		if(rows[i].trade_status!=undefined&&rows[i].trade_status!=30&&rows[i].trade_status!=25){messager.alert('订单'+rows[i].trade_no+'状态不正确，检查是否已被其他人审核。');return false;}
		if(rows[i].freeze_reason!=undefined&&rows[i].freeze_reason!=0){messager.alert('订单'+rows[i].trade_no+'已冻结，如需操作先解冻');return false;}
		if(rows[i].bad_reason!=undefined&&rows[i].bad_reason!=0){messager.alert('订单'+rows[i].trade_no+'有异常标记，请先处理');return false;}
		ids[i]=rows[i]['id'];
		version[ids[i]]=rows[i]['version_id'];
	}
	var data={};
	data.id=ids;
	data.version=version;	
	tradeCheck.passel_add_goods_ids=JSON.stringify(data);
	var url='<?php echo ($url_list["passel_add_goods"]); ?>';
	url+=url.indexOf('?') != -1 ? '&ids='+tradeCheck.passel_add_goods_ids+'&is_gift='+is_gift : '?ids='+tradeCheck.passel_add_goods_ids+'&is_gift='+is_gift;
	if (is_gift==0) {
		var buttons=[ {text:'确定',handler:function(){submitPasselAddGoodsDialog();}}, {text:'取消',handler:function(){tradeCheck.cancelDialog(0);}} ];
		tradeCheck.showDialog(0,'批量添加货品',url,412,680,buttons);
	}else{
		var buttons=[ {text:'确定',handler:function(){submitPasselAddGoodsDialog();}}, {text:'取消',handler:function(){tradeCheck.cancelDialog(0);}} ];
		tradeCheck.showDialog(0,'批量添加赠品',url,412,680,buttons);
	}
	
}
//重算赠品
function recalculationGift(){
	if(tradeCheck.selectRows==undefined){messager.alert('请选择订单！');return false;}
	var rows=tradeCheck.selectRows;
	var list=[];
	var version=[];
	var ids=[];
	for(var i in rows){
		if(rows[i].trade_status!=undefined&&rows[i].trade_status!=30&&rows[i].trade_status!=25) {
			list.push({trade_no:rows[i]['trade_no'],result_info:'订单状态不正确'});continue;
		}
		if(rows[i].freeze_reason!=undefined&&rows[i].freeze_reason!=0){
			list.push({trade_no:rows[i]['trade_no'],result_info:'订单已冻结'});continue;
		}
		if(rows[i].bad_reason!=undefined&&rows[i].bad_reason!=0){
			list.push({trade_no:rows[i]['trade_no'],result_info:'订单有异常标记'});continue;
		}
		if(rows[i].trade_from!=undefined&&rows[i].trade_from==2){
			list.push({trade_no:rows[i]['trade_no'],result_info:'手工建单不能重算赠品'});continue;
		}
		ids[i]=rows[i]['id'];
		version[ids[i]]=rows[i]['version_id'];
	}
	var data={};
	data.id=ids;
	data.version=version;
	var new_data=JSON.stringify(data);
	messager.confirm('重算赠品会删除原有赠品并会重新匹配赠品策略，计算出新的赠品，确定重算吗？',function(r){
		if(!r){return false;}
		if(ids.length>0){
			Post("<?php echo U('TradeCheck/recalculationGift');?>",{data:new_data},function(res){
				if(list.length<1&&res.status==0){
					messager.alert('赠品重算完成！');
					tradeCheck.refresh();
				}else{
					tradeCheck.dealDatagridReasonRows(res,list);
					tradeCheck.refresh();
				}
			},'JSON');
		}else{
			var res={};res.status=2;res.info={};res.info.rows=list;res.info.total=list.length;
			tradeCheck.dealDatagridReasonRows(res,undefined);
		}
	});
}
function editTradeRowStyler(i,row){
	var refund_bg_color='<?php echo ($refund_color); ?>';
	if(row.refund_status>1){return refund_bg_color;}
	return;
}
function province_select(newValue, oldValue){
	var that = this;
	var ids = newValue;
	if(ids.length >2){
		$('#tradeCheckCity').combobox('disable');
		$('#tradeCheckDistrict').combobox('disable');
	}else{
		$('#tradeCheckCity').combobox('enable');
		$('#tradeCheckDistrict').combobox('enable');
	}	
}
//回传备注和标旗
function uploadRemarkAndFlag(){
	var url = '<?php echo ($url_list["upload_remark_and_flag_url"]); ?>';
	var buttons=[ {text:'确定',handler:function(){tradeCheck.submitUploadDialog();}} ];
	tradeCheck.showDialog(0,'回传备注和标旗(仅支持淘宝平台)',url,180,350,buttons);
}
</script>

<!-- layout-south-tabs, call add_tabs js function to add tabs -->


</body>
</html>