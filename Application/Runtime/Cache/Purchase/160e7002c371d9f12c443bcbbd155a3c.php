<?php if (!defined('THINK_PATH')) exit();?><style>
    .sw-div label{vertical-align:middle;width: 75px;display:inline-block; font-size: 12px;text-align: left;}
</style>
<div>
    <form id="<?php echo ($dialog_list["form"]); ?>" method="post">

        <div class="form-div sw-div" style="margin-top:10px;">
            <label style="padding-left: 25px;">编号：</label><select id="<?php echo ($dialog_list["id"]); ?>wall_no" class="easyui-combobox sel" name="wall_no"   data-options="width:'150px',panelHeight:'150px',editable:false,required:true" disabled="true">
            <?php if(is_array($sorting_wall_no)): $i = 0; $__LIST__ = $sorting_wall_no;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vo); ?>"><?php echo ($vo); ?></option><?php endforeach; endif; else: echo "" ;endif; ?></select>
        </div>
        <div class="form-div sw-div" style="margin-top:10px;">
            <label style="padding-left: 25px;">排数：</label><input id="<?php echo ($dialog_list["id"]); ?>row_num" class="easyui-textbox txt" type="text" name="row_num"  style="width:150px;" data-options="required:true,prompt:'请填写正整数',validType:'ddPrice[1,99]'"  missingMessage="不能为空"/>
        </div>
        <div class="form-div sw-div" style="margin-top:10px;">
            <label style="padding-left: 25px;">列数：</label><input id="<?php echo ($dialog_list["id"]); ?>column_num" class="easyui-textbox txt" type="text" name="column_num"  style="width:150px;" data-options="required:true,prompt:'请填写正整数',validType:'ddPrice[1,99]'"  missingMessage="不能为空"/>
        </div>
        <div class="form-div sw-div" style="margin-top:10px;">
            <label style="padding-left: 25px;">属性：</label><select class="easyui-combobox sel" name="type"   data-options="width:'150px',panelHeight:'150px',editable:false ">
            <?php if(is_array($sorting_wall_type)): $i = 0; $__LIST__ = $sorting_wall_type;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vo["id"]); ?>"><?php echo ($vo["name"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?></select>
        </div>
        <div class="form-div sw-div" style="margin-top:10px;">
            <label style="padding-left: 25px;">停用：</label><select class="easyui-combobox sel" name="is_disabled" data-options="panelHeight:'auto',editable:false, required:true" style="width:50px;">
            <option value="0">否</option>
            <option value="1">是</option>
        </select>
        </div>
        <div class="form-div sw-div" style="margin-top:10px;">
            <a style="margin-left: 5px;" href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-search',plain:false" onclick = "sortingWall.canvasTab()";>点击预览</a>
            <label> ===============》</label>
        </div>
        <div style="margin-left:10px;margin-top:10px;width: 200px;height: 112px;padding: 5px 10px 5px 10px;border:1px solid #bbb;" >
            <!--<label style="color: blue;">说明：<br>-->
                <!--1.<span style="color:red">主</span>分拣墙中<span style="color:red">优先</span>放置<span style="color:red">待分拣</span>商品<br>-->
                <!--2.系统中只能存在<span style="color:red">一</span>个<span style="color:red">主</span>分拣墙，可存在<span style="color:red">多</span>个<span style="color:red">次</span>分拣墙</label>-->
            <label style="color: blue;">说明：<br>
                分拣墙的作用是放置一单多货，由于订单中部分货品在拣货时没有一次性拣全，暂时放拣过的货品的地方。
            </label>
        </div>
    </form>
</div>
<canvas id="<?php echo ($dialog_list["id"]); ?>myCanvas" width="715" height="420" style="margin-left:250px;margin-top: -322px;border:1px solid #bbb;">
    您的浏览器不支持画布功能！
</canvas>
<script>
    (function(){
        var dialog_id = '<?php echo ($dialog_list["id"]); ?>';
        var element_selectors ={
            'wall_no'	            : $('#'+dialog_id + 'wall_no'),
            'row_num'               : $('#'+dialog_id + 'row_num'),
            'column_num'            : $('#'+dialog_id + 'column_num'),
            'myCanvas'              : $('#'+dialog_id + 'myCanvas'),
            'datagrid_rows'         : $('#sortingwall_datagrid'),
        };
        $(function(){
            var sorting_wall_info=JSON.parse('<?php echo ($sorting_wall_info); ?>');
            var dialog_list=JSON.parse('<?php echo ($dialog_list_json); ?>');
            sortingWall.submitEditDialog=sortingWall.submitAddDialog = function(){
                if (!$("#"+dialog_list.form).form('validate')) { return false; }
                var data=$("#"+dialog_list.form).form('get');
                data.id=sorting_wall_info.id;
                if(sorting_wall_info.id!=0){data.wall_no=sorting_wall_info.wall_no;}
                $.post("<?php echo U('Purchase/SortingWall/saveSortingWall');?>",data,function(r){
                    if(r.status==1){
                        messager.alert(r.info);
                        return;
                    }
                    if(r.status==0){
                        if(data.id==0){
                            $("#"+sortingWall.params.add.id).dialog('close');
                        }else{
                            $("#"+sortingWall.params.edit.id).dialog('close');
                        }
                        sortingWall.refresh();
                        return;
                    }
                },'json');
            }
            sortingWall.canvasTab = function () {
                var wall_no = element_selectors.wall_no.textbox('getValue');
                var row_num = element_selectors.row_num.textbox('getValue');
                var column_num = element_selectors.column_num.textbox('getValue');
                var use_xy = '';
                $.post("<?php echo U('Purchase/SortingWall/getHasUseBoxByWallNo');?>",{wall_no:wall_no},function(r){
                    if(r.status==1){
                        messager.alert(r.info);
                        return;
                    }else if(r.status==0){
                        if(sorting_wall_info.id!=0){use_xy = r.info;}
                        sortingWall.canvasDoTab(wall_no,row_num,column_num,use_xy);
                    }
                },'json');
            }
            sortingWall.canvasDoTab = function (wall_no,row,column,use_xy) {
                use_xy = ',' + use_xy + ',';
                var canvas_obj = element_selectors.myCanvas[0];
                var canvas_cxt = canvas_obj.getContext('2d');
                var x, y,coordinate_x,coordinate_y,coordinate_xy,maxW,maxH;
                var w = 60,h = 30;//box长宽
                canvas_obj.width = canvas_obj.width;
                maxW = column*w;
                maxH = row*h;
                if(maxW > 700){
                    canvas_obj.width = maxW + 15;
                }else{
                    canvas_obj.width = 715;
                }
                if(maxH > 405){
                    canvas_obj.height = maxH + 15;
                }else{
                    canvas_obj.height = 420;
                }
                canvas_cxt.lineWidth='1';
                canvas_cxt.strokeStyle='#aaa';
                for(var i=1; i<=column; ++i){
                    for(var j=1; j<=row; ++j){
                        x = 8 + (i - 1) * w;
                        y = 8 + (j - 1) * h;
                        coordinate_x = x + 5;
                        coordinate_y = y + 20;
                        coordinate_xy = ',' + j + '-' + i + ',';
                        //使用中的box填充颜色
                        if(use_xy.indexOf(coordinate_xy)!=-1){
                            canvas_cxt.fillStyle = '#FF9900';
                            canvas_cxt.fillRect(x, y, w, h);
                        }
                        canvas_cxt.fillStyle = '#000';
                        canvas_cxt.rect(x, y, w, h);
                        canvas_cxt.font = '15px Arial';
                        canvas_cxt.fillText(wall_no + '-' + j + '-' + i, coordinate_x, coordinate_y);
                    }
                }
                canvas_cxt.stroke();
            }
            setTimeout(function(){
                if(sorting_wall_info.id==0){
                    element_selectors.wall_no.combobox('enable');
                }else{
                    $("#"+dialog_list.form).form('filterLoad',sorting_wall_info);
                }
            },0);

        });
    })();

</script>