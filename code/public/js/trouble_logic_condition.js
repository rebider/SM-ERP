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

//layui??????
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

    //????????????
    form.on('submit(formSubmit)', function(data){
        var params=data.field;
        var param_status = true;
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
            layer.msg('??????????????????!???????????????', {icon: 5});
            return false;
        }
        $.ajax({
            url:"",
            type:'post',//method???????????????get??????post
            dataType:'json',//????????????????????????????????????
            data:params,//?????????????????????
            contentType: "application/x-www-form-urlencoded",//????????????
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
                layer.alert('?????????????????????',{icon:5});
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
    //????????????
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

    // disabled ????????????
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


    //?????? ?????? ??????
    form.on('checkbox(allsele)', function (data) {
        //?????? ???????????????
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
                        //???????????????
                        var hiddenInput = '<input type="hidden" name="'+name+'" value = "'+value+'" class="hided" data-plat="'+plat+'">' ;
                        var edhtml = $('<span che-id="'+kid+'" class="ed '+plat_class+'" >'+ckk+'<em class="remv kbico"></em>'+hiddenInput+'</span>');
                    }

                    //?????????????????????
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
                    //???????????????
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
    //  ?????? ?????? ????????????
    form.on('checkbox(oneCho)', function (data) {
        var i = 0;
        var j = 0;
        var ckk = $(this).attr('title');
        var kid = $(this).attr('che-id');

        var type = data.elem.dataset.type, name = data.elem.dataset.name,value = data.elem.value;
        var plat_class = '';
        //???????????????
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


    // ?????? ??????  ???????????? x ??????
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

    //?????? ??????  ????????????
    function remvsele (){
        var topinp = $(this).parents('.outWindow').find('.chebox .lip input');
        var titlebox = $(this).parents('.outWindow').find('.boxall h3 input');
        topinp.removeAttr("checked");
        titlebox.removeAttr("checked");
        form.render('checkbox');
        $(this).parents('.colCheckbox').find('.kb_hadSelected').html('');
    };


    // ????????????
    $(document).on('click','.remvsele',remvsele)

    // ??????:?????? ??????li?????? ????????????: ??????li ????????????????????? OK
    $(document).on('click','.liergodic #sert0',function(){
        var btname = $(this).find('.ws').text();
        var tabname = $(this).find('.nm').text();
        var relid = $(this).attr('relid');
        var sertId = $(this).attr('id');
        if($(this).find('.layui-form-checkbox').hasClass('layui-form-checked')){
            $.addHtml(relid,sertId,tabname,btname);
            $('#OrderRules .ruleSection .flexlayer').find('.remv').remove();
        }else{
            //???????????????
            //????????????id???li??????
            $.delLi(relid);
            //????????????
            $('#rulecont01').find('input').removeAttr("checked");
            //??????????????????
            $('#rulecont01').find('.kb_hadSelected').html('');
            form.render('checkbox');
        }
    });

    //??????:????????? ???????????? yes:??????  btn2: ?????? ???????????? cancel:?????????????????????
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
            btn: ['??????','??????'],
            yes: function(index, layero){
                if(layero.find('.chebox .layui-form-checkbox').hasClass('layui-form-checked')){
                    $.delLi(relid);
                    var outs = $(layero).find('.kb_hadSelected').html();
                    $.addHtml(relid,sertId,tabname,btname,outs);
                    $('#OrderRules .ruleSection .flexlayer').find('.remv').remove();
                    layer.close(index);
                    layer.msg('????????????');
                }else{
                    layer.msg('???'+btname);
                }
                form.render('checkbox');
            },
            btn2: function(index, layero){
                //????????????
                $('#rulecont01').find('input').removeAttr("checked");
                //??????????????????
                $('#rulecont01').find('.kb_hadSelected').html('');
                form.render('checkbox');
                layer.close(index);
            },cancel: function(index, layero){
                //??????????????????????????????
                form.render('checkbox');
            }
        });
    });


    // ??????:?????? ??????li?????? ????????????: ??????li ????????????????????? OK
    $(document).on('click','.liergodic #sert1',function(){
        var btname = $(this).find('.ws').text();
        var tabname = $(this).find('.nm').text();
        var relid = $(this).attr('relid');
        var sertId = $(this).attr('id');
        if($(this).find('.layui-form-checkbox').hasClass('layui-form-checked')){
            $.addHtml(relid,sertId,tabname,btname);
            $('#OrderRules .ruleSection .flexlayer').find('.remv').remove();
        }else{
            //???????????????
            //????????????id???li??????
            $.delLi(relid);
            //????????????
            $('#rulecont02').find('input').removeAttr("checked");
            //??????????????????
            $('#rulecont02').find('.kb_hadSelected').html('');
            form.render('checkbox');
        }
    });

    //??????:????????? ???????????? yes:??????  btn2: ?????? ???????????? cancel:?????????????????????
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
            btn: ['??????','??????'],
            yes: function(index, layero){
                if(layero.find('.chebox .layui-form-checkbox').hasClass('layui-form-checked')){
                    $.delLi(relid);
                    var outs = $(layero).find('.kb_hadSelected').html();
                    $.addHtml(relid,sertId,tabname,btname,outs);
                    $('#OrderRules .ruleSection .flexlayer').find('.remv').remove();
                    layer.close(index);
                    layer.msg('????????????');
                }else{
                    layer.msg('???'+btname);
                }
                form.render('checkbox');
            },
            btn2: function(index, layero){
                //????????????
                $('#rulecont02').find('input').removeAttr("checked");
                //??????????????????
                $('#rulecont02').find('.kb_hadSelected').html('');
                form.render('checkbox');
                layer.close(index);
            },cancel: function(index, layero){
                //??????????????????????????????
                form.render('checkbox');
            }
        });
    });

    // ??????:?????? ??????li?????? ????????????: ??????li ????????????????????? OK
    $(document).on('click','.liergodic #sert2',function(){
        var btname = $(this).find('.ws').text();
        var tabname = $(this).find('.nm').text();
        var relid = $(this).attr('relid');
        var sertId = $(this).attr('id');
        if($(this).find('.layui-form-checkbox').hasClass('layui-form-checked')){
            $.addHtml(relid,sertId,tabname,btname);
            $('#OrderRules .ruleSection .flexlayer').find('.remv').remove();
        }else{
            //???????????????
            //????????????id???li??????
            $.delLi(relid);
            form.render('checkbox');
        }
    });

    //??????:????????? ???????????? yes:??????  btn2: ?????? ???????????? cancel:?????????????????????
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
            btn: ['??????','??????'],
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
                    layer.msg('????????????');
                }else{
                    layer.msg('???'+btname);
                }
            },
            btn2: function(index, layero){
                form.render('checkbox');
                layer.close(index);
            },cancel: function(index, layero){
                //??????????????????????????????
                form.render('checkbox');
            }
        });
    });


    // ????????????:?????? ??????li?????? ????????????: ??????li ????????????????????? OK
    $(document).on('click','.liergodic #sert3',function(){
        var btname = $(this).find('.ws').text();
        var tabname = $(this).find('.nm').text();
        var relid = $(this).attr('relid');
        var sertId = $(this).attr('id');
        if($(this).find('.layui-form-checkbox').hasClass('layui-form-checked')){
            $.addHtml(relid,sertId,tabname,btname);
            $('#OrderRules .ruleSection .flexlayer').find('.remv').remove();
        }else{
            //???????????????
            //????????????id???li??????
            $.delLi(relid);
            //??????????????????
            $('#rulecont05').find('.numTxtarea').val('');
            form.render('checkbox');
        }
    });

    //????????????:????????? ???????????? yes:??????  btn2: ?????? ???????????? cancel:?????????????????????
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
            btn: ['??????','??????'],
            yes: function(index, layero){
                if($(layero).find('.numTxtarea').val() == ''){
                    layer.msg('???'+btname);
                    return false;
                } else {
                    $.delLi(relid);
                    //????????????
                    var outs = $(layero).find('.numTxtarea').val();
                    //??????????????????
                    outs = outs.replace(/[\n]/g,"???");
                    //???????????????
                    var name = $(layero).find('.numTxtarea').attr('name');
                    var hiddenInput = '<input type="hidden" name="'+name+'" value = '+outs+' class="hided">' ;
                    outs = outs +hiddenInput;
                    $.addHtml(relid,sertId,tabname,btname,outs);
                    $('#OrderRules .ruleSection .flexlayer').find('span').removeClass('active');
                    layer.close(index);
                    layer.msg('????????????');
                }
            },
            btn2: function(index, layero){
                $('#rulecont05').find('.numTxtarea').val('');
                form.render('checkbox');
                layer.close(index);
            },cancel: function(index, layero){
                //??????????????????????????????
                form.render('checkbox');
            }
        });
    });


    // ???????????????:?????? ??????li?????? ????????????: ??????li ????????????????????? OK
    $(document).on('click','.liergodic #sert4',function(){
        var btname = $(this).find('.ws').text();
        var tabname = $(this).find('.nm').text();
        var relid = $(this).attr('relid');
        var sertId = $(this).attr('id');
        if($(this).find('.layui-form-checkbox').hasClass('layui-form-checked')){
            $.addHtml(relid,sertId,tabname,btname);
            $('#OrderRules .ruleSection .flexlayer').find('.remv').remove();
        }else{
            //???????????????
            //????????????id???li??????
            $.delLi(relid);
            //??????????????????
            $('#rulecont06').find('input').removeAttr("checked");
            form.render('checkbox');
        }
    });

    //???????????????:????????? ???????????? yes:??????  btn2: ?????? ???????????? cancel:?????????????????????
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
            btn: ['??????','??????'],
            yes: function(index, layero){
                if($(layero).find('.FieldEmpty .layui-form-checked').length < 1){
                    layer.msg('???'+btname);
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
                    layer.msg('????????????');
                }
            },
            btn2: function(index, layero){
                $('#rulecont06').find('input').removeAttr("checked");
                form.render('checkbox');
                layer.close(index);
            },cancel: function(index, layero){
                //??????????????????????????????
                form.render('checkbox');
            }
        });
    });


    // ????????????:?????? ??????li?????? ????????????: ??????li ????????????????????? OK
    $(document).on('click','.liergodic #sert5',function(){
        var btname = $(this).find('.ws').text();
        var tabname = $(this).find('.nm').text();
        var relid = $(this).attr('relid');
        var sertId = $(this).attr('id');
        if($(this).find('.layui-form-checkbox').hasClass('layui-form-checked')){
            $.addHtml(relid,sertId,tabname,btname);
            $('#OrderRules .ruleSection .flexlayer').find('.remv').remove();
        }else{
            //???????????????
            //????????????id???li??????
            $.delLi(relid);
            //??????????????????
            $('#rulecont07 ul li .inpblock').find('input').each(function () {
                if (!$(this).hasClass('layui-disabled')) {
                    $(this).addClass('layui-disabled');
                    var name = $(this).attr('name');
                    if (name == 'goods_length' || name == 'goods_width' || name == 'goods_height' ) {
                        $(this).val('');
                    }
                }
            });
            //??????????????????
            $('#rulecont07').find('.inpblock select').each( function () {
                    $(this).find('option:first').prop("selected", 'selected');
                }
            );
            //????????? ?????????
            $('#rulecont07').find('.inptxt .layui-form-checkbox').each(function () {
                if ($(this).hasClass('layui-form-checked')) {
                    $(this).click();
                }
            });
            form.render();
        }
    });

    //????????????:????????? ???????????? yes:??????  btn2: ?????? ???????????? cancel:?????????????????????
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
            btn: ['??????','??????'],
            yes: function(index, layero){
                if($(layero).find('.SizeRange .layui-form-checked').length < 1){
                    layer.msg('???'+btname);
                    return false;
                }else{
                    var outs = '';
                    var length_info = '';
                    var width_info = '';
                    var height_info = '';
                    var except_status = false;
                    //.SizeRange .active li??????
                    $(layero).find('.SizeRange .active').each(function(){
                        //?????????span
                        var tpe = '<span>'+$(this).find('.layui-form-checked span').text()+'</span>';
                        //select
                        var selc = '<span>'+$(this).find('.layui-form-select input').val()+'</span>';
                        //?????????

                        var inpVal = $(this).find('.kbinp').val();
                        var inp = '<span>'+inpVal+'CM</span>';
                        //???????????????
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
                    //????????????
                    if (except_status) {
                        layer.msg('???'+btname);
                        return false;
                    }

                    $.delLi(relid);
                    var sizeInfo = length_info+';'+width_info+';'+height_info;
                    var hiddenInput = '<input type="hidden" name="goods_size_info" value = "'+sizeInfo+'" class="hided">';
                    outs = outs +hiddenInput;
                    $.addHtml(relid,sertId,tabname,btname,outs);
                    $('#OrderRules .ruleSection .flexlayer').find('.ep').remove();
                    layer.close(index);
                    layer.msg('????????????');
                }
            },
            btn2: function(index, layero){
                //???????????????
                //??????????????????
                $('#rulecont07 ul li .inpblock').find('input').each(function () {
                    if (!$(this).hasClass('layui-disabled')) {
                        $(this).addClass('layui-disabled');
                        var name = $(this).attr('name');
                        if (name == 'goods_length' || name == 'goods_width' || name == 'goods_height' ) {
                            $(this).val('');
                        }
                    }
                });
                //??????????????????
                $('#rulecont07').find('.inpblock select').each( function () {
                        $(this).find('option:first').prop("selected", 'selected');
                    }
                );
                //????????? ?????????
                $('#rulecont07').find('.inptxt .layui-form-checkbox').each(function () {
                    if ($(this).hasClass('layui-form-checked')) {
                        $(this).click();
                    }
                });
                form.render();
                layer.close(index);
            },cancel: function(index, layero){
                //??????????????????????????????
                form.render('checkbox');
            }
        });
    });


    // ????????????:?????? ??????li?????? ????????????: ??????li ????????????????????? OK
    $(document).on('click','.liergodic #sert6',function(){
        var btname = $(this).find('.ws').text();
        var tabname = $(this).find('.nm').text();
        var relid = $(this).attr('relid');
        var sertId = $(this).attr('id');
        if($(this).find('.layui-form-checkbox').hasClass('layui-form-checked')){
            $.addHtml(relid,sertId,tabname,btname);
            $('#OrderRules .ruleSection .flexlayer').find('.remv').remove();
        }else{
            //???????????????
            //????????????id???li??????
            $.delLi(relid);

            //??????????????????
            $('#rulecont09').find('.nkg select').each( function () {
                    $(this).find('option:first').prop("selected", 'selected');
                }
            );
            //?????? ?????????
            $('#rulecont09').find('.inpblock input:radio').each( function () {
                    $(this).attr("checked", false);
                }
            );

            //??????????????????
            $('#rulecont09').find('.scope select').each( function () {
                    $(this).find('option:first').prop("selected", 'selected');
                }
            );

            $('#rulecont09').find('.scope input:text').each( function () {
                    $(this).val('')
                }
            );
            form.render(); //????????????
        }
    });

    //????????????:????????? ???????????? yes:??????  btn2: ?????? ???????????? cancel:?????????????????????
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
            btn: ['??????','??????'],
            yes: function(index, layero){
                //????????????
                //??????
                var unit_name = 'goods_weight_unit';
                var unit = '';
                var hiddenHtml = '';
                var outs = '';
                //?????????
                var set_val = kgross = unit_val= '';
                var except_status = false;
                var except_mes = '';
                //ul
                $(layero).find('.proWeight .flexlayer li').each(function(indexs){
                    //????????????
                    if (indexs == 0) {
                        kgross = '<span>'+$(this).find('.ngross input').val()+'</span>';
                    }
                    if (indexs == 1) {
                        unit_val = $(this).find("select[name='"+unit_name+"']").find('option:selected').val();
                    }

                    if (indexs == 2) {
                        //??????li
                        if ($(this).hasClass('alone')) {
                            var checked_val = $(this).find('input:radio:checked').val();
                            if (checked_val == 1) {
                                var goods_weight_value_min = $(this).find('input[name="goods_weight_value_min"]').val();
                                var goods_weight_value_max = $(this).find('input[name="goods_weight_value_max"]').val();
                                if (goods_weight_value_min > goods_weight_value_max) {
                                    except_status = true;
                                    except_mes = '??????????????????????????????????????????';
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
                        layer.msg('???'+btname);
                    }
                    return false;
                }
                $.delLi(relid);

                outs = outs +hiddenHtml;
                $.addHtml(relid,sertId,tabname,btname,outs);
                layer.close(index);
                layer.msg('????????????');
            },
            btn2: function(index, layero){
                //???????????????
                //????????????id???li??????
                $(this).parents('.ruleBody').find('.flexlayer li').each(function(){
                    if($(this).attr('relid') == relid){
                        $(this).remove();
                    }
                });

                //??????????????????
                $('#rulecont09').find('.nkg select').each( function () {
                        $(this).find('option:first').prop("selected", 'selected');
                    }
                );
                //?????? ?????????
                $('#rulecont09').find('.inpblock input:radio').each( function () {
                        $(this).attr("checked", false);
                    }
                );

                //??????????????????
                $('#rulecont09').find('.scope select').each( function () {
                        $(this).find('option:first').prop("selected", 'selected');
                    }
                );

                $('#rulecont09').find('.scope input:text').each( function () {
                        $(this).val('')
                    }
                );
                form.render(); //????????????
                layer.close(index);
            },cancel: function(index, layero){
                //??????????????????????????????
                form.render(); //????????????
            }
        });
    });


    // ????????????:?????? ??????li?????? ????????????: ??????li ????????????????????? OK
    $(document).on('click','.liergodic #sert7',function(){
        var btname = $(this).find('.ws').text();
        var tabname = $(this).find('.nm').text();
        var relid = $(this).attr('relid');
        var sertId = $(this).attr('id');
        if($(this).find('.layui-form-checkbox').hasClass('layui-form-checked')){
            $.addHtml(relid,sertId,tabname,btname);
            $('#OrderRules .ruleSection .flexlayer').find('.remv').remove();
        }else{
            //???????????????
            //????????????id???li??????
            $.delLi(relid);
            //????????????
            $('#rulecont08').find('input').removeAttr("checked");
            form.render('checkbox');
        }
    });

    //????????????:????????? ???????????? yes:??????  btn2: ?????? ???????????? cancel:?????????????????????
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
            btn: ['??????','??????'],
            yes: function(index, layero){
                if($(layero).find('.proAttr .layui-form-checked').length < 1){
                    layer.msg('???'+btname);
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
                    layer.msg('????????????');
                }
                form.render('checkbox');
            },
            btn2: function(index, layero){
                //????????????
                $('#rulecont08').find('input').removeAttr("checked");
                form.render('checkbox');
                layer.close(index);
            },cancel: function(index, layero){
                //??????????????????????????????
                form.render('checkbox');
            }
        });
    });


    // ??????sku:?????? ??????li?????? ????????????: ??????li ????????????????????? OK
    $(document).on('click','.liergodic #sert8',function(){
        var btname = $(this).find('.ws').text();
        var tabname = $(this).find('.nm').text();
        var relid = $(this).attr('relid');
        var sertId = $(this).attr('id');
        if($(this).find('.layui-form-checkbox').hasClass('layui-form-checked')){
            $.addHtml(relid,sertId,tabname,btname);
            $('#OrderRules .ruleSection .flexlayer').find('.remv').remove();
        }else{
            //???????????????
            //????????????id???li??????
            $.delLi(relid);
            //??????????????????
            $('#rulecont10').find('.numTxtarea').val('');
            form.render();
        }
    });

    //??????sku:????????? ???????????? yes:??????  btn2: ?????? ???????????? cancel:?????????????????????
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
            btn: ['??????','??????'],
            yes: function(index, layero){
                if($(layero).find('.numTxtarea').val() == ''){
                    layer.msg('???'+btname);
                    return false;
                } else {
                    $.delLi(relid);
                    //????????????
                    var outs = $(layero).find('.numTxtarea').val();
                    //??????????????????
                    outs = outs.replace(/[\n]/g,"???");
                    //???????????????
                    var name = $(layero).find('.numTxtarea').attr('name');
                    var hiddenInput = '<input type="hidden" name="'+name+'" value = '+outs+' class="hided">' ;
                    outs = outs +hiddenInput;
                    $.addHtml(relid,sertId,tabname,btname,outs);

                    $('#OrderRules .ruleSection .flexlayer').find('span').removeClass('active');
                    layer.close(index);
                    layer.msg('????????????');
                }
            },
            btn2: function(index, layero){
                $('#rulecont10').find('.numTxtarea').val('');
                form.render();
                layer.close(index);
            },cancel: function(index, layero){
                //??????????????????????????????
                form.render();
            }
        });
    });


    // ????????????????????????:?????? ??????li?????? ????????????: ??????li ????????????????????? OK
    $(document).on('click','.liergodic #sert10',function(){
        var btname = $(this).find('.ws').text();
        var tabname = $(this).find('.nm').text();
        var relid = $(this).attr('relid');
        var sertId = $(this).attr('id');
        if($(this).find('.layui-form-checkbox').hasClass('layui-form-checked')){
            $.addHtml(relid,sertId,tabname,btname);
            $('#OrderRules .ruleSection .flexlayer').find('.remv').remove();
        }else{
            //???????????????
            //????????????id???li??????
            $.delLi(relid);

            //??????????????????
            $('#rulecont11').find('.inpblock select').each( function () {
                    $(this).find('option:first').prop("selected", 'selected');
                }
            );
            //?????? ?????????
            $('#rulecont11').find('.inpblock input:radio').each( function () {
                    $(this).attr("checked", false);
                }
            );

            //??????????????????
            $('#rulecont11').find('.scope select').each( function () {
                    $(this).find('option:first').prop("selected", 'selected');
                }
            );

            $('#rulecont11').find('.scope input:text').each( function () {
                    $(this).val('')
                }
            );
            form.render(); //????????????
        }
    });

    //????????????????????????:????????? ???????????? yes:??????  btn2: ?????? ???????????? cancel:?????????????????????
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
            btn: ['??????','??????'],
            yes: function(index, layero){
                var checked_length = $(layero).find('.proWeight .orders_price_class input:radio:checked').length;
                if(checked_length == 0){
                    layer.msg('???'+btname);
                    return false;
                }else{
                    //??????
                    var unit_name = 'orders_price_unit';
                    //????????????
                    //??????
                    var unit = '';
                    var hiddenHtml = '';
                    var outs = '';
                    //?????????
                    var set_val = kgross = unit_val= '';
                    var except_status = false;
                    var except_mes = '';
                    //ul
                    $(layero).find('.proWeight .orders_price_class li').each(function(indexs){
                        //????????????
                        if (indexs == 0) {
                            kgross = '<span>'+$(this).find('.ngross input').val()+'</span>';
                        }
                        if (indexs == 1) {
                            unit_val = $(this).find("select[name='"+unit_name+"']").find('option:selected').val();
                            unit = $(this).find("select[name='"+unit_name+"']").find('option:selected').text();
                        }
                        //??????li
                        if ($(this).hasClass('alone')) {
                            var checked_val = $(this).find('input:radio:checked').val();
                            if (checked_val == 1) {
                                var orders_price_min = $(this).find('input[name="orders_price_min"]').val();
                                var orders_price_max = $(this).find('input[name="orders_price_max"]').val();
                                if (orders_price_min > orders_price_max) {
                                    except_status = true;
                                    except_mes = '??????????????????????????????????????????';
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
                            layer.msg('???'+btname);
                        }
                        return false;
                    }
                    $.delLi(relid);
                    outs = outs +hiddenHtml;
                    $.addHtml(relid,sertId,tabname,btname,outs);
                    $('#OrderRules .ruleSection .flexlayer').find('.ep').remove();
                    layer.close(index);
                    layer.msg('????????????');
                }
            },
            btn2: function(index, layero){
                //???????????????
                //????????????id???li??????
                $(this).parents('.ruleBody').find('.flexlayer li').each(function(){
                    if($(this).attr('relid') == relid){
                        $(this).remove();
                    }
                });

                //??????????????????
                $('#rulecont11').find('.inpblock select').each( function () {
                        $(this).find('option:first').prop("selected", 'selected');
                    }
                );
                //?????? ?????????
                $('#rulecont11').find('.inpblock input:radio').each( function () {
                        $(this).attr("checked", false);
                    }
                );

                //??????????????????
                $('#rulecont11').find('.scope select').each( function () {
                        $(this).find('option:first').prop("selected", 'selected');
                    }
                );

                $('#rulecont11').find('.scope input:text').each( function () {
                        $(this).val('')
                    }
                );
                form.render(); //????????????
                layer.close(index);
            },cancel: function(index, layero){
                //??????????????????????????????
                form.render(); //????????????
            }
        });
    });



    // ????????????:?????? ??????li?????? ????????????: ??????li ????????????????????? OK
    $(document).on('click','.liergodic #sert9',function(){
        var btname = $(this).find('.ws').text();
        var tabname = $(this).find('.nm').text();
        var relid = $(this).attr('relid');
        var sertId = $(this).attr('id');
        if($(this).find('.layui-form-checkbox').hasClass('layui-form-checked')){
            $.addHtml(relid,sertId,tabname,btname);
            $('#OrderRules .ruleSection .flexlayer').find('.remv').remove();
        }else{
            //???????????????
            //????????????id???li??????
            $.delLi(relid);

            //??????????????????
            $('#rulecont12').find('.inpblock select').each( function () {
                    $(this).find('option:first').prop("selected", 'selected');
                }
            );
            //?????? ?????????
            $('#rulecont12').find('.inpblock input:radio').each( function () {
                    $(this).attr("checked", false);
                }
            );


            $('#rulecont12').find('.scope input:text').each( function () {
                    $(this).val('')
                }
            );
            form.render(); //????????????
        }
    });

    //????????????:????????? ???????????? yes:??????  btn2: ?????? ???????????? cancel:?????????????????????
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
            btn: ['??????','??????'],
            yes: function(index, layero){
                var checked_length = $(layero).find('.proWeight .goods_count_class input:radio:checked').length;
                if(checked_length == 0){
                    layer.msg('???'+btname);
                    return false;
                } else {
                    var outs = '';
                    var set_val = kgross = unit_val = hiddenHtml = '';
                    var except_status = false;
                    $(layero).find('.proWeight .goods_count_class input:radio:checked').each(function(){
                        var checkedVal = this.defaultValue;
                        if (checkedVal == 1) {
                            //??????
                            var goods_count_min = $(this).parents('.goods_count_class').find('input[name="goods_count_min"]').val();
                            var goods_count_max = $(this).parents('.goods_count_class').find('input[name="goods_count_max"]').val();
                            if (goods_count_max == '' || goods_count_min == '') {
                                except_status = true;
                            }
                            set_val = goods_count_min+'~'+goods_count_max;
                        } else if (checkedVal == 2) {
                            //?????????
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
                        layer.msg('???'+btname);
                        return false;
                    }
                    $.delLi(relid);

                    outs = outs +hiddenHtml;
                    $.addHtml(relid,sertId,tabname,btname,outs);
                    $('#OrderRules .ruleSection .flexlayer').find('.ep').remove();
                    layer.close(index);
                    layer.msg('????????????');
                }
            },
            btn2: function(index, layero){
                //???????????????
                //????????????id???li??????
                $(this).parents('.ruleBody').find('.flexlayer li').each(function(){
                    if($(this).attr('relid') == relid){
                        $(this).remove();
                    }
                });

                //??????????????????
                $('#rulecont12').find('.inpblock select').each( function () {
                        $(this).find('option:first').prop("selected", 'selected');
                    }
                );
                //?????? ?????????
                $('#rulecont12').find('.inpblock input:radio').each( function () {
                        $(this).attr("checked", false);
                    }
                );

                $('#rulecont12').find('.scope input:text').each( function () {
                        $(this).val('')
                    }
                );
                form.render(); //????????????
                layer.close(index);
            },cancel: function(index, layero){
                //??????????????????????????????
                form.render(); //????????????
            }
        });
    });

    //??????:?????? ??????li?????? ????????????: ??????li ????????????????????? OK
    $(document).on('click','.liergodic #sert11',function(){
        var btname = $(this).find('.ws').text();
        var tabname = $(this).find('.nm').text();
        var relid = $(this).attr('relid');
        var sertId = $(this).attr('id');
        if($(this).find('.layui-form-checkbox').hasClass('layui-form-checked')){
            $.addHtml(relid,sertId,tabname,btname);
            $('#OrderRules .ruleSection .flexlayer').find('.remv').remove();
        }else{
            //???????????????
            //????????????id???li??????
            $.delLi(relid);
            form.render('checkbox');
        }
    });

    //??????:????????? ???????????? yes:??????  btn2: ?????? ???????????? cancel:?????????????????????
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
            btn: ['??????','??????'],
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
                    layer.msg('????????????');
                }else{
                    layer.msg('???'+btname);
                }
            },
            btn2: function(index, layero){
                form.render('checkbox');
                layer.close(index);
            },cancel: function(index, layero){
                //??????????????????????????????
                form.render('checkbox');
            }
        });
    });

    //??????:?????? ??????li?????? ????????????: ??????li ????????????????????? OK
    $(document).on('click','.liergodic #sert12',function(){
        var btname = $(this).find('.ws').text();
        var tabname = $(this).find('.nm').text();
        var relid = $(this).attr('relid');
        var sertId = $(this).attr('id');
        if($(this).find('.layui-form-checkbox').hasClass('layui-form-checked')){
            $.addHtml(relid,sertId,tabname,btname);
            $('#OrderRules .ruleSection .flexlayer').find('.remv').remove();
        }else{
            //???????????????
            //????????????id???li??????
            $.delLi(relid);
            form.render('checkbox');
        }
    });

    //??????:????????? ???????????? yes:??????  btn2: ?????? ???????????? cancel:?????????????????????
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
            btn: ['??????','??????'],
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
                    layer.msg('????????????');
                }else{
                    layer.msg('???'+btname);
                }
            },
            btn2: function(index, layero){
                form.render('checkbox');
                layer.close(index);
            },cancel: function(index, layero){
                //??????????????????????????????
                form.render('checkbox');
            }
        });
    });
});

$("body").bind("keydown",function(event){
    if (event.keyCode == 116) {
        event.preventDefault(); //??????????????????
        location=location;
    }
})