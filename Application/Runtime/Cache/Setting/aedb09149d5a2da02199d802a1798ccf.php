<?php if (!defined('THINK_PATH')) exit();?><style type="text/css">
	.drag-item{list-style-type:none; display:block; padding:5px; border:1px solid #ccc; margin:2px; width:300px; background:#fafafa; color:#444; }
	.drag-item img{cursor:pointer;width:24px;height: 24px;}
	.indicator{position:absolute; font-size:9px; width:10px; height:10px; display:none; color:red; }
</style>
<div id="set-datagrid-fields" style="height:500px;width:350px;">
	<form id="datagrid-fields">
		<ul style="margin:0;padding:0;margin-left:10px;">
			<?php if(is_array($fields)): $k = 0; $__LIST__ = $fields;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$field): $mod = ($k % 2 );++$k;?><li class="drag-item">
					<label><input type="checkbox" name="<?php echo ($field["name"]); ?>" value="<?php echo ($field["value"]); ?>"/><?php echo ($field["text"]); ?></label>
					<label style="float:right; margin-right: 30px;">
		 			<?php if($frozen == 1): if($field["frozen"] == 0): ?><img src="/Public/Image/Icons/unfrozen.png" onclick="fieldFrozen(this,'<?php echo ($field["name"]); ?>')"><?php endif; ?>
						<?php if($field["frozen"] == 1): ?><img src="/Public/Image/Icons/frozen.png" onclick="fieldFrozen(this,'<?php echo ($field["name"]); ?>')"><?php endif; endif; ?> 
					<fr id="<?php echo ($field["name"]); ?>" style="display:none"><?php echo ($field["frozen"]); ?></fr>
					</label>
				</li><?php endforeach; endif; else: echo "" ;endif; ?>
		</ul>
	</form>
	<div class="indicator"><i class="fa fa-share" style="color:#0a8ebb;font-size: 17px;"></i></div>
</div>
<script type="text/javascript">
	//# sourceURL=datagrid.field.js
	$(function(){
		$('#set-datagrid-fields input').each(function(k,v) {if (this.value==1) {this.checked=true; }else{this.checked=false; } });
		$('input',$('#set-datagrid-fields')).bind('click',function(e){
			if (this.checked) {this.value=1;}else{this.value=0;}
			var e = window.event || event;
			if ( e.stopPropagation ){e.stopPropagation(); }else{window.event.cancelBubble = true; }
		});
		var indicator = $('.indicator');
		$('.drag-item').draggable({
			revert:true,
			deltaX:0,
			deltaY:0,
			delay:200,       //设置拖动延迟 mm为单位
		}).droppable({
			onDragOver:function(e,source){
				if($('.panel-header',source).length==1){return;}
				indicator.css({
					display:'block',
					left:$(this).offset().left-$('#flag_set_dialog').dialog('options').left-10,
					top:$(this).offset().top+$(this).outerHeight()-$('#flag_set_dialog').dialog('options').top-5
				});
			},
			onDragLeave:function(e,source){
				indicator.hide();
			},
			onDrop:function(e,source){
				$(source).insertAfter(this);
				indicator.hide();
			}
		});
	});
	function selectAllField(){
		$('#set-datagrid-fields input').each(function(k,v) {this.value=1;this.checked=true;});
	}
	function reverSelect(){
		$('#set-datagrid-fields input').each(function(k,v) {if (this.value==1) {this.value=0;this.checked=false; }else{this.value=1;this.checked=true; } });

	}
	function fieldFrozen(img,name){
		var fr=document.getElementById(name);
		if(fr.innerHTML==1){
			fr.innerHTML=0;
			img.src="/Public/Image/Icons/unfrozen.png";
		}else{
			fr.innerHTML=1;
			img.src="/Public/Image/Icons/frozen.png";
		}
	}
	function submitDatagridField(dialog_id,show_id,code){
		var data={};
		var show={};code=code.replace('/','_').toLowerCase();
		var frozen={};//存放是否固定列
		var check='<?php echo ($check); ?>';//是否显示checkbox
		if(check){frozen['ck']='1-1';}
		$('#set-datagrid-fields input').each(function(k,v) {show[this.name]=this.value;});
		$('#set-datagrid-fields fr').each(
				function(k,v) {
					this.innerHTML==1?frozen[this.id]=show[this.id]+'-1':data[this.id]=show[this.id]+'-0';
				}
		);
		data=$.extend(frozen,data);
		$.post("<?php echo U('Setting/DatagridField/setField');?>", {code:code,fields:data}, function(res){
			//var add_check=['history_original_trade_list_datagrid','history_sales_trade_datagrid'];
			if (!res.status) {messager.info(res.info);return;}
			var opts=$('#'+show_id).datagrid('options');
			if(opts.frozenColumns[0]!=undefined){var all=opts.columns[0].concat(opts.frozenColumns[0]);}else{var all=opts.columns[0];}
			var all_col=[];
			for(var i in all){
				all_col[all[i]['field']]=all[i];
			}
			var columns=[];
			var frozens=[];
			var col=[]
			for(k in data){
				col=data[k].split('-');
				all_col[k]['hidden']=(col[0]==1?0:1);
				if(col[1]==1&&all_col[k]){
					frozens.push(all_col[k]);
				}else{
					columns.push(all_col[k]);
				}
			}
			// for (var i= 0; i < add_check.length;i++) {
			// 	if(add_check[i]==show_id){
			// 		var checkbox={'field': 'ck','checkbox':true};
			// 		columns.unshift(checkbox);
			// 	}
			// }
			opts.frozenColumns[0]=frozens;
			opts.columns[0]=columns;
			$('#'+dialog_id).dialog('close');
			$('#'+show_id).datagrid(opts);
		});
	}
</script>