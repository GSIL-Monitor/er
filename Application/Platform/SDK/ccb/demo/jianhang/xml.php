<?php
function arrtoxml($arr,$dom=0,$item=0){
    if (!$dom){
        $dom = new DOMDocument("1.0");
    }
    if(!$item){
        $item = $dom->createElement("root");
        $dom->appendChild($item);
    }
    foreach ($arr as $key=>$val){
        $itemx = $dom->createElement(is_string($key)?$key:"item");
        $item->appendChild($itemx);
        if (!is_array($val)){
            $text = $dom->createTextNode($val);
            $itemx->appendChild($text);

        }else {
            arrtoxml($val,$dom,$itemx);
        }
    }
    return $dom->saveXML();
}
$params = array();
//$params['ccbParam'] = $xml;
$params['cust_id'] = '0011';
$params['tran_code'] = 'T0008T0008';
$params['tran_sid'] = '20120627000000000621';
$params['ccParam'] = array('start_created'=>'2015-7-30 00:00:00', 'end_created'=>'2015-07-31 00:00:00', 'status'=>'');
$res = json_decode($params);
print_r($res);