<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo C('SYSTEM_NAME');?></title>
<link rel="stylesheet" type="text/css" href="/Public/Css/introjs.css"/>
<link rel='stylesheet' type='text/css' href='/Public/Css/common.css?v=<?php echo ($version_number); ?>'>
<link rel='stylesheet' type='text/css' href='http://cdn.bootcss.com/font-awesome/4.7.0/css/font-awesome.min.css'>
<link rel="stylesheet" type="text/css" href="/Public/Css/easyui.css">
<link rel="stylesheet" type="text/css" href="/Public/Css/icon.css?v=<?php echo ($version_number); ?>">
<link rel="stylesheet" type="text/css" href="/Public/Css/table.css?v=<?php echo ($version_number); ?>">
<link rel="stylesheet" type="text/css" href="/Public/Css/error.css?v=<?php echo ($version_number); ?>">
<link rel="stylesheet" type="text/css" href="/Public/Css/process.css?v=<?php echo ($version_number); ?>">
<link rel="icon" href="/Public/Image/favicon.ico"  type="image/x-icon">
<script type="text/javascript" src="/Public/Js/jquery.min.js"></script>
<script type="text/javascript" src="/Public/Js/jquery.md5.js"></script>
<!--[if lt IE 8]> <script type="text/javascript" src="/Public/Js/json.2.js?v=<?php echo ($version_number); ?>"></script> <![endif]-->
<script type="text/javascript" src="/Public/Js/jquery.easyui.min.js"></script>
<script type="text/javascript" src="/Public/Js/easyui-lang-zh_CN.js?v=<?php echo ($version_number); ?>"></script>
<script type="text/javascript" src="/Public/Js/icolor.picker.js"></script>
<script type="text/javascript" src="/Public/Js/intro.js"></script>
<script type="text/javascript" src="/Public/Js/jquery.extends.js?v=<?php echo ($version_number); ?>"></script>
<script type="text/javascript" src="/Public/Js/easyui.extends.js?v=<?php echo ($version_number); ?>"></script>
<script type="text/javascript" src="/Public/Js/erp.util.js?v=<?php echo ($version_number); ?>"></script>
<script type="text/javascript" src="/Public/Js/menu.util.js?v=<?php echo ($version_number); ?>"></script>
<script type="text/javascript" src="/Public/Js/rich-datagrid.util.js?v=<?php echo ($version_number); ?>"></script>
<script type="text/javascript" src="/Public/Js/thin-datagrid.util.js?v=<?php echo ($version_number); ?>"></script>
<script type="text/javascript" src="/Public/Js/area.js?v=<?php echo ($version_number); ?>"></script>
<script type="text/javascript" src="/Public/Js/datalist.util.js?v=<?php echo ($version_number); ?>"></script>
<script type="text/javascript" src="/Public/Js/jquery.zclip.min.js"></script>
<script type="text/javascript" src="http://g.tbcdn.cn/sj/securesdk/0.0.3/securesdk_v2.js" id="J_secure_sdk_v2" data-appkey="23305776"></script>
<script type="text/javascript" src="/Public/Js/echarts.common.min.js"></script>
<!-- <script type="text/javascript" src="/Public/Js/jquery.cookie.js"></script> -->
</head>
<body class="easyui-layout">
<div id="header" data-options="region:'north',border:false" style="padding-left: 17px;margin-bottom: 5px;">
    <a href=""><img src="/Public/Image/logo.png?v=<?php echo ($version_number); ?>" style="float: left;margin: 20px 15px 10px 0px;padding-left:17px;padding-right:17px;"/></a>
	<?php if(is_array($menu_list)): $i = 0; $__LIST__ = $menu_list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$menu): $mod = ($i % 2 );++$i;?><div class="nav-menu <?php echo ($menu["module"]); ?>">
			<div>
				<a href="javascript:void(0);" style="display: block"   >
					<img src="/Public/Image/head/<?php echo ($menu["img"]); ?>" />
					<span style="text-align: center;width: 36px;height:auto;display:block;font-family:'微软雅黑';color:#FFF;font-size: small;font-weight: normal;"><?php echo ($menu["text"]); ?></span>
				</a>
			</div>
	<div class="nav-pos" style="position: relative;"></div>
</div><?php endforeach; endif; else: echo "" ;endif; ?>
	<div style="float:right;margin-top: 18px;margin-right: 25px;">
	<!--<marquee behavior="slide" direction="down"  onmouseover="this.stop()" onmouseout="this.start()"><?php echo ($note); ?></marquee>-->
		<span class="navr form-div">
			<!--<div style="float:left;" class="day_bell">
				<a href="javascript:void(0);"  is_leaf="1" url = "index.php/Purchase/AlarmPerDay/getStockAlarmList" title = "每日预警" >
					<i class="fa fa-bell" style="font-size: 20px;"></i>
					<span style="margin-left: 17px;"></span>
				</a>
			</div>-->
			<!--common_menu-->
			<div id="alarm" style="float:left;margin-top: 12px;margin-right: 5px;display:none; cursor:pointer;"><img src="/Public/Image/bell.png" onclick="showAlarm()"  /></div>
			
			<span id="user-menu" style="margin-right:-6px;padding:1px 2px;color:#FFF;" ><?php echo ((null !== (session('account')) && (session('account') !== ""))?(session('account')):'merchant'); ?></span>
			<span><a href="<?php echo U('Home/Login/login');?>?type=logout">[ 退出 ]</a></span>
			<span><img style="float:right; margin-top: 10px;cursor: pointer;" src="/Public/Image/zx.png" onclick="sendToQQ()" title="点击联系客服"/></span>
			<div class="clear-float"></div>
		</span>
	</div>
    <div style="clear: both"></div>
</div>
<div data-options="region:'center'">
	<div class="easyui-tabs" id="container" data-options="border:false,plain:true,fit:true" >
		<div title="欢迎" style="padding:10px;font-family: '微软雅黑';background: #eee;" data-options="tools:[{iconCls:'icon-mini-refresh', handler:refreshNote}]">
<!-- 			<div class="module"> -->
<!-- 				<p class="index-title"><i class="fa fa-bookmark"></i> 待办事项:</p> -->
<!-- 				<div style="border-radius: 2px;margin-left: 20px;margin-top: 5px;"> -->
<!-- 					<div class="matter-header"onclick="showNotAuthorizeShop()" style="background: #3399CC;margin-left: 0;"> -->
<!-- 						<p><i class="fa fa-institution"></i></p> -->
<!-- 						<p>店铺未授权(<span id="shop_not_authorize"><?php echo ($todo['shop_not_authorize']); ?></span>)</p> -->
<!-- 					</div> -->
<!-- 					<div class="matter-header" onclick="showInvalidGoods()" style="background: #66CC99;"> -->
<!-- 						<p><i class="fa fa-desktop"></i></p> -->
<!-- 						<p>货品未匹配(<span id="not_mate_goods"><?php echo ($todo['not_mate_goods']); ?></span>)</p> -->
<!-- 					</div> -->
<!-- 					<div class="matter-header" data-stock-no="fast_is_blocked" data-title="单据打印" data-url="index.php/Stock/StockSalesPrint/getPrintList?stockout_no=fast_is_blocked" style="background: #FFCC33;"> -->
<!-- 						<p><i class="fa fa-file-text-o"></i></p> -->
<!-- 						<p>拦截订单(<span id="fast_is_blocked"><?php echo ($todo['fast_is_blocked']); ?></span>)</p> -->
<!-- 					</div> -->
<!-- 					<div class="matter-header" data-stock-no="fast_stockout_not_printed" data-title="单据打印" data-url="index.php/Stock/StockSalesPrint/getPrintList?stockout_no=fast_stockout_not_printed" style="background: #9966CC;"> -->
<!-- 						<p><i class="fa fa-print"></i></p> -->
<!-- 						<p>未打印物流单(<span id="fast_stockout_not_printed"><?php echo ($todo['fast_stockout_not_printed']); ?></span>)</p> -->
<!-- 					</div> -->
<!-- 					<div class="matter-header" data-stock-no="fast_printed_not_stockout" data-title="单据打印" data-url="index.php/Stock/StockSalesPrint/getPrintList?stockout_no=fast_printed_not_stockout" style="background: #CC9999;"> -->
<!-- 						<p><i class="fa fa-bus"></i></p> -->
<!-- 						<p>打印未发货(<span id="fast_printed_not_stockout"><?php echo ($todo['fast_printed_not_stockout']); ?></span>)</p> -->
<!-- 					</div> -->
<!-- 					<div class="matter-header" data-title="每日预警" data-url="index.php/Purchase/AlarmPerDay/getStockAlarmList" style="background: #FF9966;"> -->
<!-- 						<p><i class="fa fa-bell"></i></p> -->
<!-- 						<p>库存预警(<span id="alarmperday"><?php echo ($todo['alarmperday']); ?></span>)</p> -->
<!-- 					</div> -->
<!-- 					<div class="matter-header" data-title="物流同步" data-url="index.php/Stock/ApiLogisticsSync/getApiLogisticsSyncList?sync_status=2" style="background: #FF6666;"> -->
<!-- 						<p><i class="fa fa-truck"></i></p> -->
<!-- 						<p>物流同步失败(<span id="logistics_count"><?php echo ($todo['logistics_count']); ?></span>)</p> -->
<!-- 					</div> -->
<!-- 					<div class="clear"></div> -->
<!-- 				</div> -->
<!-- 			</div> -->
			<div class="module" id="newcomer" style="display: none;">
				<p class="index-title"><i class="fa fa-info-circle "></i> 新手任务:<span class="module-tag" onclick="newcomer_change(0)">关闭新手模式</span></p>
				<div style="margin-left: 20px;">
					<div class="newcomer">
						<li onclick="open_menu('下载平台货品', '<?php echo U('Help/TradeProcess/platformProcess');?>')">Step 1</li>
                		<?php if(is_array($platform)): $i = 0; $__LIST__ = $platform;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$platfrom): $mod = ($i % 2 );++$i;?><li1 onClick="open_menu('<?php echo ($platfrom["text"]); ?>', '<?php echo ($platfrom["href"]); ?>')"><?php echo ($platfrom["msg"]); ?></li1><br><?php endforeach; endif; else: echo "" ;endif; ?>
                		<br>
					</div>
					<div class="arrow">
						<i class="fa fa-arrow-right "></i>
					</div>
					<div class="newcomer">
						<li onclick="open_menu('初始化系统货品', '<?php echo U('Help/TradeProcess/goodsProcess');?>')">Step 2</li>
						<?php if(is_array($goods)): $i = 0; $__LIST__ = $goods;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$goods): $mod = ($i % 2 );++$i;?><li1 onClick="open_menu('<?php echo ($goods["text"]); ?>', '<?php echo ($goods["href"]); ?>')"><?php echo ($goods["msg"]); ?></li1><br><?php endforeach; endif; else: echo "" ;endif; ?>
					</div>
					<div class="arrow">
						<i class="fa fa-arrow-right "></i>
					</div>
					<div class="newcomer">
						<li onclick="open_menu('初始化物流和库存', '<?php echo U('Help/TradeProcess/stockProcess');?>')">Step 3</li>
						<?php if(is_array($stock)): $i = 0; $__LIST__ = $stock;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$stock): $mod = ($i % 2 );++$i;?><li1 onClick="open_menu('<?php echo ($stock["text"]); ?>', '<?php echo ($stock["href"]); ?>')"><?php echo ($stock["msg"]); ?></li1><br><?php endforeach; endif; else: echo "" ;endif; ?>
					</div>
					<div class="arrow">
						<i class="fa fa-arrow-right "></i>
					</div>
					<div class="newcomer">
						<li style="color:#0099c9;cursor: auto;">Step 4</li>
						<li1 style="margin-left:40px;" onclick="open_menu('策略设置', '<?php echo U('Help/TradeProcess/ruleProcess');?>')">策略设置</li1><br>
						<li1 style="margin-left:40px;" onclick="open_menu('高级设置', '<?php echo U('Help/TradeProcess/advancedSetting');?>')">高级设置</li1>
						<br><br>
					</div>
					<div class="arrow">
						<i class="fa fa-arrow-right "></i>
					</div>
					<div class="arrow" style="cursor: pointer;color:#0099cc;" onclick="open_menu('订单处理', '<?php echo U('Help/TradeProcess/getTradeProcess');?>')">
						<i class="fa fa-hand-peace-o" style="font-size: 50px;"></i>
						<div style="font-size: 15px;font-weight: bold;" >任务完成<br>处理订单吧</div>
					</div>
				</div>
			</div>
			<div class="module" id="trade_num" >
				<div class="index_title">
					<div class="index-vertical-line"></div><span class="index_block_title">工作台</span><span class="module-tag" onclick="newcomer_change(1)">切换新手模式</span>
					<div class="clear-float"></div>
				</div>
				<div class="module_all">
					<div class="module_sub_left">
						<div class="module_sub_title" id="index-sub-title">常用操作<img src="/Public/Image/icon_edit.png" style="float: right;cursor: pointer;" onclick="setCommonUse()" /></div>
						<div id="common_menu_div">
							<?php if(count($common_menu) > 0): ?><div class="common-operation" name='index.php/<?php echo ($common_menu[0]["module"]); ?>/<?php echo ($common_menu[0]["controller"]); ?>/<?php echo ($common_menu[0]["action"]); ?>-<?php echo ($common_menu[0]["type"]); ?>-<?php echo ($common_menu[0]["text"]); ?>' style="border-color: #57c157;" onclick="open_this_menu($(this))"><div class="menu_text"><?php echo ($common_menu[0]["text"]); ?></div></div>
								<?php else: ?>
								<div class="common-operation" style="border:0;"><img src="/Public/Image/menu_add.png" class="module_add_menu" onclick="setCommonUse()" title="点击添加常用菜单"/></div><?php endif; ?>
							<?php if(count($common_menu) > 1): ?><div class="common-operation" name='index.php/<?php echo ($common_menu[1]["module"]); ?>/<?php echo ($common_menu[1]["controller"]); ?>/<?php echo ($common_menu[1]["action"]); ?>-<?php echo ($common_menu[1]["type"]); ?>-<?php echo ($common_menu[1]["text"]); ?>' style="border-color: #29b6f6;" onclick="open_this_menu($(this))"><div class="menu_text"><?php echo ($common_menu[1]["text"]); ?></div></div>
								<?php else: ?>
								<div class="common-operation" style="border:0;"><img src="/Public/Image/menu_add.png" class="module_add_menu" onclick="setCommonUse()" title="点击添加常用菜单"/></div><?php endif; ?>
							<?php if(count($common_menu) > 2): ?><div class="common-operation" name='index.php/<?php echo ($common_menu[2]["module"]); ?>/<?php echo ($common_menu[2]["controller"]); ?>/<?php echo ($common_menu[2]["action"]); ?>-<?php echo ($common_menu[2]["type"]); ?>-<?php echo ($common_menu[2]["text"]); ?>' style="border-color: #ffa550;" onclick="open_this_menu($(this))"><div class="menu_text"><?php echo ($common_menu[2]["text"]); ?></div></div>
								<?php else: ?>
								<div class="common-operation" style="border:0;"><img src="/Public/Image/menu_add.png" onclick="setCommonUse()" title="点击添加常用菜单"/></div><?php endif; ?>
							<?php if(count($common_menu) > 3): ?><div class="common-operation" name='index.php/<?php echo ($common_menu[3]["module"]); ?>/<?php echo ($common_menu[3]["controller"]); ?>/<?php echo ($common_menu[3]["action"]); ?>-<?php echo ($common_menu[3]["type"]); ?>-<?php echo ($common_menu[3]["text"]); ?>' style="border-color: #ff745a;" onclick="open_this_menu($(this))"><div class="menu_text"><?php echo ($common_menu[3]["text"]); ?></div></div>
								<?php else: ?>
								<div class="common-operation" style="border:0;"><img src="/Public/Image/menu_add.png" onclick="setCommonUse()" title="点击添加常用菜单"/></div><?php endif; ?>
						</div>
					</div>
					<div class="module_sub_right">
						<div class="module_sub_title">代办事项</div>
						<table class="matter-header">
							<tr>
								<td onclick="gotoApiLogisticsInfo()">
									<a>物流同步失败</a>
									<p id="logistics_count" style="color:#ffa550"><?php echo ($todo['logistics_count']); ?></p>
								</td>
								<td onclick="showInvalidGoods()">
									<a>货品未匹配</a>
									<p id="not_mate_goods" style="color:#57c157"><?php echo ($todo['not_mate_goods']); ?></p>
								</td>
								<td onclick="showNotAuthorizeShop()">
									<a>店铺未授权数</a>
									<p id="shop_not_authorize" style="color:#29b6f6"><?php echo ($todo['shop_not_authorize']); ?></p>
								</td>
							</tr>
						</table>
					</div>
					<div class="clear-float"></div>
				</div>

				<div class="trade-title">
					<div class="module_sub_title" id="trade_module">销售数据分析</div>
					<div style="position: relative;margin-left: 15px;margin-top: 20px;">
						<table class="trade-button">
							<tr>
								<td onclick="reloadChart(7)">7日</td>
								<td onclick="reloadChart(15)">15日</td>
								<td onclick="reloadChart(30)">30日</td>
							</tr>

						</table>
						<div id="trade_chart" style="height:320px;"></div>
					</div>
				</div>
			</div>
			<div style="margin-top:0;float:right;margin-right: 0;width: 22%;background: #fff;padding:10px 15px;">
				<div class="index_title"><div class="index-vertical-line"></div><span class="index_block_title">账户余额</span></div>
					<!--<span class="module-tag" onclick="setAlarm()"><i class="fa fa-bell"></i> 设置提醒</span></div>-->
				<div class="balance">
					<div class="balance-body">
						<table>
							<tr style="border-bottom: 1px solid #eeeeee">
								<td style="width: 60%; text-align: left;"><p>淘宝电子面单余额</p>
									<span class="index-count">
										<?php if($todo['electronic_sheet_number'] == '查询失败'): ?><span style="color: #bfbfbf;">&nbsp;--</span>&nbsp;<span style="color: #f15151; font-size:14px;">[查询失败]</span>
											<?php elseif($alarm_num['waybill'] == '1'): ?>
												<span style="color: #f15151;"><?php echo ($todo['electronic_sheet_number']); ?></span>&nbsp;<span style="color: #f15151; font-size:14px;">[余额不足]</span>
											<?php else: ?>
												<span style="color: #57c157;"><?php echo ($todo['electronic_sheet_number']); ?></span><?php endif; ?>
									</span>
								</td>
								<td class="balance-button" onclick="showElectronicSheetDetial()"><img src="/Public/Image/details.png" /><br/>详情</td>
								<td class="balance-button" onclick="setAlarm('waybill_num_alarm','电子面单不足预警')"><img src="/Public/Image/shezhi.png" /><br/>设置</td>
							</tr>
							<tr>
								<td style="width: 60%; text-align: left"><p>短信余额</p>
									<span class="index-count">
										<?php if($todo['sms_number'] == '查询失败'): ?><span style="color: #bfbfbf;">---</span>&nbsp;<span style="color: #f15151; font-size:14px;">[查询失败]</span>
											<?php elseif($alarm_num['sms_num'] == 1): ?>
												<span style="color: #f15151;"><?php echo ($todo['sms_number']); ?></span>&nbsp;<span style="color: #f15151; font-size:14px;">[余额不足]</span>
											<?php else: ?>
												<span style="color: #29b6f6;"><?php echo ($todo['sms_number']); ?></span><?php endif; ?>
									</span>
								</td>
								<td class="balance-button" onclick="open_menu('短信发送查询','index.php/Customer/CustomerSMS/getCustomerSMSList')"><img src="/Public/Image/details.png" /><br/>详情</td>
								<td class="balance-button" onclick="setAlarm('sms_num_alarm','短信余额不足预警')"><img src="/Public/Image/shezhi.png" /><br/>设置</td>
							</tr>
							<?php if($order_cost != 0): ?><tr>
									<td style="width: 60%; text-align: left"><p>订单余额</p>
										<span class="index-count" style="color: #ffa550;">
											<?php if($order_hint == 1): ?><span style="color: #f15151;"><?php echo ($order_balance); ?></span>&nbsp;<span style="color: #f15151; font-size:14px;">[余额不足]</span>
											<?php else: ?>
												<span style="color: #29b6f6;"><?php echo ($order_balance); ?></span><?php endif; ?>
										</span>
									</td>
									<td class="balance-button" onclick="showOrderBalance()"><img src="/Public/Image/details.png" /><br/>详情</td>
									<td class="balance-button" onclick="setAlarm('order_balance','订单余额不足预警')"><img src="/Public/Image/shezhi.png" /><br/>设置</td>
								</tr><?php endif; ?>
						</table>
					</div>
				</div>
			</div>
			
			<div class="module" id="model_intro" style="margin-top: 10px;display: none;">
				<p class="index-title"><i class="fa fa-th-large"></i> 模块介绍:</p>
				<div class="model-body" style="margin-left:20px;width: 100%;height: 100px;">
					<div class="model" style="margin-left: 0;" data="1"><div class="model-inside" style="background:#3bc6d0;color: #fff;"><i class="fa fa-cogs"></i></div></div>
					<div class="model" data="2"><div class="model-inside"><i class="fa fa-cubes"></i></div></div>
					<div class="model" data="3"><div class="model-inside"><i class="fa fa-shopping-cart"></i></div></div>
					<div class="model" data="4"><div class="model-inside"><i class="fa fa-file-text-o"></i></div></div>
					<div class="model" data="5"><div class="model-inside"><i class="fa fa-home" style="font-size: 65px;"></i></div></div>
					<div class="model" data="6"><div class="model-inside"><i class="fa fa-jpy" style="font-size: 65px;"></i></div></div>
					<div class="model" data="7"><div class="model-inside"><i class="fa fa-bar-chart"></i></div></div>
					<div class="model" data="8"><div class="model-inside"><i class="fa fa-list"></i></div></div>
					<div class="clear"></div>
				</div>
				<div class="model-illustrate">
					<i class="fa fa-sort-asc hand-flag" aria-hidden="true"></i>
					<span style="display: block;margin-top: -30px;margin-left: 20px;">&nbsp;&nbsp;&nbsp;&nbsp;这里是设置模块，管理店铺基本数据，设置订单处理策略，创建打印单模板以及短信模板，管理员工权限。设置订单自动匹配货品仓库，自动匹配物流，物流资费策略等等。</span>
				</div>
			</div>
			<div style="float:right;margin-right: 0;margin-top:10px;;width: 22%;background: #fff;padding:10px 15px;">
				<div class="index_title"><div class="index-vertical-line"></div><span class="index_block_title">常见问题</span><span style="float: right;"><a href="<?php echo ($faq_url); ?>" style="color:#22b05f;width: 100%;height: 100%; font-weight: normal; font-size: 14px;" target="_blank">更多>></a></span></div>
				<div class="question">
					<div class="question-body">
						<ul>
							<li style="margin-top: 0;"><a href="<?php echo ($faq_url); ?>?type=product&class=product_question#recommended_browser" target="_blank">1. 建议使用谷歌chrome浏览器<span style="color: #22b05f;">[推荐]</span></a></li>
							<li><a href="<?php echo ($faq_url); ?>?type=product&class=product_taobao" target="_blank">2. E快帮淘宝授权流程</a></li>
							<li><a href="<?php echo ($faq_url); ?>?type=goods&class=goods_question#goods_question_add_goods" target="_blank">3. 店铺新上货品，要怎么在系统中添加对应的货品呢？</a></li>
							<li><a href="<?php echo ($faq_url); ?>?type=stock&class=logistics_sync_question#logistics_sync_question_number" target="_blank">4. 物流同步失败，后台也发不了货，提示“运单号不符合规则或已使用”怎么处理？</a></li>
							<li><a href="<?php echo ($faq_url); ?>?type=stock&class=stock_print#address_mismatch" target="_blank">5. 单据打印页面，确认发货时提示“订单发货地址与申请电子面单服务时填写的发货地址不匹配”？</a></li>
							<li><a href="<?php echo ($faq_url); ?>?type=stock&class=stock_print#stock_print_no_trade" target="_blank">6. 单据打印界面看不到订单？</a></li>
							<li><a href="<?php echo ($faq_url); ?>?type=setting&class=logistics_matching_example" target="_blank">7. 物流匹配说明及应用</a></li>
						</ul>
					</div>
				</div>
			</div>
			<div class="module" id="module_info" style="margin-top: 10px;margin-bottom: 10px;display: none;">
				<p class="index-title"><i class="fa fa-share-alt-square"></i> 流程展示:</p>
				<div style="margin:5px auto; width:100%;margin-left: 20px;" >
					<!--<table id="note-info" style="width:80%;height:200px;color:red;"> </table>-->
					<div style="float:left;"><div class="process-menu" style="display: none;"><a href="javascript:void(0);" onClick="show_list()">初始化流程</a></div></div>
					<div style="float:left;margin-left: 10px;"><div class="process-menu" style="display: none;"><a href="javascript:void(0);"  onClick="open_menu('订单处理', '<?php echo U('Help/TradeProcess/getTradeProcess');?>')">订单处理</a></div></div>
					<div style="float:left;margin-left: 10px;"><div class="process-menu" style="display: none;"><a href="javascript:void(0);" onClick="open_menu('退换流程', '<?php echo U('Help/TradeProcess/returnProcess');?>')">退换流程</a></div></div>
					<div style="float:left;margin-left: 10px;"><div class="process-menu" style="display: none;"><a href="javascript:void(0);" onClick="open_menu('盘点流程', '<?php echo U('Help/TradeProcess/checkProcess');?>')">盘点流程</a></div></div>
					<div style="float:left;margin-left: 10px;"><div class="process-menu" style="display: none;"><a style="width:120px;" href="javascript:void(0);" onClick="open_menu('退换预入库流程', '<?php echo U('Help/TradeProcess/returnPreProcess');?>')">退换预入库流程</a></div></div>
				</div>
			</div>
		</div>
	</div>
</div>
<div class="spirit">
	<span><i class="fa fa-question-circle"></i></span><a href="<?php echo ($faq_url); ?>" target="_blank">帮帮精灵</a>
</div>
<div class="small_e">
	<div class="small_span">我叫e小帮，不会的点我就好啦</div>
	<span><i class="fa fa-times" title="隐藏" style="font-size:20px;color:#0099c9;cursor:pointer;position: absolute;top: 0;right: -20px;"></i></span>
	<a  href="<?php echo ($faq_url); ?>" target="_blank"><img src="/Public/Image/Small_e.png" style="width: 100%;height: 100%;cursor: pointer;"></a>
</div>
<div id="first-in"></div>
<div id="address"></div>
<div id="flag_set_dialog"></div>
<div id="response_dialog"><table id="response_dialog_datagrid"></table></div>
<div id="reason_show_dialog"></div>
<div id="trade_exchange"></div>
<div id="invalid_goods"></div>
<div id="invalid_good_match"></div>
<div id="shop_auth_dialog"></div>
<div id="shop_not_auth_dialog"></div>
<div id="electronic_sheet_detial_dialog"></div>
<div id="show_order_balance_dialog"></div>
<div id="system_alarm_dialog"></div>
<div id="trade_select_customer"></div>
<script type="text/javascript">
//# sourceURL=index.js
$(function(){
	bindunbeforeunload();
	setMarqueeWidth();
	initMenu();
	refreshNote();
	$(".process-menu").show();
	$("#note-info").datagrid({
		title:'系统通知',headerCls:'panel-header-title',singleSelect:true,nowrap:false,url:'<?php echo U('Index/getNotificationList');?>',
		columns:[[
		          {field:'sender',title:'来自',width:'10%',align:'center',styler:formatter.styler},
		          {field:'message',title:'消息',width:'55%',align:'left',styler:formatter.styler},
		          {field:'handle_oper_id',title:'处理人',width:'10%',align:'center',styler:formatter.styler},
		          {field:'type',title:'类型',width:'10%',align:'center',styler:formatter.styler},
		          {field:'created',title:'时间',width:'15%',align:'center',styler:formatter.styler}
		]]
	});

	
	//宽度适配
	var module_w=document.getElementById("trade_module").offsetWidth;
// 	var matter_w=(module_w-80)/5+'px';
// 	var matter_arr=document.getElementsByClassName("matter-header");
// 	for (i=0;i<matter_arr.length;i++){
// 		matter_arr[i].style.width=matter_w;
// 	}
	var chart_w=module_w+'px';
	document.getElementById("trade_chart").style.width=chart_w;
	//提示信息
	var alarm=JSON.parse('<?php echo ($alarm); ?>');
	if(alarm.status==1){
		if(alarm.alarm_not_prompt_today==0){
			var msg='';
			for(i in alarm.info){
				msg+=alarm.info[i]['msg']+'<br>';
			}
			msg+='<label  style="padding-left: 3em"><input type="checkbox"/>今天不再弹窗提示</label>';
			messager.alert(msg,'warning',function(){
				if($("input[type='checkbox']").is(':checked')==1){
					var url="<?php echo U('Home/Index/notAlarmToday');?>";
					var data={};
					$.post(url);
				}
			});
		}
		document.getElementById('alarm').style.display="block";
		$('#alarm').tooltip({
			position:'top',
            content: '<span style="color:red">您有紧急消息待处理，请及时查看</span>',
            showEvent:'',
            hideEvent:'',
            onShow: function(){
                $(this).tooltip('tip').css({
                	backgroundColor:'#FFF',
                    borderColor: 'red',
                });
            },
		});
		$('#alarm').tooltip('show'); 
	}
	//判断用户是否是迭代后首次登录
	show_log();
	function show_log(){
		if("<?php echo ($show_log); ?>"==true){
			$('#first-in').dialog({
				title:'更新提示',
				iconCls:'icon-save',
				width:720,
				height:500,
				closed:false,
				inline:false,
				modal:true,
				href:"<?php echo U('Home/Index/showUpdateLog');?>",
			});
		}
	}

	//首页跳转对应页面事件
	$('.matter-header').click(function(){
		var title=$(this).attr('data-title');
		var url=$(this).attr('data-url');
		var stock_no=$(this).attr('data-stock-no');
		open_menu(title,url);
		if(title=='单据打印'){
			if($('#container').tabs('exists',title)){
				$.get("<?php echo U('Stock/StockSalesPrint/search');?>",{'stockout_no': stock_no},function(res){
					//$('#'.stockSalesPrint.params.datagrid.id).datagrid('loadData',res);
					$('#stocksalesprint_datagrid').datagrid('loadData',res);
				});
			}
		}else if(title=='物流同步'){
			if($('#container').tabs('exists',title)){
				$.get("<?php echo U('Stock/ApiLogisticsSync/search');?>",{'sync_status': 2},function(res){
					$('#apilogisticssync_getapilogisticssynclist_datagrid').datagrid('loadData',res);
				});
			}
		}
	});

	//查看电子面单余额
	$('.balance-button').click(function(){
		//todo
	});

	//右下角帮帮精灵交互
	$('.fa-times').click(function(){
		$('.small_e').stop();
		$('.fa-times').css({'display':'none'});
		$('.small_e').animate({'bottom':'-100px'});
		$('.spirit').css({'display':'block'});
		$('.spirit').animate({'right':'-20px'});
	});

	$('.small_e img').click(function(){
		$('.small_e').stop();
		$('.fa-times').css({'display':'block'});
		$('.small_e').animate({'bottom':'0px'});
	});

	$('.small_e img').mouseover(function(){
		$('.small_span').css({'display':'block'});
	});

	$('.small_e img').mouseleave(function(){
		$('.small_span').css({'display':'none'});
	});

	//模块介绍数组
	var text_arr=[
			'&nbsp;&nbsp;&nbsp;&nbsp;这里是设置模块，管理店铺基本数据，设置订单处理策略，创建打印单模板以及短信模板，管理员工权限。设置订单自动匹配货品仓库，自动匹配物流，物流资费策略等等。',
			'&nbsp;&nbsp;&nbsp;&nbsp;货品模块用来将平台上的货品与系统中的货品进行关联，可以将平台货品下载到系统，设置货品分类和品牌，创建组合装，修改系统货品的信息，管理货品的条形码，打印条形码等操作。',
			'&nbsp;&nbsp;&nbsp;&nbsp;采购模块主要是店铺的采购人员在采购货品时记录采购信息，同时检测每日预警货品一以便随时补货，也可以设置供应商货品哦。',
			'&nbsp;&nbsp;&nbsp;&nbsp;订单模块包揽了订单的下载订单（同时可以在设置中开启自动合并订单），财务审核，审核，退换等操作，同时支持手工建单，库存不足时自动转预订单，可以方便查看订单明细，以及订单的操作记录等。',
			'&nbsp;&nbsp;&nbsp;&nbsp;仓库模块主要管理仓库货品的库存数量（使用库存同步，可以将系统中货品数量同步到平台），包含了入库，出库，调拨，盘点等库存操作，同时打印物流单，发货单，查询物流同步情况，管理菜鸟电子面单都在这里进行，当然这里也记录了详细日志方便查看。',
			'&nbsp;&nbsp;&nbsp;&nbsp;账款模块主要用来处理物流资费的结算事务，计算物流资费以及核对物流对应的货品。',
			'&nbsp;&nbsp;&nbsp;&nbsp;统计模块可以查询店铺每日，每月的订单情况，以及某一种货品的销售情况。',
			'&nbsp;&nbsp;&nbsp;&nbsp;系统会将三个月前的部分数据归档，对应的订单信息以及出库单信息包括操作日志等可以在归档模块查看。'
	];

	//模块介绍交互事件
	$('.model').mouseover(function(){
		$('.model').find('div').css({'background':'#fff','color':'#3bc6d0'});
		$(this).find('div').css({'background':'#3bc6d0','color':'#fff'});
		var number=$(this).attr('data');
		var left=30+(number-1)*115;
		$('.hand-flag').css({'left':left+'px'});
		$('.model-illustrate span').html(text_arr[number-1]);
	});

	//帮帮精灵鼠标滑过事件
	$('.spirit').mouseover(function(){
		$(this).stop();
		$(this).animate({'right':'-20px'});
	});
	$('.spirit').mouseleave(function(){
		$(this).stop();
		$(this).animate({'right':'-120px'});
	});

	//订单销售图表
	var myChart = echarts.init(document.getElementById('trade_chart'));
	chart=JSON.parse('<?php echo ($chart); ?>');
	var xData=[];var num=[];var return_num=[];
	for(var i=15;i>0;i--){
	    xData.push(chart[30-i]['date'].substring(5));//X坐标轴数据
	    num.push(chart[30-i]['num']);//销售出库数据
	    return_num.push(chart[30-i]['return_num']);//退货入库数据
	}
	var option = {
			title : {
				text: '销售数据分析',
				show:false,
			},
    	    tooltip : {
    	        trigger: 'axis',
				axisPointer : {
					type : 'shadow'
				}
    	    },
    	    legend: {
    	        data:['销售出库量','退货入库量']
    	    },
    	    toolbox: {
    	        show : true,
    	        feature : {
    	            magicType : {show: true, type: ['line', 'bar']},
    	            saveAsImage : {show: true}
    	        },
    	    	right:'18px'
    	    },
    	    calculable : true,
    	    grid:{
                x:50,
                x2:50,
            }, 
    	    xAxis : [{
    	            type : 'category',
    	            data : xData,
					axisLabel: {
						show: true,
						textStyle: {
							color: '#666666'
						}
					},
					axisLine: {
						show: false
					},
					axisTick: {
						show: false
					},
			}],
    	    yAxis : [{
    	            type : 'value',
					axisLabel: {
						show: true,
						textStyle: {
							color: '#666666'
						}
					},
					axisLine: {
						lineStyle:{
							color:'#ccc'
						}
					},
					axisTick: {
						show: false
					},
			}],
    	    series : [{
    	            name:'销售出库量',
    	            type:'bar',
    	            data:num,
    	            markPoint : {
    	                data : [
    	                    {type : 'max', name: '最大值'},
    	                    {type : 'min', name: '最小值'}
    	                ]
    	            },
					itemStyle:{
						normal:{
							color:'#9bce4a'
						}
					},
    	        },
    	        {
    	            name:'退货入库量',
    	            type:'bar',
    	            data:return_num,
    	            markPoint : {
    	                data : [
    	                     {type : 'max', name: '最大值'},
        	                 {type : 'min', name: '最小值'}
    	                ]
    	            },
					itemStyle:{
						normal:{
							color:'#29b6f6'
						}
					},
    	        }
    	    ],
    	};
    // 使用刚指定的配置项和数据显示图表。
    myChart.setOption(option);
    
	//新老手模式判断
	var is_new='<?php echo ($is_new); ?>';
	if(is_new==1){
		document.getElementById('trade_num').style.display="none";
		document.getElementById('newcomer').style.display="block";
		document.getElementById('model_intro').style.display="block";
		document.getElementById('module_info').style.display="block";
	}
	
	//记录使用的浏览器和屏幕分辨率
	getBrowserAndResolution();
	//常用菜单交互
	$("#change-menu").combobox({
		onSelect: function (n) {
			if(n.text=='常用菜单' || n.text=='')return false;
			var data= n.value.split('-');
			if(data[1]!=1){
				data[0]+='?dialog=system';
			}
			open_menu(data[2],data[0]);
			$('#change-menu').combobox('select','常用菜单');
		}
	});
});
//常用操作交互
function open_this_menu(that){
	var data= that.attr('name').split('-');
	if(data[1]!=1){
		data[0]+='?dialog=system';
	}
	open_menu(data[2],data[0]);
}
//编辑常用操作
function setCommonUse(){
	var url = 'index.php/Setting/System/showCommonMenuSetting?dialog=system';
	open_menu('设置常用菜单',url);
}
function setMarqueeWidth(){var width=$(document.body).width();var navr=$('#header .navr').width();$('#header marquee').width(width-160-navr);}
function refreshNote(){
	var url="<?php echo U('Home/Index/reloadData');?>";
	var data={};
	data.type='ajax';
	//load();
	$.post(url, data, function (res) {
		for(var x in res.todo){
			$('#'+x).text(res.todo[x]);
		}
//		for(var x in res.trade_num){
//			$('#'+x).text(res.trade_num[x]+'个');
//		}
		chart=res.chart;
		reloadChart(15);
		disLoad();
	});
}
//刷新数据的遮罩层开启
function load() {
	$("<div class=\"datagrid-mask\"></div>").css({ display: "block", width: "100%", height: $(window).height() }).appendTo("body");
	$("<div class=\"datagrid-mask-msg\"></div>").html("正在加载，请稍候...").appendTo("body").css({ padding:"5px 5px 5px 30px","line-height":'15px',"border-radius":'5px',display: "block", left: ($(document.body).outerWidth(true) - 190) / 2, top: ($(window).height() - 45) / 2 });
}
//刷新数据的遮罩层关闭
function disLoad() {
	$(".datagrid-mask").remove();
	$(".datagrid-mask-msg").remove();
}
//刷新图表数据
function reloadChart(day){
    var myChart = echarts.getInstanceByDom(document.getElementById("trade_chart"));
    var xData=[];var num=[];var return_num=[];
    for(var i=(30-day);i<30;i++){
    	xData.push(chart[i]['date'].substring(5));
    	num.push(chart[i]['num']);
    	return_num.push(chart[i]['return_num']);
    }
    myChart.setOption({        //加载数据图表
        xAxis: {
            data: xData,
        },
        series: [// 根据名字对应到相应的系列
            {
            	name: '销售出库量',
            	data: num,
        	},
            {
            	name:'退货入库量',
            	data:return_num
            }
        ]
    });
}
function setDatagridField(mode,key,dg,frozen){
	frozen==1?true:frozen=0;//是否可以设置固定列
	var url="<?php echo U('Setting/DatagridField/getField');?>";
	url += (url.indexOf('?') != -1) ? '&mode='+mode+'&key='+key+'&frozen='+frozen : '?mode='+mode+'&key='+key+'&frozen='+frozen;
	var buttons=[ {text:'确定',handler:function(){ submitDatagridField('flag_set_dialog',dg,mode+'_'+key); }}, {text:'取消',handler:function(){Dialog.cancel('flag_set_dialog')}} ]; 
	var toolbar=[{ text:'全选', iconCls:'icon-ok', handler:function(){selectAllField(); }},{ text:'反选', iconCls:'icon-redo', handler:function(){reverSelect();} }];
	Dialog.show('flag_set_dialog','设置表头',url,500,350,buttons,toolbar,ismax=false);
}
function show_list(){
	var url='<?php echo U('Help/TradeProcess/showInitializeList');?>?dialog=system';
	var buttons=[
		{text:'确定',handler:function(){$('#invalid_goods').dialog('close');}},
	];
	$('#invalid_goods').dialog({
			title:'初始化流程',
			iconCls:'icon-save',
			width:330,
			height:500,
			closed:false,
			inline:false,
			modal:true,
			href:url,
			buttons:buttons
	});
}

function click_menu(title,url){
	open_menu(title, url);
	$('#invalid_goods').dialog('close');
}

//显示未匹配界面
function showInvalidGoods(){
	var url='<?php echo U('Trade/TradeCheck/getInvalidGoods');?>';
	var buttons=[
	         		{text:'确定',handler:function(){$('#invalid_goods').dialog('close');}},
	       		];
	$('#invalid_goods').dialog({ title:'未匹配货品', iconCls:'icon-save', width:764, height:560, closed:false, inline:true, modal:true, href:url, buttons:buttons });
}
function gotoApiLogisticsInfo(){
	var url="index.php/Stock/ApiLogisticsSync/getApiLogisticsSyncList?sync_status=2";
	open_menu('物流同步',url);
}

//显示未授权店铺界面
function showNotAuthorizeShop(){
	var url='<?php echo U('Home/Index/showNotificationList');?>';
	var buttons=[
	         		{text:'确定',handler:function(){$('#shop_not_auth_dialog').dialog('close');}},
	       		];
	$('#shop_not_auth_dialog').dialog({ title:'未授权店铺', iconCls:'icon-save', width:764, height:560, closed:false, inline:true, modal:true, href:url, buttons:buttons });
}

//显示电子面单详情页面
function showElectronicSheetDetial(){
	var url='<?php echo U('Home/Index/showElectronicSheetDetial');?>';
	var buttons=[
	         		{text:'确定',handler:function(){$('#electronic_sheet_detial_dialog').dialog('close');}},
	       		];
	$('#electronic_sheet_detial_dialog').dialog({ title:'电子面单详情', iconCls:'icon-save', width:760, height:360, closed:false, inline:true, modal:true, href:url, buttons:buttons });
}
//显示订单余额详情
function showOrderBalance(){
 var url='<?php echo U("Home/Index/showOrderBalance");?>';
 var buttons = [
					{text:'确定',handler:function(){$('#show_order_balance_dialog').dialog('close');}},
			   ];
 $('#show_order_balance_dialog').dialog({title:'订单余额详情',iconCls:'icon-save',width:760,height:360,closed:false,inline:true,href:url,buttons:buttons});
}
//显示通知界面
function showAlarm(){
	var url="<?php echo U('Home/Index/showSystemAlarm');?>";
	var buttons=[
				{text:'确定',handler:function(){$('#system_alarm_dialog').dialog('close');}},
	             ];
	$('#system_alarm_dialog').dialog({ title:'提示信息', iconCls:'icon-alarm', width:550, height:300, closed:false, inline:true, modal:true, href:url, buttons:buttons });
}
submitAuthForm = function () {
	var form = $("#shop_auth_form");
	if (!form.form("validate")) {
		return false;
	}
	var formData = form.serializeArray();
	var url = "<?php echo U('Setting/Shop/saveAuthInfo');?>";
	var dialog = $("#shop_auth_dialog");
	var data = {};
	for (var x in formData) {
		if (typeof(formData[x]) != "undefined") {
			data[formData[x]["name"]] = formData[x]["value"];
		}
	}
	$.post(url, {"data": data}, function (res) {
		if (!res.status) {
			dialog.dialog("close");
			$("#note-info").datagrid('reload');
			//shop.refresh();
		} else {
			messager.alert(res.msg);
		}
	})
}
function shopAuthor(shop){
	if (shop.platform_id == 60 || shop.platform_id==5 || shop.platform_id==8 || shop.platform_id==27 || shop.platform_id==29 || shop.platform_id==34 || shop.platform_id==53) {
		var id = "shop_auth_dialog";
		var buttons = [{
			text: '确定', handler: function () {
				submitAuthForm();
			}
		}];
		var url1 = "<?php echo U('Setting/Shop/getAuthInfo');?>" + "?shop_id=" + shop.shop_id;
		//shop.showDialog(id,"店铺授权",url,"200px","350px",buttons)
		$('#'+id).dialog({
			title:'店铺授权',
			iconCls:'icon-save',
			width:350,
			height:200,
			minimizable: false,
			closed:false,
			inline:true,
			modal:true,
			href:url1,
			buttons:buttons
		});
		return;
	}
	var url = "<?php echo U('Setting/Shop/authorize');?>";
	$.post(url, {
		"shop_id": shop.shop_id,
		"platform_id": shop.platform_id,
		"sub_platform_id": shop.sub_platform_id
	}, function (r) {
		if (0 == r.status) {
			window.open(r.info);
		} else if (1 == r.status) {
			messager.alert(r.info);
		}
	});



}
function initMenu(){
		var menu_list = <?php echo ($menu_list_data); ?>;
		if($.isEmptyObject(menu_list)){
			messager.alert("该账户无任何权限,请先设置权限!");
			return;
		}
		displayMenu($('body'),menu_list,1);
		var menu_sel = $('#header').find('.nav-menu').find('a').each(function(i){
		var menu_dom = $(this).parent().parent();
		var menu_class = $(menu_dom).attr('class');
		var menu_class_list = menu_class.split(/\s/);
		var menu_type;
		for(var i in menu_class_list)
		{
			if(menu_class_list[i] !='nav-menu' )
			{
				menu_type = menu_class_list[i];
			}
		}
		$('.menu2.'+menu_type).find('a').hover(function(){
			var url = $(this).attr('url');
			var title = $(this).attr('data-url');
			var is_leaf = $(this).attr('is_leaf');
			if(is_leaf == '0')
			{
				var offset 		= $(this).offset();
				var offset_div 	= $('.menu2.'+menu_type).offset();
				var a_width 	= $(this).css('width').replace('px','');
				var a_height 	= $(this).css('height').replace('px','');
				var width 		= $(this).next('div').css('width').replace('px','');
				var height 		= $(this).next('div').css('height').replace('px','');
				$(this).next('div').css({'top':'0px','left':parseInt(a_width)+'px'}).stop(true,true).show(300);
			}
		},function () {
			var is_hover = false;
			var that = this;
			setTimeout(function () {
				if(!is_hover){
					$(that).next('div').stop(true,true).hide();
				}
			},20);
			$(this).next('div').hover(function(){
				is_hover = true;
				$(this).stop(true,true).show();

			},function(){
				is_hover = false;
				if(!$(this).is(':hidden')){
					$(this).stop(true,true).hide(300);
				}
			});
		});
		$('.menu2.'+menu_type).find('a').click(function(){
			var url = $(this).attr('url');
			var title = $(this).attr('data-url');
			var is_leaf = $(this).attr('is_leaf');
			if(is_leaf == '1')
			{
				$('.menu2.'+menu_type).stop(true,true).hide();
				$('.menu2.'+menu_type).find('a').next('div').stop(true,true).hide();
				open_menu(title,url);
			}

		});
		$(this).parents('.nav-menu').hover(function(){
			var offset = $(menu_dom).find('.nav-pos').offset();
			$('.menu2').stop(true,true).hide(300);
			$('.menu2.'+menu_type).css({'top':offset.top+1+'px','left':offset.left+'px'}).stop(true,true).show(300);
			var second_block = $('.menu2.'+menu_type).find('.group');
			if(second_block.length>1){
				second_block.css({'border-right': '1px solid #64a3f8'});
				$(second_block[second_block.length-1]).css({'border-right':''});
			}
		},function(){
			var is_hover = false;
			setTimeout(function () {
				if(!is_hover){
					$('.menu2.'+menu_type).stop(true,true).hide();
				}
			},20);
			$('.menu2.'+menu_type).hover(function(){
				is_hover = true;
				$('.menu2.'+menu_type).stop(true,true).show();

			},function(){
				is_hover = false;
				if(!$(this).is(':hidden')){
					$(this).stop(true,true).hide(300);
				}
			});
		})
	});
}

function displayMenu(jq_menu,menu_list,menu_seq,menu_seq_child){
	if($.isEmptyObject(menu_list))
	{
		return;
	}
	if(menu_seq == 1){
		for(var y=0;y < menu_list.length;y++)
		{
			jq_menu.append('<div class="menu2 '+menu_list[y].module+'"></div>');
			displayMenu($('body').find('.menu2.'+menu_list[y].module),menu_list[y],2);
		}
	}
	if(menu_seq==2){
		for(var i = 0;!!menu_list.children&&i<Math.ceil(menu_list.children.length/14);i++)
		{
			jq_menu.append('<div class="block'+i+' group"></div>');
			for(var j=i*14;j<i*14+14&&j<menu_list.children.length;j++)
			{
				var text="<i class='fa "+menu_list.children[j].img+"' style='font-size: 14px;width:20px;text-align:center;'></i>"+menu_list.children[j].text;
				if(menu_list.children[j].is_leaf==0){
					text=text+"&nbsp;&nbsp;<i style='font-size: 15px;line-height: 24px;' class=' fa fa-sign-out''></i>";
				}
				jq_menu.find('.block'+i).append('<div class="item"><a href="javascript:void(0);"  is_leaf="'+menu_list.children[j].is_leaf+'" url = "'+menu_list.children[j].href+'" data-url = "'+menu_list.children[j].text+'" ><span style="margin-left: 17px;">'+text+'</span></a><div class="menu menu'+(menu_seq+1)+'_'+j+'"></div></div>');
				//jq_menu.find('.block'+i).append('<div class="item"><a href="javascript:void(0);"  is_leaf="'+menu_list.children[j].is_leaf+'" url = "'+menu_list.children[j].href+'" title = "'+menu_list.children[j].text+'" ><label style="border: none;display:inline-block;width:20px;height:18px;background-image: url(\'/Public/Image/all.png\');background-position: 216px 108px;"></label><span>'+text+'</span></a><div class="menu menu'+(menu_seq+1)+'_'+j+'"></div></div>');
				displayMenu(jq_menu.find('.block'+i),menu_list.children[j],menu_seq+1,j);
			}
		}
	}else if(menu_seq>2){
		if(!menu_list.children){
			return;
		}
		for(var k=0;k<menu_list.children.length;k++)
		{
			var text="<i class='fa "+menu_list.children[k].img+"' style='font-size: 14px;width:20px;text-align:center;'></i>"+menu_list.children[k].text;
			jq_menu.find('.menu'+menu_seq+'_'+menu_seq_child).append('<div class="item"><a href="javascript:void(0);" is_leaf="'+menu_list.children[k].is_leaf+'" url = "'+menu_list.children[k].href+'" data-url = "'+menu_list.children[k].text+'" ><label style="border: none;display:inline-block;width: 20px;height:18px;background-position: 216px 108px;"></label><span>'+text+'</span></a><div class="menu menu'+(menu_seq+1)+'_'+k+'"></div></div>');
			displayMenu(jq_menu.find('.menu'+menu_seq+'_'+menu_seq_child),menu_list.children[k],menu_seq+1,k);
		}
	}
	return;
}
function show_ini_text(content,key,data){
	var menu={};
	menu['goods_process']='show_goods_Div';
	menu['stock_process']='show_stock_Div';
	menu['platform_process']='show_platform_Div';
	var goods=document.getElementById(content);
	var text=JSON.parse(data);
	var showDiv = document.getElementById(menu[content]);
	var x=event.clientX;
	var y=parseInt(event.clientY)+parseInt(10);
	showDiv.style.left =(x-goods.getBoundingClientRect().left)+'px';
	showDiv.style.top = (y-goods.getBoundingClientRect().top)+'px';
	showDiv.style.display = 'block';
	showDiv.innerHTML = text[key];
}
function close_ini_text(key) {
	var showDiv = document.getElementById(key);
	showDiv.style.display = 'none';
	showDiv.innerHTML = '';
}
function onDrag(e){
	var d = e.data;
	if (d.left < 35){d.left = 35}
	if (d.top < 80){d.top = 80}
	if (d.left + $(d.target).outerWidth() > $(d.parent).width()){
		d.left = $(d.parent).width() - $(d.target).outerWidth();
	}
	if (d.top + $(d.target).outerHeight() > $(d.parent).height()){
		d.top = $(d.parent).height() - $(d.target).outerHeight();
	}
}
function newcomer_change(type){
	if(type==0){
		document.getElementById('newcomer').style.display="none";
		document.getElementById('model_intro').style.display="none";
		document.getElementById('trade_num').style.display="block";
		document.getElementById('module_info').style.display="none";
	}else{
		var url="<?php echo U('Home/Index/getNewcomer');?>";
		$.post(url,function(res){
			$('#newcomer li1').each(
					function(k,v) {
						if(k<8){
							this.innerHTML=res[k]['msg'];
							this.onclick=function(){
								open_menu(res[k]['text'],res[k]['href']);
							};
						}
					}
			);
		});
		document.getElementById('trade_num').style.display="none";
		document.getElementById('newcomer').style.display="block";
		document.getElementById('model_intro').style.display="block";
		document.getElementById('module_info').style.display="block";
	}
}
function setAlarm(type,info){
	var role='<?php echo ($role); ?>';
	if(role<=1){
		messager.alert('您没有管理员权限，无法设置。');return;
	}
	var url="<?php echo U('Setting/System/showSystemSetting');?>";
	url+='?dialog=system&tab_type=基本设置&config_name='+type+'&info='+info;
	open_menu('系统设置',url);
}
function getBrowserAndResolution(){
	var data={};
	var userAgent=navigator.userAgent.toLowerCase();
	if (userAgent.indexOf("qqbrowser")>=0 || userAgent.indexOf("qq")>=0){
		data.browser="qq";
	}else if(userAgent.indexOf("safari")>=0 && userAgent.indexOf("metasr")>=0){
		data.browser="sougou";
	}else if (!!window.ActiveXObject || "ActiveXObject" in window){
		data.browser="ie";
	}else{
		if (userAgent.indexOf("lbbrowser") >= 0){ 
			data.browser="liebao";
		}else if(userAgent.indexOf("firefox")>=0){ 
			data.browser="firefox";
		}else if(userAgent.indexOf("edge")>=0){ 
			data.browser="edge";
		}else if(userAgent.indexOf("chrome")>=0){ 
			var is360 = browserIs360("type", "application/vnd.chromium.remoting-viewer");
	  		if(is360){
	  			data.browser="360";
	  		}else{
	  			data.browser="chrome";
	  		}
		}else if(userAgent.indexOf("opera")>=0){ 
			data.browser="opera";
		}else if(userAgent.indexOf("safari")>=0){ 
			data.browser="safari";
		}else{ 
			data.browser="other";
		}
	}
	data.height=window.screen.height;
	data.width=window.screen.width;
	var url="<?php echo U('Index/getBrowserAndResolution');?>";
	$.post(url,{'data':data});
}
function browserIs360(option, value) {
    var mimeTypes = navigator.mimeTypes;
    for (var mt in mimeTypes) {
        if (mimeTypes[mt][option] == value) {
            return true;
        }
    }
    return false;
}
function sendToQQ(){
	var url = "http://wpa.qq.com/msgrd?v=3&uin=3194947699&site=qq&menu=yes";
	window.open(url);
}
//重写Trade模块post函数,加上超时提醒
function Post(url,data,success,dataType,errorMessage){
	if(errorMessage==""||errorMessage==undefined){
		errorMessage="您的链接中断或超时，请重试！";
	}
	$.ajax({
		  type: 'POST',
		  url: url,
		  data: data,
		  success: success,
		  dataType: dataType,
		  error: function(res){
			  messager.alert(errorMessage);
			  disLoad();
		  }
	});
}
</script>
</body>
</html>