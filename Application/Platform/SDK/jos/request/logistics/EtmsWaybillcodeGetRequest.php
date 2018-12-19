<?php
/**
 * User: wangshengyong
 */

class EtmsWaybillcodeGetRequest
{

    /**
     * @var 获取运单号数量
     */
    private $preNum;

    /**
     * @var 商家编码
     */
    private $customerCode;

    /**
     * 首先需要对业务参数进行安装首字母排序，然后将业务参数转换json字符串
     * @return string
     */
    public function getAppJsonParams()
    {
        $apiParams["preNum"]=$this->preNum;
        $apiParams["customerCode"]=$this->customerCode;
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
        return "jingdong.etms.waybillcode.get";
    }

    /**
     * @param  $preNum 
     */
    public function setPreNum ($preNum )
    {
        $this->preNum  = $preNum;
    }

    /**
     * @return
     */
    public function getPreNum ()
    {
        return $this->preNum;
    }

    /**
     * @param  $customerCode
     */
    public function setCustomerCode($customerCode)
    {
        $this->customerCode = $customerCode;
    }

    /**
     * @return
     */
    public function getCustomerCode()
    {
        return $this->customerCode;
    }


}