(function($) {
    $(document).on("click", ".sort",
        function() {
            $(".sort").each(function() {
                $(this).css("borderBottomColor", "#97a8be");
                $(this).css("borderTopColor", "#97a8be")
            });
            $(this).css("borderBottomColor", "#48576a");
            $(this).css("borderTopColor", "#48576a")
        });
    $.fn.serializeJson = function() {
        var serializeObj = {};
        var array = this.serializeArray();
        var str = this.serialize();
        $(array).each(function() {
            if (serializeObj[this.name]) {
                if ($.isArray(serializeObj[this.name])) {
                    serializeObj[this.name].push(this.value)
                } else {
                    serializeObj[this.name] = [serializeObj[this.name], this.value]
                }
            } else {
                serializeObj[this.name] = this.value
            }
        });
        return serializeObj
    } ;

    $.extend({
        delLi:function (relid) {
            $(document).find('.flexlayer li').each(function(){
                if($(this).attr('relid') == relid){
                    $(this).remove();
                }
            })
        },
        addHtml:function (relid,sertId,tabname,btname,outs) {
            var outs = outs || '';
            if (outs) {
                var rulehtml = $('<li relid="'+relid+'" id="'+sertId+'"><div class="inptxt nm">'+tabname+'<b class="type ws">'+btname+'</b>'+'</div><div class="inpblock"><div class="getlist">'+outs+'</div></div></li>');
            } else {
                var rulehtml = $('<li relid="'+relid+'" id="'+sertId+'"><div class="inptxt nm">'+tabname+'<b class="type ws">'+btname+'</b>'+'</div></li>');
            }
            $('#OrderRules .ruleSection .flexlayer').append(rulehtml);
        },
        in_array : function (search,array) {
            for(var i in array){
                if(array[i]==search){
                    return true;
                }
            }
            return false;
        }
    });
})(jQuery);

//layui加载
layui.config({ base: '../../layui/lay/modules/'}).extend({ formSelects: 'formSelects-v3'});
layui.use(['layer','form','element','laydate','table','formSelects'], function() {
    var layer = layui.layer, form = layui.form, laydate = layui.laydate, table = layui.table, element = layui.element,
        formSelects = layui.formSelects, laypage = layui.laypage;

    // time
    lay('.time-item').each(function () {
        laydate.render({
            elem: this
            , trigger: 'click'
        });
    });

    form.on('radio(radio)', function (data) {
      $(this).parents('.scope').find('input[type="number"]').removeClass('layui-disabled').removeAttr('readonly');
      $(this).parents('.scope').siblings().find('input[type="number"]').addClass('layui-disabled').attr('readonly','readonly');
        form.render()
    });

        //表单提交
    form.on('submit(formSubmit)', function(data){
        var params=data.field;
        var param_status = true;
        var sed_ware = $('.writelist .logis_settle .col span');
        var ids='';
        if(sed_ware.size()>0){
            for (var i=0;i<sed_ware.size();i++) {
                ids += sed_ware.eq(i).data('id') + ',';
            }
            ids = ids.substr(0,ids.length - 1);
            params['logistic_ids'] = ids;
        }else{
            layer.msg('请选择物流', {icon: 5});
            return false;
        }
        $('#OrderRules .ruleSection .flexlayer').find('li').each( function (){
            var relid = $(this).attr('relid');
            var hidden = $(this).find('input:hidden');
            if (hidden.length == 0) {
                param_status = true;
                return false;
            }
            params [relid] = $(this).html();
            param_status = false;
        });
        if (param_status) {
            layer.msg('已设置规则为必填项！', {icon: 5});
            return false;
        }
        $.ajax({
            url:"",
            type:'post',//method请求方式，get或者post
            dataType:'json',//预期服务器返回的数据类型
            data:params,//表格数据序列化
            contentType: "application/x-www-form-urlencoded",//表单格式
            success: function(response) {
                if (response.Status) {
                    layer.msg(response.Message, {time:2000, icon: 1});
                    setTimeout(function () {
                        parent.layer.closeAll();
                        parent.location.reload();
                        // parent.layui.table.reload('EDtable');
                        // window.opener.getTroubles();
                    },1000);
                } else {
                    var data = response.Data;
                    var alertMessage = response.Message;
                    if (data != null) {
                        for (var i=0; i < data.length; i++) {
                            alertMessage += data[i] + '<br/>'
                        }
                    }
                    layer.msg(alertMessage, {icon: 5});
                }
            },
            error:function(){
                layer.alert('操作失败！！！',{icon:5});
            }
        });
        return false;
    });

    // checkbox all
    form.on('checkbox(allChoose)', function (data,type) {
        $("input[name='check[]']").each(function () {
            this.checked = data.elem.checked;
        });
        form.render('checkbox');
    });
    //单个选中
    form.on('checkbox(oneChoose)', function (data,type) {
        var i = 0;
        var j = 0;
        $("input[name='check[]']").each(function () {
            if( this.checked === true ){
                i++;
            }
            j++;
        });
        if( i == j ){
            $(".checkboxAll").prop("checked",true);
            form.render('checkbox');
        }else{
            $(".checkboxAll").removeAttr("checked");
            form.render('checkbox');
        }
    });

    // disabled 尺寸区间
    $(document).on('click', '.SizeRange .layui-form-checkbox', function() {
        var k = $(this).parents('li');
        if($(this).hasClass('layui-form-checked')){
            k.find('.inpblock select').removeClass('layui-disabled');
            k.find('.inpblock select').removeAttr('disabled');
            k.find('.inpblock .layui-form-select').removeClass('layui-select-disabled');
            k.find('.inpblock input').removeClass('layui-disabled');
            k.find('.inpblock input').removeAttr("disabled");
            k.find('.inpblock input').attr('lay-verify','required');
            k.addClass('active');
            form.render('select');
        }else{
            k.find('.inpblock .layui-form-select').addClass('layui-select-disabled');
            k.find('.inpblock input').addClass('layui-disabled');
            k.find('.inpblock input').attr("disabled","disabled");
            k.find('.inpblock select').addClass('layui-disabled');
            k.find('.inpblock select').attr('disabled','disabled');
            k.find('.inpblock input').removeAttr('lay-verify','required');
            k.removeClass('active');
            form.render('select');
        }
    });


    //平台 店铺 全选
    form.on('checkbox(allsele)', function (data) {
        //店铺 有单独逻辑
        var type = data.elem.dataset.type;
        if (type == 2) {
            var plat = data.elem.dataset.plat;
            var plat_class = plat+'_class';
            $(this).parents('.checkboxGroup').find("input[data-plat='"+plat+"']").each(function () {
                this.checked = data.elem.checked;
                if(data.elem.checked == true){
                    var ckk = $(this).attr('title');
                    var kid = $(this).attr('che-id');
                    var type =  $(this).attr('data-type');
                    var name =  $(this).attr('data-name');
                    var value =  $(this).attr('value');
                    if (!kid) {
                        kid = 1;
                    }
                    if (typeof(name) == "undefined") {
                        var hiddenInput = '';
                        var edhtml = '';
                    } else {
                        //新增隐藏域
                        var hiddenInput = '<input type="hidden" name="'+name+'" value = "'+value+'" class="hided" data-plat="'+plat+'">' ;
                        var edhtml = $('<span che-id="'+kid+'" class="ed '+plat_class+'" >'+ckk+'<em class="remv kbico"></em>'+hiddenInput+'</span>');
                    }

                    //弹出层已选中项
                    $(this).parents('.outWindow').find('.kb_hadSelected .ed').each(function(){
                        if($(this).attr('che-id') == kid){
                            $(this).remove();
                        }
                    })
                    if (edhtml) {
                        $(this).parents('.outWindow').find('.kb_hadSelected').append(edhtml);
                    }
                }else{
                    $(this).parents('.outWindow').find('.'+plat_class).each(function () {
                        $(this).remove();
                    });
                }
            });
        } else {
            $(this).parents('.checkboxGroup').find("input[name='check[]']").each(function () {
                this.checked = data.elem.checked;
                if(data.elem.checked == true){
                    var ckk = $(this).attr('title');
                    var kid = $(this).attr('che-id');
                    var type =  $(this).attr('data-type');
                    var name =  $(this).attr('data-name');
                    var value =  $(this).attr('value');
                    //新增隐藏域
                    var hiddenInput = '<input type="hidden" name="'+name+'" value = '+value+' class="hided">';
                    var edhtml = $('<span che-id="'+kid+'" class="ed">'+ckk+'<em class="remv kbico"></em>'+hiddenInput+'</span>');

                    $(this).parents('.outWindow').find('.kb_hadSelected .ed').each(function(){
                        if($(this).attr('che-id') == kid){
                            $(this).remove();
                        }
                    })
                    $(this).parents('.outWindow').find('.kb_hadSelected').append(edhtml);
                }else{
                    $(this).parents('.outWindow').find('.kb_hadSelected').html('');
                }
            });
        }
        form.render('checkbox');
    });
    //  平台 店铺 单个选中
    form.on('checkbox(oneCho)', function (data) {
        var i = 0;
        var j = 0;
        var ckk = $(this).attr('title');
        var kid = $(this).attr('che-id');

        var type = data.elem.dataset.type, name = data.elem.dataset.name,value = data.elem.value;
        var plat_class = '';
        //新增隐藏域
        if (type == 2) {
            var plat = data.elem.dataset.plat;
            var plat_class = plat+'_class';
            var hiddenInput = '<input type="hidden" name="'+name+'" value = '+value+' class="hided" data-plat="'+plat+'">';
            var edhtml = $('<span che-id="'+kid+'" class="ed '+plat_class+'">'+ckk+'<em class="remv kbico"></em>'+hiddenInput+'</span>');
            $(this).parents('.checkboxGroup').find("input[name='check[]']").each(function () {
                if( this.checked === true ){
                    i++;
                }
                j++;
            });
            if(this.checked == true){
                $(this).parents('.outWindow').find('.kb_hadSelected').append(edhtml);
            }else{
                $(this).parents('.outWindow').find('.kb_hadSelected .ed').each(function(){
                    if($(this).attr('che-id') == kid){
                        $(this).remove();
                    }
                })
            }

            if( i == j ){
                $("."+plat+"_selectAll").prop("checked",true);
            } else {
                $("."+plat+"_selectAll").removeAttr("checked");
            }
            form.render('checkbox');
        } else {
            var hiddenInput = '<input type="hidden" name="'+name+'" value = '+value+' class="hided">';
            var edhtml = $('<span che-id="'+kid+'" class="ed">'+ckk+'<em class="remv kbico"></em>'+hiddenInput+'</span>');
            $(this).parents('.checkboxGroup').find("input[name='check[]']").each(function () {
                if( this.checked === true ){
                    i++;
                }
                j++;
            });
            if(this.checked == true){
                $(this).parents('.outWindow').find('.kb_hadSelected').append(edhtml);
            }else{
                $(this).parents('.outWindow').find('.kb_hadSelected .ed').each(function(){
                    if($(this).attr('che-id') == kid){
                        $(this).remove();
                    }
                })
            }

            if( i == j ){
                $(".selectAll").prop("checked",true);
                form.render('checkbox');
            }else{
                $(".selectAll").removeAttr("checked");
                form.render('checkbox');
            }
        }
    });


    // 平台 店铺  单个移除 x 移除
    $(document).on('click', '.kb_chbox .remv', function() {
        var reid = $(this).parent().attr('che-id');
        var topinp = $(this).parents('.outWindow').find('.chebox .lip input');
        topinp.each(function(){
            if($(this).attr('che-id') == reid){
                var plat = $(this).find('input').context.dataset.plat;
                if (typeof(plat) == "undefined") {
                    $('.selectAll').removeAttr("checked");
                } else {
                    $('.'+plat+'_selectAll').removeAttr("checked");
                }
                $(this).removeAttr("checked");
                form.render('checkbox');
            };
        })
        $(this).parent('.ed').remove();
    });

    //平台 店铺  取消全部
    function remvsele (){
        var topinp = $(this).parents('.outWindow').find('.chebox .lip input');
        var titlebox = $(this).parents('.outWindow').find('.boxall h3 input');
        topinp.removeAttr("checked");
        titlebox.removeAttr("checked");
        form.render('checkbox');
        $(this).parents('.colCheckbox').find('.kb_hadSelected').html('');
    };


    // 移除已选
    $(document).on('click','.remvsele',remvsele)

    // 平台:选中 生成li标签 取消选中: 清除li 清除弹出层数据 OK
    $(document).on('click','.liergodic #sert0',function(){
        var btname = $(this).find('.ws').text();
        var tabname = $(this).find('.nm').text();
        var relid = $(this).attr('relid');
        var sertId = $(this).attr('id');
        if($(this).find('.layui-form-checkbox').hasClass('layui-form-checked')){
            $.addHtml(relid,sertId,tabname,btname);
            $('#OrderRules .ruleSection .flexlayer').find('.remv').remove();
        }else{
            //未选中处理
            //清除相应id的li标签
            $.delLi(relid);
            //清空全选
            $('#rulecont01').find('input').removeAttr("checked");
            //清空选中数据
            $('#rulecont01').find('.kb_hadSelected').html('');
            form.render('checkbox');
        }
    });

    //平台:弹出层 数据操作 yes:确定  btn2: 取消 清空数据 cancel:右上角关闭回调
    $(document).on("click",".flexlayer #sert0",function(){
        var btname = $(this).find('.ws').text();
        var tabname = $(this).find('.nm').text();
        tabname = tabname.replace(new RegExp(btname,'g'),"");
        var relid = $(this).attr('relid');
        var sertId = $(this).attr('id');
        layer.open({
            type: 1,
            title: btname,
            area: ['680px','450px'],
            content: $('#rulecont01'),
            btn: ['确定','取消'],
            yes: function(index, layero){
                if(layero.find('.chebox .layui-form-checkbox').hasClass('layui-form-checked')){
                    $.delLi(relid);
                    var outs = $(layero).find('.kb_hadSelected').html();
                    $.addHtml(relid,sertId,tabname,btname,outs);
                    $('#OrderRules .ruleSection .flexlayer').find('.remv').remove();
                    layer.close(index);
                    layer.msg('添加成功');
                }else{
                    layer.msg('请'+btname);
                }
                form.render('checkbox');
            },
            btn2: function(index, layero){
                //清空全选
                $('#rulecont01').find('input').removeAttr("checked");
                //清空选中数据
                $('#rulecont01').find('.kb_hadSelected').html('');
                form.render('checkbox');
                layer.close(index);
            },cancel: function(index, layero){
                //关闭按钮不做任何操作
                form.render('checkbox');
            }
        });
    });


    // 店铺:选中 生成li标签 取消选中: 清除li 清除弹出层数据 OK
    $(document).on('click','.liergodic #sert1',function(){
        var btname = $(this).find('.ws').text();
        var tabname = $(this).find('.nm').text();
        var relid = $(this).attr('relid');
        var sertId = $(this).attr('id');
        if($(this).find('.layui-form-checkbox').hasClass('layui-form-checked')){
            $.addHtml(relid,sertId,tabname,btname);
            $('#OrderRules .ruleSection .flexlayer').find('.remv').remove();
        }else{
            //未选中处理
            //清除相应id的li标签
            $.delLi(relid);
            //清空全选
            $('#rulecont02').find('input').removeAttr("checked");
            //清空选中数据
            $('#rulecont02').find('.kb_hadSelected').html('');
            form.render('checkbox');
        }
    });

    //店铺:弹出层 数据操作 yes:确定  btn2: 取消 清空数据 cancel:右上角关闭回调
    $(document).on("click",".flexlayer #sert1",function(){
        var btname = $(this).find('.ws').text();
        var tabname = $(this).find('.nm').text();
        tabname = tabname.replace(new RegExp(btname,'g'),"");
        var relid = $(this).attr('relid');
        var sertId = $(this).attr('id');
        layer.open({
            type: 1,
            title: btname,
            area: ['680px','450px'],
            content: $('#rulecont02'),
            btn: ['确定','取消'],
            yes: function(index, layero){
                if(layero.find('.chebox .layui-form-checkbox').hasClass('layui-form-checked')){
                    $.delLi(relid);
                    var outs = $(layero).find('.kb_hadSelected').html();
                    $.addHtml(relid,sertId,tabname,btname,outs);
                    $('#OrderRules .ruleSection .flexlayer').find('.remv').remove();
                    layer.close(index);
                    layer.msg('添加成功');
                }else{
                    layer.msg('请'+btname);
                }
                form.render('checkbox');
            },
            btn2: function(index, layero){
                //清空全选
                $('#rulecont02').find('input').removeAttr("checked");
                //清空选中数据
                $('#rulecont02').find('.kb_hadSelected').html('');
                form.render('checkbox');
                layer.close(index);
            },cancel: function(index, layero){
                //关闭按钮不做任何操作
                form.render('checkbox');
            }
        });
    });

    // 国家:选中 生成li标签 取消选中: 清除li 清除弹出层数据 OK
    $(document).on('click','.liergodic #sert2',function(){
        var btname = $(this).find('.ws').text();
        var tabname = $(this).find('.nm').text();
        var relid = $(this).attr('relid');
        var sertId = $(this).attr('id');
        if($(this).find('.layui-form-checkbox').hasClass('layui-form-checked')){
            $.addHtml(relid,sertId,tabname,btname);
            $('#OrderRules .ruleSection .flexlayer').find('.remv').remove();
        }else{
            //未选中处理
            //清除相应id的li标签
            $.delLi(relid);
            form.render('checkbox');
        }
    });

    //国家:弹出层 数据操作 yes:确定  btn2: 取消 清空数据 cancel:右上角关闭回调
    $(document).on("click",".flexlayer #sert2",function(){
        var btname = $(this).find('.ws').text();
        var tabname = $(this).find('.nm').text();
        tabname = tabname.replace(new RegExp(btname,'g'),"");
        var relid = $(this).attr('relid');
        var sertId = $(this).attr('id');
        layer.open({
            type: 1,
            title: btname,
            area: ['680px','450px'],
            content: $('#rulecont03'),
            btn: ['确定','取消'],
            yes: function(index, layero){
                if($(layero).find('.logis_settle span').length > 0 ){
                    $.delLi(relid);
                    var outs = $(layero).find('.logis_settle .col').html();

                    var hiddenInput = '';
                    layui.each($(layero).find('.logis_settle span'), function (index,item) {
                        var name = item.dataset.name,value = item.dataset.value;
                        hiddenInput += '<input type="hidden" name="'+name+'" value = '+value+' class="hided">' ;
                    })

                    outs = outs +hiddenInput;
                    $.addHtml(relid,sertId,tabname,btname,outs);

                    $('#OrderRules .ruleSection .flexlayer').find('span').removeClass('active');
                    layer.close(index);
                    layer.msg('添加成功');
                }else{
                    layer.msg('请'+btname);
                }
            },
            btn2: function(index, layero){
                form.render('checkbox');
                layer.close(index);
            },cancel: function(index, layero){
                //关闭按钮不做任何操作
                form.render('checkbox');
            }
        });
    });


    // 指定邮编:选中 生成li标签 取消选中: 清除li 清除弹出层数据 OK
    $(document).on('click','.liergodic #sert3',function(){
        var btname = $(this).find('.ws').text();
        var tabname = $(this).find('.nm').text();
        var relid = $(this).attr('relid');
        var sertId = $(this).attr('id');
        if($(this).find('.layui-form-checkbox').hasClass('layui-form-checked')){
            $.addHtml(relid,sertId,tabname,btname);
            $('#OrderRules .ruleSection .flexlayer').find('.remv').remove();
        }else{
            //未选中处理
            //清除相应id的li标签
            $.delLi(relid);
            //清空选中数据
            $('#rulecont05').find('.numTxtarea').val('');
            form.render('checkbox');
        }
    });

    //指定邮编:弹出层 数据操作 yes:确定  btn2: 取消 清空数据 cancel:右上角关闭回调
    $(document).on("click",".flexlayer #sert3",function(){
        var btname = $(this).find('.ws').text();
        var tabname = $(this).find('.nm').text();
        tabname = tabname.replace(new RegExp(btname,'g'),"");
        var relid = $(this).attr('relid');
        var sertId = $(this).attr('id');
        layer.open({
            type: 1,
            title: btname,
            area: ['680px','450px'],
            content: $('#rulecont05'),
            btn: ['确定','取消'],
            yes: function(index, layero){
                if($(layero).find('.numTxtarea').val() == ''){
                    layer.msg('请'+btname);
                    return false;
                } else {
                    $.delLi(relid);
                    var reg = /^[a-zA-Z0-9-~]{1,1000}$/;// 3-20位字母，数字，空格，中划线 ,~
                    //处理空格
                    var zipCode = $(layero).find('.numTxtarea').val();
                    var result = "";
                    if (zipCode != "") {
                        if (zipCode.length > 5000) {
                            layer.msg('排除邮编总长度已超过上限，请检查后重新填写');
                            return false;
                        }
                        var zipCodeArr = zipCode.split("\n");
                        if (zipCodeArr.length > 0) {
                            for (var i = 0; i < zipCodeArr.length; i++) {
                                if (zipCodeArr[i].trim() != "" && !reg.test(zipCodeArr[i].trim())) {
                                    layer.msg('包含邮编含有非法字符，请检查后重新填写');
                                    return false;
                                }
                                if (zipCodeArr[i].indexOf("~") != -1) { //判断邮编范围左右值合法性
                                    var strArr = zipCodeArr[i].trim().split("~");
                                    if (strArr.length != 2) {
                                        layer.msg('排除邮编范围只能是单区间，请检查后重新填写');
                                        return false;
                                    } else {
                                        if (!isNaN(Number(strArr[0])) && !isNaN(Number(strArr[1]))) {//范围左右值是纯数字
                                            if (parseInt(strArr[0]) >= parseInt(strArr[1])) {
                                                layer.msg('排除邮编范围区间不合法，请检查后重新填写');
                                                return false;
                                            }
                                        } else {
                                            if (strArr[0] >= strArr[1]) {
                                                layer.msg('排除邮编范围区间不合法，请检查后重新填写');
                                                return false;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                    //去掉回车换行
                    outs = zipCode.replace(/[\n]/g,"、");
                    //隐藏域拼接
                    var name = $(layero).find('.numTxtarea').attr('name');
                    var hiddenInput = '<input type="hidden" name="'+name+'" value = '+outs+' class="hided">' ;
                    outs = outs +hiddenInput;
                    $.addHtml(relid,sertId,tabname,btname,outs);
                    $('#OrderRules .ruleSection .flexlayer').find('span').removeClass('active');
                    layer.close(index);
                    layer.msg('添加成功');
                }
            },
            btn2: function(index, layero){
                $('#rulecont05').find('.numTxtarea').val('');
                form.render('checkbox');
                layer.close(index);
            },cancel: function(index, layero){
                //关闭按钮不做任何操作
                form.render('checkbox');
            }
        });
    });


    // 指定字段空:选中 生成li标签 取消选中: 清除li 清除弹出层数据 OK
    $(document).on('click','.liergodic #sert4',function(){
        var btname = $(this).find('.ws').text();
        var tabname = $(this).find('.nm').text();
        var relid = $(this).attr('relid');
        var sertId = $(this).attr('id');
        if($(this).find('.layui-form-checkbox').hasClass('layui-form-checked')){
            $.addHtml(relid,sertId,tabname,btname);
            $('#OrderRules .ruleSection .flexlayer').find('.remv').remove();
        }else{
            //未选中处理
            //清除相应id的li标签
            $.delLi(relid);
            //清空选中数据
            $('#rulecont06').find('input').removeAttr("checked");
            form.render('checkbox');
        }
    });

    //指定字段空:弹出层 数据操作 yes:确定  btn2: 取消 清空数据 cancel:右上角关闭回调
    $(document).on("click",".flexlayer #sert4",function(){
        var btname = $(this).find('.ws').text();
        var tabname = $(this).find('.nm').text();
        tabname = tabname.replace(new RegExp(btname,'g'),"");
        var relid = $(this).attr('relid');
        var sertId = $(this).attr('id');
        layer.open({
            type: 1,
            title: btname,
            area: ['680px','450px'],
            content: $('#rulecont06'),
            btn: ['确定','取消'],
            yes: function(index, layero){
                if($(layero).find('.FieldEmpty .layui-form-checked').length < 1){
                    layer.msg('请'+btname);
                    return false;
                }else{
                    $.delLi(relid);
                    var outs = '';
                    var name = '';
                    var type = '';
                    var hiddenInput = '';
                    $(layero).find('.FieldEmpty .layui-form-checked .imt').each(function(){
                        var span = '<span>'+$(this).html()+'</span>';
                        if (name == '') {
                            name = $(this).attr('data-name');
                        }
                        outs += span;
                        type = $(this).attr('data-value');
                        if (hiddenInput == '') {
                            hiddenInput = '<input type="hidden" name="'+name+'" value = '+type+' class="hided">' ;
                        } else {
                            hiddenInput += '<input type="hidden" name="'+name+'" value = '+type+' class="hided">' ;
                        }
                    })
                    outs = outs+hiddenInput;
                    $.addHtml(relid,sertId,tabname,btname,outs);
                    $('#OrderRules .ruleSection .flexlayer').find('.ep').remove();
                    layer.close(index);
                    layer.msg('添加成功');
                }
            },
            btn2: function(index, layero){
                $('#rulecont06').find('input').removeAttr("checked");
                form.render('checkbox');
                layer.close(index);
            },cancel: function(index, layero){
                //关闭按钮不做任何操作
                form.render('checkbox');
            }
        });
    });


    // 商品尺寸:选中 生成li标签 取消选中: 清除li 清除弹出层数据 OK
    $(document).on('click','.liergodic #sert5',function(){
        var btname = $(this).find('.ws').text();
        var tabname = $(this).find('.nm').text();
        var relid = $(this).attr('relid');
        var sertId = $(this).attr('id');
        if($(this).find('.layui-form-checkbox').hasClass('layui-form-checked')){
            $.addHtml(relid,sertId,tabname,btname);
            $('#OrderRules .ruleSection .flexlayer').find('.remv').remove();
        }else{
            //未选中处理
            //清除相应id的li标签
            $.delLi(relid);
            //清空选中数据
            $('#rulecont07 ul li .inpblock').find('input').each(function () {
                if (!$(this).hasClass('layui-disabled')) {
                    $(this).addClass('layui-disabled');
                    var name = $(this).attr('name');
                    if (name == 'goods_length' || name == 'goods_width' || name == 'goods_height' ) {
                        $(this).val('');
                    }
                }
            });
            //下拉框初始化
            $('#rulecont07').find('.inpblock select').each( function () {
                    $(this).find('option:first').prop("selected", 'selected');
                }
            );
            //复选框 初始化
            $('#rulecont07').find('.inptxt .layui-form-checkbox').each(function () {
                if ($(this).hasClass('layui-form-checked')) {
                    $(this).click();
                }
            });
            form.render();
        }
    });

    //商品尺寸:弹出层 数据操作 yes:确定  btn2: 取消 清空数据 cancel:右上角关闭回调
    $(document).on("click",".flexlayer #sert5",function(){
        var btname = $(this).find('.ws').text();
        var tabname = $(this).find('.nm').text();
        tabname = tabname.replace(new RegExp(btname,'g'),"");
        var relid = $(this).attr('relid');
        var sertId = $(this).attr('id');
        layer.open({
            type: 1,
            title: btname,
            area: ['680px','450px'],
            content: $('#rulecont07'),
            btn: ['确定','取消'],
            yes: function(index, layero){
                if($(layero).find('.SizeRange .layui-form-checked').length < 1){
                    layer.msg('请'+btname);
                    return false;
                }else{
                    var outs = '';
                    var length_info = '';
                    var width_info = '';
                    var height_info = '';
                    var except_status = false;
                    //.SizeRange .active li标签
                    $(layero).find('.SizeRange .active').each(function(){
                        //选中的span
                        var tpe = '<span>'+$(this).find('.layui-form-checked span').text()+'</span>';
                        //select
                        var selc = '<span>'+$(this).find('.layui-form-select input').val()+'</span>';
                        //输入框

                        var inpVal = $(this).find('.kbinp').val();
                        var inp = '<span>'+inpVal+'CM</span>';
                        //判断长宽高
                        var name = $(this).find('.kbinp').attr('name');
                        var unit = name+'_unit';
                        var unit_val = $(this).find("select[name='"+unit+"']").find('option:selected').val();
                        if (inpVal == '' || except_status == true) {
                            except_status = true;
                        }
                        if (name == 'goods_length') {
                            length_info = unit_val+','+inpVal;
                        } else if (name == 'goods_width') {
                            width_info = unit_val+','+inpVal;
                        } else if (name == 'goods_height') {
                            height_info = unit_val+','+inpVal;
                        }
                        outs += '<div class="lp">'+tpe + selc + inp+'</div>';
                    })
                    //后面完善
                    if (except_status) {
                        layer.msg('请'+btname);
                        return false;
                    }

                    $.delLi(relid);
                    var sizeInfo = length_info+';'+width_info+';'+height_info;
                    var hiddenInput = '<input type="hidden" name="goods_size_info" value = "'+sizeInfo+'" class="hided">';
                    outs = outs +hiddenInput;
                    $.addHtml(relid,sertId,tabname,btname,outs);
                    $('#OrderRules .ruleSection .flexlayer').find('.ep').remove();
                    layer.close(index);
                    layer.msg('添加成功');
                }
            },
            btn2: function(index, layero){
                //未选中处理
                //清空选中数据
                $('#rulecont07 ul li .inpblock').find('input').each(function () {
                    if (!$(this).hasClass('layui-disabled')) {
                        $(this).addClass('layui-disabled');
                        var name = $(this).attr('name');
                        if (name == 'goods_length' || name == 'goods_width' || name == 'goods_height' ) {
                            $(this).val('');
                        }
                    }
                });
                //下拉框初始化
                $('#rulecont07').find('.inpblock select').each( function () {
                        $(this).find('option:first').prop("selected", 'selected');
                    }
                );
                //复选框 初始化
                $('#rulecont07').find('.inptxt .layui-form-checkbox').each(function () {
                    if ($(this).hasClass('layui-form-checked')) {
                        $(this).click();
                    }
                });
                form.render();
                layer.close(index);
            },cancel: function(index, layero){
                //关闭按钮不做任何操作
                form.render('checkbox');
            }
        });
    });


    // 商品重量:选中 生成li标签 取消选中: 清除li 清除弹出层数据 OK
    $(document).on('click','.liergodic #sert6',function(){
        var btname = $(this).find('.ws').text();
        var tabname = $(this).find('.nm').text();
        var relid = $(this).attr('relid');
        var sertId = $(this).attr('id');
        if($(this).find('.layui-form-checkbox').hasClass('layui-form-checked')){
            $.addHtml(relid,sertId,tabname,btname);
            $('#OrderRules .ruleSection .flexlayer').find('.remv').remove();
        }else{
            //未选中处理
            //清除相应id的li标签
            $.delLi(relid);

            //下拉框初始化
            $('#rulecont09').find('.nkg select').each( function () {
                    $(this).find('option:first').prop("selected", 'selected');
                }
            );
            //单选 初始化
            $('#rulecont09').find('.inpblock input:radio').each( function () {
                    $(this).attr("checked", false);
                }
            );

            //下拉框初始化
            $('#rulecont09').find('.scope select').each( function () {
                    $(this).find('option:first').prop("selected", 'selected');
                }
            );

            $('#rulecont09').find('.scope input:text').each( function () {
                    $(this).val('')
                }
            );
            form.render(); //更新全部
        }
    });

    //商品重量:弹出层 数据操作 yes:确定  btn2: 取消 清空数据 cancel:右上角关闭回调
    $(document).on("click",".flexlayer #sert6",function(){
        var btname = $(this).find('.ws').text();
        var tabname = $(this).find('.nm').text();
        tabname = tabname.replace(new RegExp(btname,'g'),"");
        var relid = $(this).attr('relid');
        var sertId = $(this).attr('id');
        layer.open({
            type: 1,
            title: btname,
            area: ['680px','450px'],
            content: $('#rulecont09'),
            btn: ['确定','取消'],
            yes: function(index, layero){
                    //计算方式
                    //单位
                    var unit_name = 'goods_weight_unit';
                    var unit = '';
                    var hiddenHtml = '';
                    var outs = '';
                    //设置值
                    var set_val = kgross = unit_val= '';
                    var except_status = false;
                    var except_mes = '';
                    //ul
                    $(layero).find('.proWeight .flexlayer li').each(function(indexs){
                        //计算方式
                        if (indexs == 0) {
                            kgross = '<span>'+$(this).find('.ngross input').val()+'</span>';
                        }
                        if (indexs == 1) {
                            unit_val = $(this).find("select[name='"+unit_name+"']").find('option:selected').val();
                        }

                        if (indexs == 2) {
                            //单选li
                            if ($(this).hasClass('alone')) {
                                var checked_val = $(this).find('input:radio:checked').val();
                                if (checked_val == 1) {
                                    var goods_weight_value_min = $(this).find('input[name="goods_weight_value_min"]').val();
                                    var goods_weight_value_max = $(this).find('input[name="goods_weight_value_max"]').val();
                                    if (parseFloat(goods_weight_value_min) > parseFloat(goods_weight_value_max)) {
                                        except_status = true;
                                        except_mes = '最小值大于最大值！请检查参数';
                                        return false;
                                    }
                                    set_val = goods_weight_value_min+'~'+goods_weight_value_max;
                                    if (goods_weight_value_max == '' || goods_weight_value_min == '') {
                                        except_status = true;
                                        return false;
                                    }
                                } else if (checked_val == 2) {
                                    var goods_weight_unit = $(this).find('select[name="goods_weight_unit_type"]').find('option:selected').val();
                                    var goods_weight_value = $(this).find('input[name="goods_weight_value"]').val();
                                    set_val = goods_weight_unit+goods_weight_value;
                                    if (goods_weight_value == '') {
                                        except_status = true;
                                        return false;
                                    }
                                } else {
                                    except_status = true;
                                    return false;
                                }
                            }
                            var ykg = '<span>'+'('+set_val +')'+unit_val+'</span>';
                            outs = kgross  + ykg;
                            hiddenHtml = '<input type="hidden" name="goods_weight_info[]" value = "'+set_val+'" class="hided">' ;
                            hiddenHtml += '<input type="hidden" name="goods_weight_info[]" value = "'+unit_val+'" class="hided">' ;
                        }
                    })
                    if (except_status) {
                        if (except_mes) {
                            layer.msg(except_mes);
                        } else {
                            layer.msg('请'+btname);
                        }
                        return false;
                    }
                    $.delLi(relid);

                    outs = outs +hiddenHtml;
                    $.addHtml(relid,sertId,tabname,btname,outs);
                    layer.close(index);
                    layer.msg('添加成功');
            },
            btn2: function(index, layero){
                //未选中处理
                //清除相应id的li标签
                $(this).parents('.ruleBody').find('.flexlayer li').each(function(){
                    if($(this).attr('relid') == relid){
                        $(this).remove();
                    }
                });

                //下拉框初始化
                $('#rulecont09').find('.nkg select').each( function () {
                        $(this).find('option:first').prop("selected", 'selected');
                    }
                );
                //单选 初始化
                $('#rulecont09').find('.inpblock input:radio').each( function () {
                        $(this).attr("checked", false);
                    }
                );

                //下拉框初始化
                $('#rulecont09').find('.scope select').each( function () {
                        $(this).find('option:first').prop("selected", 'selected');
                    }
                );

                $('#rulecont09').find('.scope input:text').each( function () {
                        $(this).val('')
                    }
                );
                form.render(); //更新全部
                layer.close(index);
            },cancel: function(index, layero){
                //关闭按钮不做任何操作
                form.render(); //更新全部
            }
        });
    });


    // 商品属性:选中 生成li标签 取消选中: 清除li 清除弹出层数据 OK
    $(document).on('click','.liergodic #sert7',function(){
        var btname = $(this).find('.ws').text();
        var tabname = $(this).find('.nm').text();
        var relid = $(this).attr('relid');
        var sertId = $(this).attr('id');
        if($(this).find('.layui-form-checkbox').hasClass('layui-form-checked')){
            $.addHtml(relid,sertId,tabname,btname);
            $('#OrderRules .ruleSection .flexlayer').find('.remv').remove();
        }else{
            //未选中处理
            //清除相应id的li标签
            $.delLi(relid);
            //清空全选
            $('#rulecont08').find('input').removeAttr("checked");
            form.render('checkbox');
        }
    });

    //商品属性:弹出层 数据操作 yes:确定  btn2: 取消 清空数据 cancel:右上角关闭回调
    $(document).on("click",".flexlayer #sert7",function(){
        var btname = $(this).find('.ws').text();
        var tabname = $(this).find('.nm').text();
        tabname = tabname.replace(new RegExp(btname,'g'),"");
        var relid = $(this).attr('relid');
        var sertId = $(this).attr('id');
        layer.open({
            type: 1,
            title: btname,
            area: ['680px','450px'],
            content: $('#rulecont08'),
            btn: ['确定','取消'],
            yes: function(index, layero){
                if($(layero).find('.proAttr .layui-form-checked').length < 1){
                    layer.msg('请'+btname);
                    return false;
                }else{
                    var outs = '';
                    var hiddenInput = '';
                    $(layero).find('.proAttr .layui-form-checked span').each(function(){
                        outs += '<span>'+$(this).html()+'</span>';
                    })

                    $(layero).find('.proAttr input[type="checkbox"]:checked').each(function(index,item){
                        var attr_id = this.defaultValue;
                        hiddenInput += '<input type="hidden" name="goods_attr[]" value = "'+attr_id+'" class="hided">';
                    })

                    $.delLi(relid);
                    outs = outs +hiddenInput;
                    $.addHtml(relid,sertId,tabname,btname,outs);

                    $('#OrderRules .ruleSection .flexlayer').find('.ep').remove();
                    layer.close(index);
                    layer.msg('添加成功');
                }
                form.render('checkbox');
            },
            btn2: function(index, layero){
                //清空全选
                $('#rulecont08').find('input').removeAttr("checked");
                form.render('checkbox');
                layer.close(index);
            },cancel: function(index, layero){
                //关闭按钮不做任何操作
                form.render('checkbox');
            }
        });
    });


    // 指定sku:选中 生成li标签 取消选中: 清除li 清除弹出层数据 OK
    $(document).on('click','.liergodic #sert8',function(){
        var btname = $(this).find('.ws').text();
        var tabname = $(this).find('.nm').text();
        var relid = $(this).attr('relid');
        var sertId = $(this).attr('id');
        if($(this).find('.layui-form-checkbox').hasClass('layui-form-checked')){
            $.addHtml(relid,sertId,tabname,btname);
            $('#OrderRules .ruleSection .flexlayer').find('.remv').remove();
        }else{
            //未选中处理
            //清除相应id的li标签
            $.delLi(relid);
            //清空选中数据
            $('#rulecont10').find('.numTxtarea').val('');
            form.render();
        }
    });

    //指定sku:弹出层 数据操作 yes:确定  btn2: 取消 清空数据 cancel:右上角关闭回调
    $(document).on("click",".flexlayer #sert8",function(){
        var btname = $(this).find('.ws').text();
        var tabname = $(this).find('.nm').text();
        tabname = tabname.replace(new RegExp(btname,'g'),"");
        var relid = $(this).attr('relid');
        var sertId = $(this).attr('id');
        layer.open({
            type: 1,
            title: btname,
            area: ['680px','450px'],
            content: $('#rulecont10'),
            btn: ['确定','取消'],
            yes: function(index, layero){
                if($(layero).find('.numTxtarea').val() == ''){
                    layer.msg('请'+btname);
                    return false;
                } else {
                    $.delLi(relid);
                    //处理空格
                    var outs = $(layero).find('.numTxtarea').val();
                    //去掉回车换行
                    outs = outs.replace(/[\n]/g,"、");
                    //隐藏域拼接
                    var name = $(layero).find('.numTxtarea').attr('name');
                    var hiddenInput = '<input type="hidden" name="'+name+'" value = '+outs+' class="hided">' ;
                    outs = outs +hiddenInput;
                    $.addHtml(relid,sertId,tabname,btname,outs);

                    $('#OrderRules .ruleSection .flexlayer').find('span').removeClass('active');
                    layer.close(index);
                    layer.msg('添加成功');
                }
            },
            btn2: function(index, layero){
                $('#rulecont10').find('.numTxtarea').val('');
                form.render();
                layer.close(index);
            },cancel: function(index, layero){
                //关闭按钮不做任何操作
                form.render();
            }
        });
    });


    // 指定订单总额区间:选中 生成li标签 取消选中: 清除li 清除弹出层数据 OK
    $(document).on('click','.liergodic #sert10',function(){
        var btname = $(this).find('.ws').text();
        var tabname = $(this).find('.nm').text();
        var relid = $(this).attr('relid');
        var sertId = $(this).attr('id');
        if($(this).find('.layui-form-checkbox').hasClass('layui-form-checked')){
            $.addHtml(relid,sertId,tabname,btname);
            $('#OrderRules .ruleSection .flexlayer').find('.remv').remove();
        }else{
            //未选中处理
            //清除相应id的li标签
            $.delLi(relid);

            //下拉框初始化
            $('#rulecont11').find('.inpblock select').each( function () {
                    $(this).find('option:first').prop("selected", 'selected');
                }
            );
            //单选 初始化
            $('#rulecont11').find('.inpblock input:radio').each( function () {
                    $(this).attr("checked", false);
                }
            );

            //下拉框初始化
            $('#rulecont11').find('.scope select').each( function () {
                    $(this).find('option:first').prop("selected", 'selected');
                }
            );

            $('#rulecont11').find('.scope input:text').each( function () {
                    $(this).val('')
                }
            );
            form.render(); //更新全部
        }
    });

    //指定订单总额区间:弹出层 数据操作 yes:确定  btn2: 取消 清空数据 cancel:右上角关闭回调
    $(document).on("click",".flexlayer #sert10",function(){
        var btname = $(this).find('.ws').text();
        var tabname = $(this).find('.nm').text();
        tabname = tabname.replace(new RegExp(btname,'g'),"");
        var relid = $(this).attr('relid');
        var sertId = $(this).attr('id');
        layer.open({
            type: 1,
            title: btname,
            area: ['680px','450px'],
            content: $('#rulecont11'),
            btn: ['确定','取消'],
            yes: function(index, layero){
                var checked_length = $(layero).find('.proWeight .orders_price_class input:radio:checked').length;
                if(checked_length == 0){
                    layer.msg('请'+btname);
                    return false;
                }else{
                    //币种
                    var unit_name = 'orders_price_unit';
                    //计算方式
                    //单位
                    var unit = '';
                    var hiddenHtml = '';
                    var outs = '';
                    //设置值
                    var set_val = kgross = unit_val= '';
                    var except_status = false;
                    var except_mes = '';
                    //ul
                    $(layero).find('.proWeight .orders_price_class li').each(function(indexs){
                        //计算方式
                        if (indexs == 0) {
                            kgross = '<span>'+$(this).find('.ngross input').val()+'</span>';
                        }
                        if (indexs == 1) {
                            unit_val = $(this).find("select[name='"+unit_name+"']").find('option:selected').val();
                            unit = $(this).find("select[name='"+unit_name+"']").find('option:selected').text();
                        }
                        //单选li
                        if ($(this).hasClass('alone')) {
                            var checked_val = $(this).find('input:radio:checked').val();
                            if (checked_val == 1) {
                                var orders_price_min = $(this).find('input[name="orders_price_min"]').val();
                                var orders_price_max = $(this).find('input[name="orders_price_max"]').val();
                                if (parseFloat(orders_price_min) > parseFloat(orders_price_max)) {
                                    except_status = true;
                                    except_mes = '最小值大于最大值！请检查参数';
                                    return false;
                                }
                                if (orders_price_min == '' || orders_price_max == '') {
                                    except_status = true;
                                    return false;
                                }
                                set_val = orders_price_min+'~'+orders_price_max;
                            } else if (checked_val == 2) {
                                var orders_price_unit = $(this).find('select[name="orders_price_unit_type"]').find('option:selected').val();
                                var orders_price = $(this).find('input[name="orders_price"]').val();
                                if (orders_price == '') {
                                    except_status = true;
                                    return false;
                                }
                                set_val = orders_price_unit+orders_price;
                            } else {
                                except_status = true;
                                return false;
                            }
                        }
                        if (indexs == 2) {
                            var ykg = '<span>'+'('+set_val +') '+unit+'</span>';
                            outs = kgross  + ykg;
                            hiddenHtml = '<input type="hidden" name="orders_price[]" value = "'+set_val+'" class="hided">' ;
                            hiddenHtml += '<input type="hidden" name="orders_price[]" value = "'+unit_val+'" class="hided">' ;
                        }
                    })
                    if (except_status) {
                        if (except_mes) {
                            layer.msg(except_mes);
                        } else {
                            layer.msg('请'+btname);
                        }
                        return false;
                    }
                    $.delLi(relid);
                    outs = outs +hiddenHtml;
                    $.addHtml(relid,sertId,tabname,btname,outs);
                    $('#OrderRules .ruleSection .flexlayer').find('.ep').remove();
                    layer.close(index);
                    layer.msg('添加成功');
                }
            },
            btn2: function(index, layero){
                //未选中处理
                //清除相应id的li标签
                $(this).parents('.ruleBody').find('.flexlayer li').each(function(){
                    if($(this).attr('relid') == relid){
                        $(this).remove();
                    }
                });

                //下拉框初始化
                $('#rulecont11').find('.inpblock select').each( function () {
                        $(this).find('option:first').prop("selected", 'selected');
                    }
                );
                //单选 初始化
                $('#rulecont11').find('.inpblock input:radio').each( function () {
                        $(this).attr("checked", false);
                    }
                );

                //下拉框初始化
                $('#rulecont11').find('.scope select').each( function () {
                        $(this).find('option:first').prop("selected", 'selected');
                    }
                );

                $('#rulecont11').find('.scope input:text').each( function () {
                        $(this).val('')
                    }
                );
                form.render(); //更新全部
                layer.close(index);
            },cancel: function(index, layero){
                //关闭按钮不做任何操作
                form.render(); //更新全部
            }
        });
    });



    // 商品数量:选中 生成li标签 取消选中: 清除li 清除弹出层数据 OK
    $(document).on('click','.liergodic #sert9',function(){
        var btname = $(this).find('.ws').text();
        var tabname = $(this).find('.nm').text();
        var relid = $(this).attr('relid');
        var sertId = $(this).attr('id');
        if($(this).find('.layui-form-checkbox').hasClass('layui-form-checked')){
            $.addHtml(relid,sertId,tabname,btname);
            $('#OrderRules .ruleSection .flexlayer').find('.remv').remove();
        }else{
            //未选中处理
            //清除相应id的li标签
            $.delLi(relid);

            //下拉框初始化
            $('#rulecont12').find('.inpblock select').each( function () {
                    $(this).find('option:first').prop("selected", 'selected');
                }
            );
            //单选 初始化
            $('#rulecont12').find('.inpblock input:radio').each( function () {
                    $(this).attr("checked", false);
                }
            );


            $('#rulecont12').find('.scope input:text').each( function () {
                    $(this).val('')
                }
            );
            form.render(); //更新全部
        }
    });

    //商品数量:弹出层 数据操作 yes:确定  btn2: 取消 清空数据 cancel:右上角关闭回调
    $(document).on("click",".flexlayer #sert9",function(){
        var btname = $(this).find('.ws').text();
        var tabname = $(this).find('.nm').text();
        tabname = tabname.replace(new RegExp(btname,'g'),"");
        var relid = $(this).attr('relid');
        var sertId = $(this).attr('id');
        layer.open({
            type: 1,
            title: btname,
            area: ['680px','450px'],
            content: $('#rulecont12'),
            btn: ['确定','取消'],
            yes: function(index, layero){
                var checked_length = $(layero).find('.proWeight .goods_count_class input:radio:checked').length;
                if(checked_length == 0){
                    layer.msg('请'+btname);
                    return false;
                } else {
                    var outs = '';
                    var set_val = kgross = unit_val = hiddenHtml = '';
                    var except_status = false;
                    $(layero).find('.proWeight .goods_count_class input:radio:checked').each(function(){
                        var checkedVal = this.defaultValue;
                        if (checkedVal == 1) {
                            //区间
                            var goods_count_min = $(this).parents('.goods_count_class').find('input[name="goods_count_min"]').val();
                            var goods_count_max = $(this).parents('.goods_count_class').find('input[name="goods_count_max"]').val();
                            if (goods_count_max == '' || goods_count_min == '') {
                                except_status = true;
                            }
                            if(parseInt(goods_count_min) > parseInt(goods_count_max)){
                                except_status = true;
                                btname = '最大值要大于最小值！';
                            }
                            set_val = goods_count_min+'~'+goods_count_max;
                        } else if (checkedVal == 2) {
                            //运算符
                            var goods_count_unit = $(this).parents('.goods_count_class').find('select[name="goods_count_unit_type"]').find('option:selected').val();
                            var goods_count = $(this).parents('.goods_count_class').find('input[name="goods_count"]').val();
                            if (goods_count == '') {
                                except_status = true;
                            }
                            set_val = goods_count_unit+goods_count;
                        }
                        var titleVal = this.attributes.title.nodeValue;
                        outs = '<span>'+titleVal +'('+set_val+')'+'</span>';

                        hiddenHtml = '<input type="hidden" name="goods_count[]" value = "'+set_val+'" class="hided">' ;
                    })
                    if (except_status) {
                        layer.msg('请'+btname);
                        return false;
                    }
                    $.delLi(relid);

                    outs = outs +hiddenHtml;
                    $.addHtml(relid,sertId,tabname,btname,outs);
                    $('#OrderRules .ruleSection .flexlayer').find('.ep').remove();
                    layer.close(index);
                    layer.msg('添加成功');
                }
            },
            btn2: function(index, layero){
                //未选中处理
                //清除相应id的li标签
                $(this).parents('.ruleBody').find('.flexlayer li').each(function(){
                    if($(this).attr('relid') == relid){
                        $(this).remove();
                    }
                });

                //下拉框初始化
                $('#rulecont12').find('.inpblock select').each( function () {
                        $(this).find('option:first').prop("selected", 'selected');
                    }
                );
                //单选 初始化
                $('#rulecont12').find('.inpblock input:radio').each( function () {
                        $(this).attr("checked", false);
                    }
                );

                $('#rulecont12').find('.scope input:text').each( function () {
                        $(this).val('')
                    }
                );
                form.render(); //更新全部
                layer.close(index);
            },cancel: function(index, layero){
                //关闭按钮不做任何操作
                form.render(); //更新全部
            }
        });
    });

    //仓库:选中 生成li标签 取消选中: 清除li 清除弹出层数据 OK
    $(document).on('click','.liergodic #sert11',function(){
        var btname = $(this).find('.ws').text();
        var tabname = $(this).find('.nm').text();
        var relid = $(this).attr('relid');
        var sertId = $(this).attr('id');
        if($(this).find('.layui-form-checkbox').hasClass('layui-form-checked')){
            $.addHtml(relid,sertId,tabname,btname);
            $('#OrderRules .ruleSection .flexlayer').find('.remv').remove();
        }else{
            //未选中处理
            //清除相应id的li标签
            $.delLi(relid);
            form.render('checkbox');
        }
    });

    //仓库:弹出层 数据操作 yes:确定  btn2: 取消 清空数据 cancel:右上角关闭回调
    $(document).on("click",".flexlayer #sert11",function(){
        var btname = $(this).find('.ws').text();
        var tabname = $(this).find('.nm').text();
        tabname = tabname.replace(new RegExp(btname,'g'),"");
        var relid = $(this).attr('relid');
        var sertId = $(this).attr('id');
        layer.open({
            type: 1,
            title: btname,
            area: ['680px','450px'],
            content: $('#rulecont13'),
            btn: ['确定','取消'],
            yes: function(index, layero){
                if($(layero).find('.logis_settle span').length > 0 ){
                    $.delLi(relid);
                    var outs = $(layero).find('.logis_settle .col').html();

                    var hiddenInput = '';
                    layui.each($(layero).find('.logis_settle span'), function (index,item) {
                        var name = item.dataset.name,value = item.dataset.value;
                        hiddenInput += '<input type="hidden" name="'+name+'" value = '+value+' class="hided">' ;
                    })

                    outs = outs +hiddenInput;
                    $.addHtml(relid,sertId,tabname,btname,outs);

                    $('#OrderRules .ruleSection .flexlayer').find('span').removeClass('active');
                    layer.close(index);
                    layer.msg('添加成功');
                }else{
                    layer.msg('请'+btname);
                }
            },
            btn2: function(index, layero){
                form.render('checkbox');
                layer.close(index);
            },cancel: function(index, layero){
                //关闭按钮不做任何操作
                form.render('checkbox');
            }
        });
    });

    //物流:选中 生成li标签 取消选中: 清除li 清除弹出层数据 OK
    $(document).on('click','.liergodic #sert12',function(){
        var btname = $(this).find('.ws').text();
        var tabname = $(this).find('.nm').text();
        var relid = $(this).attr('relid');
        var sertId = $(this).attr('id');
        if($(this).find('.layui-form-checkbox').hasClass('layui-form-checked')){
            $.addHtml(relid,sertId,tabname,btname);
            $('#OrderRules .ruleSection .flexlayer').find('.remv').remove();
        }else{
            //未选中处理
            //清除相应id的li标签
            $.delLi(relid);
            form.render('checkbox');
        }
    });

    //物流:弹出层 数据操作 yes:确定  btn2: 取消 清空数据 cancel:右上角关闭回调
    $(document).on("click",".flexlayer #sert12",function(){
        var btname = $(this).find('.ws').text();
        var tabname = $(this).find('.nm').text();
        tabname = tabname.replace(new RegExp(btname,'g'),"");
        var relid = $(this).attr('relid');
        var sertId = $(this).attr('id');
        layer.open({
            type: 1,
            title: btname,
            area: ['680px','450px'],
            content: $('#rulecont14'),
            btn: ['确定','取消'],
            yes: function(index, layero){
                if($(layero).find('.logis_settle span').length > 0 ){
                    $.delLi(relid);
                    var outs = $(layero).find('.logis_settle .col').html();

                    var hiddenInput = '';
                    layui.each($(layero).find('.logis_settle span'), function (index,item) {
                        var name = item.dataset.name,value = item.dataset.value;
                        hiddenInput += '<input type="hidden" name="'+name+'" value = '+value+' class="hided">' ;
                    })

                    outs = outs +hiddenInput;
                    $.addHtml(relid,sertId,tabname,btname,outs);

                    $('#OrderRules .ruleSection .flexlayer').find('span').removeClass('active');
                    layer.close(index);
                    layer.msg('添加成功');
                }else{
                    layer.msg('请'+btname);
                }
            },
            btn2: function(index, layero){
                form.render('checkbox');
                layer.close(index);
            },cancel: function(index, layero){
                //关闭按钮不做任何操作
                form.render('checkbox');
            }
        });
    });

    // 排除邮编:选中 生成li标签 取消选中: 清除li 清除弹出层数据 OK
    $(document).on('click','.liergodic #sert13',function(){
        var btname = $(this).find('.ws').text();
        var tabname = $(this).find('.nm').text();
        var relid = $(this).attr('relid');
        var sertId = $(this).attr('id');
        if($(this).find('.layui-form-checkbox').hasClass('layui-form-checked')){
            $.addHtml(relid,sertId,tabname,btname);
            $('#OrderRules .ruleSection .flexlayer').find('.remv').remove();
        }else{
            //未选中处理
            //清除相应id的li标签
            $.delLi(relid);
            //清空选中数据
            $('#rulecont04').find('.numTxtarea').val('');
            form.render('checkbox');
        }
    });

    //排除邮编:弹出层 数据操作 yes:确定  btn2: 取消 清空数据 cancel:右上角关闭回调
    $(document).on("click",".flexlayer #sert13",function(){
        var btname = $(this).find('.ws').text();
        var tabname = $(this).find('.nm').text();
        tabname = tabname.replace(new RegExp(btname,'g'),"");
        var relid = $(this).attr('relid');
        var sertId = $(this).attr('id');
        layer.open({
            type: 1,
            title: btname,
            area: ['680px','450px'],
            content: $('#rulecont04'),
            btn: ['确定','取消'],
            yes: function(index, layero){
                if($(layero).find('.numTxtarea').val() == ''){
                    layer.msg('请'+btname);
                    return false;
                } else {
                    $.delLi(relid);

                    var reg = /^[a-zA-Z0-9-~]{1,1000}$/;// 3-20位字母，数字，空格，中划线 ,~
                    //处理空格
                    var zipCode = $(layero).find('.numTxtarea').val();
                    var result = "";
                    if (zipCode != "") {
                        if (zipCode.length > 5000) {
                            layer.msg('排除邮编总长度已超过上限，请检查后重新填写');
                            return false;
                        }
                        var zipCodeArr = zipCode.split("\n");
                        if (zipCodeArr.length > 0) {
                            for (var i = 0; i < zipCodeArr.length; i++) {
                                if (zipCodeArr[i].trim() != "" && !reg.test(zipCodeArr[i].trim())) {
                                    layer.msg('包含邮编含有非法字符，请检查后重新填写');
                                    return false;
                                }
                                if (zipCodeArr[i].indexOf("~") != -1) { //判断邮编范围左右值合法性
                                    var strArr = zipCodeArr[i].trim().split("~");
                                    if (strArr.length != 2) {
                                        layer.msg('排除邮编范围只能是单区间，请检查后重新填写');
                                        return false;
                                    } else {
                                        if (!isNaN(Number(strArr[0])) && !isNaN(Number(strArr[1]))) {//范围左右值是纯数字
                                            if (parseInt(strArr[0]) >= parseInt(strArr[1])) {
                                                layer.msg('排除邮编范围区间不合法，请检查后重新填写');
                                                return false;
                                            }
                                        } else {
                                            if (strArr[0] >= strArr[1]) {
                                                layer.msg('排除邮编范围区间不合法，请检查后重新填写');
                                                return false;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }

                    //去掉回车换行
                    outs = zipCode.replace(/[\n]/g,"、");
                    //隐藏域拼接
                    var name = $(layero).find('.numTxtarea').attr('name');
                    var hiddenInput = '<input type="hidden" name="'+name+'" value = '+outs+' class="hided">' ;
                    outs = outs +hiddenInput;
                    $.addHtml(relid,sertId,tabname,btname,outs);
                    $('#OrderRules .ruleSection .flexlayer').find('span').removeClass('active');
                    layer.close(index);
                    layer.msg('添加成功');
                }
            },
            btn2: function(index, layero){
                $('#rulecont04').find('.numTxtarea').val('');
                form.render('checkbox');
                layer.close(index);
            },cancel: function(index, layero){
                //关闭按钮不做任何操作
                form.render('checkbox');
            }
        });
    });


});
//保留两位小数
function clearNoNum(obj){
    obj.value = obj.value.replace(/[^\d.]/g,"");  //清除“数字”和“.”以外的字符
    obj.value = obj.value.replace(/\.{2,}/g,"."); //只保留第一个. 清除多余的
    obj.value = obj.value.replace(".","$#$").replace(/\./g,"").replace("$#$",".");
    obj.value = obj.value.replace(/^(\-)*(\d+)\.(\d\d).*$/,'$1$2.$3');//只能输入两个小数
    if(obj.value.indexOf(".")< 0 && obj.value !=""){//以上已经过滤，此处控制的是如果没有小数点，首位不能为类似于 01、02的金额
        obj.value= parseFloat(obj.value);
    }
}

    $("body").bind("keydown",function(event){
        if (event.keyCode == 116) {
            event.preventDefault(); //阻止默认刷新
            location=location;
        }
    })