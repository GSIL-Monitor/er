//# sourceURL=thin.datagrid.js
function ThinDatagrid(selector, editFun,endEditFlag,params)
{
	this.selector = selector;
	this.editIndex = undefined;
	this.tipInfo='';
	this.enableEdit=true;
	this.params = (params == undefined)?{}:params;
	if ("undefined" != typeof(editFun)){
		this.editFun = editFun;
	}else{
		this.editFun = function(index){};
	}
	this.endEditFlag= (endEditFlag==undefined)?true:false;//是否单行插入数据库
	$(selector).datagrid('options').that = this;
	$(selector).datagrid('options').onClickRow = this.clickRow;
	this.endEdit = function(checkSubmmit){
		if ("undefined" == typeof(this.editIndex)){
			return true;
		}
		if ($(this.selector).datagrid('validateRow', this.editIndex)){
			$(this.selector).datagrid('endEdit', this.editIndex);
			if (this.endEditFlag&&checkSubmmit && !$.isEmptyObject($(this.selector).datagrid('getChanges'))){
				this.extendAlertHandler('不能同时编辑多行!');
				$(this.selector).datagrid('selectRow', this.editIndex).datagrid('beginEdit', this.editIndex);
				return false;
			}else{
				this.editIndex=undefined;
				return true;
			}
		}else{
			this.extendAlertHandler('请将数据填写完整'+this.tipInfo+'!');
			$(this.selector).datagrid('selectRow', this.editIndex);
			return false;
		}
		
	};
}

ThinDatagrid.prototype = {
clickRow:function(index,row){
	var that = $(this).datagrid('options').that;
	if (that.enableEdit && that.editIndex != index && that.endEdit(true)){
		that.editIndex = index;
		$(this).datagrid('selectRow', index).datagrid('beginEdit', index);
		that.editFun(index,row);
	}
},
edit:function(){
	if (!this.endEdit(true)){
		$(this.selector).datagrid('selectRow', this.editIndex).datagrid('beginEdit', index);
		that.editFun(index);
	}
},
append:function(def){
	if (this.endEdit(true)){
		if(typeof(def)!='object'){def={};}
		$(this.selector).datagrid('appendRow',def);
		this.editIndex = $(this.selector).datagrid('getRows').length-1;
		$(this.selector).datagrid('selectRow', this.editIndex).datagrid('beginEdit', this.editIndex);
	}
},
remove:function(){
	if (this.editIndex == undefined){return;}
	$(this.selector).datagrid('cancelEdit', this.editIndex) .datagrid('deleteRow', this.editIndex);
	this.editIndex = undefined;
},
reject:function(){
	$(this.selector).datagrid('rejectChanges');
	this.editIndex = undefined;
},
/*
accept:function(submit, pagination){
	if (!this.endEdit(false)){
		var data = $(this.selector).datagrid("getChanges");
		$(this.selector).datagrid('acceptChanges');
		if (data.length != 0){
			if(!submit(data)){
				$(this).datagrid('selectRow', this.editIndex).datagrid('beginEdit', this.editIndex);
			}else if (pagination){
				$(this.selector).datagrid('reload');
				this.editIndex = undefined;
			}else{
				this.editIndex = undefined;
			}
		}
	}
},
*/
accept:function(submit){
	if (this.endEdit(false)){
		var data = $(this.selector).datagrid("getChanges");
		force = arguments[1] ? arguments[1] : false;
		$(this.selector).datagrid('acceptChanges');
		if (data.length != 0 || force){
			submit(data, this);		
		}
	}
},
extendAlertHandler:function(Info){
	var that = this;
	if(that.params.print != undefined){
		if( that.params.print.type != undefined && that.params.print.type == 'print_template'){
			$('#'+that.params.print.object_id).hide();
			
			messager.alert(Info,'',function(){
				$('#'+that.params.print.object_id).show();
			});
			setTimeout(function(){
				$('[class="panel window messager-window"] a[class="panel-tool-close"]').bind('click',function(){
					$('#'+that.params.print.object_id).show();
				});
				$('[class="panel window messager-window"]').bind('keydown',function(evnet){
					   if (event.keyCode == 27) {
						   $('#'+that.params.print.object_id).show();
					   }
				});
			},0);
		}
	}else{
		messager.alert(Info);
	}
}
};
