<?php
namespace Common\Common;

use Platform\Common\PHPMailer;

/**
 * @author Citying
 * 公共工具类
 */
class UtilTool {

    const UPPER_UPLOAD = '工作时间导入限制10000条,<br>其余时间限制为50000条<br>　　　  工作时间：8:00~12:00,13:00~19:00';

    /**
     * @param unknown $sourceArr   需要转换的数组
     * @param unknown $key         主键
     * @param unknown $parentKey   指向的父主键
     * @param unknown $childrenKey 生成的子类主键
     * @return Ambigous <multitype:, multitype:unknown > 多维嵌套 array
     */
    static public function array2tree($sourceArr, $key, $parentKey, $childrenKey) {
        $tempSrcArr = array();
        foreach ($sourceArr as $v) {
            $tempSrcArr [$v [$key]] = $v;
        }
        $i     = 0;
        $count = count($sourceArr);
        for ($i = ($count - 1); $i >= 0; $i--) {
            if (isset ($tempSrcArr [$sourceArr [$i] [$parentKey]])) {
                $tArr                                        = array_pop($tempSrcArr);
                $tempSrcArr[$tArr[$parentKey]][$childrenKey] = (isset($tempSrcArr[$tArr[$parentKey]][$childrenKey]) && is_array($tempSrcArr[$tArr[$parentKey]][$childrenKey])) ? $tempSrcArr[$tArr[$parentKey]][$childrenKey] : array();
                array_push($tempSrcArr [$tArr [$parentKey]] [$childrenKey], $tArr);
            }
        }
        $treeArr = array();
        foreach ($tempSrcArr as $v) {
            array_push($treeArr, $v);
        }
        return $treeArr;
    }

    /**
     * @param array  $arr
     * @param string $key
     * @param string $value
     * @return array
     */
    static public function array2dict($arr, $key = 'id', $value = 'name') {
        $dict = array();
        foreach ($arr as $v) {
            $dict[strval(empty($key) ? $v : $v[$key])] = empty($value) ? $v : $v[$value];
        }
        return $dict;
    }

    /**
     * @param array $data
     */
    static public function html_entity_encode_array($data) {
        $result = array();
        foreach ($data as $k => $v) {
            $result[$k] = is_array($v) ? self::html_entity_encode_array($v) : htmlentities($v);
        }
        return $result;
    }

    /**
     * @param string $json_data 接受到的json
     * @param number $type      0代表正常解析  1代表解析以后再转换(针对form表单序列化处理)
     * @return Ambigous <mixed, multitype:mixed >
     */
    static public function json2array($json_data, $type = 0) {
        $json_data = html_entity_decode($json_data);
        $arr_data  = array();
        if ($type == 0) {
            $arr_data = json_decode($json_data, true);
        } else if ($type == 1) //表单json解析后需要做数组转换
        {
            $arr_tmp  = json_decode($json_data, true);
            $arr_data = self::array2dict($arr_tmp, 'name', 'value');
        }
        $arr_data = self::html_entity_encode_array($arr_data);
        return $arr_data;
    }

    /**
     * @param array  $arr_data 需转换的数组
     * @param array  $arr_name 转换时提供的name数组
     * @param number $num      name=>value的对数
     * @param        array     用于格式化后formatter
     * @return array list
     */
    static public function array2show($arr_data, $arr_name, $num = 1, $arr_formatter = array()) {
        $list    = array();
        $i       = 1;
        $arr_tmp = array();
        $arr     = empty($arr_data[0]) ? $arr_data : $arr_data[0];
        foreach ($arr as $k => $v) {
            $tmp     = array('name' . $i => $arr_name[$k], 'value' . $i => empty($arr_formatter) ? $v : (empty($arr_formatter[$k]) ? $v : $arr_formatter[$k][$v]));
            $arr_tmp = array_merge($arr_tmp, $tmp);
            if ($num == 1 || $i % $num == 0) {
                $i       = 0;
                $list[]  = $arr_tmp;
                $arr_tmp = array();
            }
            $i++;
        }
        if (!empty($arr_tmp)) {
            $list[] = $arr_tmp;
        }
        return $list;
    }

    /**
     * 字段设置默认值
     * @return unknown
     */
    static public function pregTitle($code) {

        $model                 = M('cfg_user_data');
        $path                  = get_log_path('debug');
        $conditions['user_id'] = '1';
        $conditions['type']    = '1';
        $conditions['code']    = $code;
        $result                = $model->where($conditions)->field('data')->find();
        $titleStr              = $result['data'];
        $split_display         = preg_match('/[\)](,)[\(]/', $titleStr, $match, PREG_OFFSET_CAPTURE);
        if ($split_display == 0) {
            $is_display_set = $titleStr;
            $un_display_set = "";
        } else {
            $is_display_set = substr($titleStr, 0, $match[1][1]);
            $un_display_set = substr($titleStr, $match[1][1] + 1);
        }

        preg_match_all('/[\(]([^\(\)]*)[\)]/', $is_display_set, $is_display_arr, PREG_PATTERN_ORDER);
        preg_match_all('/[\(]([^\(\)]*)[\)]/', $un_display_set, $un_display_arr, PREG_PATTERN_ORDER);

        $full_fields_arr[0] = array();  //不显示的field
        $full_fields_arr[1] = array();    //显示的field
        $shows              = array();
        //生成fields  字段的数组
        foreach ($is_display_arr [1] as $key => $value) {
            $temp                             = preg_split('/,/', $value);
            $full_fields_arr[1]["{$temp[1]}"] = array('field' => $temp[0], 'width' => $temp[2], 'hidden' => false);
            echo "'{$temp[1]}'=>array('field'=>'{$temp[0]}','width'=>'{$temp[2]}'),</br>";
            $shows[] = "'{$temp[1]}'=>array('field'=>'{$temp[0]}','width'=>'{$temp[2]}'),\n";
        }
        foreach ($un_display_arr [1] as $key => $value) {
            $temp                             = preg_split('/,/', $value);
            $full_fields_arr[0]["{$temp[1]}"] = array('field' => $temp[0], 'width' => $temp[2], 'hidden' => true);
            echo "'{$temp[1]}'=>array('field'=>'{$temp[0]}','width'=>'{$temp[2]}'),</br>";
            $shows[] = "'{$temp[1]}'=>array('field'=>'{$temp[0]}','width'=>'{$temp[2]}'),\n";
        }
        \Think\Log::write(print_r($shows, true), \Think\Log::INFO, '', $path);
        return array_merge($full_fields_arr[1], $full_fields_arr[0]);
    }

    /**
     * 返回包含有中文字符的json，防止中文字符被json_ecode转码
     * @param array $encode_array 需要转化的数组
     */
    static public function parseChineseCharOfJson($encode_array) {
        return urldecode(json_encode(urlencodArr($encode_array)));
    }

    //将字符串转换成十进制数字
    /**
     * @param        $str
     * @param string $type
     * @return int
     */
    static public function sumStr($str, $type = "lower") {
        switch ($type) {
            case "lower":
                $base = 96;
                break;
            case "upper":
                $base = 64;
                break;
            default:
                E("不支持该类型");
        }
        $sum = 0;
        $k   = 1;
        for ($i = strlen($str) - 1; $i >= 0; $i--) {
            $sum += (ord($str[$i]) - $base) * $k;
            $k *= 26;
        }
        return $sum;
    }

    //将Excel表格转换为数组
    static public function Excel2Arr($name, $file, $str = "") {
        //允许文件后缀名
        $allowed_types = array("xls", "xlsx");
        //取得文件名后缀
        $ext = substr(strrchr($name, "."), 1);
        if (!in_array_case($ext, $allowed_types)) {
            unset($file);
            E("文件名不符合要求，请重新导入");
            return false;
        }
        try {
            //获取卖家账号
            $sid = get_sid();
            //移动表格到runtime目录下，该目录定期删除
            $filePath = APP_PATH . "Runtime/File/" . time() . "__" . $sid . "__" . $str . "__" . md5(md5(time()));
            move_uploaded_file($file, $filePath);
        } catch (\Exception $e) {
            \Think\Log::write($e->getMessage());
            unset($file);
            E("文件错误，无法读取");
            return false;
        }
        require_once(APP_PATH . "Common/SDK/PHPExcel/Classes/PHPExcel.php");
        set_time_limit(0);
        $PHPReader = new \PHPExcel_Reader_Excel2007();
        if (!$PHPReader->canRead($filePath)) {
            $PHPReader = new \PHPExcel_Reader_Excel5();
            if (!$PHPReader->canRead($filePath)) {
                unset($filePath);
                E("文件错误，无法读取");
                return false;
            }
        }
        $PHPExcel   = $PHPReader->load($filePath);
        $sheetCount = $PHPExcel->getSheetCount();
        $sheetNames = $PHPExcel->getSheetNames();

        $objExcel = array();
        for ($SheetID = 0; $SheetID < $sheetCount; $SheetID++) {

            $name         = $sheetNames[$SheetID];
            $currentSheet = $PHPExcel->getSheetByName($name);

            $allColumn = $currentSheet->getHighestColumn();
            $allColumn = self::sumStr($allColumn, "upper");

            $allRow = $currentSheet->getHighestRow();
            if(workTimeUploadNum()<$allRow){
                SE (self::UPPER_UPLOAD);
            }
            for ($currentRow = 1; $currentRow <= $allRow; $currentRow++) {
                for ($currentColumn = 1; $currentColumn <= $allColumn; $currentColumn++) {
                    $val                                                  = $currentSheet->getCellByColumnAndRow($currentColumn - 1, $currentRow)->getValue();
                    $val                                                  = $val != null ? $val : "";
                    $objExcel[$name][$currentRow - 1][$currentColumn - 1] = html_filter($val);
                }
            }
        }

        return $objExcel;
    }
    /*
     * $arr 要导出的数据,二维数组
     * $title excel sheet的标题
     * $excel_no 数据字段翻译，作为表头标题，一维数组，下标为字段名，值为中文名
     * $width_arr 宽度数组 一维索引数组。跟表头一一对应。
     * $filename 导出的excel名
     * $creator 导出人
     * */
    //将数组转换为Excel表格
    static public function Arr2Excel($arr, $title, $excel_no, $width_arr, $filename, $creator) {
        //$phpExcel = new PHPExcel();
        if (empty($arr)) {
            $result = array('status' => 1, 'info' => '导出数据为空，请添加！');
            return $result;
        }
        //返回所有的字段
        $keys_data_arr = array_keys($arr[0]);
        //统计有多少个字段
        $count_keys = count($keys_data_arr);
        //excel表头标记
        $excel_sign = array(
            'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z',
            'AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH', 'AI', 'AJ', 'AK', 'AL', 'AM', 'AN', 'AO', 'AP', 'AQ', 'AR', 'AS', 'AT', 'AU', 'AV', 'AW', 'AX', 'AY', 'AZ',
            'BA', 'BB', 'BC', 'BD', 'BE', 'BF', 'BG', 'BH', 'BI', 'BJ', 'BK', 'BL', 'BM', 'BN', 'BO', 'BP', 'BQ', 'BR', 'BS', 'BT', 'BU', 'BV', 'BW', 'BX', 'BY', 'BZ',
            'CA', 'CB', 'CC', 'CD', 'CE', 'CF', 'CG', 'CH', 'CI', 'CJ', 'CK', 'CL', 'CM', 'CN', 'CO', 'CP', 'CQ', 'CR', 'CS', 'CT', 'CU', 'CV', 'CW', 'CX', 'CY', 'CZ',
            'DA', 'DB', 'DC', 'DD', 'DE', 'DF', 'DG', 'DH', 'DI', 'DJ', 'DK', 'DL', 'DM', 'DN', 'DO', 'DP', 'DQ', 'DR', 'DS', 'DT', 'DU', 'DV', 'DW', 'DX', 'DY', 'DZ',
            'EA', 'EB', 'EC', 'Ed', 'EE', 'EF', 'EG', 'EH', 'EI', 'EJ', 'EK', 'EL', 'EM', 'EN', 'EO', 'EP', 'EQ', 'ER', 'ES', 'ET', 'EU', 'EV', 'EW', 'EX', 'EY', 'EZ',
            'FA', 'FB', 'FC', 'Fd', 'EF', 'FF', 'FG', 'FH', 'FI', 'FJ', 'FK', 'FL', 'FM', 'FN', 'FO', 'FP', 'FQ', 'FR', 'FS', 'FT', 'FU', 'FV', 'FW', 'FX', 'FY', 'FZ'
        );

        require_once APP_PATH . "Common/SDK/PHPExcel/Classes/PHPExcel.php";
        require_once APP_PATH . "Common/SDK/PHPExcel/Classes/PHPExcel/IOFactory.php";
        require_once APP_PATH . "Common/SDK/PHPExcel/Classes/PHPExcel/Reader/Excel5.php";
        $objPHPExcel = new \PHPExcel();
        //设置属性
        $objPHPExcel->getProperties()->setCreator($creator);
        $objPHPExcel->getProperties()->setTitle($title);
        $objPHPExcel->getProperties()->setSubject('题目');
        //设置宽度 如果没有传设置宽度的数组默认为自动设置宽度
        $objPHPExcel->setActiveSheetIndex(0);
        //自动设置宽度会出问题，尽量不用。自动设置宽度前面2列正常，后面的大部分都会错误。
        if (count($width_arr) == 0) {
            for ($i = 0; $i < $count_keys; $i++) {
                $objPHPExcel->getActiveSheet()->getColumnDimension("$excel_sign[$i]")->setAutoSize(true);
            }
        } else {
            for ($i = 0; $i < $count_keys; $i++) {
                $objPHPExcel->getActiveSheet()->getColumnDimension("$excel_sign[$i]")->setWidth($width_arr[$i]);
            }
        }
        //设置表头
        for ($i = 0; $i < $count_keys; $i++) {
            $indexy     = $excel_sign[$i] . '1';
            $excel_head = empty($excel_no[$keys_data_arr[$i]]) ? $keys_data_arr : $excel_no[$keys_data_arr[$i]];
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($indexy, $excel_head);

        }
        //设置值  在设置值的时候设置了该单元格的格式为文本，避免出现科学记数法显示超过9位数字
        foreach ($arr as $k => $v) {
            $j = $k + 2;
            for ($i = 0; $i < $count_keys; $i++) {
                $indexyy = $excel_sign[$i] . $j;
                $objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($indexyy, '' . $v[$keys_data_arr[$i]], \PHPExcel_Cell_DataType::TYPE_STRING);
            }
        }
        $objPHPExcel->getActiveSheet()->setTitle($filename);
        $objPHPExcel->setActiveSheetIndex(0);
        ob_end_clean();//清除缓冲区，避免乱码
        header("Content-type: application/octet-stream;charset=utf-8");
        header("Content-type:test/csv");
        header("Content-Type:application/vnd.openxmlformats-officedocument.spreadsheetml.sheet;charset=UTF-8");
        $filenames = $filename . '_' . date("Y-m-d", time()) . '_' . time() . '.xls';
        $filenames = iconv('utf-8', 'gb2312', $filenames);
        header("Content-Disposition: attachment;filename={$filenames}");
        header('Cache-Control: max-age=0');
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }

    /*
    static public function getClientIp() {
        $server = I('server');
        //\Think\Log::write("server var:".print_r($server, true));

        $remote_addr = I('server.REMOTE_ADDR');
        if (self::isIp($remote_addr)){
            return $remote_addr;
        }

        $x_forwarded_for = I('server.HTTP_X_FORWARDED_FOR');
        $ip_list = explode(',', $x_forwarded_for);
        foreach ($ip_list as $ip){
            if (self::isIp($ip)) {
            return $ip;
            }
        }

        $client_ip       = I('server.HTTP_CLIENT_IP');
        if (self::isIp($client_ip)) {
            return $client_ip;
        }

        return '0.0.0.0';
    }
    */
    static public function isIp($str) {
        $ip = explode('.', $str);
        for ($i = 0; $i < count($ip); $i++) {
            if ($ip[$i] > 255) {
                return false;
            }
        }
        return preg_match('/^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$/', $str);
    }

    static public function SMS($mobiles, $message, $code, $sid="") {
        require_once(APP_PATH . "Platform/SDK/sms/sms_client.php");
        $sid = empty($sid)?get_sid():$sid;
        $client = new  \sms_client();
        $client->appKey = '999999';
        $client->appSecret = 'e063bed69948a2566e3de55250c815af';
        $client->apiMethod = 'wdt.sms.send.send';
        if(substr($mobiles,'-1')==','){
            $mobiles=substr($mobiles,0,strlen($mobiles)-1);
        }
        $params = array(
            array('sid'=>$sid,'version'=>0,'msg'=>$message,'phone'=>$mobiles,'ext'=>$code)
        );
        $p=array('sms'=>json_encode($params));
        $result = $client->execute($p);
        if($result->code==0){
            $result->msg = "发送成功";
        }
        $res['status']=$result->code;
        $res['info'] = $result->msg;
        return $res;

    }

    /**
     * 设置错误日志邮件缓存
     * @param unknown $message
     * @param string  $type
     * @param string  $sid
     */
    static public function setMailCache($message, $type = 'trade') {
        /*$mail_cache_path = C('SYSTEM_LOGS_PATH') . 'mailcache' . DIRECTORY_SEPARATOR . $type . '.cache.log';
        $mail_cache_dir  = dirname($mail_cache_path);
        if (!is_dir($mail_cache_dir)) {
            mkdir($mail_cache_dir, 0755, true);
        }
        if (!is_file($mail_cache_path)) {
            return file_put_contents($mail_cache_path, $message, FILE_APPEND | LOCK_EX);
        }
        //邮件缓存错误日志大于10kb(10240) 20kb或缓存时间超过10min(600) 30min发送一次 错误日志邮件
        if (filemtime($mail_cache_path) + 1800 < time() || filesize($mail_cache_path) > 20480) {
            $sendto_email = C('MAIL_ADDRESS_' . strtoupper($type));
            $subject      = "{$type}模块的错误日志信息";
            $content      = file_get_contents($mail_cache_path);
            $content .= $message;
            if (self::sendMail($content, $sendto_email, $subject)) {
                file_put_contents($mail_cache_path, '', LOCK_EX);
            }
        } else {
            file_put_contents($mail_cache_path, $message, FILE_APPEND | LOCK_EX);
        }*/
        $sendto_email = C('MAIL_ADDRESS_' . strtoupper($type));
        $subject      = "{$type}模块的错误日志信息";
        self::sendMail($message, $sendto_email, $subject);
    }

    static public function sendMail($message, $addresses, $subject = '',$resend=false,$count=0) {
        try {
            /*$date = date('Y-m-d',time());
            $email_cache=M('email_cache')->where(array('msg'=>$message,'created'=>$date))->find();
            if(!empty($email_cache)){
                $num = $email_cache['num'];
                if($num>20){
                    SE('Email send toomany:' . $num);
                }else{
                    $sql = "UPDATE `email_cache` SET `num`=$num+1 WHERE `msg`='{$message}' AND `created`='{$date}'";
                    M('')->execute($sql);
                }
            }else{
                $sql = "INSERT INTO `email_cache` (`msg`,`num`,`created`) VALUES ('{$message}',1,'{$date}')";
                M('')->execute($sql);
            }*/
            $mail = Register::get('phpmailer');
            if (!$mail) {
                $mail      = new PHPMailer();
                $smtp_mail = C('SMTP_MAIL');
                $mail->IsSMTP();
                $mail->Host     = $smtp_mail['host'];
                $mail->SMTPAuth = true;
				$mail->SMTPSecure = 'ssl';

                if($resend ==true)
                {
                    $mail->Username =$smtp_mail['username1'];// 发件人邮箱
                    $mail->Password =$smtp_mail['password1'];// 发件人邮箱密码
                    $mail->From     =$smtp_mail['username1'];// 发件人邮箱
                }else{
                    $mail->Username =$smtp_mail['username'];
                    $mail->Password =$smtp_mail['password'];
                    $mail->From     =$smtp_mail['username'];
                }
                $mail->FromName = C('SYSTEM_NAME');// 发送人姓名

                $mail->CharSet = "UTF-8";
                $mail->setLanguage('zh_cn');
                $mail->Encoding = "base64";
                Register::set('phpmailer', $mail);
            }
            $mail->Subject = $subject;
            $mail->Body    = $message;
            foreach ($addresses as $val) {
                $mail->AddAddress($val);
            }
            if (!$mail->Send()) {
                $count++;
                Register::set('phpmailer', false);
                if($count>1){
                    E('Email send failed:' . $mail->ErrorInfo);
                }else{
                    self::sendMail($message, $addresses, $subject,true,$count);
                }
            }
        } catch (\Exception $e) {
            \Think\Log::write($e->getMessage(), 'MAIL');
            return false;
        }
        return true;
    }

    /**
     * @param $arr
     * @return bool
     * 检查一个数字的值是否全部为空，如果为空则返回true
     */
    static public function checkArrValue($arr) {
        $res = true;
        foreach ($arr as $v) {
            if ($v !== "") {
                $res = false;
                break;
            }
        }
        return $res;
    }


    /**
     * @param           $value
     * @param int       $type
     * @param bool|true $filter
     * @return bool|int|mixed|string
     * 检查
     */
    static public function check_search_form_value($value, $type = 1, $filter = true) {
        switch ($type) {
            case 1: //文本检查
                $value = trim_all($value, 1);
                if (empty($value) && ($value !== 0) && ($value !== '0')) {
                    return false;
                }
                return $filter === true ? addslashes(trim_all($value, 1)) : $value;
            case 2:  //数字检查
                if(stripos($value,','))
                {
                    return addslashes($value);
                }
                if (!check_regex('number', $value)) {
                    return false;
                }
                return $filter === true ? intval($value) : $value;
            case 3: //date 日期数据检查
                if (!check_regex('date', $value)) {
                    return false;
                }
                return $filter === true ? addslashes($value) : $value;
            case 4: //time 时间数据检查
                if (!check_regex('time', $value)) {
                    return false;
                }
                return $filter === true ? addslashes($value) : $value;
        }
    }

    /*
     * 事件触发的短信策略消息发送
     * @event_type 事件类型
     * @order_id  订单编号
     *
    */
    static public function crm_sms_record_insert($event_type,$order_id){
        $operator_id = get_operator_id();
        $db = M('');
        //获取客户信息
        $sql = "SELECT st.buyer_nick,st.receiver_name,TRIM(st.receiver_mobile) as receiver_mobile,st.receivable,ss.shop_name,cl.logistics_name,st.logistics_no,st.trade_time,st.receiver_area,st.receiver_address,
			st.discount,st.cod_amount,st.post_amount,st.src_tids,st.weight,st.shop_id,NOW() as send_time,st.pay_time,dl.logistics_name as logistics_type,st.split_from_trade_id
			FROM sales_trade st
			LEFT JOIN crm_customer cc USING (customer_id)
			LEFT JOIN cfg_shop ss ON ss.shop_id = st.shop_id
			LEFT JOIN cfg_logistics cl ON cl.logistics_id = st.logistics_id
			LEFT JOIN dict_logistics dl ON dl.logistics_type = cl.logistics_type
			WHERE st.trade_id = $order_id";
        $result = $db->query($sql);
        $res = $result[0];
        //网名
        $nick_name = $res['buyer_nick'];
        //收件人
        $name = $res['receiver_name'];
        //收件人手机号
        $receivable = $res['receiver_mobile'];
        //店铺名
        $shop_name = $res['shop_name'];
        //物流公司
        $logistics_name = $res['logistics_name'];
        //物流单号
        $logistics_no = $res['logistics_no'];
        //下单时间
        $trade_time = $res['trade_time'];
        //优惠金额
        $discount = $res['discount'];
        //货到付款金额
        $cod_amount = $res['cod_amount'];
        //邮费
        $post_amount = $res['post_amount'];
        //原始单号
        $src_tids = $res['src_tids'];
        //重量
        $weight = $res['weight'];
        //店铺id
        $shop_id = $res['shop_id'];
        //发送时间
        $send_time = $res['send_time'];
        //支付时间
        $pay_time = $res['pay_time'];
        //物流类别
        $logistics_type = $res['logistics_type'];
        //拆分订单 原单id
        $split_from_trade_id = $res['split_from_trade_id'];
        //收货地区
        $receiver_area = $res['receiver_area'];
        //收货地址
        $receiver_address = $res['receiver_address'];

        if($res['receiver_mobile']==''){
            \Think\Log::write('短信策略获取手机号为空');
            return false;
        }

        //获取发送短信的配置(一个手机号一段时间发送一次)
        $cfg_member_send_sms_limit_time = get_config_value('crm_member_send_sms_limit_time',0);

        $rule_sql = "SELECT cssr.template_id,cssr.delay_time
		FROM cfg_sms_send_rule cssr
		WHERE cssr.shop_id = %d AND cssr.event_type = %d AND is_disabled=0";
        $rule_res = $db->query($rule_sql,$shop_id,$event_type);
        foreach($rule_res as $k=>$v){
            //获取短信内容
            $sql = "SELECT content,is_split,sign FROM cfg_sms_template WHERE rec_id = {$v['template_id']}";
            $res = $db->query($sql);
            if($res[0]['is_split']==2 && $v['split_from_trade_id']==0){
                continue;
            }elseif($res[0]['is_split'] && $v['split_from_trade_id']<>0 ){
                $exists = 0;
                $sql = "SELECT COUNT(1) FROM sales_trade st,
				(SELECT sto1.trade_id FROM sales_trade_order sto1,sales_trade_order sto2
				WHERE sto1.src_tid= sto2.src_tid  AND sto1.trade_id <> sto2.trade_id AND sto2.trade_id = {$order_id}) st2
				WHERE st.trade_id = st2.trade_id AND st.trade_status <95";
                $res = $db->query($sql);
                $exists = $res[0]['count(1)'];
            }
            $sign = $res[0]['sign'];
            $sign = "【{$sign}】";
            $content = $res[0]['content'];
            $content = str_replace('{客户网名}',$nick_name,$content);
            $content = str_replace('{原始单号}',$src_tids,$content);
            $content = str_replace('{客户姓名}',$name,$content);
            $content = str_replace('{店铺名称}',$shop_name,$content);
            $content = str_replace('{物流单号}',$logistics_no,$content);
            $content = str_replace('{物流公司}',$logistics_name,$content);
            $content = str_replace('{下单时间}',$trade_time,$content);
            $content = str_replace('{发货时间}',$send_time,$content);
            $content = str_replace('{收货地区}',$receiver_area,$content);
            $content = str_replace('{收货地址}',$receiver_address,$content);
            //拆分订单后面添加提示
            $content .= $exists>0?'您还有未拆分的订单':'';
            $content.=$sign;
            //获取短信内容长度
            $length = iconv_strlen($content, 'UTF-8');
            $sms_num = ceil($length/66);
            //获取短信批次号
            $batch_no=$db->query("SELECT FN_SYS_NO('sms')");
            $batch_no = $batch_no[0]["fn_sys_no('sms')"];
            //发送时间确定
            $sms_send_time = date('Y-m-d H:i:s',time()+($v['delay_time']*60));
            //检查是否满足发送短信条件(设置里同一个手机号多久只能发一条短信的设置。如果开启的话这里满足条件就跳过了)
            if($cfg_member_send_sms_limit_time>0){
                $limit_time = date('Y-m-d H:i:s',strtotime($sms_send_time)-$cfg_member_send_sms_limit_time*60);
                $sql = "SELECT 1 FROM crm_sms_record WHERE (status=0 OR status = 1 OR status = 2) AND
			            (timer_time>='{$limit_time}' AND timer_time<'{$sms_send_time}') AND phone_num=1 AND phones='{$receivable}'";
                $is_exists = $db->query($sql);
                if(!empty($is_exists)){
                    continue;
                }
            }

            //插入短信发送表
            $insert_sql = "INSERT INTO crm_sms_record(`status`,sms_type,send_type,operator_id,phones,phone_num,message,timer_time,send_time,batch_no,pre_count,try_times,error_msg,created)
		                   VALUES(0,1,1,$operator_id,$receivable,1,'{$content}','{$sms_send_time}',0,'{$batch_no}','{$sms_num}',0,'',NOW())";
            $db->execute($insert_sql);
            //签收事件时防止再次发送
            /*IF P_EventType=8  AND EXISTS(SELECT 1 FROM sales_trade WHERE trade_id= P_OrderId AND NOT(trade_mask&256) ) THEN
			UPDATE sales_trade SET trade_mask = trade_mask|256 WHERE trade_id= P_OrderId;
		END IF;*/

        }
    }


}


?>
