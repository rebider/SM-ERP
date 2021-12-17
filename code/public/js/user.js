function rmclass (obj,name,css) {
    var selector = $('.myForm').find('input[name="'+name+'"]');
    if (obj.value.length > 0) {
        selector.removeClass(css);
    } else {
        selector.addClass(css);
    }
}
layui.config({ base: '../../layui/lay/modules/'}).extend({ formSelects: 'formSelects-v3'});
layui.use(['layer','form','element','laydate','table', 'formSelects'], function(){
    var layer = layui.layer,form = layui.form,laydate = layui.laydate,table = layui.table,element = layui.element,formSelects = layui.formSelects;

    var $ = layui.$, active = {
        userDel: function(){ //删除获取选中数据
            var checkStatus = table.checkStatus('EDtable'),
                data = checkStatus.data;
            var user_ids = '';
            var msg = '删除用户';
            Object.keys(data).forEach(function(key){
                user_ids +=data[key].user_id+',';
            });
            if (user_ids == ',' || user_ids == '') {
                layer.msg('请选中用户!');
                return false;
            }
            layer.confirm( '<span style="text-align: center;display: block;">确定'+msg+'</span>' , {
                btn: ['确定', '取消'] //按钮
            }, function () {
                $.ajax({
                    url: "/user/del",
                    type: 'get',
                    dataType: 'json',
                    data: {ids: user_ids},
                    success: function (res) {
                        //删除成功
                        if (res.code == 200) {
                            layer.msg(res.msg,{
                                time:1000,
                                end:function () {
                                    getUser();
                                }})
                            return false;
                        } else {
                            layer.alert(res.msg, {icon: 5});
                            return false;
                        }
                    }
                });
            }, function () {

            });
        }
    };

    form.verify({
        protocol: function (value, item) {
            if (!$(".protocol").is(":checked")) {
                return "请选择同意注册协议";
            }
        },
        password: function (value, item) {
            if (value == '') {
                return "密码不能为空";
            }
        },
        user_code:function (value, item) {
            if (value == '') {
                return "账号名不能为空";
            }
        },
        username: function (value, item) {
            if (value == '') {
                return "联系人姓名不能为空";
            }
        },
        mobileEmpty: function (value, item) {
            if (value == '') {
                return "手机号不能为空";
            }
        },
        company: function (value, item) {
            if (value == '') {
                return "公司名称不能为空";
            }
        },
        emailEmpty: function (value, item) {
            if (value == '') {
                return "邮箱不能为空";
            }
        },
    });

    //查询
    form.on('submit(searBtn)', function(data){
        getUser(data);
        return false;
    });


    //初始化
    function getUser(data){
        var data = data ? data : '';
        if (data == '') {
            var state = $('input[name="state"]:checked').val();
            var user_code = $('input[name="user_code"]').val();
            var params= new Object();
            params.state=state;
            params.user_code=user_code;
        } else {
            var params=data.field;
        }

        var index = layer.msg('数据请求中', {icon: 16});
        table.render({
            elem: '#EDtable'
            ,url:'/user/search'
            ,method: 'get'
            ,where: params
            ,cols: [[
                {checkbox: true}
                ,{field:'', title: '序号', width:50, type:'numbers'}
                ,{field:'user_code', title:'账号名称'}
                ,{field:'state',title:'是否启用', templet: function (d) {
                    return d.state == 1 ? '是' : '否';
                }}
                ,{field:'created_at', title:'创建时间'}
                ,{field:'updated_at', title:'更新时间'}
                ,{fixed: 'right', title:'操作', toolbar: '#userAction'}
            ]]
            ,limit:20
            ,page: true
            ,limits:[20,30,40,50]
            ,done:function () {
                layer.close(index);
            }
        });

        //监听表格复选框选择
        table.on('checkbox(EDtable)', function(obj){

        });

        $('.userDel').on('click', function(){
            var type = $(this).data('type');
            active[type] ? active[type].call(this) : '';
        });
    };

    //初始化获取数据
    if ($('input[name="user_code"]').is('.voin')) {
        getUser();
    }

    //新增
    form.on('submit(userNew)', function(){
        userAddOrEdit('新增子账号', '/user/add');
        return false;
    });

    form.on('submit(reset)', function(data){
        window.location.reload();
        return false;
    });

    //新增或编辑方法
    function userAddOrEdit(title, url){
        var btn = '添加';
        if (title == '编辑') {
            btn = '确定';
        }
        layer.open({
            title: title,
            type: 2,
            shadeClose: true,
            area: ["760px","700px"],
            content: [url,'no'],
            btn: [btn,'取消'],
            yes: function (index, layero) {
                var myForm = layer.getChildFrame('#myForm');
                //异常状态
                //必填项校验
                var exception_mes = [];
                var state = myForm.find('select[name="state"]').val();
                if (state == '') {
                    exception_mes.push('启用状态为必选项')
                }

                var user_code = myForm.find('input[name="user_code"]').val();
                if (user_code == '') {
                    exception_mes.push('账号名不能为空')
                }

                var username = myForm.find('input[name="username"]').val();
                if (username == '') {
                    exception_mes.push('联系人不能为空')
                }

                var password = myForm.find('input[name="password"]').val();
                if (typeof(password) != "undefined") {
                    if (password == '') {
                        exception_mes.push('密码不能为空')
                    }

                    var password_confirmation = myForm.find('input[name="password_confirmation"]').val();
                    if (password_confirmation == '') {
                        exception_mes.push('确认密码不能为空')
                    }
                    if (password != password_confirmation) {
                        exception_mes.push('两次密码不一致')
                    }
                    /*
                      1、长度：8 位≤长度≤50 位。
                      2： 不能使用中文字符。
                      3：等于 8 位时，必须包含数字、小写
                      字母、大写字母或特殊字符。
                      4、不能包含用户名。
                      5、不能有连续 3 次的字符或一些顺序
                      的字符。
                      7、不能包含空格。
                      */
                    if(!(password.length>=8 && password.length<=50)){
                        layer.msg('密码长度：8 位≤长度≤50 位。', {icon: 5});
                    }
                    var chinese =/[\u4e00-\u9fa5]/;
                    if(chinese.test(password)){
                        exception_mes.push('密码不能使用中文字符');
                    }
                    if (password.length ==8) {
                        var numberLetter =/[0-9a-z]+/,
                            Bigstr = /[A-Z]/,
                            str =/((?=[\x21-\x7e]+)[^A-Za-z0-9])/;
                        if(!(numberLetter.test(password) && (str.test(password) || Bigstr.test(password)))){
                            exception_mes.push('密码必须包含数字、小写字母、大写字母或特殊字符。');
                        }
                    }
                    if(password.indexOf(user_code)>= 0){
                        exception_mes.push('密码不能包含用户名');
                    }
                    var LxStr = function(str){
                        var arr = str.split('');
                        var flag = false;
                        for (var i = 1; i < arr.length-1; i++) {
                            var firstIndex = arr[i-1].charCodeAt();
                            var secondIndex = arr[i].charCodeAt();
                            var thirdIndex = arr[i+1].charCodeAt();
                            thirdIndex - secondIndex == 1;
                            secondIndex - firstIndex==1;
                            if((thirdIndex - secondIndex == 1)&&(secondIndex - firstIndex==1)){
                                flag = true;
                            }
                        }
                        return flag;
                    }
                    var  repectStr =/([0-9a-zA-Z])\1{2}/;
                    if(LxStr(password) || repectStr.test(password)){
                        exception_mes.push('密码不能有连续 3 次的字符或一些顺序的字符');
                    }
                    var blank =/\s/;
                    if(blank.test(password)){
                        exception_mes.push('密码不能包含空格');
                    }
                }
                var email = myForm.find('input[name="email"]').val();
                if (email == '') {
                    exception_mes.push('邮箱不能为空')
                }

                var mobile = myForm.find('input[name="mobile"]').val();
                if (mobile == '') {
                    exception_mes.push('手机号不能为空')
                }

                if (exception_mes.length > 0 ) {
                    var alertMessage = '请求参数错误<br/>';
                    for (var i=0; i < exception_mes.length; i++) {
                        alertMessage += exception_mes[i] + '<br/>'
                    }
                    layer.msg(alertMessage, {icon: 5});
                    return false;
                }

                $.ajax({
                    type: 'post',
                    dataType: 'json',
                    url: url,
                    data: myForm.serialize(),
                    success: function(response) {
                        if (response.Status) {
                            layer.msg(response.Message, {time:2000, icon: 1});
                            setTimeout(function () {
                                layer.close(index);
                                getUser();
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
                    error: function(e, x, d) {
                        layer.msg(d, {icon: 5})
                    }
                });
            }
        });
    }

    //监听工具条
    table.on('tool(EDtable)', function(obj){
        var data = obj.data;
        if (obj.event === 'edit') { //编辑
            userAddOrEdit('编辑', '/user/edit/'+data.user_id);
        }
        if (obj.event === 'editMenus') { //菜单权限
            menusOrShops('设置菜单权限', '/user/menus/'+data.user_id);
        }
        if (obj.event === 'editShops') { //店铺权限
            menusOrShops('设置店铺权限', '/user/shops/'+data.user_id);
        }
    });

    function menusOrShops(title, url){
        layer.open({
            title: title,
            type: 2,
            shadeClose: true,
            area: ["900px","800px"],
            content: [url],
            // content: [url,'no'],
            btn: ['确认','关闭'],
            yes: function (index, layero) {
                var myForm = layer.getChildFrame('#myForm');
                var menusPra = [];
                var nums = 0;
                var is_meus = false;
                if (title == '设置菜单权限') {
                    is_meus = true;
                }
                myForm.find('.chekid').each(function () {
                    if ($(this).prop('checked')) {
                        nums ++;
                        if (is_meus) {
                            if (!menusPra.includes($(this).attr('data-parid'))) {
                                menusPra.push($(this).attr('data-parid'))
                            }
                        }
                    }
                });
                if (nums == 0 ) {
                    layer.msg('权限配置不能为空', {icon: 5});
                    return false
                }
                if (is_meus) {
                    for ( var i = 0; i <menusPra.length; i++){
                        var hiddenInput = '<input type="hidden" name="checkMenusPar[]" value = '+menusPra[i]+' class="hided">';
                        myForm.append(hiddenInput);
                    }
                }
                $.ajax({
                    type: 'post',
                    dataType: 'json',
                    url: url,
                    data: myForm.serialize(),
                    success: function(response) {
                        if (response.Status) {
                            layer.msg(response.Message, {time:2000, icon: 1});
                            setTimeout(function () {
                                layer.close(index);
                                //权限配置不重新加载
                                // getUser();
                            },2000);
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
                    error: function(e, x, d) {
                        layer.msg(d, {icon: 5})
                    }
                });
            }
        });
    }

    //全选
    form.on('checkbox(allsele)', function (data) {
        //店铺 有单独逻辑
            $(this).parents('.checkboxGroup').find("input[name='check[]']").each(function () {
                this.checked = data.elem.checked;
            });
        form.render('checkbox');
    });
    // 单个选中
    form.on('checkbox(oneCho)', function (data) {
        var i = 0;
        var j = 0;
        var menu = data.elem.dataset.menu;
        var menu_class = menu+'_checkboxGroup';
        var menuAll_class = menu+'_selectAll';
        //新增隐藏域
            $(this).parents('.'+menu_class).find("input[name='check[]']").each(function () {
                if( this.checked === true ){
                    i++;
                }
                j++;
            });
            if( i == j ){
                $("."+menuAll_class).prop("checked",true);
                form.render('checkbox');
            }else{
                $("."+menuAll_class).removeAttr("checked");
                form.render('checkbox');
            }
    });

    //重新渲染适用对象
    function handlerObjectIds(responseData) {
        formSelects.render({
            name: 'objectIds',
            on: function(data, arr) { //监听数据变化
                handlerObjectIdsVal(arr);
            },
            data: {
                arr: responseData,
                name: 'name',
                val: 'roleId-id',
                selected: 'sel',
                disabled: 'dis'
            }
        })
    }

    //处理选择对象值
    function handlerObjectIdsVal(arr) {
        var str = arr.map(function (val) {
            return val.val;
        }).join(',');
        $('.objectIds').val(str);
    }

    //确认修改
    form.on('submit(editPassword)', function(){
        var myForm = $('#myForm');
        var exception_mes = [];
        var password = myForm.find('input[name="password"]').val();
        if (password == '') {
            exception_mes.push('密码不能为空')
        }

        var password_confirmation = myForm.find('input[name="password_confirmation"]').val();
        if (password_confirmation == '') {
            exception_mes.push('确认密码不能为空')
        }
        if (password != password_confirmation) {
            exception_mes.push('两次密码不一致')
        }

        if (exception_mes.length > 0 ) {
            var alertMessage = '请求参数错误<br/>';
            for (var i=0; i < exception_mes.length; i++) {
                alertMessage += exception_mes[i] + '<br/>'
            }
            layer.msg(alertMessage, {icon: 5});
            return false;
        }
        $.ajax({
            type: 'post',
            dataType: 'json',
            url: '/user/editPassword',
            data: myForm.serialize(),
            success: function(response) {
                if (response.Status) {
                    layer.msg(response.Message, {time:2000, icon: 1});
                    setTimeout(function () {
                        parent.location.reload();
                    },1000);
                } else {
                    var responseData = response.Data;
                    var alertMessage = response.Message;
                    if (responseData != null) {
                        for (var i=0; i < responseData.length; i++) {
                            alertMessage += responseData[i] + '<br/>'
                        }
                    }
                    layer.msg(alertMessage, {icon: 5});
                }
            },
            error: function(e, x, d) {
                layer.msg(d, {icon: 5})
            }
        });
        return false;
    });
});