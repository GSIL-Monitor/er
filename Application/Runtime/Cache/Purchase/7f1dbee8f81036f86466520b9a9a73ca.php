<?php if (!defined('THINK_PATH')) exit();?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<!-- <link rel="stylesheet" type="text/css" href="/Public/Css/easyui.css">
<link rel="stylesheet" type="text/css" href="/Public/Css/icon.css">
<link rel="stylesheet" type="text/css" href="/Public/Css/table.css">
<script type="text/javascript" src="/Public/Js/jquery.min.js"></script>
<script type="text/javascript" src="/Public/Js/jquery.easyui.min.js"></script>
<script type="text/javascript" src="/Public/Js/easyui-lang-zh_CN.js"></script>
<script type="text/javascript" src="/Public/Js/datagrid.extends.js"></script>
<script type="text/javascript" src="/Public/Js/jquery.extends.js"></script>
<script type="text/javascript" src="/Public/Js/tabs.util.js"></script>
<script type="text/javascript" src="/Public/Js/erp.util.js"></script>
<script type="text/javascript" src="/Public/Js/rich-datagrid.util.js"></script>
<script type="text/javascript" src="/Public/Js/thin-datagrid.util.js"></script>
<script type="text/javascript" src="/Public/Js/datalist.util.js"></script>
<script type="text/javascript" src="/Public/Js/area.js"></script>
-->
</head>
<body>
<!-- layout-datagrid -->
<div class="easyui-layout" data-options="fit:true" style="width:100%;height:100%;overflow:hidden;" id="panel_layout">
<!-- layout-center-datagrid -->
 
<div data-options="region:'center'" style="width:100%;background:#eee;"><table id="<?php echo ($datagrid["id"]); ?>" class="easyui-datagrid" data-options='<?php $dataOptions = array_merge(array ( 'border' => false, 'fit' => true, 'fitColumns' => true, 'rownumbers' => true, 'singleSelect' => true, 'pagination' => true, 'pageList' => array ( 0 => 20, 1 => 50, 2 => 100, ), 'pageSize' => 20, ), $datagrid["options"]);if(isset($dataOptions['toolbar']) && substr($dataOptions['toolbar'],0,1) != '#'): unset($dataOptions['toolbar']); endif;if(isset($dataOptions['methods'])): unset($dataOptions['methods']);endif; echo trim(json_encode($dataOptions), '{}[]').((isset($datagrid["options"]['toolbar']) && substr($datagrid["options"]['toolbar'],0,1) != '#')?',"toolbar":'.$datagrid["options"]['toolbar']:null).(isset($datagrid["options"]['methods'])? ','.$datagrid["options"]['methods']:null); ?>' style="<?php echo ($datagrid["style"]); ?>" ><thead><tr><?php if(is_array($datagrid["fields"])):foreach ($datagrid["fields"] as $key=>$arr):if(isset($arr['formatter'])):unset($arr['formatter']);endif;if(isset($arr['methods'])):unset($arr['methods']);endif;if(isset($arr['editor'])):unset($arr['editor']);endif;echo "<th data-options='".trim(json_encode($arr), '{}[]').(isset($datagrid["fields"][$key]['formatter'])?",\"formatter\":".$datagrid["fields"][$key]['formatter']:null).(isset($datagrid["fields"][$key]['editor'])?",\"editor\":".$datagrid["fields"][$key]['editor']:null).(isset($datagrid["fields"][$key]['methods'])?",".$datagrid["fields"][$key]['methods']:null)."'>".$key."</th>";endforeach;endif; ?></tr></thead></table></div> 
<!-- layout-south-tabs -->


</div>
<!-- dialog -->
 <div id="<?php echo ($id_list["add"]); ?>"></div> <div id="<?php echo ($id_list["edit"]); ?>"></div> 
<!-- toolbar -->

    <div id="<?php echo ($id_list["tool_bar"]); ?>" style="padding:5px;height:auto">
        <form id="<?php echo ($id_list["form"]); ?>" class="easyui-form" method="post">
            <div class="form-div">
                <label >移动墙属性：</label>
                <label style="color: #FF0000" id="old_wall_type">分拣墙</label> ========> <label style="color: #FF0000" id="new_wall_type">缺货墙</label>
                <label style="margin-left: 51px;">分配方式 <input class="easyui-combobox txt" text="txt" name="distribution_way" data-options="width:100,panelHeight:110,valueField:'id',textField:'name',data:[{'id':'0','name':'顺序分配'},{'id':'1','name':'倒序分配'},{'id':'2','name':'随机分配'}],editable:false,value:'0'"></label>
                <a href="javascript:void(0)" style="margin-left: 30px;" class="easyui-linkbutton" data-options="iconCls:'icon-ok'" onclick = "box_goods_trans.boxDistribution()";>一键分配</a>
            </div>
            <div class="form-div">
                <label >移动墙编号：</label>
                <label >当前墙 </label><input class="easyui-textbox txt" type="text" name="old_wall_no" data-options="width:100,disabled:true" /> ========>
                <label >目标墙 </label><select class="easyui-combobox sel" name="new_wall_no" data-options="editable:false,width:100,panelHeight:'150px',onChange:function(newValue,oldValue){sortingBox.newWallNoOnChange(newValue,oldValue,this);}"  ><?php if(is_array($form_data)): $i = 0; $__LIST__ = $form_data;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vo["wall_no"]); ?>"><?php echo ($vo["wall_no"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?></select>
            </div>
        </form>
    </div>
<script type="text/javascript">
    //# sourceURL=box_goods_trans.js
$(function(){
    var toolbar_id = '<?php echo ($id_list["tool_bar"]); ?>';
    var element_selectors ={
        'old_wall_no'		:$('#'+toolbar_id+" :input[name='old_wall_no']"),
        'new_wall_no'		:$('#'+toolbar_id+" :input[name='new_wall_no']"),
        'distribution_way'	:$('#'+toolbar_id+" :input[name='distribution_way']"),
        'old_wall_type'		:$('#old_wall_type'),
        'new_wall_type'		:$('#new_wall_type'),
    };
    box_goods_trans = new Object();
    var old_wall_no = JSON.parse('<?php echo ($old_wall_no); ?>');
    var form_data_map = JSON.parse('<?php echo ($form_data_map); ?>');
    var newData = [];
    box_goods_trans.params = JSON.parse('<?php echo ($params); ?>');
    box_goods_trans.element_selectors = element_selectors;
    sortingBox.newWallNoOnChange = function (newValue, oldValue) {
        box_goods_trans.setNewWallType();
    }
    box_goods_trans.setNewWallType = function(){
        var old_wall_no = box_goods_trans.element_selectors.old_wall_no.textbox('getValue');
        var new_wall_no = box_goods_trans.element_selectors.new_wall_no.combobox('getValue');
        box_goods_trans.element_selectors.old_wall_type.text(form_data_map[old_wall_no]);
        box_goods_trans.element_selectors.new_wall_type.text(form_data_map[new_wall_no]);
        $.post("<?php echo U('Purchase/SortingWall/getAllWallBoxByNo');?>", {new_wall_no:new_wall_no}, function(r){
            if(r){
                newData = JSON.parse(r);
                var rows = $('#'+box_goods_trans.params.datagrid.id).datagrid('getRows');
                for(var i = 0; i < rows.length; ++i){
                    var rowIndex = $('#'+box_goods_trans.params.datagrid.id).datagrid('getRowIndex',rows[i]);
                    $('#'+box_goods_trans.params.datagrid.id).datagrid('updateRow',{index:rowIndex,row:{new_box_no:''}});
                }
            }
        });
    }
    box_goods_trans.boxDistribution = function(){
        dict_map_box_no = {};
        var distribution_way = box_goods_trans.element_selectors.distribution_way.combobox('getValue');
        var rows = $('#'+box_goods_trans.params.datagrid.id).datagrid('getRows');
        var updateDate_len = newData.length;
        if(updateDate_len==0){messager.info('该分拣墙没有多余的分拣框可使用，请更换分拣墙');}
        var randomData = [];
        if(distribution_way==2){randomData = newData.concat();randomData.sort(function(){ return 0.5 - Math.random(); });}
        for(var i = 0; i < rows.length; ++i){
            var rowIndex = $('#'+box_goods_trans.params.datagrid.id).datagrid('getRowIndex',rows[i]);
            var updateDate = '';
            switch (distribution_way){
                case '0' :
                    updateDate = newData[i]==undefined?'':newData[i]['box_no'];
                    break;
                case '1' :
                    updateDate = newData[updateDate_len-1]==undefined?'':newData[updateDate_len-1]['box_no'];
                    updateDate_len -= 1;
                    break;
                case '2' :
                    updateDate = randomData[i]==undefined?'':randomData[i]['box_no'];
                    break;
            }
            $('#'+box_goods_trans.params.datagrid.id).datagrid('updateRow',{index:rowIndex,row:{new_box_no:updateDate}});
            dict_map_box_no[updateDate]=true;
        }
    }
    $.extend($.fn.datagrid.methods, {
        editCell: function (jq, param) {
            return jq.each(function () {
                var opts = $(this).datagrid('options');
                var fields = $(this).datagrid('getColumnFields', true).concat($(this).datagrid('getColumnFields'));
                for (var i = 0; i < fields.length; i++) {
                    var col = $(this).datagrid('getColumnOption', fields[i]);
                    col.editor1 = col.editor;
                    if (fields[i] != param.field) {
                        col.editor = null;
                    }
                }
                $(this).datagrid('beginEdit', param.index);
                for (var i = 0; i < fields.length; i++) {
                    var col = $(this).datagrid('getColumnOption', fields[i]);
                    col.editor = col.editor1;
                }
            });
        }
    });
    $.extend($.fn.validatebox.defaults.rules, {
        new_box_no_unique: {
            validator: function (value) {
                if(!(!value)){
                    if(dict_map_box_no[value]){return false;}
                }
                return true;
            },
            message: "分拣框重复，请重新选择!"
        }
    });
    var dict_map_box_no = {};
    var editIndex = undefined;
    //点击单元格触发的事件
    function onClickCell(index, field) {
        if (endEditing()) {
            $('#'+box_goods_trans.params.datagrid.id).datagrid('selectRow', index)
                    .datagrid('editCell', { index: index, field: field });
            editIndex = index;
            if(field == 'new_box_no'){
                var ed = $('#'+box_goods_trans.params.datagrid.id).datagrid('getEditor', {index: index, field: 'new_box_no'});
                $(ed.target).combobox('loadData', newData);
            }
        }
    }
    //判断是否编辑结束
    function endEditing() {
        if (editIndex == undefined) { return true }
        if ($('#'+box_goods_trans.params.datagrid.id).datagrid('validateRow', editIndex)) {
            $('#'+box_goods_trans.params.datagrid.id).datagrid('endEdit', editIndex);
            editIndex = undefined;
            return true;
        } else {
            return false;
        }
    }
    //编辑完单元格之前触发的事件
    function onBeginEdit(index, row, changes) {
        var new_box_no_val = $('#'+box_goods_trans.params.datagrid.id).datagrid('getSelected');
        if(new_box_no_val.new_box_no!=''){dict_map_box_no[new_box_no_val.new_box_no]=false;}
    }
    //编辑完单元格之后触发的事件
    function onAfterEdit(index, row, changes) {
        var new_box_no_val = $('#'+box_goods_trans.params.datagrid.id).datagrid('getSelected');
        if(new_box_no_val.new_box_no!=''){dict_map_box_no[new_box_no_val.new_box_no]=true;}
        $('#'+box_goods_trans.params.datagrid.id).datagrid('endEdit', editIndex);
        $('#'+box_goods_trans.params.datagrid.id).datagrid('refreshRow', index);
        editIndex = undefined;
    }
    setTimeout(function(){
        $('#'+box_goods_trans.params.form.id).form('filterLoad',old_wall_no);
        box_goods_trans.setNewWallType();
        $('#'+box_goods_trans.params.datagrid.id).datagrid('options').onAfterEdit = function(index, row, changes){onAfterEdit(index, row, changes);};
        $('#'+box_goods_trans.params.datagrid.id).datagrid('options').onBeginEdit = function(index, row, changes){onBeginEdit(index, row, changes);};
        $('#'+box_goods_trans.params.datagrid.id).datagrid('options').onClickCell = function(index,field,value){onClickCell(index,field,value)};
        $('#'+box_goods_trans.params.datagrid.id).datagrid('enableCellEditing');
    },0);
});

</script>

<!-- layout-south-tabs, call add_tabs js function to add tabs -->


</body>
</html>