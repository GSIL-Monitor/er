<?php
/**
 * User: wangshengyong
 */

class EtmsWaybillSendRequest
{

    /**
     * @var 运单号
     */
    private $deliveryId;

    /**
     * @var 销售平台（京东快递物流系统维护的数据字典）
     */
    private $salePlat;
	
	 /**
     * @var 商家编码
     */
    private $customerCode;

    /**
     * @var 商家发货商品的唯一标识
     */
    private $orderId;
	
	 /**
     * @var 如果该订单为京东平台之外的订单，请为空。
     */
    private $thrOrderId;

    /**
     * @var 是否客户打印运单(是：1，否：0。不填或者超出范围，默认是1)
     */
    private $selfPrintWayBill;
	
	 /**
     * @var 取件方式(上门收货：1，自送：2。不填或者超出范围，默认是1)
     */
    private $pickMethod;

    /**
     * @var 包装要求(不需包装：1，简单包装：2，特殊包装：3。不填或者超出范围，默认是1) 
     */
    private $packageRequired;
	
	/**
     * @var 寄件人姓名
     */
    private $senderName;

    /**
     * @var 寄件人地址
     */
    private $senderAddress;
	
	 /**
     * @var 寄件人电话
     */
    private $senderTel;

    /**
     * @var 寄件人手机(寄件人电话、手机至少有一个不为空) 
     */
    private $senderMobile;
	
	 /**
     * @var 寄件人邮编
     */
    private $senderPostcode;

    /**
     * @var 收件人名称
     */
    private $receiveName;
	
	 /**
     * @var 收件人地址
     */
    private $receiveAddress;
	
	/**
     * @var 收件人省
     */
    private $province;

    /**
     * @var 收件人市
     */
    private $city;
	
	 /**
     * @var 收件人县
     */
    private $county;

    /**
     * @var 收件人镇
     */
    private $town;
	
	 /**
     * @var 收件人电话
     */
    private $receiveTel;

    /**
     * @var 收件人手机号(收件人电话、手机至少有一个不为空)
     */
    private $receiveMobile;
	
	 /**
     * @var 收件人邮编
     */
    private $postcode;

    /**
     * @var 包裹数(大于0，小于1000)
     */
    private $packageCount;
	
	/**
     * @var 重量(单位：kg，保留小数点后两位，默认为0) 
     */
    private $weight;

    /**
     * @var 包裹长(单位：cm,保留小数点后两位)
     */
    private $vloumLong;
	
	 /**
     * @var 包裹宽(单位：cm，保留小数点后两位)
     */
    private $vloumWidth;

    /**
     * @var 包裹高(单位：cm，保留小数点后两位)
     */
    private $vloumHeight;
	
	 /**
     * @var 体积(单位：CM3，保留小数点后两位，默认为0)
     */
    private $vloumn;

    /**
     * @var 商品描述
     */
    private $description;
	
	 /**
     * @var 是否代收货款(是：1，否：0。不填或者超出范围，默认是0)
     */
    private $collectionValue;

    /**
     * @var 代收货款金额(保留小数点后两位)
     */
    private $collectionMoney;

	/**
     * @var 是否保价(是：1，否：0。不填或者超出范围，默认是0) 
     */
    private $guaranteeValue;
	
	 /**
     * @var 保价金额(保留小数点后两位)
     */
    private $guaranteeValueAmount;

    /**
     * @var 签单返还(是：1，否：0。不填或者超出范围，默认是0)
     */
    private $signReturn;
	
	 /**
     * @var 时效(普通：1，工作日：2，非工作日：3，晚间：4。不填或者超出范围，默认是1) 
     */
    private $aging;

    /**
     * @var 运输类型(陆运：1，航空：2。不填或者超出范围，默认是1) 
     */
    private $transType;

    /**
     * 首先需要对业务参数进行安装首字母排序，然后将业务参数转换json字符串
     * @return string
     */
    public function getAppJsonParams()
    {
        $apiParams["deliveryId"]=$this->deliveryId;
        $apiParams["salePlat"]=$this->salePlat;
		$apiParams["customerCode"]=$this->customerCode;
        $apiParams["orderId"]=$this->orderId;
		$apiParams["thrOrderId"]=$this->thrOrderId;
        $apiParams["selfPrintWayBill"]=$this->selfPrintWayBill;
		$apiParams["pickMethod"]=$this->pickMethod;
		$apiParams["packageRequired"]=$this->packageRequired;
        $apiParams["senderName"]=$this->senderName;
		$apiParams["senderAddress"]=$this->senderAddress;
        $apiParams["senderTel"]=$this->senderTel;
		$apiParams["senderMobile"]=$this->senderMobile;
        $apiParams["senderPostcode"]=$this->senderPostcode;
		$apiParams["receiveName"]=$this->receiveName;
        $apiParams["receiveAddress"]=$this->receiveAddress;
        $apiParams["province"]=$this->province;
		$apiParams["city"]=$this->city;
        $apiParams["town"]=$this->town;
		$apiParams["receiveTel"]=$this->receiveTel;
        $apiParams["receiveMobile"]=$this->receiveMobile;
		$apiParams["postcode"]=$this->postcode;
        $apiParams["packageCount"]=$this->packageCount;
		$apiParams["weight"]=$this->weight;
        $apiParams["vloumLong"]=$this->vloumLong;
		$apiParams["vloumWidth"]=$this->vloumWidth;
        $apiParams["vloumHeight"]=$this->vloumHeight;
		$apiParams["vloumn"]=$this->vloumn;
        $apiParams["description"]=$this->description;
		$apiParams["collectionValue"]=$this->collectionValue;
        $apiParams["collectionMoney"]=$this->collectionMoney;
		$apiParams["guaranteeValue"]=$this->guaranteeValue;
		$apiParams["guaranteeValueAmount"]=$this->guaranteeValueAmount;
        $apiParams["signReturn"]=$this->signReturn;
		$apiParams["aging"]=$this->aging;
        $apiParams["transType"]=$this->transType;
		
        ksort($apiParams);
        return json_encode($apiParams);
    }

    /**
     *
     * 获取方法名称
     * @return string
     */
    public function getApiMethod()
    {
        return "jingdong.etms.waybill.send";
    }

    /**
     * @param  $deliveryId 
     */
    public function setDeliveryId($deliveryId)
    {
        $this->deliveryId  = $deliveryId;
    }

    /**
     * @return
     */
    public function getDeliveryId()
    {
        return $this->deliveryId;
    }

    /**
     * @param  $salePlat
     */
    public function setSalePlat($salePlat)
    {
        $this->salePlat = $salePlat;
    }

    /**
     * @return
     */
    public function getSalePlat()
    {
        return $this->salePlat;
    }
	
	/**
     * @param  $customerCode 
     */
    public function setCustomerCode($customerCode)
    {
        $this->customerCode  = $customerCode;
    }

    /**
     * @return
     */
    public function getCustomerCode()
    {
        return $this->customerCode;
    }

    /**
     * @param  $orderId
     */
    public function setOrderId($orderId)
    {
        $this->orderId = $orderId;
    }

    /**
     * @return
     */
    public function getOrderId()
    {
        return $this->orderId;
    }
	
	/**
     * @param  $thrOrderId 
     */
    public function setThrOrderId($thrOrderId)
    {
        $this->thrOrderId  = $thrOrderId;
    }

    /**
     * @return
     */
    public function getThrOrderId()
    {
        return $this->thrOrderId;
    }

    /**
     * @param  $selfPrintWayBill
     */
    public function setSelfPrintWayBill($selfPrintWayBill)
    {
        $this->selfPrintWayBill = $selfPrintWayBill;
    }

    /**
     * @return
     */
    public function getSelfPrintWayBill()
    {
        return $this->selfPrintWayBill;
    }
	
	/**
     * @param  $pickMethod 
     */
    public function setPickMethod($pickMethod)
    {
        $this->pickMethod  = $pickMethod;
    }

    /**
     * @return
     */
    public function getPickMethod()
    {
        return $this->pickMethod;
    }

    /**
     * @param  $packageRequired
     */
    public function setPackageRequired($packageRequired)
    {
        $this->packageRequired = $packageRequired;
    }

    /**
     * @return
     */
    public function getPackageRequired()
    {
        return $this->packageRequired;
    }
	
	/**
     * @param  $senderName 
     */
    public function setSenderName($senderName)
    {
        $this->senderName  = $senderName;
    }

    /**
     * @return
     */
    public function getSenderName()
    {
        return $this->senderName;
    }

    /**
     * @param  $senderAddress
     */
    public function setSenderAddress($senderAddress)
    {
        $this->senderAddress = $senderAddress;
    }

    /**
     * @return
     */
    public function getSenderAddress()
    {
        return $this->senderAddress;
    }
	
	/**
     * @param  $senderTel 
     */
    public function setSenderTel($senderTel)
    {
        $this->senderTel  = $senderTel;
    }

    /**
     * @return
     */
    public function getSenderTel()
    {
        return $this->senderTel;
    }

    /**
     * @param  $senderMobile
     */
    public function setSenderMobile($senderMobile)
    {
        $this->senderMobile = $senderMobile;
    }

    /**
     * @return
     */
    public function getSenderMobile()
    {
        return $this->senderMobile;
    }
	
	/**
     * @param  $senderPostcode 
     */
    public function setSenderPostcode($senderPostcode)
    {
        $this->senderPostcode  = $senderPostcode;
    }

    /**
     * @return
     */
    public function getSenderPostcode()
    {
        return $this->senderPostcode;
    }

    /**
     * @param  $receiveName
     */
    public function setReceiveName($receiveName)
    {
        $this->receiveName = $receiveName;
    }

    /**
     * @return
     */
    public function getReceiveName()
    {
        return $this->receiveName;
    }
	
	/**
     * @param  $receiveAddress 
     */
    public function setReceiveAddress($receiveAddress)
    {
        $this->receiveAddress  = $receiveAddress;
    }

    /**
     * @return
     */
    public function getrReceiveAddress()
    {
        return $this->receiveAddress;
    }
	
	/**
     * @param  $province 
     */
    public function setProvince($province)
    {
        $this->province  = $province;
    }

    /**
     * @return
     */
    public function getProvince()
    {
        return $this->province;
    }

    /**
     * @param  $city
     */
    public function setCity($city)
    {
        $this->city = $city;
    }

    /**
     * @return
     */
    public function getCity()
    {
        return $this->city;
    }
	
	/**
     * @param  $county 
     */
    public function setCounty($county)
    {
        $this->county  = $county;
    }

    /**
     * @return
     */
    public function getCounty()
    {
        return $this->county;
    }

    /**
     * @param  $town
     */
    public function setTown($town)
    {
        $this->town = $town;
    }

    /**
     * @return
     */
    public function getTown()
    {
        return $this->town;
    }
	
	/**
     * @param  $receiveTel 
     */
    public function setReceiveTel($receiveTel)
    {
        $this->receiveTel  = $receiveTel;
    }

    /**
     * @return
     */
    public function getReceiveTel()
    {
        return $this->receiveTel;
    }

    /**
     * @param  $receiveMobile
     */
    public function setReceiveMobile($receiveMobile)
    {
        $this->receiveMobile = $receiveMobile;
    }

    /**
     * @return
     */
    public function getReceiveMobile()
    {
        return $this->receiveMobile;
    }
	
	/**
     * @param  $postcode 
     */
    public function setPostcode($postcode)
    {
        $this->postcode  = $postcode;
    }

    /**
     * @return
     */
    public function getPostcode()
    {
        return $this->postcode;
    }

    /**
     * @param  $packageCount
     */
    public function setPackageCount($packageCount)
    {
        $this->packageCount = $packageCount;
    }

    /**
     * @return
     */
    public function getPackageCount()
    {
        return $this->packageCount;
    }
	
	/**
     * @param  $weight 
     */
    public function setWeight($weight)
    {
        $this->weight  = $weight;
    }

    /**
     * @return
     */
    public function getWeight()
    {
        return $this->weight;
    }

    /**
     * @param  $vloumLong
     */
    public function setVloumLong($vloumLong)
    {
        $this->vloumLong = $vloumLong;
    }

    /**
     * @return
     */
    public function getVloumLong()
    {
        return $this->vloumLong;
    }
	
	/**
     * @param  $vloumWidth 
     */
    public function setVloumWidth($vloumWidth)
    {
        $this->vloumWidth  = $vloumWidth;
    }

    /**
     * @return
     */
    public function getVloumWidth()
    {
        return $this->vloumWidth;
    }

    /**
     * @param  $vloumHeight
     */
    public function setVloumHeight($vloumHeight)
    {
        $this->vloumHeight = $vloumHeight;
    }

    /**
     * @return
     */
    public function getVloumHeight()
    {
        return $this->vloumHeight;
    }
	
	/**
     * @param  $vloumn
     */
    public function setVloumn($vloumn)
    {
        $this->vloumn = $vloumn;
    }

    /**
     * @return
     */
    public function getVloumn()
    {
        return $this->vloumn;
    }
	
	/**
     * @param  $description 
     */
    public function setDescription($description)
    {
        $this->description  = $description;
    }

    /**
     * @return
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param  $collectionValue
     */
    public function setCollectionValue($collectionValue)
    {
        $this->collectionValue = $collectionValue;
    }

    /**
     * @return
     */
    public function getCollectionValue()
    {
        return $this->collectionValue;
    }
	
	/**
     * @param  $collectionMoney 
     */
    public function setCollectionMoney($collectionMoney)
    {
        $this->collectionMoney  = $collectionMoney;
    }

    /**
     * @return
     */
    public function getCollectionMoney()
    {
        return $this->collectionMoney;
    }

    /**
     * @param  $guaranteeValue
     */
    public function setGuaranteeValue($guaranteeValue)
    {
        $this->guaranteeValue = $guaranteeValue;
    }

    /**
     * @return
     */
    public function getGuaranteeValue()
    {
        return $this->guaranteeValue;
    }
	
	/**
     * @param  $guaranteeValueAmount 
     */
    public function setGuaranteeValueAmount($guaranteeValueAmount)
    {
        $this->guaranteeValueAmount  = $guaranteeValueAmount;
    }

    /**
     * @return
     */
    public function getGuaranteeValueAmount()
    {
        return $this->guaranteeValueAmount;
    }

    /**
     * @param  $signReturn
     */
    public function setSignReturn($signReturn)
    {
        $this->signReturn = $signReturn;
    }

    /**
     * @return
     */
    public function getSignReturn()
    {
        return $this->signReturn;
    }
	
	/**
     * @param  $aging 
     */
    public function setAging($aging)
    {
        $this->aging  = $aging;
    }

    /**
     * @return
     */
    public function getAging()
    {
        return $this->aging;
    }

    /**
     * @param  $transType
     */
    public function setTransType($transType)
    {
        $this->transType = $transType;
    }

    /**
     * @return
     */
    public function getTransType()
    {
        return $this->transType;
    }

}