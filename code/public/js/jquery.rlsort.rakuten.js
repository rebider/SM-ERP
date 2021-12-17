/**
 * 乐天分类获取
 * Author: zt12779
 * created at: 2019年6月17日16:30:12
 * @type {string}
 */


//select内部HTML String
let rlAreaCont = "";
//乐天分类节点 Object
let categoryNode = null;
//已选择的分类 Object
let levelSelectedContent = {};

//程序主入口
function rakutenCategory(initialize = true, parentId = 0, level = 1, currentIndex = 0) {
    //Ajax请求
    getContent(parentId)

    //循环节点，把节点拼接起来
    rlAreaCont = "";
    $.each(categoryNode, function (k, v) {
        rlAreaCont += '<li onClick="rakutenCategory( false,' + v.genreId + ',' + v.categories_lv + ',' + k + ');" data-id="' + v.genreId + '"><a href="javascript:void(0)">' + v.genreName + '</a></li>';
    });

    //乐天分类看展示数据
    let selectedString = [];
    //下一级分类
    let nextsort = ++level;
    //需要移除“on”标记的分类
    let classRemove = level - 1;
    //已选择的选择框
    let sortClass = $("#rlsort" + classRemove + " li");

    //初始化时不进行任何数据处理
    if (initialize) {
        $("#rlsort1").html(rlAreaCont).show();
        return false;
    } else {
        //如果后面有子分类
        if (categoryNode.length > 0) {
            $("#rlsort" + nextsort).html(rlAreaCont).show();
        }
        //设置选中样式
        sortClass.eq(currentIndex).addClass("on").siblings("li").removeClass("on");

        //如果重新选择已勾选的父类菜单
        if (levelSelectedContent[classRemove]) {
            //矫正当前菜单等级
            let correctCurrentLevel = level - 1;
            //重写当前节点的值
            levelSelectedContent[correctCurrentLevel] = sortClass.eq(currentIndex).text();

            //如果当前矫正节点为1，重置第二级分类，隐藏第二级以后的选择框
            if (correctCurrentLevel == 1) {
                $("#rlsort" + (correctCurrentLevel + 2)).html('').hide();
                $("#rlsort" + (correctCurrentLevel + 3)).html('').hide();
                //清除已选的非当前等级分类数据
                $.each(levelSelectedContent, function (k, v) {
                    if (k > (correctCurrentLevel+1)) {
                        delete levelSelectedContent[k]
                    }
                })
            }
            //如果当前矫正节点为2，重置后面的分类
            if (correctCurrentLevel == 2) {
                $("#rlsort" + (correctCurrentLevel + 2)).html('').hide();
                $.each(levelSelectedContent, function (k, v) {
                    if (k >= (correctCurrentLevel + 1)) {
                        delete levelSelectedContent[k]
                    }
                })
            }

            if (correctCurrentLevel == 3) {
                $.each(levelSelectedContent, function (k, v) {
                    if (k > correctCurrentLevel) {

                        delete levelSelectedContent[k];
                        // $("#rlsort" + (correctCurrentLevel + 1)).html('').hide();
                    }
                })
            }

        } else {
            //设置新添加的分类
            levelSelectedContent[level - 1] = sortClass.eq(currentIndex).text();
        }

        //把分类名从对象中取出来
        $.each(levelSelectedContent, function (k, v) {
            selectedString.push(v)
        });
        //拼接分类数组
        $("#rlselectedSort").html(selectedString.join(' > '));
    }

    //设置最终选定的分类ID
    if (level == 4 || categoryNode.length <= 0) {
        $("#rakuten_category_id").val(sortClass.eq(currentIndex).attr('data-id'))
    }
    if (categoryNode.length <= 0) {
        $("#rlsort"+ level).html('').hide();
    }
}

//Ajax请求分类
function getContent(parentId) {
    $.ajax({
        url: "/Goods/onlineRakuten/getCategory"
        , type: "get"
        , dataType: "json"
        , async: false
        , data: {
            'parentId': parentId
        }
        , success: function (res) {
            if (res.code == 0) {
                categoryNode = res.data
            } else {
                layer.msg(res.msg, {icon: 6});
            }
        }
        , errors: function (e, x, t) {
            layer.msg('服务器开小差了，请重试', {icon: 6});
        }
    });
}

function restoreNode()
{
    let sortedElement = $(".wareSort").find('ul');
    $.each(sortedElement, function (k, v) {
        let selectedElement = $(this).find('.on');
        levelSelectedContent[k+1] = selectedElement.text();
    });
}

//初始化
// rakutenCategory();