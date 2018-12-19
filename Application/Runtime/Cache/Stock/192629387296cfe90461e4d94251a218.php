<?php if (!defined('THINK_PATH')) exit();?><form id="<?php echo ($id_list["form"]); ?>" class="form-div">
    <div class="form-div">
        <label><input type="checkbox" id="intelligence_return_multiple_way_prompt" name="intelligence_return_multiple_way_prompt" onclick="checkboxOnclick(this)" />提示方式为"提示区提示+弹窗提示"(不勾选则只有弹窗提示)</label></br>
        <label><input type="checkbox" id="intelligence_return_font_match_prompt" name="intelligence_return_font_match_prompt" onclick="checkboxOnclick(this)" />当订单的客服备注中有"换、邮"字样时进行提示</label></br>
        <label style="margin-left: 5px;">打印方式：</label><input class="easyui-combobox txt" editable="false" text="txt" id="intelligence_return_print_way" name="intelligence_return_print_way" data-options="panelHeight:'auto',valueField:'id',textField:'name',data:[{'id':'0','name':'立即打印'},{'id':'1','name':'手动打印'}],value:'0'"></br>
        <label style="margin-left: 5px;">默认搜索：</label><input class="easyui-combobox txt" editable="false" text="txt" id="intelligence_return_search_way" name="intelligence_return_search_way" data-options="panelHeight:'auto',valueField:'id',textField:'name',data:[{'id':'0','name':'物流单号'},{'id':'1','name':'发出物流单号'},{'id':'2','name':'手机号'},{'id':'3','name':'客户网名'},{'id':'4','name':'原始单号'},{'id':'5','name':'条码'}],value:'0'"></br>
    </div>
</form>
<script>
    //# sourceURL=return_stock_in_setting.js
    $(function(){
        setTimeout(function(){
            var setting = JSON.parse('<?php echo ($setting); ?>');
            $('#intelligence_return_setting_stockinorder_form input').each(function(){
                var that = this;
                var name = that.name;
                if (that.type == "checkbox")
                {
                    if (typeof(setting) != "undefined" && (setting[name] == 1))
                    {
                        that.value = 1;
                        that.indeterminate = false;
                        that.checked = true;
                    }
                    else if(typeof(setting) != "undefined" && (setting[name] == '3'))
                    {
                        that.value = 'all';
                        that.indeterminate = true;
                    }
                    else
                    {
                        that.value = 0;
                        that.indeterminate = false;
                        that.checked = false;
                    }
                }
                else if(name == 'intelligence_return_print_way' || name == 'intelligence_return_search_way')
                {
                    setting[name]==undefined?setting[name]=0:setting[name]=setting[name];
                    //$('#intelligence_return_setting_stockinorder_form :input[name="'+name+'"]').combobox('setValue', setting[name]);
                    $('#'+name).combobox('setValue', setting[name]);
                    //that.value = setting[name];
                }
            });
        },0);
    });
    function checkboxOnclick(that) {
        var value = that.value;
        if (value == 0) {
            that.checked = true;
            that.value = 1;
        } else {
            that.checked = false;
            that.value = 0;
        }
    }
</script>