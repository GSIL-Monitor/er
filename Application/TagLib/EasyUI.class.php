<?php
namespace TagLib;
use Think\Template\TagLib;
/**
 * easyUI标签库
 */
class EasyUI extends TagLib{
    // 标签定义
    protected $tags   =  array(
        // 标签定义： attr 属性列表 close 是否闭合（0 或者1 默认1） alias 标签别名 level 嵌套层次
        'datagrid'     => array('attr'=>'id,style,class,options,fields','close'=>0)
    );

    /**
     * easyui - datagrid
     * 格式： <easyui:datagrid id="id" options="options" fields="fields" style="" />
     * @param array $tag 标签属性
     * @return string|void
     */
    public function _datagrid($tag) {
        $id    = !empty($tag['id']) ? $tag['id'] : strtolower(CONTROLLER_NAME.'_'.ACTION_NAME.'_datagrid');//设置默认table的id属性 $this->autoBuildVar($tag['id']);//
        $style = !empty($tag['style']) ? $tag['style'] : '';
        $class = !empty($tag['class']) ? $tag['class'] : 'easyui-datagrid';
        //默认参数
    	$dataOptions = array(
    		'border'       => false,
    		'fit'          => true,
    		'fitColumns'   => true,
    		'rownumbers'   => true,
    		'singleSelect' => true,
    		'pagination'   => true,
    	    'pageList'     => array(20,50,100),
    	    'pageSize'     => cookie('pagesize') ? cookie('pagesize') : 20,//设置默认20条信息一页
    	);
    	$options = $tag['options'] ? $this->autoBuildVar($tag['options']) : 'array()';
        $fields  = $tag['fields'] ? $this->autoBuildVar($tag['fields']) : 'null';
        
       $parseStr = '<table id="'. $id .'" class="'.$class.'" data-options=\'<?php $dataOptions = array_merge('. var_export($dataOptions, true). ', '. $options .');if(isset($dataOptions[\'toolbar\']) && substr($dataOptions[\'toolbar\'],0,1) != \'#\'): unset($dataOptions[\'toolbar\']); endif;if(isset($dataOptions[\'methods\'])): unset($dataOptions[\'methods\']);endif; echo trim(json_encode($dataOptions), \'{}[]\').((isset('. $options .'[\'toolbar\']) && substr('. $options .'[\'toolbar\'],0,1) != \'#\')?\',"toolbar":\'.'. $options .'[\'toolbar\']:null).(isset('.$options.'[\'methods\'])? \',\'.'.$options.'[\'methods\']:null); ?>\' style="'. $style .'" ><thead><tr>';
		$parseStr .= '<?php if(is_array('. $fields .')):foreach ('. $fields .' as $key=>$arr):if(isset($arr[\'formatter\'])):unset($arr[\'formatter\']);endif;if(isset($arr[\'methods\'])):unset($arr[\'methods\']);endif;if(isset($arr[\'editor\'])):unset($arr[\'editor\']);endif;echo "<th data-options=\'".trim(json_encode($arr), \'{}[]\').(isset('. $fields .'[$key][\'formatter\'])?",\"formatter\":".'. $fields .'[$key][\'formatter\']:null).(isset('. $fields .'[$key][\'editor\'])?",\"editor\":".'. $fields .'[$key][\'editor\']:null).(isset('. $fields .'[$key][\'methods\'])?",".'. $fields .'[$key][\'methods\']:null)."\'>".$key."</th>";endforeach;endif; ?>';
        $parseStr .= '</tr></thead></table>';
        
        return $parseStr;
    }
}