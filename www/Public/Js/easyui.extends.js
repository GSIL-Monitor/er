//datagrid--扩展
$.extend($.fn.datagrid.methods, {
    /**
     * 开打提示功能
     * @param {} jq
     * @param {} params 提示消息框的样式
     * @return {}
     */
    doCellTip : function(jq, params) {
        function showTip(data, td, e) {
            if ($(td).text() == "" || $(td).html().search('<img') != -1||$(td).html().search('<span')!=-1) return;
            data.tooltip.text($(td).text()).css({
                        top : (e.pageY + 15) + 'px',
                        left : (e.pageX + 20) + 'px',
                        'z-index' : $.fn.window.defaults.zIndex,
                        display : 'block'
            });
        };
        function unSelectTd(td,e){
            //单元格失去焦点
            var text=$('input',$(td)).val().html_encode();
            var style=$('input',$(td)).attr('style');
            if(!text||!style||style=='display: none;') return;
            style=style.substring(0,style.indexOf('border'));
            var html='<div style="'+style+'" class="'+$('input',$(td)).attr('class')+'">'+text+'</div>';
            $(td).html(html);
        };
        return jq.each(function() {
            var grid = $(this);
            var options = $(this).data('datagrid'); //获取 datagrid 数据
            if (!options.tooltip) {
                var panel = grid.datagrid('getPanel').panel('panel');
                var defaultCls = {
                    'border' : '1px solid #333',
                    'padding' : '1px',
                    'color' : '#333',
                    'background' : '#f7f5d1',
                    'position' : 'absolute',
                    'max-width' : '300px',
                    'font-size' : '12px',//字体保持和datagrid中一致
                    'border-radius' : '1px',
                    '-moz-border-radius' : '1px',
                    '-webkit-border-radius' : '1px',
                    'display' : 'none'
                }
                var tooltip = $("<div id='celltip'></div>").appendTo('body');
                tooltip.css($.extend({}, defaultCls, params.cls));
                options.tooltip = tooltip;
                panel.find('.datagrid-body').each(function() {
                    var delegateEle = $(this).find('> div.datagrid-body-inner').length ? $(this).find('> div.datagrid-body-inner')[0] : this;
                    $(delegateEle).undelegate('td', 'mouseover').undelegate(
                            'td', 'mouseout').undelegate('td', 'mousemove').undelegate('td','blur')
                            .delegate('td', {
                                'mouseover' : function(e) {
                                    if (params.delay) {
                                        if (options.tipDelayTime)
                                            clearTimeout(options.tipDelayTime);
                                        var that = this;
                                        options.tipDelayTime = setTimeout(
                                                function() {
                                                    showTip(options, that, e);
                                                }, params.delay);
                                    } else {
                                        showTip(options, this, e);
                                    }
                                },
                                'mouseout' : function(e) {
                                    if (options.tipDelayTime)
                                        clearTimeout(options.tipDelayTime);
                                      options.tooltip.css({ 'display' : 'none' });
                                },
                                'mousemove' : function(e) {
                                    var that = this;
                                    if (options.tipDelayTime) {
                                        clearTimeout(options.tipDelayTime);
                                        options.tipDelayTime = setTimeout(
                                                function() {
                                                    showTip(options, that, e);
                                                }, params.delay);
                                    } else {
                                        showTip(options, that, e);
                                    }
                                },
                                'blur' :function(e){
                                    unSelectTd(this,e)
                                }
                            });
                });
            }
        });
    },
    /**
     * 关闭消息提示功能
     * @param {} jq
     * @return {}
     */
    cancelCellTip : function(jq) {
        return jq.each(function() {
                    var data = $(this).data('datagrid');
                    if (data.tooltip) {
                        data.tooltip.remove();
                        data.tooltip = null;
                        var panel = $(this).datagrid('getPanel').panel('panel');
                        panel.find('.datagrid-body').undelegate('td',
                                'mouseover').undelegate('td', 'mouseout')
                                .undelegate('td', 'mousemove')
                    }
                    if (data.tipDelayTime) {
                        clearTimeout(data.tipDelayTime);
                        data.tipDelayTime = null;
                    }
                });
    },
    /**
     * 设置单元格复制
     * *@param {} jq
     * @return {}
     */
    copyCell: function(jq){
        function selectTd(td){
            //单击  获取 datagrid 每个单元格
          if($(td).html().indexOf("checkbox")<0&&$(td).html().indexOf("http://www.taobao.com/webww")<0&&$(td).html().indexOf('javascript:void(0)')<0&&$(td).html().indexOf('<img')<0&&$(td).html().indexOf("span")<0){
            if($(td).html().indexOf('<div')==-1){$('input',$(td)).selectRange(0,$('input',$(td)).val().length); return; }
            var text=$(td).text();
            var html='<input style="'+$('div',$(td)).attr('style')+'border:0px;background-color:transparent;" class="'+$('div',$(td)).attr('class')+'" tabindex="2" readOnly="true" value="'+text+'"/>';
            $(td).html(html);
            $('input',$(td)).selectRange(0,text.length);
          }
        };
        function unSelectTd(td){
            //单元格失去焦点
            var text=$('input',$(td)).val();
            var style=$('input',$(td)).attr('style');
            if(!text||!style) return;
            style=style.substring(0,style.indexOf('border'));
            var html='<div style="'+style+'" class="'+$('input',$(td)).attr('class')+'">'+text+'</div>';
            $(td).html(html);
        };
        return jq.each(function(){
              var dg = $(this);
              var opts = dg.datagrid('options');
              opts.oldOnClickCell = opts.onClickCell;
              opts.onClickCell = function(index, field){
              var panel = dg.datagrid('getPanel').panel('panel');
              var gridBody=panel.find('.datagrid-view2 > div.datagrid-body');
              index+=1;
			  if(opts.that){opts.that.selectField=field;}
              var td=$('tbody tr:nth-child('+index+') > td[field="'+field+'"]',$(gridBody));
              var clazz=$('div',$(td)).attr('class');
			  //获取冻结列的单元格信息
              if ($(td).html()==undefined) {
                var gridBody=panel.find('.datagrid-view1 > div.datagrid-body');
                if (opts.that) {opts.that.selectField=field;}
                var td = $('tbody tr:nth-child('+index+') > td[field="'+field+'"]',$(gridBody));
                var clazz=$('div',$(td)).attr('class');
              }
              if(!clazz||clazz.indexOf('datagrid-editable')==-1){ selectTd(td); }
              //$(td).on('blur',function(){console.log('ddd'); unSelectTd(td); });
              opts.oldOnClickCell.call(this, index-1, field);
            }
        });
    },
    /**
     * 单元格编辑
     * *@param {} jq  param
     * @return {}
     */
    editCell: function(jq,param){
  		return jq.each(function(){
          var opts = $(this).datagrid('options');
          var fields = $(this).datagrid('getColumnFields',true).concat($(this).datagrid('getColumnFields'));
          for(var i=0; i<fields.length; i++){
              var col = $(this).datagrid('getColumnOption', fields[i]);
              col.editor1 = col.editor;
              if (fields[i] != param.field){
                col.editor = null;
              }
          }
          $(this).datagrid('beginEdit', param.index);
          var ed = $(this).datagrid('getEditor', param);
          var dg = $(this);
          if (ed){
              if ($(ed.target).hasClass('textbox-f')){
                  if (!param.is_blur) {$(ed.target).textbox('textbox').focus();}
                  else{
                      if($(ed.target).textbox('textbox').siblings('a'))
                      {
                          $(ed.target).textbox('textbox').siblings('a').mousedown(function(){$(ed.target).textbox('shieldFocus');});
                      }
                      $(ed.target).textbox('textbox').focus().bind('blur',function(){
                          if($(ed.target).textbox('options').more_button != undefined && $(ed.target).textbox('options').more_button == true){
                              return false;
                          }
                        setTimeout(function(){
                          if(dg.datagrid('validateRow', param.index)){ dg.datagrid('endEdit', param.index);dg.datagrid('unselectRow',  param.index); param.index=undefined;}
                          else{ return false; }
                        },0);
                      });
                  }
              } else {
                 if (!param.is_blur) {$(ed.target).focus();}
                 else{
                      $(ed.target).focus().bind('blur',function(){
                        setTimeout(function(){
                          if(dg.datagrid('validateRow', param.index)){ dg.datagrid('endEdit', param.index);dg.datagrid('unselectRow',  param.index); param.index=undefined;}
                          else{ return false; }
                        },0);
                      });
                 }
              }
          }
          for(var i=0; i<fields.length; i++){
              var col = $(this).datagrid('getColumnOption', fields[i]);
              col.editor = col.editor1;
          }
      });
  	},
	 /**
     * 设置可编辑单元格
     * *@param {} jq
     * @return {}
     */
    enableCellEditing: function(jq,param){
        return jq.each(function(){
            var dg = $(this);
            param = !param ? {is_blur:true} : (typeof(param)=='object'?param:{is_blur:param});
            var opts = dg.datagrid('options');
            opts.oldOnClickCell = opts.onClickCell;
            opts.onClickCell = function(index, field){
                if (opts.editIndex != undefined){
                    if (dg.datagrid('validateRow', opts.editIndex)){
                        dg.datagrid('endEdit', opts.editIndex);
                        opts.editIndex = undefined;
                    } else {
                        return;
                    }
                }
                param.index=index; param.field=field;
                dg.datagrid('selectRow', index).datagrid('editCell',param);
                // dg.datagrid('selectRow', index).datagrid('editCell', {
                //     index: index,
                //     field: field,
                // });
                // index=undefined;
                opts.editIndex = index;
                opts.oldOnClickCell.call(this, index, field);
            }
        });
    }
});
//验证拓展--easyui
$.extend($.fn.validatebox.defaults.rules, {
	  chinese: {
        validator: function (value, param) {
          return /^[\u0391-\uFFE5]+$/.test(value);
        },
        message: '请输入汉字'
    },
    chineseOrEngOrNum: {
        validator: function (value, param) {
            return /^[\u0391-\uFFE5a-zA-Z0-9]+$/.test(value);
        },
        message: '请输入汉字,数字或英文'
    },
    english : {// 验证英语
        validator : function(value) {
            return /^[A-Za-z]+$/i.test(value);
        },
        message : '请输入英文'
    },
    ip : {// 验证IP地址
        validator : function(value) {
            return /\d+\.\d+\.\d+\.\d+/.test(value);
        },
        message : 'IP地址格式不正确'
    },
    datetime: {
        validator: function (value, param) {
          return (/^(1|2\d{3}-((0[1-9])|(1[0-2]))-((0[1-9])|([1-2][0-9])|(3([0|1]))))( (\d{2}):(\d{2}):(\d{2}))?$/).test(value);
        },
        message: '时间格式不正确yy-mm-dd'
    },
    zip: {
        validator: function (value, param) {
          return /^[0-9]\d{5}$/.test(value);
        },
        message: '邮政编码不存在'
    },
    QQ: {
        validator: function (value, param) {
          return /^[1-9]\d{4,10}$/.test(value);
        },
        message: 'QQ号码不正确'
    },
    mobile: {
        validator: function (value, param) {
          return /^(?:13\d|15\d|18\d|17\d|14\d|19\d|16\d)-?\d{5}(\d{3}|\*{3})$/.test(value);
        },
        message: '手机号码不正确'
    },
    telno:{
        validator:function(value,param){
          return /^(\d{3}-|\d{4}-)?(\d{8}|\d{7})?(-\d{1,6})?$/.test(value);
        },
        message:'电话号码不正确'
    },
    mobileAndTel: {
        validator: function (value, param) {
          return /(^([0\+]\d{2,3})\d{3,4}\-\d{3,8}$)|(^([0\+]\d{2,3})\d{3,4}\d{3,8}$)|(^([0\+]\d{2,3}){0,1}13\d{9}$)|(^\d{3,4}\d{3,8}$)|(^\d{3,4}\-\d{3,8}$)/.test(value);
        },
        message: '请正确输入电话号码'
    },
    number: {
        validator: function (value, param) {
          return /^[0-9]+.?[0-9]*$/.test(value);
        },
        message: '请输入数字'
    },
    money:{
        validator: function (value, param) {
         	return (/^(([1-9]\d*)|\d)(\.\d{1,2})?$/).test(value);
        },
        message:'请输入正确的金额'

    },
    mone:{
        validator: function (value, param) {
       	  return (/^(([1-9]\d*)|\d)(\.\d{1,2})?$/).test(value);
        },
        message:'请输入整数或小数'

    },
    integer:{
        validator:function(value,param){
          return /^[+]?[1-9]\d*$/.test(value);
        },
        message: '请输入最小为1的整数'
    },
    integ:{
        validator:function(value,param){
          return /^[+]?[0-9]\d*$/.test(value);
        },
        message: '请输入大于等于0的整数'
    },
    range:{
        validator:function(value,param){
          if(/^[1-9]\d*$/.test(value)){
            return value >= param[0] && value <= param[1]
          }else{
            return false;
          }
        },
        message:'输入的数字在{0}到{1}之间'
    },
    minLength:{
        validator:function(value,param){
          return value.length >=param[0]
        },
        message:'至少输入{0}个字'
    },
    maxLength:{
        validator:function(value,param){
          return value.length<=param[0]
        },
        message:'最多{0}个字'
    },
    //select即选择框的验证
    selectValid:{
        validator:function(value,param){
          if(value == param[0]){
            return false;
          }else{
            return true ;
          }
        },
        message:'请选择'
    },
    idCode:{
        validator:function(value,param){
          return /(^\d{15}$)|(^\d{18}$)|(^\d{17}(\d|X|x)$)/.test(value);
        },
        message: '请输入正确的身份证号'
    },
    loginName: {
        validator: function (value, param) {
          return /^[\u0391-\uFFE5\w]+$/.test(value);
        },
        message: '只允许汉字、英文字母、数字及下划线。'
    },
    equalTo: {
        validator: function (value, param) {
          return value == $(param[0]).val();
        },
        message: '两次输入的字符不一致'
    },
    englishOrNum : {
        validator : function(value) {
            return /^[a-zA-Z0-9_]{1,}$/.test(value);
        },
        message : '请输入英文、数字、下划线'
      },
    EnglishOrNum : {
        validator : function(value) {
            return /^[a-zA-Z0-9]{1,}$/.test(value);
        },
        message : '请输入英文、数字'
    },
    xiaoshu:{
        validator : function(value){
        return /^(([1-9]+)|([0-9]+\.[0-9]{1,4}))$/.test(value);
        },
        message : '最多保留四位小数！'
  	},
    ddPrice:{
        validator:function(value,param){
          if(/^[1-9]\d*$/.test(value)){
            return value >= param[0] && value <= param[1];
          }else{
            return false;
          }
        },
        message:'请输入1到100之间正整数'
    },
	 ddNumber:{
        validator:function(value,param){
          if(/^[1-9]\d*$/.test(value)){
            return value >= param[0] && value <= param[1];
          }else{
            return false;
          }
        },
        message:'请输入1到1000之间正整数'
    },
    jretailUpperLimit:{
        validator:function(value,param){
          if(/^[0-9]+([.]{1}[0-9]{1,4})?$/.test(value)){
            return parseFloat(value) > parseFloat(param[0]) && parseFloat(value) <= parseFloat(param[1]);
          }else{
            return false;
          }
        },
        message:'请输入0到100之间的最多四位小数的数字'
    },
    rateCheck:{
        validator:function(value,param){
          if(/^[0-9]+([.]{1}[0-9]{1,4})?$/.test(value)){
            return parseFloat(value) > parseFloat(param[0]) && parseFloat(value) <= parseFloat(param[1]);
          }else{
            return false;
          }
        },
        message:'请输入0到1000之间的最多四位小数的数字'
    },
    username : {// 验证用户名
        validator : function(value) {
            return /^[a-zA-Z][a-zA-Z0-9_]{5,15}$/i.test(value);
        },
        message : '用户名不合法（字母开头，允许6-16字节，允许字母数字下划线）'
    },
    email: {// 验证邮箱
        validator: function (value) {
            return /^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/i.test(value);
        },
        message: '邮箱地址不正确'
    },
    englishOrChineseOrchar:{
    	  validator: function (value) {
            return /[\u4e00-\u9fa5_a-zA-Z0-9_-]/.test(value);
        },
        message: '请输入中文或英文字母包括符号(-_)'
    },
    password:{
      	validator: function (value) {
      		var strongRegex = new RegExp("^(?=.{8,})(?=.*[A-Z])(?=.*[a-z])(?=.*[0-9]).*$", "g");
				if(false == strongRegex.test(value)){
    				return false;
    			}else{return true;}
    		},
        message: '您的密码强度较弱，请修改弱密码（密码需要包含大小写字母、数字,而且长度不小于8位）'
    },
    specialCharacter:{
        validator: function (value) {
            return /[`~!@#\$%\^\&\*\(\)\+<>\?:"\{\},\.\\\/;'\[\]]/im.test(value) == true?false:true;
        },
        message: '请不要包括特殊字符,如:\\,/,[,],%,`,~,<,>等'
    },
    specialExceptBracket:{
        validator: function (value) {
            return /[`~!@#\$%\^\&\*\+<>\?:"\{\},\.\\\/;'\[\]]/im.test(value) == true?false:true;
        },
        message: '请不要包括特殊字符,如:\\,/,[,],%,`,~,<,>等'
    },
    check_merchant_no:{
        validator: function (value) {
            return /[!@#\$%\&\*:"\\/']/im.test(value) == true?false:true;
        },
        message: '请不要包括特殊字符,如:/ \\ \' " : & % * $ # @ !'
    }
});

//form表单扩展
$.extend($.fn.form.methods, {
  get:function(jq,params){
    var form={};
    $.each(jq.serializeArray(), function() { form[this['name']] = $.trim(this['value']); });
    $.each(jq.form('options').queryParams,function(key,val){ form[key]=$.trim(val); });
   if(!(!params)){var combox={}; $.each(params,function(key,val){ combox = jq.find('select[textboxname="'+key+'"]').length==1?jq.find('select[textboxname="'+key+'"]'):jq.find('input[textboxname="'+key+'"]'); form[key] = val?combox.combo('getValues').join(','):combox.combo('getValues'); }); }
    return form;
  },
  filterLoad:function(jq,params){
    $.each(params,function(key,val){
        if(typeof val == 'string'){params[key]=val.html_decode(); }
    });
    jq.form('load',params);
    $(jq.selector+' .sel-disabled').showComboboxDisabled();
  }
});

//dialog--扩展
$.extend($.fn.dialog.defaults, {
	onMove:function(left, top){
		var parentObj = $(this).panel('panel').parent();
		var width = $(this).panel('options').width;
	    var height = $(this).panel('options').height;
	    var parentWidth = parentObj.width();
	    var parentHeight = parentObj.height();
	    if(width>parentWidth||height>parentHeight){
	    	return;
	    }
	    if (left<0||left>parentWidth-width) {
	        $(this).window('move', { left:parseInt((parentWidth-width)/2),top:top });
	    }
	    if (top<0||top>parentHeight-height) {
	        $(this).window('move', { left:left,top:parseInt((parentHeight-height)/2) });
	    }
	}
});
//tab--扩展
$.extend($.fn.tabs.methods, {
    //显示遮罩
    loading: function (jq, msg) {
        return jq.each(function () {
            var panel = $(this).tabs("getSelected");
            if (msg == undefined) {
                msg = "正在加载数据，请稍候...";
            }
            $("<div class=\"datagrid-mask\"></div>").css({ display: "block", width: panel.width(), height: panel.height() }).appendTo(panel);
            $("<div class=\"datagrid-mask-msg\"></div>").html(msg).appendTo(panel).css({ display: "block", left: (panel.width() - $("div.datagrid-mask-msg", panel).outerWidth()) / 2, top: (panel.height() - $("div.datagrid-mask-msg", panel).outerHeight()) / 2 });
        });
    },
    //隐藏遮罩
    loaded: function (jq) {
        return jq.each(function () {
            var panel = $(this).tabs("getSelected");
            panel.find("div.datagrid-mask-msg").remove();
            panel.find("div.datagrid-mask").remove();
        });
    }
});
//textbox--扩展
$.extend($.fn.textbox.methods, {
    //屏蔽失去焦点
    shieldFocus: function (jq) {
        $(jq).textbox('options').more_button = true;
    },
    //撤销屏蔽焦点
    releaseFocus: function (jq) {
        $(jq).textbox('textbox').focus();
        $(jq).textbox('options').more_button = false;
    }
});