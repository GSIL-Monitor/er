//# sourceURL=cfgOperReason.js
function CfgOperReason(params){
    var that = this;
    this.that = this;
    this.params =  params;
    this.now_class_id = params.class_id;
}
CfgOperReason.prototype = {
    cfgOperReasonInitShow: function(){
        var that = this;
        var display_class_id = parseInt(that.params.class_id);
        var display_index = undefined;
        var now_rows = $('#'+that.params.datalist.id).datalist('getRows');
        for(var r = 0;r<now_rows.length;r++)
        {
            if(parseInt(now_rows[r].class_id) == display_class_id )
            {
                display_index = r;
            }
            
        }
         $('#'+that.params.datalist.id).datalist('selectRow',display_index);
        if(that.params.is_dialog == true)
        {
            $("#"+that.params.datalist.id).datagrid('options').onClickRow = function(index,row){that.shieldClick(index,row);};
        }
        else
        {
             $("#"+that.params.datalist.id).datagrid('options').onClickRow = function(index,row){that.normalClick(index,row);};
        }
        
    },
    shieldClick: function(index,row){
        var that = this;
        var sel_class_id = parseInt(row.id);
        var display_class_id = parseInt(that.params.class_id);
        var display_index = undefined;
        var now_rows = $('#'+that.params.datalist.id).datalist('getRows');
        for(var r = 0;r<now_rows.length;r++)
        {
            if(parseInt(now_rows[r].class_id) == display_class_id )
            {
                display_index = r;
            }
            
        }
        if( sel_class_id != display_class_id)
        {
            
            $('#'+that.params.datalist.id).datalist('selectRow',display_index);
        }
    },
    normalClick: function(index,row){
        var that = this;
        var select_row = $('#'+that.params.datalist.id).datalist('getSelected');
        that.params.class_id = select_row.class_id;
        $.post(that.params.datagrid.refresh_url,{class_id:select_row.class_id,is_dialog:false},function(data){
            $('#'+that.params.datagrid.id).datagrid('loadData',data);
            $("#cor_show_disabled").val('0');
            $("#cor_but_disabled").linkbutton({text:'显示停用'});
        },'json');
    },
    submitCfgOperReasonsDialog : function(parent_win_params){
            if(!(this instanceof ThinDatagrid))
            {
                return;
            }
            var that = this;
            var left = this.cfgOperReasonLeft;
            if(!that.endEdit(true)){return;}
            var selector = $('#'+left.params.datagrid.id);
            var data={};
            data['add']=selector.datagrid('getChanges','inserted');
            data['update']=selector.datagrid('getChanges','updated');
            var rows=selector.datagrid('getRows');
            var len=(data['add'].length>=data['update'].length?data['add'].length:data['update'].length);
            if(typeof parent_win_params != "undefined")
                if(rows.length==0){ $('#'+parent_win_params.dialog.id).dialog('close');return;}
            for(var i=0;i<rows.length;i++){
                for(var j=0;j<len;j++){
                    if(j<data['add'].length&&data['add'][j].title==rows[i].title&&data['add'][j].id!=rows[i].id){
                        messager.alert('原因名称重名-'+rows[i].title);return;
                    }
                    if(j<data['update'].length&&data['update'][j].title==rows[i].title&&data['update'][j].id!=rows[i].id){
                        messager.alert('原因名称重名-'+rows[i].title);return;
                    }
                }
            }
            $.post(left.params.datagrid.url+"?class_id="+left.params.class_id,data,function(res){
                if(!res.status){
                    messager.alert(res.info);
                }else if(typeof parent_win_params != "undefined" )
                {
                    $('#'+parent_win_params.dialog.id).dialog('close');
                    if(res.info == 'null')
                    {
                        $('#'+parent_win_params.dialog.id).dialog('close');
                        return;
                    }
                    //var res_ids=JSON.parse(res.info);
                    var res_ids=res.info;
                    if( left.params.is_dialog == true)
                    {
                        $('#'+parent_win_params.id_list.reason_list).combobox("loadData",res_ids);
                    }

                }
                if( left.params.is_dialog == false)
                {
                    selector.datagrid({url:left.params.datagrid.refresh_url+"?class_id="+left.params.class_id});
                }
                $("#cor_show_disabled").val('0');
                $("#cor_but_disabled").linkbutton({text:'显示停用'});
            },'json');

            
    },
       
};
