<?php
namespace Common\Common;

use Stock\Common\StockFields;
use Goods\Common\GoodsFields;
use Setting\Common\SettingFields;
//这个类不再使用，以前使用的地方会逐渐修改过来
class DatagridExtention
{
    static public function getIdList($key_list = array(),$prefix=''){
        empty($prefix) ?$prefix: $prefix = $prefix . '_';
        $name =strtolower($prefix . CONTROLLER_NAME);
        foreach ($key_list as $value) {
            $id_list[$value] = $name.'_'.$value;
        }
        return $id_list;
    }

    static public function getMainDatagrid($id,$url,$toolbar,$fields){
        $datagrid = array(
            'id' => $id,
            'options' => array(
                'title' => '',
                'url' => $url,
                'toolbar' => "#{$toolbar}",
                'fitColumns' => false,
            ),
            'fields' => $fields,
            'class' => 'easyui-datagrid',
        );
        return $datagrid;
    }

    static public function getTabDatagrid($datagridId,$fields){
        $datagrid = array(
            'id'         =>$datagridId,
            'options'    => array(
                'title'      => '',
                'url'        => null,
                'fitColumns' => false,
                'pagination' => false,
                'rownumbers' => true,

            ),
            'fields'=>$fields,
            'class'=>'easyui-datagrid',
            'style'=>'padding:5px;'
        );
        return $datagrid;
    }

    static public function getRichDatagrid($app,$field_name,$url,$id_prefix='',$id_list=array('tool_bar','add','edit','select','form','tab_container','hidden_flag','datagrid','delete')){
        $data = array();

        $data['id_list'] = self::getIdlist($id_list,$id_prefix);

//        switch ($app){
//            case 'Goods':
//                $fields = GoodsFields::getGoodsFields($field_name);
//                break;
//            case 'Setting':
//                $fields = SettingFields::getSettingFields($field_name);
//                break;
//            case 'Stock':
//                $fields = StockFields::getStockFields($field_name);
//                break;
//        }
        $fields = get_field($app,$field_name);
        $data['datagrid'] = self::getMainDatagrid($data['id_list']['datagrid'],$url,$data['id_list']['tool_bar'],$fields);
        return $data;
    }

}
