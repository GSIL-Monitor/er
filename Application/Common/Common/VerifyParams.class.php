<?php

/**
 * Created by PhpStorm.
 * User: gaosong
 * Date: 15/9/24
 * Time: 下午4:03
 */
namespace Common\Common;
class VerifyParams
{
    /**
     * 设置默认值
     * @param string $subject 需要正则验证的字符串
     * @param string $pattern 验证的类型
     * @param array $result 通过这个两个参数返回四种类型
     * @param string $illchar 过滤非法字符,默认关闭
     * @param string $exverify 过滤其它指定字符，默认关闭
     * @param string $extext 过滤字符替换成制定字符,默认是空
     * @return string|bool
     */
    // 正则类型验证
    static public function regexVerify($pattern, $subject, $result = array(0, 0), $illchar, $exverify, $extext = '')
    {
        $s = trim($subject);
        if (!empty($illchar)) {
            $s = preg_replace('/[\'.,:;*?~`!#$%^&+=)(<>{}]|\]|\[|\/|\\\|\"|\|/', '', $s);
        }
        if (!empty($everify)) {
            $s = preg_replace($exverify, $extext, $s);
        }
        // check_regex($s,'moblie');
        switch ($pattern) {
            //手机号验证->Ok
            case 'mobile':
                $regex = '/^13[\d]{9}$|^14[5,7]{1}\d{8}$|^15[^4]{1}\d{8}$|^17[0,6,7,8]{1}\d{8}$|^18[\d]{9}$|^19[\d]{9}$|^16[\d]{9}$/';
                break;
            //座机号验证->OK
            case 'telno':
                $regex = "/[\d]{3}-[\d]{8}|[\d]{4}-[\d]{7,8}/";
                break;
            //邮箱验证->Ok
            case 'email':
                $regex = "/^[\w-]+(\.[\w-]+)*@[\w-]+(\.[\w-]+)+$/";
                break;
            //验证邮编->OK
            case 'zip':
                $regex = "/^[0-9]{5,}$/";
                break;
            //验证只有数字和字母->OK
            case 'aA1':
                $regex = "/^[A-Za-z0-9]+$/";
                break;
            //验证url->这个有点问题只匹配了开头的http://
            case 'url':
                $regex = "/^(http:\/\/)/";
                break;
            //验证由数字、26个英文字母或者下划线组成的字符串->OK
            case 'aA_1':
                $regex = "/^[\w]+$/";
//                $regex = "/^[A-Za-z0-9\_]+$/";
                break;
            //gbk编码匹配中文->OK
            case 'zh_gbk':
                $regex = "/^[\x7f-\xff]+$/";
                break;
            //utf8编码匹配中文->没有测试！
            case 'zh_utf8':
                $regex = "/^[\u4e00-\u9fa5]+$/";
                break;
            //gbk编码匹配中文英文数字下划线横线->OK
            case 'zh_aA_1-gbk':
                $regex = "/^[a-zA-Z0-9_" . "\x7f-\xff" . "-" . "]+$/";
                break;
            //gbk编码匹配中文英文数字下划线->OK
            case 'zh_aA_1_gbk':
                $regex = "/^[a-zA-Z0-9_" . "\x7f-\xff" . "]+$/";
                break;
            //utf8编码匹配中文英文数字下划线->未测试！
            case 'zh_aA_1_utf8':
                $regex = "/^[a-zA-Z0-9_x80-xff]+[^_]$/g";
                break;
            //utf8编码匹配中文英文数字下划线横线->未测试！
            case 'zh_aA_1-utf8':
                $regex = "/^[a-zA-Z0-9_x80-xff." - ".]+[^_]$/g";
                break;
            //匹配非负整数->OK
            case '+0':
                $regex = "/^\d+$/";
                break;
            //匹配正整数->OK
            case '+1':
                $regex = "/^[1-9]*[1-9][0-9]*$/";
                break;
            //匹配负整数->OK
            case '-1':
                $regex = "/^-[1-9]*[1-9][0-9]*$/";
                break;
            //匹配整数->OK
            case '+-1':
                $regex = "/^-?\d+$/";
                break;
            //匹配非负浮点数->Ok
            case "0.1":
                $regex = "/^\d+(\.\d+)?$/";
                break;
            //匹配浮点数->OK
            case "+-0.1":
                $regex = "/^(-?\d+)(\.\d+)?$/";
                break;
            //匹配由26个英文字母组成的字符串->OK
            case "aA":
                $regex = "/^[A-Za-z]+$/";
                break;
            //匹配由26个英文字母的大写组成的字符串->OK
            case 'A':
                $regex = "/^[A-Z]+$/";
                break;
            //由26个英文字母的小写组成的字符串->OK
            case 'a':
                $regex = "/^[a-z]+$/";
                break;
            //请输入大于等于0的整数
            case 'integ':
                $regex = "/^[+]?[0-9]\d*$/";
                break;
            default:
                return "cann't match!";
        }
        if ($result == array(0, 1)) {
            return preg_match($regex, $subject) ? $subject : false;
        } elseif ($result == array(1, 1)) {
            return preg_match($regex, $subject) ? $subject : "false";
        } elseif ($result == array(1, 0)) {
            return preg_match($regex, $subject) ? "true" : "false";
        } else {
            return preg_match($regex, $subject) ? true : false;
        }
    }

    /**
     * 设置默认值
     * @param array $pattern 验证范围
     * @param string $subject 需要验证的变量
     * @return string|bool
     */
    // 类型是否在范围验证
    static public function paramsInField($pattern = array(), $subject, $kv = 'v')
    {
        empty($kv) ? $kv = 'v' : $kv = $kv;
        switch ($kv) {
            case 'k':
                foreach ($pattern as $k => $v) {
                    if ($subject == $k) {
                        return true;
                    }
                }
                break;
            case 'v':
                foreach ($pattern as $v) {
                    if ($subject == $v) {
                        return true;
                    }
                }
                break;

        }
        return false;
    }
}