//# sourceURL=erp.js

(function () {
    /*function getData(id) {

     return data[id];
     }*/
    var data = {};
    //订单平台状态
    data["api_trade_status"] = [
        {"id": "all", "name": "全部", "selected": true},
        {"id": "10", "name": "未确认"},
        {"id": "20", "name": "待尾款"},
        {"id": "30", "name": "待发货"},
        {"id": "40", "name": "部分发货"},
        {"id": "50", "name": "已发货"},
        {"id": "60", "name": "已签收"},
        {"id": "70", "name": "已完成"},
        {"id": "80", "name": "已退款"},
        {"id": "90", "name": "已关闭"}
    ];
    //订单状态
    data['trade_status'] = [
        {"id": "all", "name": "全部", "selected": true},
        {"id": "30", "name": "待客审"},
        {"id": "35", "name": "待财审"},
        {"id": "55", "name": "已审核"},
        {"id": "95", "name": "已发货"},
        {"id": "110", "name": "已完成"},
        {"id": "100", "name": "已签收"},
        {"id": "25", "name": "预订单"},
        {"id": "5", "name": "已取消"},
        {"id": "10", "name": "待付款"},
        {"id": "12", "name": "待尾款"},
        {"id": "15", "name": "等未付"},
        {"id": "105", "name": "部分打款"},
        {"id": "19", "name": "预订单前处理"},
        {"id": "20", "name": "前处理"},
        //{"id": "16", "name": "延时审核"},
        //{"id": "21", "name": "委外前处理"},
        //{"id": "22", "name": "抢单前处理"},
        //{"id": "27", "name": "待抢单"},
        //{"id": "40", "name": "待递交仓库"},
        //{"id": "45", "name": "递交仓库中"},
        //{"id": "53", "name": "已递交仓库"},
        {"id": "115", "name": "无需处理"},
        //{"id": "120", "name": "被合并"}
    ];
    //发货状态-出库状态
    data['consign_status'] = [
        {"id": "all", "name": "全部", "selected": true},
        {"id": "1", "name": "已验货"},
        {"id": "2", "name": "已称重"},
        {"id": "4", "name": "已出库"},
        {"id": "8", "name": "物流同步"},
		{"id": "1073741824", "name": "原始单已完成"},
        //{"id": "16", "name": "已分拣"},
    ];
    //处理状态
    data["process_status"] = [
        {"id": "all", "name": "全部", "selected": true},
        {"id": "10", "name": "待递交"},
        {"id": "20", "name": "已递交"},
        {"id": "30", "name": "部分发货"},
        {"id": "40", "name": "已发货"},
        {"id": "50", "name": "部分结算"},
        {"id": "60", "name": "已完成"},
        {"id": "70", "name": "已取消"},
    ];
    //支付状态
    data["pay_status"] = [
        {"id": "all", "name": "全部", "selected": true},
        {"id": 0, "name": "未付款"},
        {"id": 1, "name": "部分付款"},
        {"id": 2, "name": "已付款"}
    ];
	//wms委外出入库订单类型
	data['wms_order_type'] = [
		 {"id": "all", "name": "全部", "selected": true},
		{'id':1,'name':'委外出库'},
		{'id':2,'name':'委外入库'},
	];
	//wms委外出入库订单状态
	data['wms_order_status'] = [
		{"id": "all", "name": "全部", "selected": true},
		{'id':10,'name':'已取消'},
	//	{'id':20,'name':'编辑中'},
	//	{'id':30,'name':'待审核'},
		{'id':40,'name':'待推送'},
		{'id':50,'name':'推送失败'},
		{'id':60,'name':'待出库'},
		{'id':65,'name':'待入库'},
		{'id':70,'name':'部分出库'},
		{'id':75,'name':'部分入库'},
		{'id':80,'name':'已完成'},
	];
	data['transport_mode'] = [
		 {"id": "all", "name": "全部", "selected": true},
		{'id':0,'name':'到仓自提'},
		{'id':1,'name':'快递'},
		{'id':2,'name':'干线物流'},
	];
	//档口单
	data['stalls_status'] = [
		{"id":"all","name":"全部","selected":true},
		{'name':'已取消','id':'10'},
		{'name':'编辑中','id':'20'},
		//{'name':'已审核','id':'40'},
		//{'name':'部分到货','id':'50'},
		//{'name':'已到货','id':'60'},
		//{'name':'待结算','id':'70'},
		//{'name':'部分结算','id':'80'},
		{'name':'已完成','id':'90'},
		
	];
	data['unique_print_status'] = [
		{"id":"all","name":"全部","selected":true},
		{'name':'未打印','id':'0'},
		{'name':'已打印','id':'1'},
	];
	
    //支付方式
    data["pay_method"] = [
        {"id": "all", "name": "全部", "selected": true},
        {"id": 1, "name": "在线转账"},
        {"id": 2, "name": "现金"},
        {"id": 3, "name": "银行转账"},
        {"id": 4, "name": "邮局汇款"},
        {"id": 5, "name": "预付款"},
        {"id": 6, "name": "刷卡"}
    ];
    //退款状态
    data["refund_status"] = [
        {"id": "all", "name": "全部", "selected": true},
        {"id": 0, "name": "无退款"},
        {"id": 1, "name": "申请退款"},
        {"id": 2, "name": "部分退款"},
        {"id": 3, "name": "全部退款"},
        {"id": 4, "name": ""}
    ];
    //子订单退款状态
    data["order_refund_status"] = [
        {"id": "all", "name": "全部", "selected": true},
        {"id": 0, "name": "无退款"},
        {"id": 1, "name": "取消退款"},
        {"id": 2, "name": "已申请退款"},
        {"id": 3, "name": "等待退货"},
        {"id": 4, "name": "等待收货"},
        {"id": 5, "name": "退款成功"}
     ];
    //退款类型
    data["refund_type"] = [
        {"id": "all", "name": "全部", "selected": true},
        //{"id": "0", "name": "取消订单"},
        {"id": "1", "name": "退款"},//（未发货，退款申请）
        {"id": "2", "name": "退货"},
        {"id": "3", "name": "换货"},
        {"id": "4", "name": "退款不退货"},
		{"id": "5", "name": "破损补发"}
    ];
    //退款类型-下拉表
    data["refund_type_select"] = [
        {"id": "all", "name": "全部", "selected": true},
        {"id": "2", "name": "退货"},
        {"id": "3", "name": "换货"},
        {"id": "4", "name": "退款不退货"},
		{"id": "5", "name": "破损补发"}
    ];
    
    
    //资金流向-下拉表
    data["flow_type"] = [
        {"id": "1", "name": "商家->买家","selected": true},
        {"id": "2", "name": "买家->商家"}
    ];
    //发票类别
    data["invoice_type"] = [
        {"id": "all", "name": "全部", "selected": true},
        {"id": 0, "name": "不需要"},
        {"id": 1, "name": "普通发票"},
        {"id": 2, "name": "增值税发票"}
    ];
    //订单来源
    data["trade_from"] = [
        {"id": "all", "name": "全部", "selected": true},
        {"id": "1", "name": "API抓单"},
        {"id": "2", "name": "手工建单"},
        {"id": "3", "name": "excel导入"},
        // {"id": "4", "name": "现款销售"}
    ];
    //退货单建单方式
    data["from_type"] = [
        {"id": "all", "name": "全部", "selected": true},
        {"id": "1", "name": "api抓单", "selected": true},
        {"id": "2", "name": "手工建单", "selected": true}
    ];
    //发货条件
    data["delivery_term"] = [
        {"id": "all", "name": "全部", "selected": true},
        {"id": "1", "name": "款到发货"},
        {"id": "2", "name": "货到付款"},
        // {"id": "3", "name": "分期付款"}
    ];
    //分销类型
    // data["fenxiao_type"] = [
    // {"id": "all", "name": "全部", "selected": true},
    // {"id": "0", "name": "非分销订单"},
    // {"id": "1", "name": "代销"},
    // {"id": "2", "name": "经销"}
    // ];
    //物流方式
    data["logistics_type"] = [
        {"id": "all", "name": "全部", "selected": true},
        {"id": "-1", "name": "无"},
        {"id": "30", "name": "CCES"},
        {"id": "3", "name": "EMS"},
        {"id": "45", "name": "EMS经济快递"},
        {"id": "53", "name": "E速宝"},
        {"id": "71", "name": "RUSTON"},
        {"id": "81", "name": "ZTOGZ"},
        {"id": "82", "name": "ZTOSH"},
        {"id": "31", "name": "东方汇"},
        {"id": "2", "name": "中国邮政"},
        {"id": "12", "name": "中远"},
        {"id": "5", "name": "中通快递"},
        {"id": "11", "name": "中铁快运"},
        {"id": "25", "name": "亚风"},
        {"id": "1311", "name": "京邦达(京东快递)"},
        {"id": "35", "name": "优速快递"},
        {"id": "56", "name": "佳吉快递"},
        {"id": "83", "name": "保宏物流"},
        {"id": "73", "name": "信丰物流"},
        {"id": "37", "name": "全一快递"},
        {"id": "7", "name": "全峰快递"},
        {"id": "15", "name": "全日通快递"},
        {"id": "2000", "name": "其他"},
        {"id": "57", "name": "凡宇速递"},
        {"id": "55", "name": "北京EMS"},
        {"id": "23", "name": "华强物流"},
        {"id": "17", "name": "发网"},
        {"id": "80", "name": "合众阳晟"},
        {"id": "54", "name": "同城快递"},
        {"id": "41", "name": "四川快捷"},
        {"id": "52", "name": "国通快递"},
        {"id": "4", "name": "圆通速递"},
        {"id": "49", "name": "城市100"},
        {"id": "84", "name": "增益速递"},
        {"id": "27", "name": "大田"},
        {"id": "58", "name": "天地华宇"},
        {"id": "16", "name": "天天快递"},
        {"id": "19", "name": "宅急送"},
        {"id": "29", "name": "安得"},
        {"id": "47", "name": "尚橙物流"},
        {"id": "59", "name": "居无忧"},
        {"id": "48", "name": "广东EMS"},
        {"id": "22", "name": "德邦物流"},
        {"id": "14", "name": "快捷快递"},
        {"id": "34", "name": "新邦物流"},
        {"id": "1", "name": "无单号物流"},
        {"id": "24", "name": "星辰急便"},
        {"id": "0", "name": "未知"},
        {"id": "50", "name": "汇强快递"},
        {"id": "70", "name": "派易国际物流77"},
        {"id": "39", "name": "浙江ABC"},
        {"id": "36", "name": "港中能达"},
        {"id": "77", "name": "燕文上海"},
        {"id": "79", "name": "燕文义乌"},
        {"id": "74", "name": "燕文北京"},
        {"id": "76", "name": "燕文国际"},
        {"id": "75", "name": "燕文广州"},
        {"id": "78", "name": "燕文深圳"},
        {"id": "6", "name": "申通快递"},
        {"id": "10", "name": "百世汇通"},
        {"id": "20", "name": "百世物流"},
        {"id": "60", "name": "美国速递"},
        {"id": "18", "name": "联昊通"},
        {"id": "21", "name": "联邦快递"},
        {"id": "42", "name": "贝业新兄弟"},
        {"id": "33", "name": "远长"},
        {"id": "72", "name": "速尔"},
        {"id": "51", "name": "邮政国内小包"},
        {"id": "28", "name": "长发"},
        {"id": "26", "name": "长宇"},
        {"id": "1309", "name": "青岛日日顺"},
        {"id": "9", "name": "韵达快递"},
        {"id": "8", "name": "顺丰速运"},
        {"id": "40", "name": "飞远(爱彼西)配送"},
        {"id": "46", "name": "飞远配送"},
        {"id": "32", "name": "首业"},
        {"id": "38", "name": "黑猫宅急便"},
        {"id": "13", "name": "龙邦速递"},
        {"id": "87", "name": "安能物流"},
        {"id": "88", "name": "卡行天下"},
        {"id": "89", "name": "圆通航运"},
		{"id": "90", "name": "九曳鲜配"},
		{"id": "91", "name": "德邦快递"},
    ];
    //线下电子面单--支持的物流
    data["logistics_type_xx"] = [
        {"id": "all", "name": "全部", "selected": true},
        {"id": "1311", "name": "京邦达(京东快递)"},
        {"id": "8", "name": "顺丰速运"},
    ];
    //菜鸟电子面单--支持的物流
    data["logistics_type_cn"] = [
        {"id": "3", "name": "EMS"},
        {"id": "4", "name": "圆通速递"},
        {"id": "5", "name": "中通快递"},
        {"id": "6", "name": "申通快递"},
        {"id": "7", "name": "全峰快递"},
        {"id": "8", "name": "顺丰速运"},
        {"id": "9", "name": "韵达快递"},
        {"id": "10", "name": "百世汇通"},
        {"id": "14", "name": "快捷快递"},
        {"id": "16", "name": "天天快递"},
        {"id": "19", "name": "宅急送"},
        {"id": "22", "name": "德邦物流"},
        {"id": "35", "name": "优速快递"},
        {"id": "45", "name": "EMS经济快件"},
        {"id": "51", "name": "邮政国内小包"},
        {"id": "52", "name": "国通快递"},
    ];

    //电子面单 --code--name
    data['logistics_name_code']=[
        {"id":"EMS","name":"EMS"},
        {"id":"EYB","name":"EMS经济快件"},
        {"id":"YTO","name":"圆通速递"},
        {"id":"ZTO","name":"中通快递"},
        {"id":"STO","name":"申通快递"},
        {"id":"QFKD","name":"全峰快递"},
        {"id":"SF","name":"顺丰速运"},
        {"id":"YUNDA","name":"韵达快递"},
        {"id":"HTKY","name":"百世汇通"},
        {"id":"FAST","name":"快捷快递"},
        {"id":"TTKDEX","name":"天天快递"},
        {"id":"ZJS","name":"宅急送"},
        {"id":"DBKD","name":"德邦物流"},
        {"id":"UC","name":"优速快递"},
        {"id":"POSTB","name":"邮政国内小包"},
        {"id":"GTO","name":"国通快递"},
    ];

    //电子面单
    data["logistics_type_code"] = [
        {"id": "3", "name": "EMS"},
        {"id": "4", "name": "YTO"},
        {"id": "5", "name": "ZTO"},
        {"id": "6", "name": "STO"},
        {"id": "7", "name": "QFKD"},
        {"id": "8", "name": "SF"},
        {"id": "9", "name": "YUNDA"},
        {"id": "10", "name": "HTKY"},
        {"id": "14", "name": "FAST"},
        {"id": "16", "name": "TTKDEX"},
        {"id": "19", "name": "ZJS"},
        {"id": "22", "name": "DBKD"},
        {"id": "35", "name": "UC"},
        {"id": "45", "name": "EYB"},
        {"id": "51", "name": "POSTB"},
        {"id": "52", "name": "GTO"},
    ];

    //物流追踪的物流状态
    data["logistics_trace_type"] = [
        {"id": "0", "name": "待查询"},
        {"id": "1", "name": "待取件"},
        {"id": "2", "name": "已取件"},
        {"id": "3", "name": "在途中"},
        {"id": "4", "name": "待派件"},
        {"id": "5", "name": "签收"},
        {"id": "6", "name": "拒收"},
        {"id": "7", "name": "已处理"},
        {"id": "99", "name": "推送失败"}
    ];


    //平台信息
    data["platform_id"] = [
        {"id": "all", "name": "全部", "selected": true},
        {"id": "0", "name": "线下"},
        {"id": "1", "name": "淘宝"},
        //{"id": "2", "name": "淘宝分销"},
        {"id": "3", "name": "京东"},
        // {"id": "4", "name": "拍拍"},
        {"id": "5", "name": "亚马逊"},
        //{"id": "6", "name": "1号店"},
        {"id": "7", "name": "当当网"},
        {"id": "8", "name": "库巴"},
        {"id": "9", "name": "阿里巴巴"},
        // {"id": "10", "name": "ECShop"},
        // {"id": "11", "name": "麦考林"},
        // {"id": "12", "name": "V+"},
        {"id": "13", "name": "苏宁"},
        {"id": "14", "name": "唯品会"},
        // {"id": "15", "name": "易迅"},
        // {"id": "16", "name": "聚美"},
        {"id": "17", "name": "有赞（口袋通）"},
        // {"id": "19", "name": "微铺宝"},
        {"id": "20", "name": "美丽说(蘑菇街)"},
        {"id": "22", "name": "贝贝网"},
        // {"id": "23", "name": "ecstore"},
        {"id": "24", "name": "折800"},
        // {"id": "25", "name": "融e购"},
        // {"id": "26", "name": "穿衣助手"},
        {"id": "27", "name": "楚楚街"},
          {"id": "28", "name": "微盟旺店"},
         {"id": "29", "name": "卷皮网"},
        // {"id": "30", "name": "嘿客"},
         {"id": "31", "name": "飞牛"},
        //{"id": "32", "name": "微店"},
        {"id": "33", "name": "拼多多"},
        {"id": "34", "name": "蜜芽宝贝"},
        {"id": "37", "name": "速卖通"},
        {"id": "47", "name": "人人店"},
        // {"id": "127", "name": "其它"},
        {"id": "50", "name": "网易考拉海购"},
        {"id": "60", "name": "返利网"},
    ];
    //子平台信息
    data["sub_paltfom_id"] = {
        "0": [{"id": 0, "name": "淘宝集市"}, {"id": 1, "name": "天猫商城"}],
        "1": [{"id": 0, "name": "SOP"}, {"id": 1, "name": "LBP"}]
    };

    //支付宝授权状态
    data["pay_auth_state"] = [
        {"id": "all", "name": "全部"},
        {"id": "0", "name": "未授权"},
        {"id": "1", "name": "已授权"},
        {"id": "2", "name": "授权失效"},
        {"id": "3", "name": "授权停用"}
    ];

    //子订单类型
    data["order_type"] = [
        {"id": "all", "name": "全部", "selected": true},
        {"id": "0", "name": "正常物品"},
        {"id": "1", "name": "虚拟货品"},
        {"id": "2", "name": "服务"}
    ];

    //订单类型
    data["trade_type"] = [
        {"id": "all", "name": "全部", "selected": true},
        {"id": "1", "name": "网店销售"},
        {"id": "2", "name": "线下零售"},
        {"id": "3", "name": "售后换货"},
       // {"id": "4", "name": "批发业务"}
    ];

    //订单日志操作类型
    //*****************当前可用id 173 ,新加id需改备注****************
    data['type'] = [
        {"id": "all", "name": "全部", "selected": true},
        {"id": "9" , "name": "订单审核"},
        {"id": "10", "name": "强制审核"},
        {"id": "12", "name": "快速审核"},
        {"id": "28", "name": "订单冻结"},
        {"id": "29", "name": "订单解冻"},
        {"id": "30", "name": "订单驳回"},
        {"id": "35", "name": "订单清除驳回和异常"},
        {"id": "37", "name": "订单拆分"},
        {"id": "38", "name": "手动合并订单"},
        {"id": "33", "name": "转入审核"},
        {"id": "45", "name": "订单财务审核"},
        {"id": "46", "name": "进入财务审核"},
        {"id": "20", "name": "修改物流公司"},
        {"id": "21", "name": "修改物流单号"},
        {"id": "170", "name": "修改预估邮费"},
        {"id": "40", "name": "修改收货人姓名"},
        {"id": "41", "name": "修改收货人手机"},
        {"id": "42", "name": "修改收货人电话"},
        {"id": "43", "name": "修改客服备注"},
        {"id": "44", "name": "修改收货人地址"},
        {"id": "47", "name": "修改收货人大头笔"},
        {"id": "48", "name": "修改店铺"},
        {"id": "49", "name": "修改打印备注"},
        {"id": "53", "name": "修改业务员"},
        {"id": "54", "name": "修改订单类型"},
        {"id": "56", "name": "修改发票类型"},
        {"id": "57", "name": "修改发票抬头"},
        {"id": "58", "name": "修改发票内容"},
        {"id": "59", "name": "修改发货条件"},
        {"id": "17", "name": "平台更换货品"},
        {"id": "23", "name": "删除货品"},
        {"id": "60", "name": "添加货品"},
        {"id": "61", "name": "修改货品"},
        {"id": "18", "name": "更换规格"},
        {"id": "19", "name": "更换货品"},
        {"id": "6", "name": "拦截出库"},
        {"id": "160", "name": "取消拦截"},
        {"id": "120", "name": "撤销验货或称重"},
        {"id": "140", "name": "物流同步"},
        {"id": "155", "name": "获取物流单号"},
        {"id": "165", "name": "回收电子面单号"},
        {"id": "162", "name": "添加多物流单号"},
        {"id": "163", "name": "修改多物流单号"},
        {"id": "164", "name": "删除多物流单号"},
        {"id": "141", "name": "人工重新物流同步"},
        {"id": "142", "name": "人工取消物流同步"},
        {"id": "143", "name": "人工设置物流同步成功"},
        {"id": "7", "name": "退款"},
        {"id": "8", "name": "部分退款"},
        {"id": "80", "name": "客户打款"},
        {"id": "32", "name": "收件地址变化"},
        {"id": "11", "name": "发票变化"},
        {"id": "34", "name": "仓库变化"},
        {"id": "13", "name": "恢复子订单"},
        {"id": "14", "name": "订单递交处理完毕"},
        {"id": "15", "name": "子订单退款"},
        {"id": "16", "name": "订单开发票"},
        {"id": "90", "name": "清除打印"},
        {"id": "91", "name": "标记打印"},
        {"id": "100", "name": "验货"},
        {"id": "101", "name": "打包"},
        {"id": "102", "name": "称重"},
        {"id": "103", "name": "出库"},
        {"id": "104", "name": "发货中"},
        {"id": "105", "name": "发货"},
        {"id": "1", "name": "下单"},
        {"id": "2", "name": "付款"},
        {"id": "3", "name": "递交"},
        {"id": "4", "name": "关闭"},
        {"id": "5", "name": "自动合并订单"},
        {"id": "110", "name": "查看号码"},
        {"id": "50", "name": "历史订单导入"},
        {"id": "55", "name": "订单导出"},
        {"id": "161", "name": "订单删除"},
        {"id": "166", "name": "订单无需处理"},
        {"id": "167", "name": "恢复无需处理订单"},
        {"id": "168", "name": "撤销出库"},
        {"id": "169", "name": "执行策略"},
        {"id": "171", "name": "拣货墙(框)"},
		{"id": "172", "name": "拦截赠品"},
		{"id": "300", "name": "委外仓储"}
        // {"id": "24", "name": "销售出库单签入 订单签入"},
        // {"id": "25", "name": "订单强制签出 强制销售出库单签出"},
        // {"id": "26", "name": "开具发票"},
        // {"id": "27", "name": "开具红字发票"},
        // {"id": "31", "name": "现款销售订单"},
        // {"id": "51", "name": "修改换出货品"},
        // {"id": "52", "name": "修改退款货品"},
        // {"id": "62", "name": "修改退换类型"},
        // {"id": "63", "name": "修改退换订单"},
        // {"id": "64", "name": "修改退换原因"},
        // {"id": "65", "name": "修改退款金额"},
        // {"id": "66", "name": "修改买家账号"},
        // {"id": "67", "name": "修改退换单退货仓库"},
        // {"id": "68", "name": "修改退回货品物流信息"},
        // {"id": "69", "name": "修改换出货品物流信息"},
    ];

    //系统日志类型
    data['sys_other_log_type'] = [
        {"id": "all", "name": "全部", "selected": true},
       // {"id": "1", "name": "用户登录"},
        //{"id": "2", "name": "仓库"},
       // {"id": "3", "name": "店铺授权"},
       // {"id": "4", "name": "停止店铺授权"},
        {"id": "5", "name": "系统设置"},
        {"id": "6", "name": "赠品策略"},
       // {"id": "7", "name": "仓库店铺策略设置"},
        {"id": "8", "name": "物流策略"},
        //{"id": "9", "name": "备注提取策略"},
        //{"id": "10", "name": "转预订单策略"},
       // {"id": "11", "name": "库存同步"},
        //{"id": "12", "name": "回访策略"},
       // {"id": "13", "name": "实施助手清理"},
       // {"id": "14", "name": "平台货品设置"},
        {"id": "15", "name": "库存同步策略"},
        //{"id": "16", "name": "自定义属性值"},
        //{"id": "17", "name": "查看原始单敏感信息"},
		{"id": "18", "name": "店铺"},
		{"id": "19", "name": "仓库"},
		{"id": "20", "name": "员工"},
		{"id": "21", "name": "物流"},
        {"id": "22", "name": "全局操作"},
        {"id": "23", "name": "分拣墙"},
    ];

    //商品日志操作类型
    data['operator_type']=[
        {"id": "all", "name": "全部", "selected": true},
        {"id": "11", "name": "添加货品"},
        {"id": "12", "name": "添加单品"},
        {"id": "13", "name": "添加组合装"},
        {"id": "31", "name": "删除货品"},
        {"id": "32", "name": "删除单品"},
        {"id": "33", "name": "删除组合装"},
        {"id": "51", "name": "修改货品"},
        {"id": "52", "name": "修改单品"},
        {"id": "53", "name": "修改组合装"},
        {"id": "54", "name": "品牌操作"},
        {"id": "58", "name": "条形码操作"},
        {"id": "60", "name": "转化为组合装"},
    ];

    //入库单状态
    // Any questions please contact gaosong
    data['stockin_status'] = [
        {"id": "all", "name": "全部", "selected": true},
        {"id": "10", "name": "已取消"},
        {"id": "20", "name": "编辑中"},
        {"id": "30", "name": "待审核"},
       {"id": "32", "name": "待推送"},
        {"id": "35", "name": "委外待入库"},
       {"id": "40", "name": "待关联"},
        {"id": "50", "name": "待价格确认"},
        {"id": "60", "name": "待结算"},
        {"id": "70", "name": "暂估结算"},
        {"id": "80", "name": "已完成"},
    ];

    //入库类型
    // Any questions please contact gaosong
    data['stockin_type'] = [
        {"id": "all", "name": "全部", "selected": true},
		{"id": "1", "name": "采购入库"},
        {"id": "2", "name": "调拨入库"},
        {"id": "3", "name": "退货入库"},
        {"id": "4", "name": "盘盈入库"},
        //{"id": "5", "name": "生产入库"},
        {"id": "6", "name": "其他入库"},
        //{"id": "7", "name": "少发入库"},
        //{"id": "8", "name": "纠错入库"},
        {"id": "9", "name": "初始化入库"},
		{"id":"12","name": "委外入库"},
    ];

    //平台货品状态
    //table:api_goods_spec
    data["api_goods_spec_status"] = [
        {"id": "all", "name": "全部", "selected": true},
        {"id": "0", "name": "删除"},
        {"id": "1", "name": "在架"},
        {"id": "2", "name": "下架"}
    ];

    //系统货品类型
    //table:api_goods_spec.match_target_type
    data["match_target_type"] = [
        {"id": "all", "name": "全部", "selected": true},
        {"id": "0", "name": "无"},
        {"id": "1", "name": "单品"},
        {"id": "2", "name": "组合装"}
    ];

    //占用库存方式
    //table:api_goods_spec.hold_stock_type
    data["hold_stock_type"] = [
        {"id": "all", "name": "全部", "selected": true},
        {"id": "0", "name": "无"},
        {"id": "1", "name": "拍下减库存"},
        {"id": "2", "name": "付款减库存"}
    ];

    //仓库类型
    data["warehouse_type"] = [
        {"id": "all", "name": "全部", "selected": true},
        {"id": "1", "name": "普通仓库"},
        // {"id": "2", "name": "物流宝"},
        // {"id": "3", "name": "京东仓储"},
        // {"id": "4", "name": "科捷"},
        // {"id": "5", "name": "顺丰曼哈顿"},
        {"id": "11", "name": "奇门仓储"},
        {"id": "0", "name": "不限"}
    ];
    //仓库类型
    //table:api_trade.wms_type
    data["wms_type"] = [
        {"id": "all", "name": "全部", "selected": true},
        {"id": "0", "name": "任意仓库"},
        {"id": "1", "name": "普通仓库"},
        {"id": "2", "name": "物流宝"},
        {"id": "3", "name": "京东仓储"},
        {"id": "4", "name": "其他"}
    ];
    //客户类别
    //author:luyanfeng
    //table:crm_customer.type
    data["customer_type"] = [
        {"id": "all", "name": "全部", "selected": true},
        {"id": "0", "name": "普通客户"},
        // {"id": "1", "name": "经销商"}
    ];

    //性别
    //author:luyanfeng
    data["sex"] = [
        {"id": "all", "name": "全部", "selected": true},
        {"id": "0", "name": "未确定"},
        {"id": "1", "name": "男"},
        {"id": "2", "name": "女"}
    ];
    //销售出库单状态  销售出库状态还是跟出库单状态是有区别的，所以要分开写两个
    data['salesstockout_status'] = [
        {"id": "all", "name": "全部", "selected": true},
        //{"id":"5"   ,"name":"已取消"},
        //{"id":"48"  ,"name":"编辑中"},
        //{"id":"50"  ,"name":"待审核"},
        {"id":"57"  ,"name":"待推送"},
		{"id":"56"  ,"name":"推送失败"},
        //{"id":"53"  ,"name":"同步失败"},
        //{"id":"54"  ,"name":"获取面单号"},
        {"id": "55", "name": "已审核"},
        //{"id": "90", "name": "部分发货"},
        {"id": "95", "name": "已发货"},
		{"id": "60", "name": "待出库"},
        {"id": "100", "name": "已签收"},
        {"id": "105", "name": "部分打款"},
        {"id": "110", "name": "已完成"}
    ];
	//盘点状态  
	 data["stockpd_status"] = [
        {"id": "all", "name": "全部", "selected": true},
        {"id": "1", "name": "编辑中"},
       {"id": "2", "name": "已完成"},
        {"id": "3", "name": "已取消"},
    ];
	//盘点方案 -- 开始只有单品盘点
	data["stockpd_type"] = [
		{"id": "all", "name": "全部", "selected": true},
		{"id":"0","name":"单品盘点"},
		{"id":"1","name":"货位盘点"},
		{"id":"2","name":"明细盘点"},
	]
    //出库单状态
    data["stockout_status"] = [
        {"id": "all", "name": "全部", "selected": true},
        {"id": "5", "name": "已取消"},
        {"id": "48", "name": "编辑中"},
       {"id": "57", "name": "待推送"},
       {"id": "53", "name": "同步失败"},
//        {"id": "54", "name": "获取面单号"},
       {"id": "55", "name": "已审核"},
	   {"id": "56", "name": "推送失败"},
	    {"id": "60", "name": "待出库"},
//        {"id": "90", "name": "部分发货"},
       {"id": "95", "name": "已发货"},
//        {"id": "100", "name": "已签收"},
//        {"id": "105", "name": "部分打款"},
        {"id": "110", "name": "已完成"},
    ];

    //历史出库单中出库单状态
    data["history_stockout_status"] = [
        {"id": "all", "name": "全部", "selected": true},
        {"id": "5", "name": "已取消"},
        //{"id": "48", "name": "编辑中"},
        //{"id": "52", "name": "待推送"},
        //{"id": "53", "name": "同步失败"},
        //{"id": "54", "name": "获取面单号"},
        //{"id": "55", "name": "已审核"},
       // {"id": "90", "name": "部分发货"},
        {"id": "95", "name": "已发货"},
        {"id": "100", "name": "已签收"},
        //{"id": "105", "name": "部分打款"},
        {"id": "110", "name": "已完成"},
    ];


    //出库单管理中出库单类别   homedown
    data["stockout_type"] = [
        {"id": "all", "name": "全部", "selected": true},
        {"id": "2", "name": "调拨出库"},
		{"id": "3", "name": "采购退货出库"},
        {"id": "4", "name": "盘亏出库"},
        {"id": "7", "name": "其它出库"},
        {"id": "11","name":"初始化出库"},
		 {"id": "13","name":"委外出库"},
    ];

    //出库单汇总中出库单类别   homedown
    data["stockout_type_all"] = [
        {"id": "all", "name": "全部", "selected": true},
        {"id": "1", "name": "销售出库"},
        {"id": "2", "name": "调拨出库"},
        {"id": "3", "name": "采购退货出库"},
        {"id": "4", "name": "盘亏出库"},
        //{"id": "5", "name": "生产出库"},
        //{"id": "6", "name": "现款销售出库"},
        {"id": "7", "name": "其它出库"},
        //{"id": "8", "name": "多发出库"},
        //{"id": "9", "name": "纠错出库"},
        //{"id": "10", "name": "保修配件出库"},
        {"id": "11","name":"初始化出库"},
		{"id": "13","name":"委外出库"},
    ];


    //出库原因 homedown
    data["stockout_reason"] = [
        //{"id": "all", "name": "-请选择-"},
        {"id": "7", "name": "其它出库","selected":true},
		 {"id": "3", "name": "采购退货出库"},
    ];
	//调拨单状态
	 data["stocktrans_status"] = [
        {"id": "all", "name": "全部", "selected": true},
        {"id": "10"   ,"name":"已取消"},
        {"id": "20"  ,"name":"编辑中"},
     //   {"id": "30"  ,"name":"待审核"},
      //  {"id": "40"  ,"name":"已审核"},
		{"id": "42"  ,"name":"出库单待推送"},
		{"id": "44"  ,"name":"出库单推送失败"},
		{"id": "46"  ,"name":"待出库"},
		{"id": "50"  ,"name":"部分出库"},
		{"id": "62"  ,"name":"入库单待推送"},
		{"id": "64"  ,"name":"入库单推送失败"},
		{"id": "66"  ,"name":"待入库"},
        {"id": "90"  ,"name":"调拨完成"},
    ];
	//调拨类型
	 data["stocktrans_type"] = [
		{"id": "all", "name": "全部", "selected": true},
		{"id": "1"   ,"name":"快速调拨"},
	];
    //调拨类型
	 data["stocktrans_mode"] = [
		{"id": "all", "name": "全部", "selected": true},
		{"id": "0"   ,"name":"单品调拨"},
		{"id": "1"   ,"name":"货位调拨"},
	];
    //担保方式
    data["guarantee_mode"] = [
        {"id": "all", "name": "全部", "selected": true},
        {"id": "1", "name": "担保"},
        {"id": "2", "name": "非担保"},
        {"id": "3", "name": "在线非担保"},
    ];
    //赠品方式
    data["gift_type"] = [
        {"id": "all", "name": "全部", "selected": true},
        {"id": "0", "name": "非赠品"},
        {"id": "1", "name": "自动赠品"},
        {"id": "2", "name": "手工赠送"},
    ];
    //平台退款状态
    data["api_refund_status"] = [
        {"id": "all", "name": "全部", "selected": true},
        {"id": "0", "name": ""},
        {"id": "1", "name": "取消退款"},
        {"id": "2", "name": "已申请退款"},
        {"id": "3", "name": "等待退款"},
        {"id": "4", "name": "等待收货"},
        {"id": "5", "name": "退款成功"},
    ];
    //货品类型
    data["goods_type"] = [
        {"id": "all", "name": "全部", "selected": true},
        {"id": "1", "name": "销售商品"},
        // {"id": "2", "name": "原材料"},
        // {"id": "3", "name": "包装"},
        // {"id": "4", "name": "周转材料"},
        // {"id": "5", "name": "虚拟商品"},
        // {"id": "6", "name": "固定资产"},
        {"id": "0", "name": "其它"},
    ];
    //大件分类
     data["large_type"] = [
	 {"id": "all", "name": "全部", "selected": true},
     {"id": "0", "name": "非大件"},
     {"id": "1", "name": "普通大件"},
     {"id": "2", "name": "独立大件"},
     ];
    //退换-处理状态
    data["refund_process_status"] = [
        {"id": "all", "name": "全部", "selected": true},
        {"id": "10", "name": "已取消"},
        {"id": "20", "name": "待审核"},
        {"id": "30", "name": "已同意"},
        {"id": "40", "name": "已拒绝"},
        {"id": "50", "name": "待财审"},
        {"id": "60", "name": "待收货"},
		{"id": "63", "name": "待推送"},
        {"id": "64", "name": "推送失败"},
        {"id": "65", "name": "推送成功 "},
        {"id": "70", "name": "部分到货"},
        {"id": "80", "name": "待结算 "},
        {"id": "90", "name": "已完成"},
    ];
    //退换入库 dialog-- 搜索
    data["refund_process_status_stockin"] = [
        {"id": "all", "name": "全部", "selected": true},
        {"id": "60", "name": "待收货"},
        {"id": "70", "name": "部分到货"},
	];                                
    //执行价格  
    data["execute_price"] = [
        {"id": "all", "name": "全部", "selected": true},
        {"id": "retail_price", "name": "零售价"},
        // {"id": "wholesale_price", "name": "批发价"},
        {"id": "market_price", "name": "市场价"},
        // {"id": "member_price", "name": "会员价"},
    ];
    //标旗
    data["remark_flag"] = [
        {"id": "all", "name": "全部", "selected": true},
        {"id": "0", "name": "无"},
        {"id": "1", "name": "红"},
        {"id": "2", "name": "黄"},
        {"id": "3", "name": "绿"},
        {"id": "4", "name": "蓝"},
        {"id": "5", "name": "紫"}
    ];
    //有无备注
    data["remark"] = [
        {"id": "all", "name": "全部", "selected": true},
        {"id": "0", "name": "无备注"},
        {"id": "1", "name": "有备注"},
        {"id": "2", "name": "有客服备注"},
        {"id": "3", "name": "有买家留言"},
        {"id": "4", "name": "备注+留言"}
    ];
    //字体用于设计标识
    data["font_family"] = [
        {"font_id": "SimSun", "font_name": "宋体"},
        {"font_id": "Microsoft YaHei", "font_name": "微软雅黑"},
        {"font_id": "SimHei", "font_name": "黑体"},
        {"font_id": "KaiTi", "font_name": "楷体"},
        {"font_id": "DFKai-SB", "font_name": "标楷体"},
        {"font_id": "MingLiU", "font_name": "细明体"},
    ];
    //单号类型
    data["bill_type"] = [
        // {"id": "all", "name": "全部", "selected": true},
        {"id": "0", "name": "普通物流单", "selected": true},
        {"id": "1", "name": "线下电子面单"},
        {"id": "2", "name": "菜鸟电子面单"},
    ];
    //组合装打印内容
    data["suit_print_type"] = [
        {"id": "0", "name": "组合装明细", "selected": true},
        {"id": "1", "name": "组合装名称"},
        {"id": "2", "name": "组合装名称及明细"},
    ];
    //物流同步状态

    data['logistics_sync_status'] = [
        {'name': '淘宝发货等待判断', 'id': '-3'},
        {'name': '淘宝发货不可达', 'id': '-2'},
        {'name': '淘宝发货可达', 'id': '-1'},
        {'name': '等待同步', 'id': '0'},
        {'name': '提交运单信息失败', 'id': '1'},
        {'name': '同步失败', 'id': '2'},
        {'name': '同步成功', 'id': '3'},
        {'name': '手动设置同步成功', 'id': '4'},
        {'name': '手动取消同步', 'id': '5'},
    ];
    //入库原因  homedown
    data['stockin_reason'] = [
        {'name': '-请选择-', 'id': 'all'},
        {'name': '快速采购', 'id': '1'},
         {'name': '采购入库', 'id': '11'},
        {'name': '退换入库', 'id': '3'},     
	   {'name': '其它入库', 'id': '6'},
		
    ];
	//盘点方案 --开始只有单品盘点
	data['pd_mode'] = [
		//{'name': '-请选择-', 'id': 'all'},
		{'name': '单品盘点', 'id': '0'},
		//{'name': '货位盘点', 'id': '1'},
		//{'name': '明细盘点', 'id': '2'},
	];
	//采购状态
	data['purchase_status'] = [
		{'name':'全部','id':'all','selected': true},
		{'name':'已取消','id':'10'},
		{'name':'编辑中','id':'20'},
		{'name':'待审核','id':'30'},
		{'name':'待推送','id':'43'},
		{'name':'推送失败','id':'45'},
		{'name':'推送成功','id':'48'},
		{'name':'已审核','id':'40'},
		{'name':'部分到货','id':'50'},
		//{'name':'已到货','id':'60'},
		//{'name':'待结算','id':'70'},
		//{'name':'部分结算','id':'80'},
		{'name':'已完成','id':'90'},
	];
    //采购退货状态
    data['purchase_return_status'] = [
        {'name':'全部','id':'all','selected': true},
        {'name':'已取消','id':'10'},
        {'name':'编辑中','id':'20'},
        // {'name':'待审核','id':'30'},
         {'name':'待推送','id':'42'},
         {'name':'推送失败','id':'44'},
         {'name':'委外待出库','id':'46'},
        {'name':'已审核','id':'40'},
        {'name':'部分出库','id':'50'},
        {'name':'已完成','id':'90'},
    ];
    // 入库开单使用  homedown
    data['list_price'] = [
        {'name': '0.0000', 'id': 'all'},
//        {'name': '批发价', 'id': 'wholesale_price'},
        {'name': '零售价', 'id': 'retail_price'},
        {'name': '最低价', 'id': 'lowest_price'},
//        {'name': '会员价', 'id': 'member_price'},
        {'name': '市场价', 'id': 'market_price'},
        {'name': '原价', 'id': 'src_price'},
    ];
    /*data['roles_mask'] = [
        {'name': '业务员', 'id': '1'},
        {'name': '审单员', 'id': '2'},
        {'name': '打单员', 'id': '4'},
        {'name': '扫描员', 'id': '8'},
        {'name': '打包员', 'id': '16'},
        {'name': '拣货员', 'id': '32'},
        {'name': '发货员', 'id': '64'},
        {'name': '结算员', 'id': '128'},
        {'name': '检视员', 'id': '256'},
    ];*/
    //"0待使用","1top已使用",L"3有故障",L"4回收失败",L"5待回收","6已回收"',
    data['stock_logistics_no_status'] = [
        {'name': '全部', 'id': 'all',"selected": true},
        {'name':'待使用','id':'0'},
        {'name': '已使用', 'id': '1'},
        {'name': '有故障', 'id': '3'},
        {'name': '回收失败', 'id': '4'},
        {'name': '待回收', 'id': '5'},
        {'name': '已回收', 'id': '6'},
        {'name': '已完成', 'id': '7'},
    ];
    data['history_stock_logistics_no_status'] = [
        {'name': '全部', 'id': 'all',"selected": true},
        {'name': '已使用', 'id': '1'},
        {'name': '有故障', 'id': '3'},
        {'name': '回收失败', 'id': '4'},
        {'name': '已回收', 'id': '6'},
    ];
    //设置扫描类别  扫描验货  homedown
    data['scan_class'] = [
        {'name':'订单','id':'trade_no','selected':true},
        {'name':'货品条码','id':'barcode'},
        {'name':'货品唯一码','id':'unique_code'}
	];
    //拦截原因  销售出库 、单据打印 拦截原因提示信息  homedown
    data['stockout_block_reason'] =[
        {'id':'all', 'name':'无',"selected": true},
		{'id':'1',   'name':'申请退款'},
		{'id':'2',   'name':'已退款'},
		{'id':'4',   'name':'地址被修改'},
		{'id':'8',   'name':'发票被修改'},
		{'id':'16',  'name':'物流被修改'},
		{'id':'32',  'name':'仓库变化'},
		{'id':'64',  'name':'备注修改'},
		{'id':'128', 'name':'更换货品'},
		{'id':'256', 'name':'取消退款'},
	//	{'id':'512', 'name': '放弃抢单'},
	//	{'id':'1024','name': '其他'},
		{'id':'2048','name': '拦截赠品'},
		{'id':'4096','name': '平台已发货'},
    ];
    //异常原因
    data['bad_reason'] =[
        {'id':'all', 'name':'无',"selected": true},
        {'id':'1',   'name':'无库存记录'},
        {'id':'2',   'name':'地址发生变化'},
        {'id':'4',   'name':'发票变化'},
        {'id':'8',   'name':'仓库变化'},
        {'id':'16',  'name':'备注变化'},
        {'id':'32',  'name':'平台更换货品'},
        {'id':'64',  'name':'退款'},
        {'id':'128', 'name':'平台换货自动更换订单货品'},
        {'id':'256', 'name':'平台已发货'},
        {'id':'512', 'name':'拦截赠品'},
    ];
    //员工角色
    data["role"] = [
        {"id": "all", "name": "全部", "selected": true},
        {"id": "2", "name": "超级管理员"},
        {"id": "0", "name": "普通用户"},
        {"id": "1", "name": "管理员"},
	];
    //打印状态
    data['print_status'] =[
        {'id':'0','name':'未打印'},
        {'id':'1','name':'已打印'},
        {'id':'3','name':'打印中'},
        {'id':'all','name':'全部'},
    ];
    //修改打印状态
    data['print_status_chg'] = [
        {'id':'0','name':'未打印'},
        {'id':'1','name':'已打印'},
        {'id':'all','name':'不修改'},
    ];
    //多物流打印状态
    data['multiple_print_status'] =[
        {'id':'0','name':''},
        {'id':'1','name':'已打印'},
        {'id':'2','name':'未打印'},
        //{'id':'3','name':'打印中'},
        {'id':'all','name':'全部'},
    ];
    //打印类型
    data['print_type'] =[
        {'id':'1','name':'发货单'},
        {'id':'2','name':'物流单'},
    ];
    data["gift_rule_status"] = [
		{"id": "all", "name": "全部", "selected": true},
		{"id": "0", "name": "未开始"},
		{"id": "1", "name": "执行中"},
		{"id": "2", "name": "已结束"},
    ];
    data["time_type"] = [
		{"id": "all", "name": "全部", "selected": true},
		{"id": "1", "name": "付款时间"},
		//{"id": "2", "name": "登记时间"},
		{"id": "3", "name": "下单时间"},
    ];
    data["rule_priority"] = [
        {"id": "all", "name": "全部", "selected": true},
        {"id": "1", "name": "1"},
        {"id": "2", "name": "2"},
        {"id": "3", "name": "3"},
        {"id": "4", "name": "4"},
        {"id": "5", "name": "5"},
    ];
    data["rule_group"] = [
        {"id": "all", "name": "全部", "selected": true},
        {"id": "0", "name": "默认分组"},
    ];
    data["gift_rules"] = [
		{"id": "shop_list", "name": "店铺"},
        {"id": "time_type", "name": "策略有效期"},
        {"id": "goods_count", "name": "货品总数"},
        {"id": "specify_count", "name": "指定货品数量"},
        // {"id": "goods_amount", "name": "货款总额(包含优惠)"},
        {"id": "specify_amount", "name": "指定货品金额"},
        {"id": "receivable", "name": "订单实收(包含邮费)"},
        {"id": "specify_multiple", "name": "指定货品倍增"},
        {"id": "goods_name_keyword", "name": "宝贝名称关键词"},
        // {"id": "csremark_keyword", "name": "客服备注关键词"},
    ];
    //调拨开单中调拨类型
    data['stock_transfer_type'] = [
        //{"id": "all", "name": "-请选择-","selected":true},
        //{"id": "0", "name": "分步调拨"},
        {"id": "1", "name": "快速调拨","selected":true},
    ];
    //调拨开单中调拨方案
    data['stock_transfer_mode'] = [
        //{"id": "all", "name": "-请选择-","selected":true},
        {"id": "0"  , "name": "单品调拨","selected":true},
        {"id": "1", "name": "货位调拨"},
        //{"id": "2", "name": "明细调拨"},
    ];
    data['logistics_generate_rule'] = [
        {'id':"0","name":"单号递增","selected": true}
    ];
    data["trunc_mode"] = [
        {"id": "all", "name": "全部", "selected": true},
        {"id": "1", "name": "向上取整"},
        {"id": "2", "name": "向下取整"},
        {"id": "3", "name": "四舍五入"},
        {"id": "4", "name": "按比例"},
    ];
    data["logistics_status"] = [
        {"id": "0", "name": "待结算"},
        {"id": "1", "name": "已结算"},
        {"id": "2", "name": "已冲销"}
    ];
  //业务员提取备注前缀、后缀
    data["macro_begin"]=[
        {"id": "0", "name": "无"},
        {"id": "(", "name": "("},
        {"id": "（", "name": "（"},
        {"id": "[", "name": "["},
        {"id": "【", "name": "【"},
        {"id": "{", "name": "{"},
        {"id": "｛", "name": "｛"},
    ];
    data["macro_end"]=[
        {"id": "0", "name": "无"}, 
        {"id": ")", "name": ")"},
        {"id": "）", "name": "）"},
        {"id": "]", "name": "]"},
        {"id": "】", "name": "】"},
        {"id": "}", "name": "}"},
        {"id": "｝", "name": "｝"},
    ];
    //提取备注处理类型
    data["cs_remark_type"]=[
        {"id": "0", "name": "无"},
        {"id": "1", "name": "修改物流方式为"},
        {"id": "2", "name": "修改订单标记为"},
        {"id": "3", "name": "修改业务员为"},
        {"id": "4", "name": "修改仓库为"},
//        {"id": "5", "name": "转预订单"},
        {"id": "6", "name": "冻结订单"},
    ];
    data["client_remark_type"]=[
        {"id": "0", "name": "无"},
        {"id": "1", "name": "修改物流方式为"},
        {"id": "2", "name": "修改订单标记为"},
        {"id": "4", "name": "修改仓库为"},
        {"id": "6", "name": "冻结订单"},
    ];
    //批量拆分订单类型
    data["passel_split_type"]=[
        {"id": "0", "name": "根据订单中相同货品进行批量拆分"},
        {"id": "1", "name": "根据订单重量进行批量拆分"},
    ];
    //订单财审情况
    data["trade_fc_status"]=[
        {"id":"all", "name":"全部","selected": true},
        {"id":"1", "name":"未财审"},
        {"id":"2", "name":"已财审"},
    ];
    //库存货品日志
    data["stockspeclog_type"]=[
        {"id":"all", "name":"全部","selected": true},
        {"id":"1", "name":"更新警戒库存"},
        {"id":"2", "name":"入库"},
        {"id":"3", "name":"出库"},
    ];
    data["is_match"]=[
        {"id":"all", "name":"全部","selected": true},
        {"id":"0", "name":"未匹配"},
        {"id":"1", "name":"已匹配"}
    ];
    data["is_name_changed"]=[
        {"id":"all", "name":"全部","selected": true},
        {"id":"1", "name":"已改变"},
        {"id":"0", "name":"未改变"}
    ];
    data["sms_event_type"]=[
        {"id":"0", "name":"无"},
        {"id":"1", "name":"发货"},
        {"id":"2", "name":"退货入库"},
        {"id":"3", "name":"换货发出"},
        {"id":"4", "name":"催未付款"},
        {"id":"8", "name":"签收"}
    ];
    data["sms_status"]=[
        {"id":"0", "name":"待发送"},
        {"id":"1", "name":"发送中"},
        {"id":"2", "name":"已发送"},
        {"id":"3", "name":"发送失败"},
        {"id":"4", "name":"取消发送"},
    ];
    //原始退款单类型
    data["api_refund_type"]=[
        {"id": "all", "name": "全部", "selected": true},
        {"id":"0","name":"取消订单"},
        {"id":"1","name":"退款"},
        {"id":"2","name":"退货"},
        {"id":"3","name":"换货"},
        {"id":"4","name":"退款不退货"},
    ]
    //原始退款单系统状态
    data["api_refund_process_status"]=[
        {"id": "all", "name": "全部", "selected": true},
        {"id":"0", "name":"待递交"},
        {"id":"10","name":"已取消"},
        {"id":"20","name":"已递交"},
        {"id":"90","name":"已完成"},
    ];
    //原始退款单平台状态
    data["api_refund_status"]=[
	    {"id": "all", "name": "全部", "selected": true},
        {"id":"1","name":"取消退款"},
        {"id":"2","name":"已申请退款"},
        {"id":"3","name":"等待退货"},
        {"id":"4","name":"等待收货"},
        {"id":"5","name":"退款成功"},
    ];
    //原始退款单退款原因
    data["api_refund_reason"]=[
	    {"id": "all", "name": "全部", "selected": true},
        {"id":"1", "name":"与商家协商一致退款"},
        {"id":"2", "name":"卖家缺货"},
        {"id":"3", "name":"拍错了/订单信息错误"},
        {"id":"4", "name":"不想要了"},
        {"id":"5", "name":"商品需要维修"},
        {"id":"6", "name":"包装/商品破损"},
        {"id":"7", "name":"卖家发错货"},
        {"id":"8", "name":"商品错发/漏发"},
        {"id":"9", "name":"多拍/拍错/不想要"},
        {"id":"10","name":"大小/尺寸/重量/厚度不符"},
        {"id":"11","name":"少件/漏发"},
        {"id":"12","name":"假冒品牌"},
        {"id":"13","name":"发票问题"},
        {"id":"14","name":"退运费"},
        {"id":"15","name":"收到商品描述不符"},
        {"id":"16","name":"商品质量问题"},
        {"id":"17","name":"七天无理由退换货"},
        {"id":"18","name":"未按约定时间发货"},
        {"id":"19","name":"其他"},
    ];
    //原始退款单客服介入
    data["api_refund_cs_status"]=[
        {"id":"0","name":"客服不介入"},
        {"id":"1","name":"需要客服介入"},
        {"id":"2","name":"客服已经介入"},
        {"id":"3","name":"客服初审完成"},
        {"id":"4","name":"客服主管复审失败"},
        {"id":"5","name":"客服处理完成"},
    ];
    //档口货品对账状态
    data["stat_stalls_status"]=[
        {"id": "all", "name": "全部", "selected": true},
        {"id":"0","name":"未导入"},
        {"id":"1","name":"待结算"},
        {"id":"2","name":"已结算"},
    ];
    formatter = {};
    formatter.type_market = function(value, row, index){
        if(1 == value){
            return '短信';
        } else if (0 == value){
            return '邮件';
        }
    };
    formatter.status_market = function(value, row, index){
        if(1 == value){
            return '已营销';
        } else if (0 == value){
            return '未营销';
        }else if (3 == value){
            return '营销失败';
        }
    };
    formatter.type_sms = function(value, row, index){
        if(1 == value){
            return '定时';
        } else if (0 == value){
            return '实时';
        }
    };
	formatter.sys_other_log_type = function (value, row, index) {
        var sys_other_log_type = data["sys_other_log_type"];
        for (var x in sys_other_log_type) {
            if (sys_other_log_type[x]["id"] == value) {
                return sys_other_log_type[x]["name"];
            }
        }
        return value;
    }
    formatter.sms_status = function (value, row, index) {
        var sms_status = data["sms_status"];
        for (var x in sms_status) {
            if (sms_status[x]["id"] == value) {
                return sms_status[x]["name"];
            }
        }
        return value;
    };
    formatter.image = function (value, row, index) {
        if (value) {
            return '<img class="td-img" src=\"' + value + '\" width=100px height=50px>';
        } else {
            return "";
        }
    }
    formatter.print_img = function (value, row, index) {
        var show_img ;
        if($('#img_describe').is('div')){
            show_img = $('#img_describe')
        }else{
            show_img = $('<div id="img_describe" style="display:none;position:absolute;z-index:100099;"></div>').appendTo('body');
        }
        formatter.print_img.showPic =  function(sUrl){
            var x,y;
            x = event.clientX+2;
            y = event.clientY+2;

            $(show_img).html("<img style='width:250px;height:250px' src=\"" + sUrl + "\">");
           var width = $(show_img).width();
           var height  = $(show_img).height();
            var parentWidth = $(show_img).parent().width();
            var parentHeight = $(show_img).parent().height();
            if( x >parentWidth-width){
                x = event.clientX-2-width;
            }
            if( y >parentHeight-height){
                y = event.clientY-2-height;
            }
            $(show_img).css({left:x,top:y,display:'block'});
            // alert('tes');
        };
        formatter.print_img.hiddenPic = function(){
            $(show_img).css({display:'none'});
            $(show_img).html("");
            // alert('tes');
        }
        var img_html = '';
        var count=0;
        if (value) {
            var img_ar = value.split(',');
            for(var i in img_ar)
            {
                if(count>6)
                {
                    break;
                }
                var info = img_ar[i].split('::');
                if(info[0] == '无')
                {
                    //img_html += '<div style="display: inline-block;position: relative;border: 1px solid #7CAAB1"><span class="sku_qty">'+info[1]+'</span><img src=\"/public/Image/null.png\" width=50px height=40px  title="'+info[2]+'"/></div>';
                    img_html += '<div style="display: inline-block;position: relative;border: 1px solid #7CAAB1"><img src=\"/public/Image/null.png\" width=50px height=40px  title="'+info[2]+'"/></div>';
                }else{
                    //img_html += '<div style="display: inline-block;position: relative;border: 1px solid #7CAAB1"><span class="sku_qty">'+info[1]+'</span><img src=\"'+info[0]+'\" width=50px height=40px  onmouseout="formatter.print_img.hiddenPic();" onmousemove="formatter.print_img.showPic(\''+info[0]+'\');"/></div>';
                    img_html += '<div style="display: inline-block;position: relative;border: 1px solid #7CAAB1"><img src=\"'+info[0]+'\" width=50px height=40px  onmouseout="formatter.print_img.hiddenPic();" onmousemove="formatter.print_img.showPic(\''+info[0]+'\');"/></div>';
                }
                count++;
            }
        }
        return img_html;
    }
     formatter.styler = function(value, row, index){
        return 'color:red;';
    }
    formatter.boolen = function (value, row, index) {
        if (1 == value) {
            return '是';
        } else if (0 == value) {
            return '否';
        }
    };
	formatter.show_checkbox=function(value, row, index) {
        if (1 == value) {
            return '<input type="checkbox" name="DataGridCheckbox" checked="checked">';
        } else if (0 == value) {
            return '<input type="checkbox" name="DataGridCheckbox">';
        }
    };
    formatter.sms_type = function(value, row, index){
        if(1 == value){
            return '营销短信';
        } else if (0 == value){
            return '非营销短信';
        }
    };
    formatter.sms_send_sms_type = function(value, row, index){
        if(1 == value){
            return '非营销短信';
        } else{
            return '营销短信';
        }
    };
    formatter.sms_event_type =function (value,row,index){
        switch(value) {
            case '0':
                return '无';
                break;
            case '1':
                return '发货';
                break;
            case '2':
                return '退货入库';
                break;
            case '3':
                return '换货发出';
                break;
            case '4':
                return '催未付款';
                break;
           /* case '5':
                return '签收';
                break;*/
        }
    };
    formatter.print_status = function (value, row, index) {
        if (1 == value) {
            return '已打印';
        } else if (0 == value) {
            return '未打印';
        } else if (3 == value) {
            return '打印中';
        }
    };
    formatter.print_type = function (value, row, index) {
        if (1 == value) {
            return '发货单';
        } else if (2 == value) {
            return '物流单';
        } else if (3 == value) {
            return '分拣单';
        }
    };
    formatter.print_status_chg = function (value, row, index) {
        var print_status_chg = data["print_status_chg"];
        for (var x in print_status_chg) {
            if (print_status_chg[x]["id"] == value) {
                return print_status_chg[x]["name"];
            }
        }
        return value;
    };
    formatter.multiple_print_status = function (value, row, index) {
        if (1 == value) {
            return '已打印';
        } else if (0 == value) {
            return '';
        } else if (2 == value) {
            return '未打印';
        } else if (3 == value) {
            return '打印中';
        }
    };
    formatter.sex = function (value, row, index) {
    	var sex = data["sex"];
    	for (var x in sex) {
    		if (sex[x]["id"] == value) {
    			return sex[x]["name"];
    		}
    	}
    	return value;
    };
    formatter.pay_auth_state = function (value, row, index) {
        var pay_auth_state = data["pay_auth_state"];
        for (var x in pay_auth_state) {
            if (pay_auth_state[x]["id"] == value) {
                return pay_auth_state[x]["name"];
            }
        }
        return value;
    };
    formatter.telno_type = function (value, row, index) {
        if (value == 1) return "手机";
        else if (value == 2) return "固话";
        else return value;
    }
    formatter.auth_state = function (value, row, index) {
        switch (value) {
            case '0':
                return '未授权';
                break;
            case '1':
                return '已授权';
                break;
            case '2':
                return '授权失效';
                break;
        }
    };

    formatter.delivery_term = function (value, row, index) {
        var delivery_term = data["delivery_term"];
        for (var x in delivery_term) {
            if (delivery_term[x]["id"] == value) {
                return delivery_term[x]["name"];
            }
        }
        return value;
    }

    formatter.api_trade_status = function (value, row, index) {
        var api_trade_status = data["api_trade_status"];
        for (var x in api_trade_status) {
            if (api_trade_status[x]["id"] == value) {
                return api_trade_status[x]["name"];
            }
        }
        return value;
    }

    formatter.trade_status = function (value, row, index) {
        var trade_status = data["trade_status"];
        for (var x in trade_status) {
        	if (trade_status[x]["id"] == value) {
        		return trade_status[x]["name"];
            }
        }
        return value;
    }
    
    formatter.trade_status_fc = function (value, row, index) {
        var trade_status = data["trade_status"];
        if(value==55&&row['check_step']==1){
        	return '已审核，未财审';
        }else if(value==55&&row['check_step']==2){
        	return '已审核，已财审';
        }else{
        	for (var x in trade_status) {
                if (trade_status[x]["id"] == value) {
                    return trade_status[x]["name"];
                }
            }
        }
        return value;
    }

    formatter.consign_status = function (value, row, index) {
        var consign_status = data["consign_status"];
        for (var x in consign_status) {
            if (consign_status[x]["id"] == value) {
                return consign_status[x]["name"];
            }
        }
        return value;
    }

    formatter.process_status = function (value, row, index) {
        var process_status = data["process_status"];
        for (var x in process_status) {
            if (process_status[x]["id"] == value) {
                return process_status[x]["name"];
            }
        }
        return value;
    }

    formatter.pay_status = function (value, row, index) {
        var pay_status = data["pay_status"];
        for (var x in pay_status) {
            if (pay_status[x]["id"] == value) {
                return pay_status[x]["name"];
            }
        }
        return value;
    }

    formatter.pay_method = function (value, row, index) {
        var pay_method = data["pay_method"];
        for (var x in pay_method) {
            if (pay_method[x]['id'] == value) {
                return pay_method[x]["name"];
            }
        }
        return value;
    }

    formatter.refund_status = function (value, row, index) {
        var refund_status = data["refund_status"];
        for (var x in refund_status) {
            if (refund_status[x]["id"] == value) {
                return refund_status[x]["name"];
            }
        }
        return value;
    }

    formatter.order_refund_status = function (value, row, index) {
        var order_refund_status = data["order_refund_status"];
        for (var x in order_refund_status) {
            if (order_refund_status[x]["id"] == value) {
                return order_refund_status[x]["name"];
            }
        }
        return value;
    }

    formatter.refund_type = function (value, row, index) {
        var refund_type = data["refund_type"];
        for (var x in refund_type) {
            if (refund_type[x]["id"] == value) {
                return refund_type[x]["name"];
            }
        }
        return value;
    }

    formatter.operator_type = function (value, row, index) {
        var operator_type = data["operator_type"];
        for (var x in operator_type) {
            if (operator_type[x]["id"] == value) {
                return operator_type[x]["name"];
            }
        }
        return value;
    }

    formatter.invoice_type = function (value, row, index) {
        var invoice_type = data["invoice_type"];
        for (var x in invoice_type) {
            if (invoice_type[x]["id"] == value) {
                return invoice_type[x]["name"];
            }
        }
        return value;
    }

    formatter.trade_from = function (value, row, index) {
        var trade_from = data["trade_from"];
        for (var x in trade_from) {
            if (trade_from[x]["id"] == value) {
                return trade_from[x]["name"];
            }
        }
        return value;
    }

    formatter.from_type = function (value, row, index) {
        var from_type = data["from_type"];
        for (var x in from_type) {
            if (from_type[x]["id"] == value) {
                return from_type[x]["name"];
            }
        }
        return value;
    }

    formatter.logistics_type = function (value, row, index) {
        var logistics_type = data["logistics_type"];
        for (var x in logistics_type) {
            if (logistics_type[x]["id"] == value) {
                return logistics_type[x]["name"];
            }
        }
        return value;
    }

    formatter.logistics_trace_type = function (value, row, index) {
        var logistics_trace_type = data["logistics_trace_type"];
        for (var x in logistics_trace_type) {
            if (logistics_trace_type[x]["id"] == value) {
                return logistics_trace_type[x]["name"];
            }
        }
        return value;
    }

    formatter.platform_id = function (value, row, index) {
        var platform_id = data["platform_id"];
        for (var x in platform_id) {
            if (platform_id[x]["id"] == value) {
                return platform_id[x]["name"];
            }
        }
        return value;
    }

    formatter.order_type = function (value, row, index) {
        var order_type = data["order_type"];
        for (var x in order_type) {
            if (order_type[x]["id"] == value) {
                return order_type[x]["name"];
            }
        }
        return value;
    }

    formatter.trade_type = function (value, row, index) {
        var trade_type = data["trade_type"];
        for (var x in trade_type) {
            if (trade_type[x]["id"] == value) {
                return trade_type[x]["name"];
            }
        }
        return value;
    }

    formatter.stockin_status = function (value, row, index) {
        var stockin_status = data["stockin_status"];
        for (var x in stockin_status) {
            if (stockin_status[x]["id"] == value) {
                return stockin_status[x]['name'];
            }
        }
        return value;
    }
    formatter.stockout_type = function (value, row, index) {
        var stockout_type = data["stockout_type"];
        for (var x in stockout_type) {
            if (stockout_type[x]["id"] == value) {
                return stockout_type[x]['name'];
            }
        }
        return value;
    }
    formatter.stockout_type_all = function (value, row, index) {
        var stockout_type_all = data["stockout_type_all"];
        for (var x in stockout_type_all) {
            if (stockout_type_all[x]["id"] == value) {
                return stockout_type_all[x]['name'];
            }
        }
        return value;
    }
	formatter.stockpd_type = function (value, row,index){
		var stockpd_type = data["stockpd_type"];
		for(var x in stockpd_type){
			if(stockpd_type[x]["id"] == value){
				return stockpd_type[x]["name"];
			}
		}
		return value;
	}
	formatter.purchase_status = function (value, row,index){
		var purchase_status = data["purchase_status"];
		for(var x in purchase_status){
			if(purchase_status[x]["id"] == value){
				return purchase_status[x]["name"];
			}
		}
		return value;
	}
    formatter.purchase_return_status = function (value, row,index){
        var purchase_return_status = data["purchase_return_status"];
        for(var x in purchase_return_status){
            if(purchase_return_status[x]["id"] == value){
                return purchase_return_status[x]["name"];
            }
        }
        return value;
    }
	 formatter.stocktrans_type = function (value, row, index) {
        var stocktrans_type = data["stocktrans_type"];
        for (var x in stocktrans_type) {
            if (stocktrans_type[x]["id"] == value) {
                return stocktrans_type[x]['name'];
            }
        }
        return value;
    }
    formatter.stocktrans_mode = function (value, row, index) {
        var stocktrans_mode = data["stocktrans_mode"];
        for (var x in stocktrans_mode) {
            if (stocktrans_mode[x]["id"] == value) {
                return stocktrans_mode[x]['name'];
            }
        }
        return value;
    }
    formatter.stockin_type = function (value, row, index) {
        var stockin_type = data["stockin_type"];
        for (var x in stockin_type) {
            if (stockin_type[x]["id"] == value) {
                return stockin_type[x]['name'];
            }
        }
        return value;
    }
    formatter.api_goods_spec_status = function (value, row, index) {
        var status = data["api_goods_spec_status"];
        for (var x in status) {
            if (status[x]["id"] == value) {
                return status[x]["name"];
            }
        }
        return value;
    }

    formatter.match_target_type = function (value, row, index) {
        var type = data["match_target_type"];
        for (var x in type) {
            if (type[x]["id"] == value) {
                return type[x]["name"];
            }
        }
        return value;
    }

    formatter.hold_stock_type = function (value, row, index) {
        var hold_stock_type = data["hold_stock_type"];
        for (var x in hold_stock_type) {
            if (hold_stock_type[x]["id"] == value) {
                return hold_stock_type[x]["name"];
            }
        }
        return value;
    }
    formatter.warehouse_type = function (value, row, index) {
        var warehouse_type = data["warehouse_type"];
        for (var x in warehouse_type) {
            if (warehouse_type[x]["id"] == value) {
                return warehouse_type[x]["name"];
            }
        }
        return value;
    }
    formatter.wms_type = function (value, row, index) {
        var wms_type = data["wms_type"];
        for (var x in wms_type) {
            if (wms_type[x]["id"] == value) {
                return wms_type[x]["name"];
            }
        }
        return value;
    }
    formatter.type = function (value, row, index) {
        var type = data["type"];
        for (var x in type) {
            if (type[x]["id"] == value) {
                return type[x]["name"];
            }
        }
        return value;
    }
    formatter.sex = function (value, row, index) {
        var sex = data["sex"];
        for (var x in sex) {
            if (sex[x]["id"] == value) {
                return sex[x]["name"];
            }
        }
        return value;
    }
    formatter.stockout_status = function (value, row, index) {
        var stockout_status = data["stockout_status"];
        for (var x in stockout_status) {
            if (stockout_status[x]["id"] == value) {
                return stockout_status[x]['name'];
            }
        }
        return value;
    }

    formatter.stock_ledger_type=function(value,row,index){
        if(row.type==1){
            var status = data["stockin_type"];
        }else{
            var status = data["stockout_type_all"];
        }
        for (var x in status) {
            if (status[x]["id"] == value) {
                return status[x]['name'];
            }
        }
        return value;
    }

	formatter.stockpd_status = function (value, row, index) {
        var stockout_status = data["stockpd_status"];
        for (var x in stockout_status) {
            if (stockout_status[x]["id"] == value) {
                return stockout_status[x]['name'];
            }
        }
        return value;
    }
	 formatter.stocktrans_status = function (value, row, index) {
        var stocktrans_status = data["stocktrans_status"];
        for (var x in stocktrans_status) {
            if (stocktrans_status[x]["id"] == value) {
                return stocktrans_status[x]['name'];
            }
        }
        return value;
    }
	formatter.salesstockout_status = function (value, row, index) {
	    var stockout_status = data["salesstockout_status"];
		for (var x in stockout_status) {
			if (stockout_status[x]["id"] == value) {
				return stockout_status[x]['name'];
		    }
		}
	    return value;
	}
    formatter.salesstockout_status_fc = function (value, row, index) {
        var stockout_status = data["salesstockout_status"];
        if(value==55&&row['check_step']==1){
        	return '已审核，未财审';
        }else if(value==55&&row['check_step']==2){
        	return '已审核，已财审';
        }else{
	        for (var x in stockout_status) {
	            if (stockout_status[x]["id"] == value) {
	                return stockout_status[x]['name'];
	            }
	        }
        }
        return value;
    }
    formatter.history_stockout_status = function (value, row, index) {
        var history_stockout_status = data["history_stockout_status"];
        for (var x in history_stockout_status) {
            if (history_stockout_status[x]["id"] == value) {
                return history_stockout_status[x]['name'];
            }
        }
        return value;
    }
    formatter.fenxiao_type = function (value, row, index) {
        var fenxiao_type = data["fenxiao_type"];
        for (var x in fenxiao_type) {
            if (fenxiao_type[x]["id"] == value) {
                return fenxiao_type[x]["name"];
            }
        }
        return value;
    }
    formatter.customer_type = function (value, row, index) {
        var customer_type = data["customer_type"];
        for (var x in customer_type) {
            if (customer_type[x]["id"] == value) {
                return customer_type[x]["name"];
            }
        }
        return value;
    }
    formatter.guarantee_mode = function (value, row, index) {
        var guarantee_mode = data["guarantee_mode"];
        for (var x in guarantee_mode) {
            if (guarantee_mode[x]["id"] == value) {
                return guarantee_mode[x]["name"];
            }
        }
        return value;
    }
    formatter.bill_type = function (value, row, index) {
        var bill_type = data["bill_type"];
        for (var x in bill_type) {
            if (bill_type[x]["id"] == value) {
                return bill_type[x]["name"];
            }
        }
        return value;
    }
    formatter.gift_type = function (value, row, index) {
        var gift_type = data["gift_type"];
        for (var x in gift_type) {
            if (gift_type[x]["id"] == value) {
                return gift_type[x]["name"];
            }
        }
        return value;
    }
    formatter.api_refund_status = function (value, row, index) {
        var api_refund_status = data["api_refund_status"];
        for (var x in api_refund_status) {
            if (api_refund_status[x]["id"] == value) {
                return api_refund_status[x]["name"];
            }
        }
        return value;
    }
    formatter.goods_type = function (value, row, index) {
        var goods_type = data["goods_type"];
        for (var x in goods_type) {
            if (goods_type[x]["id"] == value) {
                return goods_type[x]["name"];
            }
        }
        return value;
    }
    formatter.large_type = function (value, row, index) {
        var large_type = data["large_type"];
        for (var x in large_type) {
            if (large_type[x]["id"] == value) {
                return large_type[x]["name"];
            }
        }
        return value;
    }
    formatter.refund_process_status = function (value, row, index) {
        var refund_process_status = data["refund_process_status"];
        for (var x in refund_process_status) {
            if (refund_process_status[x]["id"] == value) {
                return refund_process_status[x]["name"];
            }
        }
        return value;
    }
    formatter.wms_status = function (value, row, index) {
        switch (value) {
            case '0':
                return '初始化';
                break;
            case '1':
                return '失败';
                break;
            case '2':
                return '成功';
                break;
        }
        return value;
    };
    formatter.execute_price = function (value, row, index) {
        var execute_price = data["execute_price"];
        for (var x in execute_price) {
            if (execute_price[x]["id"] == value) {
                return execute_price [x]["name"];
            }
        }
        return value;
    }
    formatter.remark_flag = function (value, row, index) {
        var remark_flag = data["remark_flag"];
        for (var x in remark_flag) {
            if (remark_flag[x]["id"] == value) {
                return remark_flag [x]["name"];
            }
        }
        return value;
    }
    formatter.font_family = function (value, row, index) {
        var font_family = data["font_family"];
        for (var x in font_family) {
            if (font_family[x]["font_id"] == value) {
                return font_family [x]["font_name"];
            }
        }
        return value;
    }
    formatter.logistics_sync_status = function (value, row, index) {
        var logistics_sync_status = data["logistics_sync_status"];
        for (var x in logistics_sync_status) {
            if (logistics_sync_status[x]["id"] == value) {
                return logistics_sync_status [x]["name"];
            }
        }
        return value;
    }
    formatter.toYN = function (value, row, index) {
        switch (value) {
            case "1":
                return "是";
            case "0":
                return "否";
            default:
                return "错误";
        }
    }
    formatter.stockout_block_reason = function (value) {
        var stockout_block_reason = data["stockout_block_reason"];
        var str = '';
        for (var x in stockout_block_reason) {

            if (parseInt(value) & parseInt(stockout_block_reason[x]["id"])) {
                str += stockout_block_reason[x]["name"] + ",";
            }
        }

        return str.substr(0, str.length - 1);
    }
    formatter.sales_consign_status = function (value, row, index) {
        var consign_status = data["consign_status"];
        var str = '';
        for (var x in consign_status) {

            if (parseInt(value) & parseInt(consign_status[x]["id"])) {
                str += consign_status[x]["name"] + ",";
            }
        }

        return str.substr(0, str.length - 1);
    }
    formatter.logistics_type_code = function (value, row, index) {
        var logistics_type_code = data["logistics_type_code"];
        for (var x in logistics_type_code) {
            if (logistics_type_code[x]["id"] == value) {
                return logistics_type_code[x]["name"];
            }
        }
        return value;
    }
    formatter.logistics_name_code = function (value, row, index) {
        var logistics_name_code = data["logistics_name_code"];
        for (var x in logistics_name_code) {
            if (logistics_name_code[x]["id"] == value) {
                return logistics_name_code[x]["name"];
            }
        }
        return value;
    }
    formatter.role = function (value, row, index) {
    	var role = data["role"];
    	for (var x in role) {
    		if (role[x]["id"] == value) {
    			return role[x]["name"];
    		}
    	}
    	return value;
    }
    formatter.logistics_status = function (value, row, index) {
        var logistics_status = data["logistics_status"];
        for (var x in logistics_status) {
            if (logistics_status[x]["id"] == value) {
                return logistics_status[x]["name"];
            }
        }

    }
    /*formatter.roles_mask = function (value, row, index) {
        var roles_mask = data["roles_mask"];
        for (var x in roles_mask) {
            if (roles_mask[x]["id"] == value) {
                return roles_mask[x]["name"];
            }
        }
        return value;
    }*/
    formatter.stock_logistics_no_status = function (value, row, index) {
        var stock_logistics_no_status = data["stock_logistics_no_status"];
        for (var x in stock_logistics_no_status) {
            if (stock_logistics_no_status[x]["id"] == value) {
                return stock_logistics_no_status[x]["name"];
            }
        }
        return value;
    }
    formatter.time_type = function (value, row, index) {
    	var time_type = data["time_type"];
    	for (var x in time_type) {
    		if (time_type[x]["id"] == value) {
    			return time_type[x]["name"];
    		}
    	}
    	return value;
    }
    formatter.rule_group = function (value, row, index) {
        var rule_group = data["rule_group"];
        for (var x in rule_group) {
            if (rule_group[x]["id"] == value) {
                return rule_group[x]["name"];
            }
        }
        return value;
    }
    formatter.macro_begin = function(value, row, index){
    	var macro_begin = data["macro_begin"];
    	for(var x in macro_begin){
    		if(macro_begin[x]["id"] == value){
    			return macro_begin[x]["name"];
    		}
    	}
    	return value;
    }
    formatter.macro_end = function(value, row, index){
    	var macro_end = data["macro_end"];
    	for(var x in macro_end){
    		if(macro_end[x]["id"] == value){
    			return macro_end[x]["name"];
    		}
    	}
    	return value;
    }
	
	 formatter.unique_print_status = function(value, row, index){
    	var remark_deal_type = data["unique_print_status"];
    	for(var x in remark_deal_type){
    		if(remark_deal_type[x]["id"] == value){
    			return remark_deal_type[x]["name"];
    		}
    	}
    	return value;
    }
	formatter.stalls_status = function(value, row, index){
    	var remark_deal_type = data["stalls_status"];
    	for(var x in remark_deal_type){
    		if(remark_deal_type[x]["id"] == value){
    			return remark_deal_type[x]["name"];
    		}
    	}
    	return value;
    }
	
    formatter.cs_remark_type = function(value, row, index){
    	var remark_deal_type = data["cs_remark_type"];
    	for(var x in remark_deal_type){
    		if(remark_deal_type[x]["id"] == value){
    			return remark_deal_type[x]["name"];
    		}
    	}
    	return value;
    }
    formatter.client_remark_type = function(value, row, index){
    	var remark_deal_type = data["client_remark_type"];
    	for(var x in remark_deal_type){
    		if(remark_deal_type[x]["id"] == value){
    			return remark_deal_type[x]["name"];
    		}
    	}
    	return value;
    }
	//委外出入库订单类型字段显示
	formatter.outside_wms_type = function(value,row,index){
		var  wms_order_type = data['wms_order_type'];
		for(var x in wms_order_type){
			if(wms_order_type[x]['id'] == value){
				return wms_order_type[x]['name'];
			}
		}
		return value;
	}
	//委外出入库运输模式字段显示
	formatter.transport_mode = function(value,row,index){
		var  transport_mode = data['transport_mode'];
		for(var x in transport_mode){
			if(transport_mode[x]['id'] == value){
				return transport_mode[x]['name'];
			}
		}
		return value;
	}
	//委外出入库订单状态字段显示
	formatter.wms_order_status = function(value,row,index){
		var  wms_order_status = data['wms_order_status'];
		for(var x in wms_order_status){
			if(wms_order_status[x]['id'] == value){
				return wms_order_status[x]['name'];
			}
		}
		return value;
	}
    formatter.trade_fc_status = function(value, row, index){
    	var trade_fc_status = data["trade_fc_status"];
    	for(var x in trade_fc_status){
    		if(trade_fc_status[x]["id"] == value){
    			return trade_fc_status[x]["name"];
    		}
    	}
    	return value;
    }
    formatter.api_refund_type = function(value, row, index){
    	var api_refund_type = data["api_refund_type"];
    	for (var x in api_refund_type){
    		if(api_refund_type[x]["id"] == value){
    			return api_refund_type[x]["name"];
    		}
    	}
    }
    formatter.api_refund_process_status = function(value, row, index){
    	var api_refund_process_status = data["api_refund_process_status"];
    	for (var x in api_refund_process_status){
    		if(api_refund_process_status[x]["id"] == value){
    			return api_refund_process_status[x]["name"];
    		}
    	}
    }
    formatter.api_refund_status = function(value, row, index){
    	var api_refund_status = data["api_refund_status"];
    	for (var x in api_refund_status){
    		if(api_refund_status[x]["id"] == value){
    			return api_refund_status[x]["name"];
    		}
    	}
    }
    formatter.api_refund_reason = function(value, row, index){
    	var api_refund_reason = data["api_refund_reason"];
    	for (var x in api_refund_reason){
    		if(api_refund_reason[x]["id"] == value){
    			return api_refund_reason[x]["name"];
    		}
    	}
    }
    formatter.api_refund_cs_status = function(value, row, index){
    	var api_refund_cs_status = data["api_refund_cs_status"];
    	for (var x in api_refund_cs_status){
    		if(api_refund_cs_status[x]["id"] == value){
    			return api_refund_cs_status[x]["name"];
    		}
    	}
    }
    //档口货品对账状态显示
    formatter.stat_stalls_status = function(value, row, index){
        var stat_stalls_status = data["stat_stalls_status"];
        for (var x in stat_stalls_status){
            if(stat_stalls_status[x]["id"] == value){
                return stat_stalls_status[x]["name"];
            }
        }
    }

    formatter.get_data = function (key, type, id,isShift) {
        if (key == undefined && type == undefined) {
            return;
        }
        var arr = $.extend(true, [], data[key]);
        if (type == 'no') {
            arr[0] = {"id": "no", "name": "无", "selected": true};
        } else if (type == 'sel') {
            arr[0] = {"id": "sel", "name": "请选择", "selected": true};
        } else if (type == 'def') {
            if (id == undefined) {
                arr[1]['selected'] = true;
            } else {
                var start = isShift==undefined?1:0;
                for (var i = start; i < arr.length; i++) {if (arr[i]['id'] == id) {arr[i]['selected'] = true; } }
            }
            if(isShift == undefined){
                arr.shift();
            }

        }else if(!isNaN(parseInt(type))){
            arr=arr.splice(parseInt(type));
            arr[0]['selected']=true;
        }
        return arr;
    }
    //凡是作为下拉菜单的数据都应默认添加all选项--changtao
    /**
     * 获取data[key]中数组中下标为0的项做过映射处理的数据(::此函数只作为formatter内部调用)
     * @param key   data[key]中的key值
     * @param type  data[key]中下标为0的需要修改的数值
     * @returns {Array}
     */
    /*formatter.get_list = function(key,type){
        if (!key) {return []; }
        var map = {'no':'无', 'sel':'-请选择-', };
        var arr = $.extend(true, [], data[key]);
        if (map[type] != undefined ) {arr[0] ={"id": "all", "name": map[type]} };
        for(var i in arr){if(arr[i]['selected'] == true){arr[i]['selected'] = false; } }
        return arr;
    }*/

    /**
     * 处理下拉菜单中的默认选中项(::下拉菜单数据获取时调用)
     * @param key           对应data数组中的key值
     * @param type          data[key]中第一个默认的值
     * @param sel_id        data[key]返回的id值为sel_id的选项中添加选中属性selected = true
     * @param start         分割数组开始的位置
     * @param count         需要从start开始分割的数量
     * @returns {Array}
     */
    /*formatter.get_selected_list = function(key,type,sel_id,start,count){
        var list = this.get_list(key,type);
        var arr = []; //记录返回数据
        if($.isEmptyObject(list)){
            return [];
        }
        start = !isNaN(parseInt(start))?start:0;
        count = !isNaN(parseInt(count))?count:undefined;
        if(count == undefined) {
            arr = list.splice(start);
        }else{
            arr = list.splice(start,count);
        }
        var index = 0;
        if (sel_id != undefined) {
            for (var i = 0; i < arr.length; i++) {if (arr[i]['id'] == sel_id) {index=i; break; } }
        }
        arr[index]['selected'] = true;
        return arr;
    }*/
    formatter.get_list = function(key,type,sel_id){
        if (!key) {return []; }
        var map = {'no':'无', 'sel':'-请选择-'};
        var arr = $.extend(true, [], data[key]);
        if (!!map[type]) {arr[0]['id']='all';arr[0]['name']=map[type];};
        return sel_id==undefined?arr:this.set_selected_val(arr,sel_id);
    }
    formatter.get_splice_list = function(key,type,sel_id,start,count){
        var arr = this.get_list(key,type);
        start = !isNaN(parseInt(start))?start:0;
        arr = !isNaN(parseInt(count))?arr.splice(start,count):arr.splice(start);
        return sel_id==undefined?arr:this.set_selected_val(arr,sel_id);
    }
    formatter.set_selected_val = function(arr,sel_id){
        arr = !arr?[]:arr; var index = 0;
        if (sel_id!=undefined) {
            for (var i = 0; i < arr.length; i++) {if (arr[i]['id'] == sel_id) {index=i;} else if (arr[i]['selected']==true) {arr[i]['selected']=false;}; };
        };
        arr[index]['selected'] = true;
        return arr;
    }

    return formatter;
})();
//信息提示
(function () {
    messager = {};
    var product = 'ERP';
    messager.alert = function (msg, icon, cb, title) {
        if ($.isEmptyObject(title)) {
            title = product;
        }
        if ($.isEmptyObject(icon)) {
            icon = 'warning';
        }
        $.messager.alert(title, msg, icon, cb);
    };
	messager.info = function (msg, icon, cb, title) {
        if ($.isEmptyObject(title)) {
            title = product;
        }
        if ($.isEmptyObject(icon)) {
            icon = 'info';
        }
        $.messager.alert(title, msg, icon, cb);
    };
    messager.confirm = function (msg, cb, title) {
        if ($.isEmptyObject(title)) {
            title = product;
        }
        $.messager.confirm(title, msg, cb);
    };
    messager.tip = function (id) {
        $('#' + id).datagrid('doCellTip', {cls: {'background-color': '#eaf2ff'}, delay: 500});
    };
    return messager;
})();
(function(){
    utilTool ={};
    utilTool.array2dict = function(arr, key, value)
    {
        function formatterKey(key,data_ar,index) {
            var f_k = [];
            for(var i in key){
                if(data_ar[key[i]] != undefined){
                    f_k.push(data_ar[key[i]]);
                }
            }
            if($.isEmptyObject(data_ar)){
                return index;
            }else{
                return f_k.join('_');
            }

        }
        var key_is_ar = false;
        if(Object.prototype.toString.call(key)=='[object Array]'){
            key_is_ar = true;
        }

        key = (key == undefined)?'id':key;
        value = (value == undefined)?'name':value;
        var dict = {};
        for (k in arr) {
            dict[($.isEmptyObject(key) || key.length == 0 || $.trim(key) == '')?k : (key_is_ar?formatterKey(key,arr[k],k):arr[k][key])] = ($.isEmptyObject(value) || value.length == 0 || $.trim(value) == '')?arr[k] : arr[k][value];
        }
        return dict;
    };

    return utilTool;
})();
//验证码发送计时
var setTime=function(){
	return{
		node_but:null,
		node_txt:null,
		count:60,
		send:false,
		start:function(){
			if(this.count>0){
				this.count--;
				this.node_txt.text(this.count+'秒');
				var that = this;
	            setTimeout(function(){ that.start(); },1000);
			}else{
				 this.count = 60;
				 this.node_txt.text('重新发送');
				 this.node_but.linkbutton('enable');
				 
			}
		},
		init:function(but,txt){
			this.node_but=but;
			this.node_txt=txt;
			this.node_but.linkbutton('disable');
			this.start();
		}
	};
}();
// 获取工作时间导出量
(function (){
    workTime = {};
    workTime.getWorkTimeNum = function () {
        var myDate = new Date();
        var hour =  myDate.getHours();
        if((hour >= 8 && hour <12) || (hour >= 13 && hour <=18)){
            return 1000;
        }else{
            return 4000;
        }
    }
})();
// 页面功能权限函数
(function(){
    pageAuthority = {};
    pageAuthority.setDisabled = function(operator_id,list,button_type,select_box){
        function disabled(list){
            for(var i in list){
                var list_index = $.inArray(list[i],name);
                if(button_type[list_index] == 'combobox'){
                    select_box[list[i]][button_type[list_index]]('disable');
                }else{
                    select_box[list[i]][button_type[list_index]]({disabled:true});
                }
            }
        }
        function enable(list){
            for(var i in list){
                for(var j in right){
                    if(list[i] == right[j]['action']){
                        if(button_type[i] == 'combobox'){
                            select_box[list[i]][button_type[i]]('enable');
                        }else{
                            select_box[list[i]][button_type[i]]({disabled:false});
                        }
                    }
                }
            }
        }
        if(operator_id > 1){
            disabled(list,button_type,select_box);
            enable(list,button_type,select_box,right);
        }
    }
    return pageAuthority;
})();
/*
 * zClip :: jQuery ZeroClipboard v1.1.1
 * http://steamdev.com/zclip
 *
 * Copyright 2011, SteamDev
 * Released under the MIT license.
 * http://www.opensource.org/licenses/mit-license.php
 *
 * Date: Wed Jun 01, 2011
 */

(function(a){a.fn.zclip=function(c){if(typeof c=="object"&&!c.length){var b=a.extend({path:"/js/ZeroClipboard.swf",copy:null,beforeCopy:null,afterCopy:null,clickAfter:true,setHandCursor:true,setCSSEffects:true},c);return this.each(function(){var e=a(this);if(e.is(":visible")&&(typeof b.copy=="string"||a.isFunction(b.copy))){ZeroClipboard.setMoviePath(b.path);var d=new ZeroClipboard.Client();if(a.isFunction(b.copy)){e.bind("zClip_copy",b.copy)}if(a.isFunction(b.beforeCopy)){e.bind("zClip_beforeCopy",b.beforeCopy)}if(a.isFunction(b.afterCopy)){e.bind("zClip_afterCopy",b.afterCopy)}d.setHandCursor(b.setHandCursor);d.setCSSEffects(b.setCSSEffects);d.addEventListener("mouseOver",function(f){e.trigger("mouseenter")});d.addEventListener("mouseOut",function(f){e.trigger("mouseleave")});d.addEventListener("mouseDown",function(f){e.trigger("mousedown");if(!a.isFunction(b.copy)){d.setText(b.copy)}else{d.setText(e.triggerHandler("zClip_copy"))}if(a.isFunction(b.beforeCopy)){e.trigger("zClip_beforeCopy")}});d.addEventListener("complete",function(f,g){if(a.isFunction(b.afterCopy)){e.trigger("zClip_afterCopy")}else{if(g.length>500){g=g.substr(0,500)+"...\n\n("+(g.length-500)+" characters not shown)"}e.removeClass("hover");}if(b.clickAfter){e.trigger("click")}});d.glue(e[0],e.parent()[0]);a(window).bind("load resize",function(){d.reposition()})}})}else{if(typeof c=="string"){return this.each(function(){var f=a(this);c=c.toLowerCase();var e=f.data("zclipId");var d=a("#"+e+".zclip");if(c=="remove"){d.remove();f.removeClass("active hover")}else{if(c=="hide"){d.hide();f.removeClass("active hover")}else{if(c=="show"){d.show()}}}})}}}})(jQuery);var ZeroClipboard={version:"1.0.7",clients:{},moviePath:"/js/ZeroClipboard.swf",nextId:1,$:function(a){if(typeof(a)=="string"){a=document.getElementById(a)}if(!a.addClass){a.hide=function(){this.style.display="none"};a.show=function(){this.style.display=""};a.addClass=function(b){this.removeClass(b);this.className+=" "+b};a.removeClass=function(d){var e=this.className.split(/\s+/);var b=-1;for(var c=0;c<e.length;c++){if(e[c]==d){b=c;c=e.length}}if(b>-1){e.splice(b,1);this.className=e.join(" ")}return this};a.hasClass=function(b){return !!this.className.match(new RegExp("\\s*"+b+"\\s*"))}}return a},setMoviePath:function(a){this.moviePath=a},dispatch:function(d,b,c){var a=this.clients[d];if(a){a.receiveEvent(b,c)}},register:function(b,a){this.clients[b]=a},getDOMObjectPosition:function(c,a){var b={left:0,top:0,width:c.width?c.width:c.offsetWidth,height:c.height?c.height:c.offsetHeight};if(c&&(c!=a)){b.left+=c.offsetLeft;b.top+=c.offsetTop}return b},Client:function(a){this.handlers={};this.id=ZeroClipboard.nextId++;this.movieId="ZeroClipboardMovie_"+this.id;ZeroClipboard.register(this.id,this);if(a){this.glue(a)}}};ZeroClipboard.Client.prototype={id:0,ready:false,movie:null,clipText:"",handCursorEnabled:true,cssEffects:true,handlers:null,glue:function(d,b,e){this.domElement=ZeroClipboard.$(d);var f=99;if(this.domElement.style.zIndex){f=parseInt(this.domElement.style.zIndex,10)+1}if(typeof(b)=="string"){b=ZeroClipboard.$(b)}else{if(typeof(b)=="undefined"){b=document.getElementsByTagName("body")[0]}}var c=ZeroClipboard.getDOMObjectPosition(this.domElement,b);this.div=document.createElement("div");this.div.className="zclip";this.div.id="zclip-"+this.movieId;$(this.domElement).data("zclipId","zclip-"+this.movieId);var a=this.div.style;a.position="absolute";a.left=""+c.left+"px";a.top=""+c.top+"px";a.width=""+c.width+"px";a.height=""+c.height+"px";a.zIndex=f;if(typeof(e)=="object"){for(addedStyle in e){a[addedStyle]=e[addedStyle]}}b.appendChild(this.div);this.div.innerHTML=this.getHTML(c.width,c.height)},getHTML:function(d,a){var c="";var b="id="+this.id+"&width="+d+"&height="+a;if(navigator.userAgent.match(/MSIE/)){var e=location.href.match(/^https/i)?"https://":"http://";c+='<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="'+e+'download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=9,0,0,0" width="'+d+'" height="'+a+'" id="'+this.movieId+'" align="middle"><param name="allowScriptAccess" value="always" /><param name="allowFullScreen" value="false" /><param name="movie" value="'+ZeroClipboard.moviePath+'" /><param name="loop" value="false" /><param name="menu" value="false" /><param name="quality" value="best" /><param name="bgcolor" value="#ffffff" /><param name="flashvars" value="'+b+'"/><param name="wmode" value="transparent"/></object>'}else{c+='<embed id="'+this.movieId+'" src="'+ZeroClipboard.moviePath+'" loop="false" menu="false" quality="best" bgcolor="#ffffff" width="'+d+'" height="'+a+'" name="'+this.movieId+'" align="middle" allowScriptAccess="always" allowFullScreen="false" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" flashvars="'+b+'" wmode="transparent" />'}return c},hide:function(){if(this.div){this.div.style.left="-2000px"}},show:function(){this.reposition()},destroy:function(){if(this.domElement&&this.div){this.hide();this.div.innerHTML="";var a=document.getElementsByTagName("body")[0];try{a.removeChild(this.div)}catch(b){}this.domElement=null;this.div=null}},reposition:function(c){if(c){this.domElement=ZeroClipboard.$(c);if(!this.domElement){this.hide()}}if(this.domElement&&this.div){var b=ZeroClipboard.getDOMObjectPosition(this.domElement);var a=this.div.style;a.left=""+b.left+"px";a.top=""+b.top+"px"}},setText:function(a){this.clipText=a;if(this.ready){this.movie.setText(a)}},addEventListener:function(a,b){a=a.toString().toLowerCase().replace(/^on/,"");if(!this.handlers[a]){this.handlers[a]=[]}this.handlers[a].push(b)},setHandCursor:function(a){this.handCursorEnabled=a;if(this.ready){this.movie.setHandCursor(a)}},setCSSEffects:function(a){this.cssEffects=!!a},receiveEvent:function(d,f){d=d.toString().toLowerCase().replace(/^on/,"");switch(d){case"load":this.movie=document.getElementById(this.movieId);if(!this.movie){var c=this;setTimeout(function(){c.receiveEvent("load",null)},1);return}if(!this.ready&&navigator.userAgent.match(/Firefox/)&&navigator.userAgent.match(/Windows/)){var c=this;setTimeout(function(){c.receiveEvent("load",null)},100);this.ready=true;return}this.ready=true;try{this.movie.setText(this.clipText)}catch(h){}try{this.movie.setHandCursor(this.handCursorEnabled)}catch(h){}break;case"mouseover":if(this.domElement&&this.cssEffects){this.domElement.addClass("hover");if(this.recoverActive){this.domElement.addClass("active")}}break;case"mouseout":if(this.domElement&&this.cssEffects){this.recoverActive=false;if(this.domElement.hasClass("active")){this.domElement.removeClass("active");this.recoverActive=true}this.domElement.removeClass("hover")}break;case"mousedown":if(this.domElement&&this.cssEffects){this.domElement.addClass("active")}break;case"mouseup":if(this.domElement&&this.cssEffects){this.domElement.removeClass("active");this.recoverActive=false}break}if(this.handlers[d]){for(var b=0,a=this.handlers[d].length;b<a;b++){var g=this.handlers[d][b];if(typeof(g)=="function"){g(this,f)}else{if((typeof(g)=="object")&&(g.length==2)){g[0][g[1]](this,f)}else{if(typeof(g)=="string"){window[g](this,f)}}}}}}};
