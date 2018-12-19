<?php if (!defined('THINK_PATH')) exit();?><div style="margin: 20px 15px;">
    <div class="easyui-panel" title="新手引导" data-options="headerCls:'panel-header-title'" style="width:100%;style="margin-top: 20px;"">
        <div class="process">
            <div class="title">基本设置</div>
            <div class="content">
                <div class="pmenu">
                    <ul>
                        <li><a href="javascript:void(0);" onClick="open_menu('<?php echo ($setting["logistics"]["text"]); ?>', '<?php echo ($setting["logistics"]["href"]); ?>')"><?php echo ($setting["logistics"]["text"]); ?></a></li>
                        <li><a href="javascript:void(0);" onClick="open_menu('<?php echo ($setting["setting"]["text"]); ?>', '<?php echo ($setting["setting"]["href"]); ?>')"><?php echo ($setting["setting"]["text"]); ?></a></li>
                    </ul>
                </div>
                <div class="arrow-img1"><img src="/Public/Image/arrow_2.png"></div>
                <div class="arrow-img2"><img src="/Public/Image/arrow_1.png"></div>
                <div class="pmenu">
                    <ul>
                        <li class="p2"><a href="javascript:void(0);" onClick="open_menu('<?php echo ($setting["shop"]["text"]); ?>', '<?php echo ($setting["shop"]["href"]); ?>')"><?php echo ($setting["shop"]["text"]); ?></a></li>
                    </ul>
                </div>
                <div class="arrow-img2"><img src="/Public/Image/arrow_1.png"></div>
                <div class="pmenu">
                    <ul>
                        <li class="p2"><a href="javascript:void(0);" onClick="open_menu('<?php echo ($setting["warehouse"]["text"]); ?>', '<?php echo ($setting["warehouse"]["href"]); ?>')"><?php echo ($setting["warehouse"]["text"]); ?></a></li>
                    </ul>
                </div>
                <div class="arrow-img2"><img src="/Public/Image/arrow_1.png"></div>
                <div class="arrow-img1"><img src="/Public/Image/arrow_4.png"></div>
                <div class="pmenu">
                    <ul>
                        <li><a href="javascript:void(0);" onClick="open_menu('<?php echo ($setting["g_template"]["text"]); ?>', '<?php echo ($setting["g_template"]["href"]); ?>')"><?php echo ($setting["g_template"]["text"]); ?></a></li>
                        <li><a href="javascript:void(0);" onClick="open_menu('<?php echo ($setting["l_template"]["text"]); ?>', '<?php echo ($setting["l_template"]["href"]); ?>')"><?php echo ($setting["l_template"]["text"]); ?></a></li>
                    </ul>
                </div>
                <div class="arrow-img1"><img src="/Public/Image/arrow_2.png"></div>
                <div class="arrow-img2"><img src="/Public/Image/arrow_1.png"></div>
                <div class="pmenu">
                    <ul>
                        <li class="p2"><a href="javascript:void(0);" onClick="open_menu('<?php echo ($setting["employee"]["text"]); ?>', '<?php echo ($setting["employee"]["href"]); ?>')"><?php echo ($setting["employee"]["text"]); ?></a></li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="process">
            <div class="title">货品关系</div>
            <div class="content">
                <div class="pmenu">
                    <ul>
                        <li><a href="javascript:void(0);" onClick="open_menu('<?php echo ($goods["class"]["text"]); ?>', '<?php echo ($goods["class"]["href"]); ?>')"><?php echo ($goods["class"]["text"]); ?></a></li>
                        <li><a href="javascript:void(0);" onClick="open_menu('<?php echo ($goods["brand"]["text"]); ?>', '<?php echo ($goods["brand"]["href"]); ?>')"><?php echo ($goods["brand"]["text"]); ?></a></li>
                    </ul>
                </div>
                <div class="arrow-img1"><img src="/Public/Image/arrow_2.png"></div>
                <div class="arrow-img2"><img src="/Public/Image/arrow_1.png"></div>
                <div class="pmenu">
                    <ul>
                        <li class="p2"><a href="javascript:void(0);" onClick="open_menu('<?php echo ($goods["goods"]["text"]); ?>', '<?php echo ($goods["goods"]["href"]); ?>')"><?php echo ($goods["goods"]["text"]); ?></a></li>
                    </ul>
                </div>
                <div class="arrow-img2"><img src="/Public/Image/arrow_1.png"></div>
                <div class="arrow-img1"><img src="/Public/Image/arrow_4.png"></div>
                <div class="pmenu">
                    <ul>
                        <li title="匹配平台货品"><a href="javascript:void(0);" onClick="open_menu('<?php echo ($goods["spec"]["text"]); ?>', '<?php echo ($goods["spec"]["href"]); ?>')"><?php echo ($goods["spec"]["text"]); ?></a></li>
                        <li title="匹配平台货品"><a href="javascript:void(0);" onClick="open_menu('<?php echo ($goods["suite"]["text"]); ?>', '<?php echo ($goods["suite"]["href"]); ?>')"><?php echo ($goods["suite"]["text"]); ?></a></li>
                    </ul>
                </div>
                <div class="arrow-img1"><img src="/Public/Image/arrow_2.png"></div>
                <div class="arrow-img2"><img src="/Public/Image/arrow_1.png"></div>
                <div class="pmenu">
                    <ul>
                        <li class="p2"><a href="javascript:void(0);" onClick="open_menu('<?php echo ($goods["platform"]["text"]); ?>', '<?php echo ($goods["platform"]["href"]); ?>')"><?php echo ($goods["platform"]["text"]); ?></a></li>
                    </ul>
                </div>
                <div class="arrow-img2"><img src="/Public/Image/arrow_6.png"></div>
                <div class="pmenu">
                    <ul>
                        <li class="p2" title="递交产生平台货品"><a href="javascript:void(0);" onClick="open_menu('<?php echo ($trade["original"]["text"]); ?>', '<?php echo ($trade["original"]["href"]); ?>')"><?php echo ($trade["original"]["text"]); ?></a></li>
                </div>
            </div>
        </div>
        <div class="process">
            <div class="title">库存关系</div>
            <div class="content">
                <div class="pmenu">
                    <ul>
                        <li class="p2"><a href="javascript:void(0);" onClick="open_menu('<?php echo ($stock["in"]["text"]); ?>', '<?php echo ($stock["in"]["href"]); ?>')"><?php echo ($stock["in"]["text"]); ?></a></li>
                    </ul>
                </div>
                <div class="arrow-img2"><img src="/Public/Image/arrow_1.png"></div>
                <div class="pmenu">
                    <ul>
                        <li class="p2"><a href="javascript:void(0);" onClick="open_menu('<?php echo ($stock["in_m"]["text"]); ?>', '<?php echo ($stock["in_m"]["href"]); ?>')"><?php echo ($stock["in_m"]["text"]); ?></a></li>
                    </ul>
                </div>
                <div class="arrow-img2"><img src="/Public/Image/arrow_1.png"></div>
                <div class="pmenu">
                    <ul>
                        <li class="p2" title="可以直接导入库存"><a href="javascript:void(0);" onClick="open_menu('<?php echo ($stock["manage"]["text"]); ?>', '<?php echo ($stock["manage"]["href"]); ?>')"><?php echo ($stock["manage"]["text"]); ?></a></li>
                    </ul>
                </div>
                <div class="arrow-img2"><img src="/Public/Image/arrow_1.png"></div>
                <div class="arrow-img1"><img src="/Public/Image/arrow_4.png"></div>
                <div class="pmenu">
                    <ul>
                        <li><a href="javascript:void(0);" onClick="open_menu('<?php echo ($stock["transfer"]["text"]); ?>', '<?php echo ($stock["transfer"]["href"]); ?>')"><?php echo ($stock["transfer"]["text"]); ?></a></li>
                        <li><a href="javascript:void(0);" onClick="open_menu('<?php echo ($stock["out"]["text"]); ?>', '<?php echo ($stock["out"]["href"]); ?>')"><?php echo ($stock["out"]["text"]); ?></a></li>
                    </ul>
                </div>
                <div class="pmenu">
                    <ul>
                        <li><img src="/Public/Image/arrow_1.png"></li>
                        <li><img src="/Public/Image/arrow_1.png"></li>
                    </ul>
                </div>
                <div class="pmenu">
                    <ul>
                        <li><a href="javascript:void(0);" onClick="open_menu('<?php echo ($stock["transfer_m"]["text"]); ?>', '<?php echo ($stock["transfer_m"]["href"]); ?>')"><?php echo ($stock["transfer_m"]["text"]); ?></a></li>
                        <li><a href="javascript:void(0);" onClick="open_menu('<?php echo ($stock["out_m"]["text"]); ?>', '<?php echo ($stock["out_m"]["href"]); ?>')"><?php echo ($stock["out_m"]["text"]); ?></a></li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="process">
            <div class="title">订单处理</div>
            <div class="content">
                <div class="pmenu">
                    <ul>
                        <li title="系统设置中可以开启自动下载"><a href="javascript:void(0);" onClick="open_menu('<?php echo ($trade["down"]["text"]); ?>', '<?php echo ($trade["down"]["href"]); ?>')"><?php echo ($trade["down"]["text"]); ?></a></li>
                    </ul>
                </div>
                <div class="arrow-img1"><img src="/Public/Image/arrow_1.png"></div>
                <div class="pmenu">
                    <ul>
                        <li><a href="javascript:void(0);" onClick="open_menu('<?php echo ($trade["original"]["text"]); ?>', '<?php echo ($trade["original"]["href"]); ?>')"><?php echo ($trade["original"]["text"]); ?></a></li>
                        <li><a href="javascript:void(0);" onClick="open_menu('<?php echo ($trade["manual"]["text"]); ?>', '<?php echo ($trade["manual"]["href"]); ?>')"><?php echo ($trade["manual"]["text"]); ?></a></li>
                    </ul>
                </div>
                <div class="arrow-img1"><img src="/Public/Image/arrow_2.png"></div>
                <div class="arrow-img2"><img src="/Public/Image/arrow_1.png"></div>
                <div class="arrow-img1"><img src="/Public/Image/arrow_4.png"></div>
                <div class="pmenu">
                    <ul>
                        <li><a href="javascript:void(0);" onClick="open_menu('<?php echo ($trade["check"]["text"]); ?>', '<?php echo ($trade["check"]["href"]); ?>')"><?php echo ($trade["check"]["text"]); ?></a></li>
                        <li><a href="javascript:void(0);" onClick="open_menu('<?php echo ($trade["manage"]["text"]); ?>', '<?php echo ($trade["manage"]["href"]); ?>')"><?php echo ($trade["manage"]["text"]); ?></a></li>
                    </ul>
                </div>
                <div class="arrow-img1"><img src="/Public/Image/arrow_1.png"></div>
                <div class="arrow-img1"><img src="/Public/Image/arrow_4.png"></div>
                <div class="pmenu">
                    <ul>
                        <li><a href="javascript:void(0);" onClick="open_menu('<?php echo ($trade["print"]["text"]); ?>', '<?php echo ($trade["print"]["href"]); ?>')"><?php echo ($trade["print"]["text"]); ?></a></li>
                        <li><a href="javascript:void(0);" onClick="open_menu('<?php echo ($trade["stock_out"]["text"]); ?>', '<?php echo ($trade["stock_out"]["href"]); ?>')"><?php echo ($trade["stock_out"]["text"]); ?></a></li>
                    </ul>
                </div>
                <div class="arrow-img1"><img src="/Public/Image/arrow_2.png"></div>
                <div class="arrow-img2"><img src="/Public/Image/arrow_1.png"></div>
                <div class="pmenu">
                    <ul>
                        <li class="p2"><a href="javascript:void(0);"  onClick="open_menu('<?php echo ($trade["refund"]["text"]); ?>', '<?php echo ($trade["refund"]["href"]); ?>')"><?php echo ($trade["refund"]["text"]); ?></a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>