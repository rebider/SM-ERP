let menuObj = {};
let closebtn = '<div class="subSwitch closeSub" style="position: fixed;left: 200px;top: 50%;margin-top: -20px;cursor: pointer;\n' +
    '    display: block;width: 15px;height: 45px;right: 0;background: #36a2f5;">\n' +
    '        <i class="layui-icon layui-icon-left" style="color: white; line-height: 45px;"></i></div>';
//关闭侧边栏事件
$(document).on('click','.closeSub',function(){
    $('.submenu-left').animate({'margin-left': -200}, 600);
    $('#page-tab').removeClass('formenu').animate({left: 0}, 600);
    $('#page-content').removeClass('tolife');
    $(this).addClass('openSub');
    $(this).removeClass('closeSub');
    $(this).animate({left: 0}, 600);
    $(this).find('i').removeClass('layui-icon-left').addClass('layui-icon-right')
    let contentWrap = $('.iframe-content').contents().find('.content-wrapper');
    contentWrap.animate({margin: "40px 0 0 0"}, 600);
});
//打开侧边栏事件
$(document).on('click','.openSub',function(){
    $('.submenu-left').animate({'margin-left': 0}, 600);
    $('#page-tab').addClass('formenu').animate({left: '200px'}, 600);
    $('#page-content').addClass('tolife');
    $(this).addClass('closeSub');
    $(this).removeClass('openSub');
    $(this).animate({left: "200px"}, 600);
    $(this).find('i').removeClass('layui-icon-right').addClass('layui-icon-left')
    let contentWrap = $('.iframe-content').contents().find('.content-wrapper');
    contentWrap.animate({margin: "40px 0 0 200px"}, 600);
});
//初始化侧边栏菜单数据
$(document).ready(function () {
    $.get("/getUserMenu", {}, function (e) {
        if (e.code != 1) {
            // alert('网络错误，快捷菜单初始化失败');
            return false;
        }
        $.each(e.data, function (firstKey, firstValue) {
            menuObj[firstValue.id] = !firstValue._child ? null : firstValue._child;
        });
    });
});
//点击菜单事件
$(".jumpMenu").click(function () {
    let _this = $(this);
    let id = _this.attr('data-id');
    let menuName = _this.attr('data-parent-id');
    //没有侧边栏的事件
    if (!id in menuObj)  {
        return false;
    }

    //拼装侧边栏
    let compose = "";
    //本地缓存中有该菜单栏的数据情况下，直接调用
    if (sessionStorage[menuName] && (parseInt(id) !== 4)) {
        compose = sessionStorage[menuName];

    } else {
        //遍历当前菜单栏的子条目并且拼装
        $.each(menuObj[id], function (firstKey, firstValue) {
            compose += '<div class="col"><h2>'+ firstValue.name +'</h2><ul>';

            let liElement = null;
            if ('_child' in firstValue) {
                liElement = '';
                $.each(firstValue._child, function (childKey, childValue) {
                    let hasCount = '';
                    if (childValue.url !== '' && childValue.count) {
                        hasCount += '('+ childValue.count +')';
                    }
                    var name = childValue.menu_name?childValue.menu_name:childValue.name;
                    let urlStatus = childValue.url == '' ?
                        '<a href="javascript:void(0);">'+ childValue.name + hasCount + '</a>' :
                        '<a href="javascript:pgout(\''+ childValue.url +'\', \'' + name +'\', \''+ firstValue.parent_id +'\');" data-id="'+ firstValue.parent_id +'">'+ childValue.name + hasCount +'</a>';
                    liElement += '<li>'+ urlStatus +'</li>'
                });
            }
            compose += liElement + '</ul></div>';
        });
        //存储到浏览器
        sessionStorage.setItem(menuName, compose);
    }
    //展示
    $(".submenu-left").empty().append(closebtn + compose).show(150);
    if (parseInt($(".submenu-left").css('margin-left')) <= 0) {
        $('#page-tab').addClass('formenu').animate({left: '200px'}, 600);
        $('.submenu-left').animate({'margin-left': 0}, 600);
    }
});

//点击首页的时候，收回侧边栏
$('.homebtn').click(function () {
    $(".submenu-left").hide();
});

//切换iframe的时候，根据相应的标题展示对应的菜单栏
$(document).on('click', '#menu-list a', function () {
    let _this = $(this);
    let menuName = _this.attr('data-parent-id');
    let subMenuObj = $('.submenu-left');


    //iframe内容调整动画
    let subMenuAdjusting = function (marginAdjusting = 0) {
        let contentWrap = $('.iframe-content').contents().find('.content-wrapper');
        contentWrap.animate({margin: "40px 0 0 " + marginAdjusting}, 250);
    };

    if (menuName == '') {
        subMenuAdjusting();
    } else {
        //当非首页有菜单栏的情况下，需要重新调整页面元素
        if (sessionStorage[menuName]) {
            let compose = sessionStorage[menuName];
            subMenuObj.empty().append(closebtn + compose).show(250);

            if (parseInt(subMenuObj.css('marginLeft')) <= 0) {
                subMenuAdjusting('200px');
                subMenuObj.animate({'margin-left': 0}, 150);
                $('#page-tab').addClass('formenu').animate({left: '200px'}, 250);
            } else {
                subMenuAdjusting('200px');
            }
        } else {
            subMenuAdjusting();
            subMenuObj.animate({'margin-left': -200}, 250);
            $('#page-tab').removeClass('formenu').animate({left: 0}, 250);
            $(".closeSub").animate({left: 0}, 250);
        }
    }
});

function showShortCutJumpMenu(id) {
    let subMenuObj = $(".submenu-left", parent.document);
    //iframe内容调整动画
    let subMenuAdjusting = function (marginAdjusting = 0) {
        let contentWrap = $('.iframe-content').contents().find('.content-wrapper');
        contentWrap.animate({margin: "40px 0 0 " + marginAdjusting}, 250);
    };
    if (sessionStorage[id]) {
        let compose = sessionStorage[id];
        subMenuObj.empty().append(closebtn + compose).show(250);
        if (parseInt(subMenuObj.css('marginLeft')) <= 0) {
            subMenuAdjusting('200px');
            subMenuObj.animate({'margin-left': 0}, 150);
            $('#page-tab', parent.document).addClass('formenu').animate({left: '200px'}, 250);
        } else {
            subMenuAdjusting('200px');
        }
    } else {
        if (!menuObj[id]) {
            subMenuAdjusting();
            subMenuObj.animate({'margin-left': -200}, 250);
            $('#page-tab', parent.document).removeClass('formenu').animate({left: 0}, 250);
            $(".closeSub", parent.document).animate({left: 0}, 250);
        } else {
            let compose = '';
            $.each(menuObj[id], function (firstKey, firstValue) {
                compose += '<div class="col"><h2>'+ firstValue.name +'</h2><ul>';

                let liElement = null;
                if ('_child' in firstValue) {
                    liElement = '';
                    $.each(firstValue._child, function (childKey, childValue) {
                        let hasCount = '';
                        if (childValue.url == '' && childValue.count) {
                            hasCount += '('+ childValue.count +')';
                        }
                        var name = childValue.menu_name?childValue.menu_name:childValue.name;
                        let urlStatus = childValue.url == '' ?
                            '<a href="javascript:void(0);">'+ childValue.name + hasCount + '</a>' :
                            '<a href="javascript:pgout(\''+ childValue.url +'\', \'' + name +'\', \''+ firstValue.parent_id +'\');" data-id="'+ firstValue.parent_id +'">'+ childValue.name +'</a>';
                        liElement += '<li>'+ urlStatus +'</li>'
                    });
                }
                compose += liElement + '</ul></div>';
            });
            //存储到浏览器
            sessionStorage.setItem(id, compose);
            subMenuObj.empty().append(closebtn + compose).show(150);
            if (parseInt(subMenuObj.css('margin-left')) <= 0) {
                $('#page-tab', parent.document).addClass('formenu').animate({left: '200px'}, 600);
                subMenuObj.animate({'margin-left': 0}, 600);
            }
        }

    }
}