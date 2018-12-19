<?php
class sfClient
{
    function get_waybill($xml)
    {
        $data = array('parameters'=>array('arg0'=>$xml));
        $funcname = 'sfexpressService';
        $client = new SoapClient("http://bsp-oisp.sf-express.com/bsp-oisp/ws/expressService?wsdl");
        //$client = new SoapClient("http://bspoisp.sit.sf-express.com:11080/bsp-oisp/ws/expressService?wsdl");
        $retval = $client->__soapcall($funcname,$data);
        logx($retval->return);
        if($retval->return == '')
            return false;
        return $this->getArrayFromXml($retval->return);
    }

    function SendWaybill($xml)
    {
        $data = array('parameters'=>array('arg0'=>$xml));
        $funcname = 'sfexpressService';
        $client = new SoapClient("http://bsp-oisp.sf-express.com/bsp-oisp/ws/expressService?wsdl");
        $retval = $client->__soapcall($funcname,$data);
        logx($retval->return);
        if($retval->return == '')
            return false;
        return $this->getArrayFromXml($retval->return);
    }

    function getArrayFromXml($xml)
    {
        $dom = simplexml_load_string($xml);
        $xmlarray = $this->getNodeArray($dom);
        return $xmlarray;
    }

    function getNodeArray($node)
    {
        $array = array();
        foreach($node->attributes() as $keys=>$values)
        {
            if($keys == 'service' && $values == 'OrderService')
            {
                if($node->Head == 'OK')
                {
                    $array['Head'] = 'OK';
                    foreach($node->Body->OrderResponse->attributes() as $key=>$value)
                    {
                        $array['Body']['OrderResponse'][$key] = (string)$value;
                    }
                }
                else
                {
                    $array['Head'] = 'ERR';
                    $array['ERROR'] = (string)$node->ERROR;
                    foreach($node->ERROR->attributes() as $key=>$value)
                    {
                        $array['ERRORAttribute'][$key] = (string)$value;
                    }
                }
            }
            else if($keys == 'service' && $values == 'OrderConfirmService')
            {
                if($node->Head == 'OK')
                {
                    $array['Head'] = 'OK';
                    foreach($node->Body->OrderConfirmResponse->attributes() as $key=>$value)
                    {
                        $array['Body']['OrderResponse'][$key] = (string)$value;
                    }
                }
                else
                {
                    $array['Head'] = 'ERR';
                    $array['ERROR'] = (string)$node->ERROR;
                    foreach($node->ERROR->attributes() as $key=>$value)
                    {
                        $array['ERRORAttribute'][$key] = (string)$value;
                    }
                }
            }
            else if($keys == 'service' && $values == 'OrderReverseService')
            {
                if($node->Head == 'OK')
                {
                    $array['Head'] = 'OK';
                    foreach($node->Body->OrderReverseResponse->attributes() as $key=>$value)
                    {
                        $array['Body']['OrderReverseResponse'][$key] = (string)$value;
                    }
                }
                else
                {
                    $array['Head'] = 'ERR';
                    $array['ERROR'] = (string)$node->ERROR;
                    foreach($node->ERROR->attributes() as $key=>$value)
                    {
                        $array['ERRORAttribute'][$key] = (string)$value;
                    }
                }
            }
            else if($keys == 'service' && $values == 'RouteService')
            {
                if($node->Head == 'OK')
                {
                    $array['Head'] = 'OK';
                    if($node->Body->children())
                    {
                        foreach($node->Body->RouteResponse->attributes() as $key=>$value)
                        {
                            $array['Body']['RouteResponse'][$key] = (string)$value;
                        }
                        foreach($node->Body->RouteResponse as $key => $value)
                        {
                            $trace_info = array();
                            foreach($key->attributes() as $key=>$value)
                            {
                                $trace_info[$key] = $value;
                            }
                            $array['Body']['RouteResponse']['Route'][] = $trace_info;
                        }
                    }
                }
                else
                {
                    $array['Head'] = 'ERR';
                    $array['ERROR'] = (string)$node->ERROR;
                    foreach($node->ERROR->attributes() as $key=>$value)
                    {
                        $array['ERRORAttribute'][$key] = (string)$value;
                    }
                }
            }
        }
        return $array;
    }
}
?>