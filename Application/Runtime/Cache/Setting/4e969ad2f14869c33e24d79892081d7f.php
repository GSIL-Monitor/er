<?php if (!defined('THINK_PATH')) exit();?><div id='system_setting_tab' class="easyui-tabs" data-options="fit:true">
    <div title="基本设置" class="form-div">
        <div class="form-div">
            <!-- <label>默认每页显示</label><input class="easyui-combobox txt" editable="false" style="width:80px" text="txt" name="page_limit" data-options="valueField:'id',textField:'name',data:[{'id':'0','name':'20'},{'id':'1','name':'50'},{'id':'2','name':'100'}]">条订单（仅支持：原始订单、订单审核、单据打印）</br>-->
            <?php if($role > 0): ?><label><input type="checkbox" name="login_check_code" onclick="systemCheckboxOnclick(this)"/>开启登录短信安全验证</label></br>
                <label><input type="checkbox" name="change_password_check_code" onclick="systemCheckboxOnclick(this)"/>开启密码修改短信验证</label></br>
                <label><input type="checkbox" name="cfg_open_message_strategy" onclick="systemCheckboxOnclick(this)"/>启用短信发送策略</label></br>
                <label>客户发送短信,一个手机号</label><input type="text" class="easyui-numberbox" name="crm_member_send_sms_limit_time" data-options="min:5" style="width:70px"/><label>分钟内只能发送一次(0表示不限制)</label><br/><?php endif; ?>
            <?php if($role > 1): ?><label><input type="checkbox" name="show_number_to_star" onclick="systemCheckboxOnclick(this)"/>手机号码显示成星号</label></br>
                <label><input type="checkbox" name="waybill_num_alarm" onclick="systemCheckboxOnclick(this)"/>电子面单余额不足</label><input type="text" class="easyui-numberbox" name="waybill_num_alarm_num" data-options="min:0" style="width:70px"/>条进行提醒</br>
                <label><input type="checkbox" name="sms_num_alarm" onclick="systemCheckboxOnclick(this)"/>短信余额不足</label><input type="text" class="easyui-numberbox" name="sms_num_alarm_num" data-options="min:0" style="width:70px"/>条进行提醒</br>
            	 <label><input type="checkbox" name="order_balance" onclick="systemCheckboxOnclick(this)"/>订单余额不足</label><input type="text" class="easyui-numberbox" name="order_balance_num" data-options="min:0" style="width:70px"/>条进行提醒</br>
            	
				<hr style="border:none;border-top:2px dotted #95B8E7;"><?php endif; ?>
            <label>用户登录时间间隔&nbsp;</label><input type="text" class="easyui-numberbox" name="cfg_login_interval" data-options="min:5,max:120" style="width:70px"/><label>&nbsp;分钟(间隔范围为5 - 120分钟)</label><br/>
            <label><input type="checkbox" name="logistics_auto_sync" onclick="systemCheckboxOnclick(this)"/>启用自动物流同步</label></br>
			<label><input type="checkbox" name="sales_trade_trace_enable" onclick="systemCheckboxOnclick(this)"/>开启订单全链路</label></br>
            <label style="padding-left: 2em"><input type="checkbox" name="sales_trade_trace_operator" onclick="systemCheckboxOnclick(this)"/>订单全链路同步操作员昵称</label></br>
            <!-- <label><input type="checkbox" name="goods_auto_download" onclick="systemCheckboxOnclick(this)"/>启用淘宝货品自动下载</label></br> -->
            <label><input type="checkbox" name="order_auto_download" onclick="systemCheckboxOnclick(this)"/>自动下载订单</label></br>
            <label><input type="checkbox" name="order_auto_downloadjdsellback" onclick="systemCheckboxOnclick(this)"/>自动下载京东售后单</label></br>
            <hr style="border:none;border-top:2px dotted #95B8E7;">
            <label><input type="checkbox" name="order_auto_submit" onclick="systemCheckboxOnclick(this)"/>自动递交原始单</label></br>
            <label><input type="checkbox" name="apigoods_auto_match" onclick="systemCheckboxOnclick(this)"/>递交自动匹配货品（对新增和变化的平台货品进行自动匹配）</label></br>
			<label><input type="checkbox" name="order_deliver_auto_merge" onclick="systemCheckboxOnclick(this)"/>递交自动合并订单，合并方式：</label>
            <label><input name="order_auto_merge_mode" type="radio" value="0" style="margin-bottom: 4px;"/>店铺+买家+收件人+地址</label>
            <label><input name="order_auto_merge_mode" type="radio" value="1" style="margin-left: 20px;margin-bottom: 4px;" />分组+买家+收件人+地址</label></br>
            <label style="padding-left: 2em"><input type="checkbox" name="order_deliver_auto_merge_ban_refund" onclick="systemCheckboxOnclick(this)"/>禁止申请退款单自动合并</label></br>
            <label style="padding-left: 2em"><input type="checkbox" name="sales_trade_auto_merge_gift" onclick="systemCheckboxOnclick(this)"/>自动合并重新计算赠品</label></br>
            <label><input type="checkbox" name="order_deliver_auto_exchange" onclick="systemCheckboxOnclick(this)"/>平台换货自动更换订单货品</label></br>
             <label><input type="checkbox" name="sales_trade_warehouse_bygoods" onclick="systemCheckboxOnclick(this)"/>启用按货品选仓库策略</label></br>
             <label><input type="checkbox" name="sales_trade_logistics_bygoods" onclick="systemCheckboxOnclick(this)"/>启用按货品选物流策略</label></br>
             <label><input type="checkbox" name="order_deliver_auto_split_by_warehouse" onclick="systemCheckboxOnclick(this)"/>启用根据货品指定仓库自动拆分</label></br>
             <label><input type="checkbox" name="order_deliver_auto_split" onclick="systemCheckboxOnclick(this)"/>启用大件自动拆分</label></br>
             <label><input type="checkbox" name="sales_trade_refund_block_gift" onclick="systemCheckboxOnclick(this)"/>发生退款（申请退款）拦截同一用户仅包含赠品的订单</label></br>
             <label><input type="checkbox" name="stat_unknow_goods_amount" onclick="systemCheckboxOnclick(this)"/>统计中发货订单毛利不扣减零成本出库销售额</label></br>
            <hr style="border:none;border-top:2px dotted #95B8E7;">
            <label><input type="checkbox" name="refund_should_deliver" onclick="systemCheckboxOnclick(this)"/>自动递交类型为退款的原始退款单</label></br>
            <hr style="border:none;border-top:2px dotted #95B8E7;">
            <input class="easyui-combobox txt" style="width: 170px;" editable="false" text="txt" id="sys_control_stock" name="sys_control_stock" data-options="panelHeight:'auto',valueField:'id',textField:'name',data:[{'id':'0','name':'多规格商品不维护库存'},{'id':'1','name':'多规格商品维护库存'}]">
            <label>商家编码生成方式：</label><input class="easyui-combobox txt" style="width: 200px;" editable="false" text="txt" id="sys_goods_match_concat_code" name="sys_goods_match_concat_code" data-options="panelHeight:'auto',valueField:'id',textField:'name',data:[{'id':'0','name':'规格商家编码'},{'id':'1','name':'货品商家编码+规格商家编码'},{'id':'2','name':'货品ID+唯一ID'},{'id':'3','name':'货品商家编码+唯一ID'}]"></br>
            <label><input type="checkbox" id="sys_goods_auto_make" name="sys_goods_auto_make" onclick="systemCheckboxOnclick(this)"/>自动生成系统货品（此配置在多规格商品不维护库存时可开启）</label></br>
            <!--<label><input type="checkbox" name="sys_goods_match_concat_code" onclick="systemCheckboxOnclick(this)"/>多规格货品匹配使用“主商家编码”+“规格商家编码”方式</label></br>-->
            <hr style="border:none;border-top:2px dotted #95B8E7;">
            <label><input type="checkbox" name="sales_raw_count_exclude_gift" onclick="systemCheckboxOnclick(this)"/>订单中原始货品数量不包含赠品</label></br>
            <label><input type="checkbox" name="specname_goodsname_allow_update" onclick="systemCheckboxOnclick(this)"/>开启更新系统货品名</label></br>
            <label><input type="checkbox" name="flag_goods_name_changed" onclick="systemCheckboxOnclick(this)"/>标记平台货品名称变动的商品</label></br>
            <label><input type="checkbox" name="order_deliver_block_consign" onclick="systemCheckboxOnclick(this)"/>拦截平台已发货订单</label></br>
            <label style="padding-left: 2em"><input type="checkbox" name="prevent_online_block_consign_stockout" onclick="systemCheckboxOnclick(this)"/>不阻止确认发货</label></br>
            <label style="margin-left:3px">库存数量显示小数点位数</label><label style="padding-left: 1em"><input id="system_point_number" class="easyui-combobox txt" editable="false" text="txt" style="width:50px" name="point_number" data-options="valueField:'id',textField:'name',data:[{'id':'0','name':'0'},{'id':'1','name':'1'},{'id':'2','name':'2'},{'id':'3','name':'3'},{'id':'4','name':'4'}]"></label></br>
            <label>订单中货品摘要生成方式：</label><input class="easyui-combobox txt" editable="false" text="txt" name="single_spec_no_code" data-options="panelHeight:'auto',valueField:'id',textField:'name',data:[{'id':'0','name':'货品名称+规格名称'},{'id':'1','name':'商家编码+规格名称'}]"></br>
            <hr style="border:none;border-top:2px dotted #95B8E7;">
            <label><input type="checkbox" name="shop_disabled_search" onclick="systemCheckboxOnclick(this)"/>停用的店铺不在搜索下拉列表中显示</label></br>
            <label><input type="checkbox" name="warehouse_disabled_search" onclick="systemCheckboxOnclick(this)"/>停用的仓库不在搜索下拉列表中显示</label></br>
            <label><input type="checkbox" name="logistics_disabled_search" onclick="systemCheckboxOnclick(this)"/>停用的物流不在搜索下拉列表中显示</label></br>
            <label><input type="checkbox" name="reason_disabled_search" onclick="systemCheckboxOnclick(this)"/>停用的原因列表不在搜索下拉列表中显示</label></br>
        </div>
    </div>
    <div title="订单设置">
        <form id="trade_system_setting" class="form-div">
            <div class="form-div">
                <label><input type="checkbox" name="order_check_warn_has_unmerge" onclick="systemCheckboxOnclick(this)"/>拦截同名未合并订单</label></br>
                <label style="padding-left: 2em"><input type="checkbox" name="order_check_warn_has_unmerge_checked" onclick="systemCheckboxOnclick(this)"/>同名未合并（包含已审核订单）</label></br>
                <label style="padding-left: 2em"><input type="checkbox" name="order_check_warn_has_unmerge_freeze" onclick="systemCheckboxOnclick(this)"/>同名未合并（包含冻结）</label></br>
                <label style="padding-left: 2em"><input type="checkbox" name="order_check_warn_has_unmerge_address" onclick="systemCheckboxOnclick(this)"/>同名未合并（包含不同地址）</label></br>
                <label style="padding-left: 2em"><input type="checkbox" name="order_check_warn_has_unpay" onclick="systemCheckboxOnclick(this)"/>提示有未付款的同名未合并订单</label></br>
                <label><input type="checkbox" name="order_check_no_stock" onclick="systemCheckboxOnclick(this)"/>阻止库存不足订单通过审核</label></br>
                <label><input type="checkbox" name="order_check_black_customer" onclick="systemCheckboxOnclick(this)"/>阻止黑名单客户通过审核</label></br>
                <label><input type="checkbox" name="order_check_get_waybill" onclick="systemCheckboxOnclick(this)"/>订单审核自动获取电子面单</label></br>
                <label><input type="checkbox" name="order_check_synchronous_logistics" onclick="systemCheckboxOnclick(this)"/>订单审核自动预物流同步（此配置要与自动获取电子面单的配置同时打开，并且只支持电子面单）</label></br>
                <label><input type="checkbox" name="order_check_address_reachable" onclick="systemCheckboxOnclick(this)"/>订单审核判断物流是否可达</label></br>
                <label><input type="checkbox" name="order_limit_real_price" onclick="systemCheckboxOnclick(this)"/>限制手工建单商品折后价不低于：</label><input class="easyui-combobox txt" editable="false" text="txt" name="real_price_limit_value" data-options="panelHeight:'auto',valueField:'id',textField:'name',data:[{'id':'0','name':'最低价'},{'id':'1','name':'零售价'},{'id':'2','name':'市场价'}]"></br>
                <!-- 如有需要可以增加,{'id':'wholesale_price','name':'批发价'},{'id':'member_price','name':'会员价'} -->
                <label><input type="checkbox" name="order_allow_man_create_cod" onclick="systemCheckboxOnclick(this)"/>允许手工新建COD（货到付款）订单</label></br>               
                <label id="system_sales_trade_split_num2"><input type="checkbox" id="system_sales_trade_split_num" name="sales_trade_split_num" onclick="systemCheckboxOnclick(this)"/>允许拆分数量为小数</label></br>
                <!--<label><input type="checkbox" name="order_check_warn_unpay_min" onclick="systemCheckboxOnclick(this)"/>时间间隔限制-转换成小数</label></br>-->
                <label><input type="checkbox" name="split_merge_trade_auto_recalculation_gift" onclick="systemCheckboxOnclick(this)"/>一键拆分合并单自动重算赠品</label></br>
                <label><input type="checkbox" name="order_allow_part_sync" onclick="systemCheckboxOnclick(this)"/>开启拆单发货(淘宝、阿里巴巴、有赞平台)</label></br>
                &nbsp;<label>物流同步方式：</label>
                <label><input name="order_logistics_sync_time" type="radio" value="1" style="margin-bottom: 4px;"/>只发一个子订单即可物流同步</label>
                <label><input name="order_logistics_sync_time" type="radio" value="2" style="margin-left: 20px;margin-bottom: 4px;" />全部子订单发货才能物流同步</label></br>
                <label><input type="checkbox" name="order_mark_color_by_weight" onclick="systemCheckboxOnclick(this)"/>订单估重超过指定范围标记颜色,重量:</label><input type="text" class="easyui-numberbox txt" name="order_mark_color_weight_range" data-options="min:0,precision:4,required:true" style="width:70px"/><label>&nbsp;kg</label></br>
                <label><input type="checkbox" name="order_cal_weight_by_suite" onclick="systemCheckboxOnclick(this)"/>订单包含组合装时按照组合装重量计算订单重量</label></br>
                <?php if($role > 1): ?><label><input type="checkbox" name="order_check_force_check_pwd_is_open" onclick="systemCheckboxOnclick(this)"/>开启强制审核校验密码</label><input type="password" class="easyui-textbox" name="order_check_force_check_pwd" style="width:70px"/></br><?php endif; ?>  
                <label><input type="checkbox" name="refund_auto_agree" onclick="systemCheckboxOnclick(this)"/>退换单新建完成自动同意</label></br>
                <label><input type="checkbox" name="return_order_auto_sync_remark" onclick="systemCheckboxOnclick(this)"/>同意退货单后自动同步备注<input type="text" class="easyui-textbox" style="width:120px;" name="return_order_auto_remark"/>到线上订单（仅支持淘宝平台） </label><br>
                <label><input type="checkbox" name="return_agree_auto_sync_remark" onclick="systemCheckboxOnclick(this)"/>同意换货单后自动同步备注<input type="text" class="easyui-textbox" style="width:120px;" name="return_agree_auto_remark"/>到线上订单（仅支持淘宝平台） </label><br>
                <!--&nbsp;<label>平台货品匹配截取字符&nbsp;&nbsp;&nbsp;&nbsp;</label><input type="text" class="easyui-textbox" name="goods_match_split_char" style="width:70px"/></br>-->
                &nbsp;<label>下载订单时间间隔&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</label><input type="text" class="easyui-numberbox" name="order_sync_interval" data-options="min:5" style="width:70px"/><span>(分钟)</span> (间隔几分钟去获取订单)<br/>
                &nbsp;<label>订单延迟时间&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</label><input type="text" class="easyui-numberbox" name="order_delay_interval" data-options="min:2" style="width:70px"/><span>(分钟)</span>(客户下单后几分钟才能抓取,默认淘宝平台最小2分,其他平台最小10分)<br/>
                <!--<label><input type="checkbox" name="order_allow_part_sync" onclick="systemCheckboxOnclick(this)"/>开启拆单发货(淘宝/阿里巴巴有效)</label></br>-->
                <!--<label><input type="checkbox" name="sys_available_stock" onclick="systemCheckboxOnclick(this)"/>可发库存的配置信息</label></br>-->
                <!--<label><input type="checkbox" name="order_logistics_sync_time" onclick="systemCheckboxOnclick(this)"/>物流同步时间</label></br>-->
                <!--<label><input type="checkbox" name="order_allow_part_sync" onclick="systemCheckboxOnclick(this)"/>开启拆单发货（只对淘宝订单有效）</label></br>-->
                <div>
                <fieldset style="border:1px solid #95B8E7;padding: 2px;margin-top: 2px;width: 470px;">
                <label><input type="checkbox" name="order_go_preorder" onclick="systemCheckboxOnclick(this)"/>开启预订单</label></br>
                <label style="padding-left: 2em"><input class="easyui-combobox txt" text="txt" style="width:205px" name="order_preorder_lack_stock" data-options="valueField:'id',textField:'name',data:[{'id':'0','name':'关闭'},{'id':'1','name':'实际库存-待发货-待审核'},{'id':'2','name':'实际库存-待发货-待审核-预订量'}]">库存不足转预订单</label></br>
                <label style="padding-left: 2em"><input class="easyui-combobox txt" text="txt" style="width:100px" name="preorder_split_to_order_condition" data-options="valueField:'id',textField:'name',data:[{'id':'0','name':'关闭'},{'id':'2','name':'库存充足'}]">预订单自动拆分转入审核</label></br>
                </fieldset>
                </div>
                <div>
                <fieldset style="border:1px solid #95B8E7;padding: 2px;margin-top: 2px;width: 470px;">
                <label><input type="checkbox" name="auto_check_is_open" onclick="systemCheckboxOnclick(this)"/>开启自动审核</label></br>
                <label style="padding-left: 2em"><input type="checkbox" name="auto_check_buyer_message" onclick="systemCheckboxOnclick(this)"/>无客户备注</label>
                <label style="padding-left: 1em"><input type="checkbox" name="auto_check_csremark" onclick="systemCheckboxOnclick(this)"/>无客服备注</label>
                <label style="padding-left: 1em"><input type="checkbox" name="auto_check_no_invoice" onclick="systemCheckboxOnclick(this)"/>无发票</label>
                <label style="padding-left: 1em"><input type="checkbox" name="auto_check_no_adr" onclick="systemCheckboxOnclick(this)"/>收货地址无（村、组）</label></br>
                <label style="padding-left: 2em"><input type="checkbox" name="auto_check_under_weight" onclick="systemCheckboxOnclick(this)">订单重量低于（≤）</label><input type="text" class="easyui-numberbox" name="auto_check_max_weight" style="width:70px"/>千克</br>
                <label style="padding-left: 2em"><input class="easyui-combobox txt" text="txt" style="width:90px" name="auto_check_time_type" data-options="panelHeight:'auto',valueField:'id',textField:'name',data:[{'id':'0','name':'下单时间'},{'id':'1','name':'付款时间'}]"></label>
                <label>从：</label><input class="easyui-datetimebox" data-options="required:true,validType:'datetime'" name="auto_check_start_time" style="width:150px;"/>
                <label>至：</label><input class="easyui-datetimebox" data-options="required:true,validType:'datetime'" name="auto_check_end_time" style="width:150px;"/></br>
                </fieldset>
                </div>
                <div>
                <fieldset style="border:1px solid #95B8E7;padding: 2px;margin-top: 2px;margin-bottom:2px;width: 470px;">
                <label><input type="checkbox" name="order_fa_condition" onclick="systemCheckboxOnclick(this)"/>开启财务审核</label></br>
                <label style="padding-left: 2em"><input type="checkbox" name="order_fc_man_order" onclick="systemCheckboxOnclick(this)"/>手工建单</label>
                <label style="padding-left: 1em"><input type="checkbox" name="order_fc_excel_import" onclick="systemCheckboxOnclick(this)"/>EXCEL导入订单</label>
                <label style="padding-left: 1em"><input type="checkbox" name="order_fc_gift" onclick="systemCheckboxOnclick(this)"/>订单全是赠品进财审</label></br>
                &nbsp;<label style="padding-left: 2em">订单应收金额高于 <input type="text" class="easyui-numberbox" name="order_fc_receivable_outnumber" data-options="min:0" style="width:70px"/> 元（0表示不限制）</label></br>
                &nbsp;<label style="padding-left: 2em">优惠金额达到 <input type="text" class="easyui-numberbox" name="order_fc_discount" data-options="min:0" style="width:70px"/> 元时进财审（0表示不限制）</label></br>
                <label style="padding-left: 2em"><input type="checkbox" name="order_fc_below_costprice" onclick="systemCheckboxOnclick(this)"/>商品成交金额低于成本价时进财审</label>
            	</fieldset>
            	</div>
            </div>
        </form>
    </div>
    <div title="库存设置">
        <form id="stock_system_setting" class="easyui-form">
            <div class="form-div">
                <label><input type="checkbox" name="stockout_examine_goods" onclick="systemCheckboxOnclick(this)"/>销售出库必须验货</label></br>
                <label><input type="checkbox" name="stockout_weight_goods" onclick="systemCheckboxOnclick(this)"/>销售出库必须称重</label></br>
                <label><input type="checkbox" name="stockout_examine_auto_consign" onclick="systemCheckboxOnclick(this)"/>验货后自动发货</label></br>
                <label><input type="checkbox" name="stockout_weight_auto_consign" onclick="systemCheckboxOnclick(this)"/>称重后自动发货</label></br>
                <label><input type="checkbox" name="overflow_weight_alarm" onclick="systemCheckboxOnclick(this)"/>“称重重量 - 预估重量(差值)”超出指定范围提示</label>
                <label>( 取值范围：- <input type="text" class="easyui-numberbox" name="overflow_weight_small" data-options="min:0,precision:4" style="width:70px"/>kg ≤ 差值 ≤ <input type="text" class="easyui-numberbox" name="overflow_weight_big" data-options="min:0,precision:4" style="width:70px"/>kg )</label></br>
                <label><input type="checkbox" name="purchase_alarmstock_open" onclick="systemCheckboxOnclick(this)"/>开启库存预警</label></br>
                <label style="padding-left: 2em"><input type="checkbox" name="purchase_auto_alarmstock" onclick="systemCheckboxOnclick(this)"/>自动刷新警戒库存</label></br>
			  <label><input type="checkbox" name="stock_auto_sync" onclick="systemCheckboxOnclick(this)"/>启用库存同步</label></br>
                <label><input type="checkbox" name="spec_stocksyn_priority" onclick="systemCheckboxOnclick(this)"/>单品库存同步策略优先级高于全局库存同步策略</label></br>
                <label><input type="checkbox" name="order_check_no_stock_stockout" onclick="systemCheckboxOnclick(this)"/>阻止库存不足负库存出库</label></br>
			    <label><input type="checkbox" name="cfg_open_logistics_trace" onclick="systemCheckboxOnclick(this)"/>启用物流追踪</label></br>
                <label><input type="checkbox" name="addgoods_refresh_stock" onclick="systemCheckboxOnclick(this)"/>新增货品手动增加库存信息</label><label style = "color:red;margin-left:30px;">(新增货品在奇门仓库会自动刷新库存信息)</label></br>             
				<label style="padding-left: 4px">单据打印界面默认显示：(√代表显示，不选代表不显示，-代表显示全部)</label></br>
                <!--<label style="padding-left: 2em"><input type="checkbox" name="stockout_sendbill_print_status" onclick="systemCheckboxOnclick(this)"/>显示已打印发货单</label></br>-->
                <!--<label style="padding-left: 2em"><input type="checkbox" name="stockout_logistics_print_status" onclick="systemCheckboxOnclick(this)"/>显示已打印物流单</label></br>-->
                <label style="padding-left: 2em"><input extend_type="complex-check" onclick="$(this).triStateCheckbox('click')" name="stockout_sendbill_print_status" value="" type="checkbox" />显示已打印发货单的订单</label></br>
                <label style="padding-left: 2em"><input extend_type="complex-check" onclick="$(this).triStateCheckbox('click')" name="stockout_logistics_print_status" value="" type="checkbox" />显示已打印物流单的订单
                </label></br>
                <label><input type="checkbox" name="stockout_consign_disable_revert" onclick="systemCheckboxOnclick(this)"/>发货后禁止撤销发货</label></br>
 				<label><input type="checkbox" name="stock_scan_once" onclick="systemCheckboxOnclick(this)"/>包裹扫描一次称重(不勾选则扫描2次)</label></br>
                &nbsp;<label>如果仅扫描一次，</label><input type="text" class="easyui-numberbox" name="stock_auto_submit_time" data-options="min:1" style="width:70px"/><lable>秒后自动提交</lable></br>
             <!--   <label><input type="checkbox" name="stockout_must_checkout" onclick="systemCheckboxOnclick(this)"
                        />必须签出才可操作</label></br>
                <label><input type="checkbox" name="stockout_disable_revert" onclick="systemCheckboxOnclick(this)"
                        />禁止销售出库</label></br>
              -->
                <label><input type="checkbox" name="share_post_fee_to_cost_price" onclick="systemCheckboxOnclick(this)"/>入库时将邮费(包括其他费用)均摊到成本价</label></br>
                <label>可发库存的计算方式:</label><input type="hidden" name="sys_available_stock" value="640"/></br>
                <label style="padding-left: 2em">可发库存  =  实际库存</label>
                <label style="padding-left: 0.5em"><input type="checkbox" name="sys_available_stock-7" onclick="systemCheckboxOnclick(this)"/>－待审核量</label>
                <label style="padding-left: 0.5em"><input type="checkbox" name="sys_available_stock-9" onclick="systemCheckboxOnclick(this)"/>－待发货量</label></br>
                <label>每日预警采购量计算方式:</label><input type="hidden" name="sys_available_purchase" value="961"/></br>
                <label style="padding-left: 2em">采购量  =  实际库存 － 警戒库存</label>
                <label style="padding-left: 0.5em"><input type="checkbox" name="sys_available_purchase-7" onclick="systemCheckboxOnclick(this)"/>－待审核量</label>
                <label style="padding-left: 0.5em"><input type="checkbox" name="sys_available_purchase-9" onclick="systemCheckboxOnclick(this)"/>－待发货量</label>
                <label style="padding-left: 0.5em"><input type="checkbox" name="sys_available_purchase-8" onclick="systemCheckboxOnclick(this)"/>－未付款量</label>
                <label style="padding-left: 0.5em"><input type="checkbox" name="sys_available_purchase-6" onclick="systemCheckboxOnclick(this)"/>－预订单量</label>
                <label style="padding-left: 0.5em"><input type="checkbox" name="sys_available_purchase-0" onclick="systemCheckboxOnclick(this)"/>＋采购在途量</label></br>
                <label style="margin-left:3px">单据打印默认显示订单的时间段</label><label style="padding-left: 1em"><input  class="easyui-combobox txt" text="txt" style="width:100px" name="sales_print_time_range" data-options="valueField:'id',textField:'name',data:[{'id':'7','name':'7天'},{'id':'15','name':'15天'},{'id':'30','name':'30天'},{'id':'60','name':'60天'},{'id':'90','name':'90天'}]"></label></br>
                 <label>&nbsp;库存管理近期销量统计的天数&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</label><input type="text" class="easyui-numberbox" name="stock_out_num" data-options="min:30" style="width:70px"/>&nbsp;天<label style = "color:red;margin-left:30px;">(默认最小30天)</label> <br/>
				<label><input type="checkbox" name="stock_print_sender_from" onclick="systemCheckboxOnclick(this)"/>发件人(姓名,联系方式,发货地址)使用店铺信息(注:不勾选使用的是仓库信息)</label></br>
				<label><input type="checkbox" name="syncLogistics_examine_goods" onclick="systemCheckboxOnclick(this)"/>预物流同步前必须验货</label></br>
                <label><input type="checkbox" name="syncLogistics_check_weight" onclick="systemCheckboxOnclick(this)"/>预物流同步前必须称重</label></br>
                <hr style="border:none;border-top:2px dotted #95B8E7;">
                <label><input type="checkbox" name="stockin_auto_commit" onclick="systemCheckboxOnclick(this)"/>入库开启自动提交</label></br>
                <label><input type="checkbox" name="stockout_auto_commit" onclick="systemCheckboxOnclick(this)"/>出库开启自动提交</label></br>
                <label><input type="checkbox" name="stocktransfer_auto_commit" onclick="systemCheckboxOnclick(this)"/>调拨开启自动提交</label></br>
                <label><input type="checkbox" name="stockinventory_auto_commit" onclick="systemCheckboxOnclick(this)"/>盘点开启自动提交</label></br>
                <hr style="border:none;border-top:2px dotted #95B8E7;">
			</div>
        </form>
    </div>
    <div title="自定义属性">
        <form id="cfg_prop_setting" class="easyui-form">
            <div class="form-div">
                <label>单品自定义属性1:</label><input type="text" class="easyui-textbox" style="width:120px;" name="goods_spec_prop1" /><br/>
                <label>单品自定义属性2:</label><input type="text" class="easyui-textbox" style="width:120px;" name="goods_spec_prop2" /><br/>
                <label>单品自定义属性3:</label><input type="text" class="easyui-textbox" style="width:120px;" name="goods_spec_prop3" /><br/>
                <label>单品自定义属性4:</label><input type="text" class="easyui-textbox" style="width:120px;" name="goods_spec_prop4" /><br/>
            </div>
        </form>
    </div>
    <?php if($stalls_init == 1): ?><div title="档口设置">
            <!--<hr style="border:none;border-top:2px dotted #95B8E7;">
            <label style="margin-left: 20px;color: #FF0000;"><a href="javascript:void(0)" class="easyui-linkbutton" onclick="hideStallsBtn();">关闭档口模式</a>&ndash;&gt;关闭之后将无法使用档口模式。再开启请联系管理员。<br/></label>
            <hr style="border:none;border-top:2px dotted #95B8E7;">-->
            <form id="stalls_system_setting" class="easyui-form">
                <div class="form-div">
                    <label><input type="checkbox" name="stall_wholesale_mode" onclick="systemCheckboxOnclick(this)"/>订单审核生成档口缺货明细单</label></br>
                    <label><input type="checkbox" name="order_check_give_storting_box" onclick="systemCheckboxOnclick(this)"/>订单审核自动分配分拣框</label></br>
                    <!--<label><input type="checkbox" name="hot_order_print_auto_consign" onclick="systemCheckboxOnclick(this)"/>爆款单打印物流单后自动发货</label></br>-->

					<label><input type="checkbox" name="open_hot" onclick="systemCheckboxOnclick(this)"/>当缺货明细内货品数量大于</label><input type="text" class="easyui-numberbox" name="hot_num" data-options="min:0" style="width:70px"/>自动生成爆款单(货品必须是一单一货未生成档口单,且未打印唯一码的)</br>
                    <label><input type="checkbox" name="dynamic_allocation_box" onclick="systemCheckboxOnclick(this)"/>切换动态分拣框</label></br>
                    <label><input type="checkbox" name="cancel_sorting_order_auto_stockout" onclick="systemCheckboxOnclick(this)"/>档口单取消订单（取消、全额退款、删除、清除）后，已分拣入库货品自动完成出库</label></br>
                </div>
            </form>
        </div><?php endif; ?>
    <!--<div title="货品设置"></div>
   -->
    <!--<div title="客户设置"></div>-->
    <!-- </div>-->
</div>
<script> 
    //# sourceURL=SystemSetting.js
    $(function () {
        //初始化，为页面各个元素填充数据
        var setting = JSON.parse('<?php echo ($setting); ?>');
        $("#reason_show_dialog input").each(function () {
            var that = this;
            if (that.type == "checkbox") {
                var name = that.name;
                if(name.search("sys_available_stock") != -1){return;}
                if(name.search("sys_available_purchase") != -1){return;}
				if (typeof(setting) != "undefined" && (setting[name] == 1)) {
					that.value = 1;
                    that.indeterminate = false;
					that.checked = true;
				}
                else if(typeof(setting) != "undefined" && (setting[name] == '3')){
                    that.value = 'all';
                    that.indeterminate = true;
                }
                else {
					that.value = 0;
                    that.indeterminate = false;
					that.checked = false;
				}
				
            } else if(that.type =="radio"){
            	var name=that.name;
                var index = setting[name];
                if(name =='order_logistics_sync_time'&&index!=0)
                    index = setting[name]-1;
            	$("input[name='"+name+"']").get(index).checked=true;
            }else {
                var name = that.name;
                if(name == "cfg_login_interval"){
                    (setting[name]==undefined || $.trim(setting[name])=='' || setting[name].length==0)?setting[name]=30:setting[name]=setting[name];
                }
                if(name == "order_preorder_lack_stock"){
                    setting[name]==undefined?setting[name]=0:setting[name]=setting[name];
                }
				if(name == "point_number"){
                    setting[name]==undefined?setting[name]=0:setting[name]=setting[name];
                }
                if(name == "real_price_limit_value"){
                    setting[name]==undefined?setting[name]='0':setting[name]=setting[name];
                }
                if(name == "sys_available_stock"){
                    (setting[name]==undefined || setting[name]=='')?setting[name]=640:setting[name]=setting[name];
                    var available_stock_elements = $('#reason_show_dialog'+" :input[name^='sys_available_stock']");
                    var available_value = setting[name];
                    available_stock_elements.each(function(){
                        var split_ar = $(this).attr('name').split('-');
                        if(parseInt(available_value)&Math.pow(2,parseInt(split_ar[1]))){
                            $(this).prop('checked',true);
                            $(this).attr('value',1);
                        }else{
                            $(this).attr('value',0);
                        }
                    });
                }
                if(name == "sys_available_purchase"){
                    (setting[name]==undefined || setting[name]=='')?setting[name]=0:setting[name]=setting[name];
                    var available_purchase_elements = $('#reason_show_dialog'+" :input[name^='sys_available_purchase']");
                    var available_value = setting[name];
                    available_purchase_elements.each(function(){
                        var split_ar = $(this).attr('name').split('-');
                        if(parseInt(available_value)&Math.pow(2,parseInt(split_ar[1]))){
                            $(this).prop('checked',true);
                            $(this).attr('value',1);
                        }else{
                            $(this).attr('value',0);
                        }
                    });
                }
                if(name == "goods_spec_prop1" || name == "goods_spec_prop2" || name == "goods_spec_prop3" || name == "goods_spec_prop4"){
                    setting[name]==undefined?setting[name]='':setting[name]=setting[name];
                }
                that.value = setting[name];
            }
        });
        $('#system_setting_tab').tabs({
            border: false,
            fit: true
        });
        if(setting['point_number']==0){
            $('#system_sales_trade_split_num').attr('disabled',true);
            document.all.system_sales_trade_split_num2.style.color='#999';
        }
        $('#system_point_number').combobox({
            onChange:function(){
                if($('#system_point_number').combobox('getValue')==0){
                    $('#system_sales_trade_split_num').attr('disabled',true);
                    document.all.system_sales_trade_split_num2.style.color='#999';
                }
                else{
                    $('#system_sales_trade_split_num').attr('disabled',false);
                    document.all.system_sales_trade_split_num2.style.color='#000000';
                }
            }
        });

        if(!$('input:checkbox[name="order_check_get_waybill"]').prop('checked')){
            $('input:checkbox[name="order_check_synchronous_logistics"]').prop('disabled', true);
        }
        setTimeout(function(){
            var tab_type = '<?php echo ($tab_type); ?>';
            if(!$.isEmptyObject($('#system_setting_tab').tabs('getTab',tab_type))){
                var config_name = '<?php echo ($config_name); ?>';
                var tab =  $('#system_setting_tab').tabs('select',tab_type);
                var position='bottom';
                var deltaX=0;
                if(config_name=='auto_check_is_open'){
            		position='right';
                    deltaX=70;
            	}
                $("#system_setting_tab :input[name='"+config_name+"']").tooltip({
                	position:position,
                	deltaX:deltaX,
                    content: '<span style="color:#000">点击这里,设置<?php echo ($info); ?></span>',
                    showEvent:'',
                    hideEvent:'mousedown',
                    onShow: function(){
                        $(this).tooltip('tip').css({
                            backgroundColor: '#FFFF66',
                           // borderColor: '#666'
                        });
                        if(typeof onCloseMenuDialog == 'function'){
                            var dialog_old_onclose =  onCloseMenuDialog;
                        }else{
                            var dialog_old_onclose = function(){};
                        }

                        onCloseMenuDialog = function(){
                            dialog_old_onclose.apply(this);
                            if($("#system_setting_tab :input[name='"+config_name+"']").length>0){
                                $("#system_setting_tab :input[name='"+config_name+"']").tooltip('hide');
                            }
                        };
                    },
                });
                if(config_name!='auto_check_is_open'){
                	$("#system_setting_tab :input[name='"+config_name+"']").tooltip({
                		onPosition: function(){
                        	$(this).tooltip('tip').css('left', $(this).offset().left);
                            $(this).tooltip('arrow').css('left', 20);
                        }
                	});
                }
                $("#system_setting_tab :input[name='"+config_name+"']").tooltip('show');

                $($('#system_setting_tab').tabs('getTab',tab_type)).animate({scrollTop:0},1000)
            }
            $('.setting_cover').css('display','none');

            //初始化系统设置数据时 是否维护库存 和 商家编码生成规则的联动值。 （是否维护库存后添加,所以需要根据商家编码生成规则去选择是否维护库存）
            //是否维护库存来确定  自动生成系统货品的配置是否可以勾选
            var sys_control_stock = $('#sys_control_stock');
            var un_control_stock_data = [{'id':'2','name':'货品ID+唯一ID'},{'id':'3','name':'货品商家编码+唯一ID'}];
            var control_stock_data = [{'id':'0','name':'规格商家编码'},{'id':'1','name':'货品商家编码+规格商家编码'}];
            var sys_control_stock_data = sys_control_stock.combobox('getData');
            var sys_goods_match_concat_code = $('#sys_goods_match_concat_code');
            if(sys_goods_match_concat_code.val()<2)
            {
                sys_control_stock.combobox('select',sys_control_stock_data[1].id);
                sys_goods_match_concat_code.combobox('loadData',control_stock_data);
                $('#sys_goods_auto_make').attr('disabled',true);
            }else if(sys_goods_match_concat_code.val()>=2){
                $('#sys_goods_auto_make').attr('disabled',false);
                sys_control_stock.combobox('select',sys_control_stock_data[0].id);
                sys_goods_match_concat_code.combobox('loadData', un_control_stock_data);

            }
        },0);
        //给是否管理库存下拉框添加onChange事件。
        $('#sys_control_stock').combobox({
            onChange: function(n,o)
            {
                var sys_goods_match_concat_code = $('#sys_goods_match_concat_code');
                var data = sys_goods_match_concat_code.combobox('getValue');
                if(n==1)//维护库存
                {
                    var control_stock_data = [{'id':'0','name':'规格商家编码'},{'id':'1','name':'货品商家编码+规格商家编码'}];
                    if(data>1)
                    {
                        sys_goods_match_concat_code.combobox('select',control_stock_data[0].id);
                    }
                    sys_goods_match_concat_code.combobox('loadData',control_stock_data);
                    $('#sys_goods_auto_make').prop('checked',false);
                    $('#sys_goods_auto_make').attr('value',0);
                    $('#sys_goods_auto_make').attr('disabled',true);
                }else if(n==0){
                    var un_control_stock_data = [{'id':'2','name':'货品ID+唯一ID'},{'id':'3','name':'货品商家编码+唯一ID'}];
                    if(data<2)
                    {
                        sys_goods_match_concat_code.combobox('select',un_control_stock_data[0].id);
                    }
                    sys_goods_match_concat_code.combobox('loadData', un_control_stock_data);
                    $('#sys_goods_auto_make').attr('disabled',false);
                }

            }
        })
    });
    //checkbox的onclick事件    
	function systemCheckboxOnclick(that) {

        var value = that.value;
			if (value == 0) {
				that.checked = true;
				that.value = 1;
			} else {
				that.checked = false;
				that.value = 0;
			}
        if(that.name == 'dynamic_allocation_box'){
            $.post("<?php echo U('Purchase/SortingWall/getSortingBoxIsFinish');?>", {value:value}, function (res) {
                switch (res.value){
                    case '0':
                        if(res.count!=0){
                            that.checked = false;
                            that.value = 0;
                            messager.alert('分拣框正在使用中，请处理完所有档口订单再切换至生成动态分拣框模式');
                        }
                        break;
                    case '1':
                        if(res.count!=0){
                            that.checked = true;
                            that.value = 1;
                            messager.alert('分拣框正在使用中，请处理完所有档口订单再切换至一般分拣框模式');
                        }
                        break;
                }
            });
        }
        if(that.name.search('sys_available_stock') != -1)
        {
            var available_stock_elements = $('#reason_show_dialog' + " :input[name^='sys_available_stock']");
            var available_value = 0;
            available_stock_elements.each(function(){
                var split_ar = $(this).attr('name').split('-');
                if($(this).prop('checked')){
                    available_value= parseInt(available_value) | Math.pow(2,parseInt(split_ar[1]));
                }
            });
            $('#reason_show_dialog'+" :input[name='sys_available_stock']").attr('value',available_value);
        }
        if(that.name.search('sys_available_purchase') != -1)
        {
            var available_purchase_elements = $('#reason_show_dialog' + " :input[name^='sys_available_purchase']");
            var available_value = 0;
            available_purchase_elements.each(function(){
                var split_ar = $(this).attr('name').split('-');
                if($(this).prop('checked')){
                    available_value= parseInt(available_value) | Math.pow(2,parseInt(split_ar[1]));
                }
            });
            $('#reason_show_dialog'+" :input[name='sys_available_purchase']").attr('value',available_value);
        }
        if(that.name == 'order_check_get_waybill'){

            if(that.value == 1){
                $('input:checkbox[name="order_check_synchronous_logistics"]').prop('disabled',false);
            }else {
                $('input:checkbox[name="order_check_synchronous_logistics"]').prop('checked',false);
                $('input:checkbox[name="order_check_synchronous_logistics"]').attr('value',0);
                $('input:checkbox[name="order_check_synchronous_logistics"]').prop('disabled',true);
            }
        }
	}

    //提交表单
    function submitMenuDialog() {
        var data = {};
        //获取表单的value值
        $("#reason_show_dialog input").each(function () {
            var that = this;
            if(that.name.search('sys_available_stock') !=-1){
                if(that.name !='sys_available_stock'){
                    return;
                }else{
                    data[that.name] = that.value;
                }
            }
            if(that.name.search('sys_available_purchase') !=-1){
                if(that.name !='sys_available_purchase'){
                    return;
                }else{
                    data[that.name] = that.value;
                }
            }
            if(that.type=="radio"){
            	data[that.name]=$("input[name='"+that.name+"']:checked").val();
            	return;
            }
            if (typeof(that.name) != "undefined" && that.name != "" && that.name != 0) {
                data[that.name] = that.value;
            }
        });
        //发送数据
        var url = "<?php echo U('System/updateSystemSetting');?>";
        $.post(url, {"data": data}, function (res) {
            if (res.status) {
                //messager.alert(res.info, "info");
            } else {
                messager.alert(res.info);
            }
        });
    }
    //关闭档口模式
    function hideStallsBtn(){
        var stalls_len = $('#stalls_system_setting input').length;
        $('#stalls_system_setting input').each(function(index){
            var that = this;
            if(that.type == 'checkbox'){
                that.checked = false;
                that.value = 0;
            }
            if(index == stalls_len - 1){
                $.post("<?php echo U('System/hideStallsSetting');?>", '', function (res) {
                    if (res.status) {
                        $("a[data-url='档口单管理']").parent().remove();
                        $("a[data-url='档口缺货明细']").parent().remove();
                        $("a[data-url='档口货品对账']").parent().remove();
                        $("a[data-url='档口采购员账单']").parent().remove();
                        $("a[data-url='历史采购员账单']").parent().remove();
                        messager.alert(res.info);
                        $('#reason_show_dialog').dialog('close');
                    }else{
                        messager.alert(res.info);
                    }
                });
                submitMenuDialog();
            }
        });
    }
</script>