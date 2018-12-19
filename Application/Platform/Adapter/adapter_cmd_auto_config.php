<?php

//开启自动推送的卖家信息
/*
$g_sid_list = array(
    'wdt2_newdev' => array(
        WMS_METHOD_TRADE_ADD    => 2000, //配置每次推单的数量
        WMS_METHOD_SKU_ADD      => 1,
        WMS_METHOD_PURCHASE_ADD => 1,
        WMS_METHOD_REFUND_ADD   => 1
        'batch_flag' => array(
            ORDER_TYPE_STOCK_SYNC => 1
        )
    )
);

$g_goods_multi_line_sid_list = array(
	'卖家帐号'=>array(
		'货主id'=>array(
			'multi_line_flag' =>1,
			'deleted_jod_gift_srctid_flag'=>1,
		),
	),
);

*/

global $g_sid_list;
$g_sid_list = array(

    'qigege2'=>array(
        WMS_METHOD_SKU_ADD      => 1,
        WMS_METHOD_PURCHASE_ADD => 1,
        WMS_METHOD_REFUND_ADD   => 1,
    ),
    'weilun2'=>array(
        WMS_METHOD_SKU_ADD      => 1,
    ),
    'janezt2'=>array(
        WMS_METHOD_REFUND_ADD   => 1,
    ),
    'lianen2'=>array(
        WMS_METHOD_PURCHASE_ADD => 1,
        WMS_METHOD_REFUND_ADD   => 1,
    ),
    'jishang2'=>array(
        WMS_METHOD_TRADE_ADD => 2500,
    ),
	'zichu2'=>array(
        WMS_METHOD_TRADE_ADD => 2000,
    ),
    'chinstudio2'=>array(
        WMS_METHOD_TRADE_ADD => 2000,
    ),
    'mgxx2'=>array(
        //WMS_METHOD_PURCHASE_ADD => 1,
		WMS_METHOD_TRADE_ADD => 2000,
        'stop_flag' => array(
            ORDER_TYPE_STOP_WAITING_PO => 1,//停止等待
        )
    ),
    'eptison2'=>array(
        'plan_flag' => array(
            ORDER_TYPE_PLAN_PURCHASE => 1,//计划单
        )
    ),
    'miqin2'=>array(
        WMS_METHOD_REFUND_ADD   => 1,//自动推送退货单
    ),
    'bonida2'=>array(
        'batch_flag' => array(
            ORDER_TYPE_STOCK_SYNC => 1,//库存查询批次支持
        )
    ),
    'zoneco2'=>array(
        'batch_flag' => array(
            ORDER_TYPE_STOCK_SYNC => 1,//库存查询批次支持
        ),
    ),
);

$g_goods_multi_line_sid_list = array(
        'fangxing2'=>array(
                'niwaxiaomaibu'=>array(
                        'multi_line_flag' =>1,
                        'deleted_jod_gift_srctid_flag'=>1,
                ),
        ),
        'mtmt2'=>array(
                'c001'=>array(
                        'multi_line_flag' =>1,
                ),
        ),
);
