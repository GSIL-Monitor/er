<?php if (!defined('THINK_PATH')) exit();?><div style="width:100%;height:auto;">
    <div class="easyui-panel" title="第一步------下载平台货品" collapsible="false" style="width:100%;height:auto;padding: 5px 20px;">
        <ul>
            <li style="list-style-type:none;">1：创建店铺</li>
            <li style="list-style-type:none;float: left;">2：下载平台货品</li>
            <a href="javascript:void(0)" style="float: right;" class="easyui-linkbutton next_link" data-options="iconCls:'icon-next',plain:true" onClick="click_menu('下载平台货品', '<?php echo U('Help/TradeProcess/platformProcess');?>')">前往</a>
            <div style="clear: both;"></div>
        </ul>
    </div>
    <div class="easyui-panel" title="第二步------初始化系统货品" collapsible="false" style="width:100%;height:auto;padding: 5px 20px;">
        <ul>
            <li style="list-style-type:none;">1：创建货品分类</li>
            <li style="list-style-type:none;">2：创建货品品牌</li>
            <li style="list-style-type:none;float: left;">3：创建货品档案</li>
            <a href="javascript:void(0)" style="float: right;" class="easyui-linkbutton next_link" data-options="iconCls:'icon-next',plain:true" onClick="click_menu('初始化系统货品', '<?php echo U('Help/TradeProcess/goodsProcess');?>')">前往</a>
            <div style="clear: both;"></div>
        </ul>
    </div>
    <div class="easyui-panel" title="第三步------初始化物流和库存" collapsible="false" style="width:100%;height:auto;padding: 5px 20px;">
        <ul >
            <li style="list-style-type:none;">1：创建物流</li>
            <li style="list-style-type:none;">2：创建仓库</li>
            <li style="list-style-type:none;float: left;">3：初始化库存</li>
            <a href="javascript:void(0)" style="float: right;" class="easyui-linkbutton next_link" data-options="iconCls:'icon-next',plain:true" onClick="click_menu('初始化物流和库存', '<?php echo U('Help/TradeProcess/stockProcess');?>')">前往</a>
            <div style="clear: both;"></div>
        </ul>
    </div>
    <div class="easyui-panel" title="第四步------策略设置（该步设置好将大大提高效率哦）" collapsible="false" style="width:100%;height:auto;padding: 5px 20px;">
        <ul >
            <li style="list-style-type:none;float: left;">1：选仓策略</li>
            <li style="list-style-type:none;padding-left: 30px;">　　　4：备注提取</li>
            <li style="list-style-type:none;float: left;">2：物流匹配</li>
            <li style="list-style-type:none;margin-right: 30px;">　　　5：赠品策略</li>
            <li style="list-style-type:none;float: left;">3：物流资费</li>
            <a href="javascript:void(0)" style="float: right;" class="easyui-linkbutton next_link" data-options="iconCls:'icon-next',plain:true" onClick="click_menu('策略设置', '<?php echo U('Help/TradeProcess/ruleProcess');?>')">前往</a>
            <div style="clear: both;"></div>
        </ul>
    </div>
</div>