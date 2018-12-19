<?php
namespace Setting\Model;

use Think\Exception\BusinessLogicException;
use Think\Model;


class ShopModel extends Model {

    protected $tableName = 'cfg_shop';
    protected $pk        = 'shop_id';
    protected $_validate = array(
        array("shop_name", "checkName", "请不要包括特殊字符,如:\\,/,[,],%,`,~,<,>等", 2, "callback")
    );

    protected function checkName($shop_name) {
        if (check_regex("specialCharacter", $shop_name))
            return false;
        return true;
    }

    public function getAuthShop($shop_id) {
        $sql = "select shop_id,app_key,platform_id,auth_state from cfg_shop ss where ss.shop_id = %d";
        $res = $this->query($sql, array($shop_id));
        return $res;
    }

    /**
     * @param $page
     * @param $rows
     * @param $search
     * @param $sort
     * @param $order
     * @return mixed
     * 获取店铺列表
     * author:luyanfeng
     */
    public function getShopList($page = 1, $rows = 10, $search = array(), $sort = 'cs.shop_id', $order = 'desc') {
        try {
            $page  = intval($page);
            $rows  = intval($rows);
            $sort  = addslashes($sort);
            $order = addslashes($order);
            $cfg_shop_db = M("cfg_shop");
            $where       = "WHERE true ";
            foreach ($search as $k => $v) {
                if ($v === "") continue;
                switch ($k) {
                    case "shop_name":
                        set_search_form_value($where, "shop_name", $v, "cs", 10, "AND");
                        break;
                    case "account_nick":
                        set_search_form_value($where, "account_nick", $v, "cs", 1, "AND");
                        break;
                    case "platform_id":
                        if($v!='all'){
                            set_search_form_value($where, "platform_id", $v, "cs", 1, "AND");
                        }
                        break;
                    default:
                        break;
                }
            }
            if($search['show_disabled']!=1){
                //是否显示停用的店铺
                $where = $where.' AND is_disabled=0';
            }
            $limit = ($page - 1) * $rows . "," . $rows;
            $sort  = $sort . " " . $order;
            //先搜索出需要显示的店铺的shop_id
            $sql_result = "SELECT cs.shop_id FROM cfg_shop cs $where ORDER BY $sort LIMIT $limit";
            //拼接进完整的SQL语句，查询出完整的结果
            $sql          = "SELECT cs_1.shop_id AS id,cs_1.shop_name,cs_1.platform_id,cs_1.sub_platform_id,cs_1.account_nick,cs_1.auth_time,cs_1.auth_state,cs_1.expire_time,
                      (cs_1.push_rds_id<>0) is_push,cs_1.wms_check,cs_1.pay_auth_state,cs_1.is_nomerge,cs_1.is_nosplit,cs_1.is_setwarebygoods,cs_1.group_id,cs_1.is_disabled,cs_1.pay_auth_state,cs_1.sub_auth_state,cs_1.sub_expires_time
                      FROM cfg_shop cs_1
                      INNER JOIN ( " . $sql_result . " ) cs_2 ON(cs_1.shop_id=cs_2.shop_id)";
            $sql_count    = "SELECT cs.shop_id AS id FROM cfg_shop cs $where";
            $res["total"] = count($cfg_shop_db->query($sql_count));
            $res["rows"]  = $cfg_shop_db->query($sql);
        } catch (\Exception $e) {
            \Think\Log::write($e->getMessage());
            $res["total"] = 0;
            $res["rows"]  = array();
        }
        return $res;
    }


    /**
     * @return bool
     * 验证店铺是否存在
     * author:luyanfeng
     */
    public function checkShop($value, $key = 'shop_id') {
        try {
            $map[$key] = $value;
            $result    = $this->field('shop_id')->where($map)->find();
            if (!empty($result)) {
                return true;
            }
        } catch (\PDOException $e) {
            \Think\Log::write($e->getMessage());
        }
        return false;
    }

    /**
     * @param        $value
     * @param string $name
     * @param string $fields
     * @return mixed
     * 获取店铺信息
     * author:luyanfeng
     */
    public function getShop($value, $name = "shop_id", $fields = "shop_id") {
        try {
            $map[$name]    = $value;
            $result        = $this->field($fields)->where($map)->select();
            $res["status"] = 1;
            $res["info"]   = "操作成功";
            $res["data"]   = $result;
        } catch (\Exception $e) {
            \Think\Log::write($e->getMessage());
            $res["status"] = 0;
            $res["info"]   = "未知错误，请联系管理员";
            $res["data"]   = array();
        }
        return $res;
    }

    public function getShopInfo($where, $fields = "shop_id") {
        try {
            $result        = $this->field($fields)->where($where)->select();
            $res["status"] = 0;
            $res["info"]   = $result;
        } catch (\Exception $e) {
            \Think\Log::write($e->getMessage());
            $res["status"] = 1;
        }
        return $res;
    }

    /**
     * @param $data
     * @return bool
     * 添加或更新店铺
     * author:luyanfeng
     */
    public function updateShop($data) {		
        try {
            $data['province'] = !isset($data['province'])?0:$data['province'];
            $data['city'] = !isset($data['city'])?0:$data['city'];
            $data['district'] = !isset($data['district'])?0:$data['district'];

            $oldShop = $this->getShopById($data['shop_id']);
            if (!$this->create($data)) {
                E($this->getError());
            }
            //唯品会平台还需要将备注信息填写为vendor_id,并存入account_id字段中
            if($oldShop['platform_id'] == 14 && $oldShop['remark'] != $data['remark']){
                $data['account_id'] = $data['remark'];
            }
            if(isset($data["shop_id"])){
				$this->data($data)->save();					
				$arr_sys_other_log=array();	
                if($oldShop['shop_name']!= $data['shop_name']){
                    $arr_sys_other_log[]=array(
                        'type'=>18,
                        'operator_id'=>get_operator_id(),
                        'create'=>date("Y-m-d G:i:s"),
                        'data'=>$data["shop_id"],
                        'message'=>'编辑店铺--店铺名称--从 “' . $oldShop["shop_name"] .'” 到 “'. $data["shop_name"].'”',
                    );
                }
                if($oldShop['contact']!=$data['contact']){
                    $arr_sys_other_log[]=array(
                        'type'=>18,
                        'operator_id'=>get_operator_id(),
                        'create'=>date("Y-m-d G:i:s"),
                        'data'=>$data["shop_id"],
                        'message'=>'编辑店铺--联系人--从“' . $oldShop["contact"] .'” 到 “'. $data["contact"].'”',
                    );
					
                }
				if(($oldShop['province']!=$data['province'])||($oldShop['city']!=$data['city'])||($oldShop['district']!=$data['district'])){ 
                    $sql='SELECT IF('.$oldShop['province'].'=0,"无",p1.name) AS p_name1,
                            IF('.$oldShop['city'].'=0,"无",c1.name) AS c_name1,
                            IF('.$oldShop['district'].'=0,"无",d1.name) AS d_name1,
                            IF('.$data['province'].'=0,"无",p2.name) AS p_name2, 
                            IF('.$data['city'].'=0,"无",c2.name) AS c_name2, 
                            IF('.$data['district'].'=0,"无",d2.name) AS d_name2  
                            FROM dict_province p1, dict_province p2,dict_city c1,dict_city c2,dict_district d1,dict_district d2
                            WHERE IF('.$oldShop['province'].'=0,true, p1.province_id='.$oldShop['province'].') 
                            AND IF('.$data['province'].'=0,true, p2.province_id='.$data['province'].')
                            AND IF('.$oldShop['city'].'=0,true, c1.province_id='.$oldShop['province'].' AND c1.city_id='.$oldShop['city'] .')  
                            AND IF('.$data['city'].'=0,true, c2.province_id='.$data['province'].' AND c2.city_id='.$data['city'] .') 
                            AND IF('.$oldShop['district'].'=0,true, d1.city_id='.$oldShop['city'].' AND d1.district_id='.$oldShop['district'] .')  
                            AND IF('.$data['district'].'=0,true, d2.city_id='.$data['city'].' AND d2.district_id='.$data['district'] .') LIMIT 1';
                    $address=$this->query($sql);
                    $arr_sys_other_log[]=array(
                        'type'=>18,
                        'operator_id'=>get_operator_id(),
                        'create'=>date("Y-m-d G:i:s"),
                        'data'=>$data["shop_id"],
                        'message'=>'编辑店铺--省市区--从“' . $address[0]['p_name1'] .' '. $address[0]['c_name1'] .' '.$address[0]['d_name1'] .'” 到 “'. $address[0]['p_name2'].' '. $address[0]['c_name2'].' '. $address[0]['d_name2'].'”'
                    );
                }
				if($oldShop['address']!=$data['address']){ 
                    $arr_sys_other_log[]=array(
                        'type'=>18,
                        'operator_id'=>get_operator_id(),
                        'create'=>date("Y-m-d G:i:s"),
                        'data'=>$data["shop_id"],
                        'message'=>'编辑店铺--地址--从“' . $oldShop["address"] .'” 到 “'. $data["address"].'”',
                        );   
                }
                if($oldShop['mobile']!= $data['mobile']){
                    $arr_sys_other_log[]=array(
                        'type'=>18,
                        'operator_id'=>get_operator_id(),
                        'create'=>date("Y-m-d G:i:s"),
                        'data'=>$data["shop_id"],
                        'message'=>'编辑店铺--电话号码--从“' . $oldShop["mobile"] .'” 到 “'. $data["mobile"].'”'
                    );
                }
                if($oldShop['telno']!= $data['telno']){
                    $arr_sys_other_log[]=array(
                        'type'=>18,
                        'operator_id'=>get_operator_id(),
                        'create'=>date("Y-m-d G:i:s"),
                        'data'=>$data["shop_id"],
                        'message'=>'编辑店铺--手机号码--从“' . $oldShop["telno"] .'” 到 “'. $data["telno"].'”'
                        );
				}
				if($oldShop['email']!= $data['email']){
                    $arr_sys_other_log[]=array(
                        'type'=>18,
                        'operator_id'=>get_operator_id(),
                        'create'=>date("Y-m-d G:i:s"),
                        'data'=>$data["shop_id"],
                        'message'=>'编辑店铺--Email--从“' . $oldShop["email"] .'” 到 “'. $data["email"].'”'
                        );
				}
				if($oldShop['zip']!= $data['zip']){
                    $arr_sys_other_log[]=array(
                        'type'=>18,
                        'operator_id'=>get_operator_id(),
                        'create'=>date("Y-m-d G:i:s"),
                        'data'=>$data["shop_id"],
                        'message'=>'编辑店铺--邮编--从“' . $oldShop["zip"] .'” 到 “'. $data["zip"].'”'
                        );
				}
				if($oldShop['remark']!= $data['remark']){
                    $arr_sys_other_log[]=array(
                        'type'=>18,
                        'operator_id'=>get_operator_id(),
                        'create'=>date("Y-m-d G:i:s"),
                        'data'=>$data["shop_id"],
                        'message'=>'编辑店铺--备注--从“' . $oldShop["remark"] .'” 到 “'. $data["remark"].'”'
                        );
				}
				if($oldShop['logistics_id']!= $data['logistics_id']){
					$history_logistics=$this->getLogisticsById($oldShop['logistics_id']);
			        $now_logistics=$this->getLogisticsById($data['logistics_id']);
                    $arr_sys_other_log[]=array(
                        'type'=>18,
                        'operator_id'=>get_operator_id(),
                        'create'=>date("Y-m-d G:i:s"),
                        'data'=>$data["shop_id"],
                        'message'=>'编辑店铺--使用物流--从“' . $history_logistics['logistics_name'].'” 到 “'. $now_logistics['logistics_name'].'”'
                        );
				}
				if($oldShop['cod_logistics_id']!= $data['cod_logistics_id']){
					$history_cod_logistics=$this->getLogisticsById($oldShop['cod_logistics_id']);
			        $now_cod_logistics=$this->getLogisticsById($data['cod_logistics_id']);
                    $arr_sys_other_log[]=array(
                        'type'=>18,
                        'operator_id'=>get_operator_id(),
                        'create'=>date("Y-m-d G:i:s"),
                        'data'=>$data["shop_id"],
                        'message'=>'编辑店铺--COD物流--从“' . $history_cod_logistics['logistics_name'].'” 到 “'. $now_cod_logistics['logistics_name'].'”'
                        );
				}
				if($oldShop['is_disabled']!= $data['is_disabled']){
					if($oldShop["is_disabled"]=='0'){
						$old_is_disabled="否";
					}else{
						$old_is_disabled="是";
					}
					if($data["is_disabled"]=='0'){
						$is_disabled="否";
					}else{
						$is_disabled="是";
					}
                    $arr_sys_other_log[]=array(
                        'type'=>18,
                        'operator_id'=>get_operator_id(),
                        'create'=>date("Y-m-d G:i:s"),
                        'data'=>$data["shop_id"],
                        'message'=>'编辑店铺--停用--从“' . $old_is_disabled .'” 到 “'. $is_disabled .'”'
                        );
				}
                 M("sys_other_log")->addall($arr_sys_other_log);
			}else{
				$shopId            = $this->data($data)->add();
				$arr_sys_other_log = array(
                    "type"        => "18",
                    "operator_id" => get_operator_id(),
                    "data"        => $shopId,
                    "message"     => "创建店铺--店铺名称--“" . $data["shop_name"].'”',
                    "careted"     => date("Y-m-d G:i:s")					
                );
				M("sys_other_log")->data($arr_sys_other_log)->add();
			}
			
			
            $res["status"] = 1;
            $res["info"]   = "操作成功";
        } catch (\PDOException $e) {
            \Think\Log::write($e->getMessage());
            $res["status"] = 0;
            $res["info"]   = "系统错误，请联系管理员";
        } catch (\Exception $e) {
            $res["status"] = 0;
            $res["info"]   = $e->getMessage();
        }
        return $res;
    }

    public function getWayBillAuthShopList() {
        return $res = $this->field('shop_id AS id,shop_name AS name')->where(array('platform_id' => array('in', '1,2')))->select();
    }

    public function getAuthInfo($id) {
        try {
            $res = $this->where(array("shop_id" => $id))->field("platform_id,shop_id,account_nick,app_key")->find();
            $res = $res;
        } catch (\PDOException $e) {
            \Think\Log::write($e->getMessage());
            SE(self::PDO_ERROR);
        } catch (\Exception $e) {
            \Think\Log::write($e->getMessage());
            SE(self::PDO_ERROR);
        }
        return $res;
    }

    public function saveAuthInfo($data) {
        try {
            if (empty($data["shop_id"])) {
                SE("店铺id不能为空");
            }
            $data["auth_time"]  = date("Y-m-d G:i:s", time());
            $data["auth_state"] = 1;
            $this->data($data)->save();
        } catch (\PDOException $e) {
            \Think\Log::write($e->getMessage());
            SE(self::PDO_ERROR);
        } catch (BusinessLogicException $e) {
            SE($e->getMessage());
        } catch (\Exception $e) {
            \Think\Log::write($e->getMessage());
            SE(self::PDO_ERROR);
        }
    }

    function importShop($data){
        $plat_form_arr = array(
            '线下','淘宝',"淘宝分销","京东","拍拍","亚马逊","1号店","当当网","库巴","阿里巴巴","ECShop","麦考林","V+","苏宁","唯品会","易迅","聚美","有赞","微铺宝",
            "美丽说","蘑菇街","贝贝网","ecstore","折800","融e购","穿衣助手","楚楚街","微盟旺店","卷皮网","嘿客","飞牛","微店","拼多多","人人店"
        );
        foreach($plat_form_arr as $k=>$v){
            if($data['platform_id']==$v){
                $data['platform_id'] = $k;
                break;
            }
        }
        if(!is_int($data['platform_id'])){
            SE('不支持的店铺平台');
        }
        if($data['platform_id']==1 && (!empty($data['sub_platform_id'])&&($data['sub_platform_id']!='淘宝集市'&& $data['sub_platform_id']!='天猫商城'))){
            SE('淘宝平台子平台支持淘宝集市或天猫商城');
        }
        if($data['platform_id']==3 && (!empty($data['sub_platform_id'])&&($data['sub_platform_id']!='SOP' && $data['sub_platform_id']!= 'LBP' && $data['sub_platform_id']!='SOPL' && $data['sub_platform_id']!='FBP' && $data['sub_platform_id']!='海外购'))){
            SE('京东平台子平台支持SOP、LBP、SOPL、FBP、海外购');
        }
        if($data['platform_id']==1 ){
            $data['sub_platform_id'] = $data['sub_platform_id']=='天猫商城'?1:0;
        }
        if($data['platform_id']==3 ){
            if($data['sub_platform_id']=='LBP'){
                $data['sub_platform_id'] = 1;
            }elseif($data['sub_platform_id']=='SOPL'){
                $data['sub_platform_id'] = 2;
            }elseif($data['sub_platform_id']=='FBP'){
                $data['sub_platform_id'] = 3;
            }elseif($data['sub_platform_id'] == '海外购'){
                $data['sub_platform_id'] = 4;
            }else{
                $data['sub_platform_id'] = 0;
            }
        }
        if(empty($data['shop_name'])){
            SE('店铺名不能为空');
        }
        $find_shop = $this->field('shop_name')->where(array('shop_name'=>$data['shop_name']))->find();
        if($find_shop){
            SE('该店铺已存在:'.$find_shop['shop_name']);
        }

        $cfg_logistics_db  = M("cfg_logistics");
        $cfg_logistics = $cfg_logistics_db->field('logistics_id AS id,logistics_name AS name')->select();
        foreach($cfg_logistics as $v){
            if($data['logistics_id'] == $v['name']){
                $data['logistics_id'] = intval($v['id']);
                break;
            }
        }
        $cod_cfg_logistics = $cfg_logistics_db->alias('cl')->field('logistics_id AS id,logistics_name AS name')->where(array('is_support_cod'=>1))->select();
        foreach($cod_cfg_logistics as $v){
            if($data['cod_logistics_id'] == $v['name']){
                $data['cod_logistics_id'] = intval($v['id']);
                break;
            }
        }
        $data['logistics_id'] = empty($data['logistics_id'])?0:$data['logistics_id'];
        $data['cod_logistics_id'] = empty($data['cod_logistics_id'])?0:$data['cod_logistics_id'];
        if(!is_int($data['logistics_id']) || !is_int($data['cod_logistics_id'])){
            SE('使用物流或COD物流填写错误，请查看您所设置的物流进行填写');
        }
        if(!empty($data['province'])){
            $dict_province = $this->table('dict_province')->field('province_id')->where(array('country_id'=>0,'name'=>$data['province']))->find();
            if(!$dict_province){
                SE('无效省份');
            }else{
                $data['province'] = $dict_province['province_id'];
            }
        }
        if(!empty($data['city'])){
            $dict_city = $this->table('dict_city')->field('city_id')->where(array('province_id'=>$data['province'],'name'=>$data['city']))->find();
            if(!$dict_city){
                SE('无效城市');
            }else{
                $data['city'] = $dict_city['city_id'];
            }
        }
        if(!empty($data['district'])){
            $dict_district = $this->table('dict_district')->field('district_id')->where(array('city_id'=>$data['city'],'name'=>$data['district']))->find();
            if(!$dict_district){
                SE('无效地区');
            }else{
                $data['district'] = $dict_district['district_id'];
            }
        }
        if(!empty($data['mobile'])&&!preg_match('/^(?:13\d|15\d|18\d|17\d|14\d)-?\d{5}(\d{3}|\*{3})$/',$data['mobile']))SE('非法手机号码');
        if(!empty($data['email'])&&!preg_match("/^([0-9A-Za-z\\-_\\.]+)@([0-9a-z]+\\.[a-z]{2,3}(\\.[a-z]{2})?)$/i",$data['email'])) SE('非法邮箱');
        //if(!empty($data['website'])&&!preg_match("^http://[_a-zA-Z0-9-]+(.[_a-zA-Z0-9-]+)*$",$data['website'])) SE('非法网址');
        //校验 完成，插入店铺
        $data['created'] = date("Y-m-d G:i:s");
        $shop_id = $this->data($data)->add();
        $arr_sys_other_log = array(
            "type"        => "2",
            "operator_id" => get_operator_id(),
            "data"        => $shop_id,
            "message"     => "导入店铺--" . $data["shop_name"],
            "careted"     => date("Y-m-d G:i:s")
        );
        M("sys_other_log")->data($arr_sys_other_log)->add();
    }

    public function getShopByName($shop_name){
        $res = array();
        try{
            $res=$this->field('shop_id,platform_id,sub_platform_id')->where(array('shop_name'=>$shop_name))->find();
        }catch (\PDOException $e){
            \Think\Log::write('getShopByName SQL ERR'.$e->getMessage());
            SE(self::PDO_ERROR);
        }catch (\Exception $e){
            \Think\Log::write('getShopByName'.$e->getMessage());
            SE(self::PDO_ERROR);
        }
        return json_encode($res);
    }
	//通过店铺的id获得店铺的名字
	public function getShopById($shop_id){
        $res = array();
        try{
			$res=$this->field('platform_id,app_key,shop_name,contact,mobile,telno,zip,email,website,remark,cod_logistics_id,logistics_id,is_disabled,province,city,district,address')->where(array('shop_id'=>$shop_id))->find();
        }catch (\PDOException $e){
            \Think\Log::write('getShopById SQL ERR'.$e->getMessage());
            SE(self::PDO_ERROR);
        }catch (\Exception $e){
            \Think\Log::write('getShopById'.$e->getMessage());
            SE(self::PDO_ERROR);
        }
        return $res;
    }
	//通过物流的id获得物流的名字	
	public function getLogisticsById($logistics_id){
		$res=array();
		$cfg_logistics_db  = M("cfg_logistics");
        try{
			$res=$cfg_logistics_db->field('logistics_name')->where(array('logistics_id'=>$logistics_id))->find();
        }catch (\PDOException $e){
            \Think\Log::write('getLogisticsById SQL ERR'.$e->getMessage());
            SE(self::PDO_ERROR);
        }catch (\Exception $e){
            \Think\Log::write('getLogisticsById'.$e->getMessage());
            SE(self::PDO_ERROR);
        }
        return $res;
    }

}