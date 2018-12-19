<?php
require_once "../vipapis/delivery/DvdDeliveryServiceClient.php";
require_once('config.php');
	//批量发货
	try
	{
		$service=\vipapis\delivery\DvdDeliveryServiceClient::getService();
    	$ctx=\Osp\Context\InvocationContextFactory::getInstance();
		$ctx->setAppKey('a876c4cc');
		$ctx->setAppSecret('77780A5819EC3CFBE648436DB9F95492');
		$ctx->setAppURL("http://sandbox.vipapis.com/");
		//单包裹..
		$ship_list=array();
		$ship=new \vipapis\delivery\Ship();
		$ship->order_id='123123123';
		$ship->carrier_code='123';
		// global $vipshop_logistics_name_map;
		$ship->carrier_name='aaaa';
		$ship->package_type="1";
		$packages=array();
		$package=new \vipapis\delivery\Package();
		$package_product_list=array();
		
			$product=new \vipapis\delivery\PackageProduct();
			$product->barcode='wdsadq';
			$product->amount='2';
			$package_product_list[]=$product;
		
		$package->package_product_list=$package_product_list;
		$package->transport_no='123123';
		$packages[0]=$package;
		
		$ship->packages=$packages;
   		$ship_list[0]=$ship;
		
    	$retval=$service->ship('550',$ship_list);
		
	}
	catch(\Osp\Exception\OspException $e)
	{
		print_r($e);
	}

	print_r($retval);
	echo $retval->fail_data[0]->carrier_code;
?>