(function() {
    var scrollSetp = 500,
    operationWidth = 90,
    leftOperationWidth = 30,
    animatSpeed = 150,
    linkframe = function(url, value) {
        $("#menu-list a.active").removeClass("active");
        $("#menu-list a[data-url='" + url + "'][data-value='" + value + "']").addClass("active");
        $("#page-content iframe.active").removeClass("active");
        $("#page-content .iframe-content[data-url='" + url + "'][data-value='" + value + "']").addClass("active");
        $("#menu-all-ul li.active").removeClass("active");
        $("#menu-all-ul li[data-url='" + url + "'][data-value='" + value + "']").addClass("active")
    },
    move = function(selDom) {
        var nav = $("#menu-list");
        var releft = selDom.offset().left;
        var wwidth = parseInt($("#page-tab").width());
        var left = parseInt(nav.css("margin-left"));
        if (releft < 0 && releft <= wwidth) {
            nav.animate({
                "margin-left": (left - releft + leftOperationWidth) + "px"
            },
            animatSpeed)
        } else {
            if (releft + selDom.width() > wwidth - operationWidth) {
                nav.animate({
                    "margin-left": (left - releft + wwidth - selDom.width() - operationWidth) + "px"
                },
                animatSpeed)
            }
        }
    },
    createmove = function() {
        var nav = $("#menu-list");
        var wwidth = parseInt($("#page-tab").width());
        var navwidth = parseInt(nav.width());
        if (wwidth - operationWidth < navwidth) {
            nav.animate({
                "margin-left": "-" + (navwidth - wwidth + operationWidth) + "px"
            },
            animatSpeed)
        }
    },
    closemenu = function() {
        $(this.parentElement).animate({
            "width": "0",
            "padding": "0"
        },
        0,
        function() {
            // var kbthis = $(this);
            // if (kbthis.hasClass("active")) {
            //     var linext = kbthis.next();
            //     if (linext.length > 0) {
            //         linext.click();
            //         move(linext);
            //     } else {
            //         var liprev = kbthis.prev();
            //         if (liprev.length > 0) {
            //            $(this).prev().mousedown();
            //             move(liprev);
            //         }
            //     }
            // }
            // this.remove();
            // $("#page-content .iframe-content[data-url='" + kbthis.data("url") + "'][data-value='" + kbthis.data("value") + "']").remove();
        });
        event.stopPropagation()
    },
    init = function() {
        $("#page-prev").on("click",
        function() {
            var nav = $("#menu-list");
            var left = parseInt(nav.css("margin-left"));
            if (left !== 0) {
                nav.animate({
                    "margin-left": (left + scrollSetp > 0 ? 0 : (left + scrollSetp)) + "px"
                },
                animatSpeed)
            }
        });
        $("#page-next").on("click",
        function() {
            var nav = $("#menu-list");
            var left = parseInt(nav.css("margin-left"));
            var wwidth = parseInt($("#page-tab").width());
            var navwidth = parseInt(nav.width());
            var allshowleft = -(navwidth - wwidth + operationWidth);
            if (allshowleft !== left && navwidth > wwidth - operationWidth) {
                var temp = (left - scrollSetp);
                nav.animate({
                    "margin-left": (temp < allshowleft ? allshowleft: temp) + "px"
                },
                animatSpeed)
            }
        });
        $("#page-operation").on("click",
        function() {
            var menuall = $("#menu-all");
            if (menuall.is(":visible")) {
                menuall.hide()
            } else {
                menuall.show()
            }
        });
        $("body").on("mousedown",
        function(event) {
            if (! (event.target.id === "menu-all" || event.target.id === "menu-all-ul" || event.target.id === "page-operation" || event.target.id === "page-operation" || event.target.parentElement.id === "menu-all-ul")) {
                $("#menu-all").hide();
            }
        })
    };
    $.fn.tab = function() {
        init();
        this.on("click",
        function() {
            var linkUrl = $(this).attr("href");
            var linkHtml = this.text.trim();
            var selDom = $("#menu-list a[data-url='" + linkUrl + "'][data-value='" + linkHtml + "']");
            var parentId = $(this).attr('data-id');
            if (selDom.length === 0) {
                var iel = $("<i>", {
                    "class": "menu-close"
                }).on("mouseup", closemenu);
                $("<a>", {
                    "html": linkHtml,
                    "href": "javascript:void(0);",
                    "data-url": linkUrl,
                    "data-value": linkHtml,
                    'data-parent-id': parentId
                }).on("click",function() {
                    var jthis = $(this);
                    linkframe(jthis.data("url"), jthis.data("value"))
                }).append(iel).appendTo("#menu-list");
                $("<iframe>", {
                    "class": "iframe-content",
                    "data-url": linkUrl,
                    "data-value": linkHtml,
                    src: linkUrl
                }).appendTo("#page-content");
                $("<li>", {
                    "html": linkHtml,
                    "data-url": linkUrl,
                    "data-value": linkHtml
                });
                createmove()
            } else {
                move(selDom)
            }
            linkframe(linkUrl, linkHtml);
            return false
        });
        return this
    }
   
})();
$(function(){

    // !--控制左侧菜单(无菜单删除)
    $('.homebtn').click(function() {
        $('#page-tab').removeClass('formenu');
        $('.defaultIframe').addClass('active');
    });

    $('.kbin-menu .down_menu .kba').click(function(){
        $('#page-tab').addClass('formenu');
    });

    $(document).on('click', '#menu-list a:not(.defaultTab)', function(){
        $('#page-tab').addClass('formenu');
        
    });
    // ---结束

    $(document).on('click','#menu-list .menu-close',function(e){
        var kbthis = $(this).parent('a');
        var nextkb = $(this).parent('a').next();
        var prevkb = $(this).parent('a').prev();
        if(kbthis.hasClass('active')){
            if(nextkb.length > 0){
                nextkb.click();

            }else{
                if(prevkb.length > 0){
                    prevkb.click();

                }
            }
        }
        $(this).parent('a').remove();
        $("#page-content .iframe-content[data-url='" + kbthis.data("url") + "'][data-value='" + kbthis.data("value") + "']").remove(); 

        // 控制左侧菜单(无菜单删除)
        if($('.defaultTab').hasClass('active')){
            $(this).parents('#page-tab').removeClass('formenu');
        }

        e.stopPropagation();
        e.preventDefault();
    });

    // iframe里的链接点击新开
    $(document).on('click', '.inlayer', function(){
        $('#menu-list a').removeClass('active');
        $('.iframe-content').not('.inlayerUrl').removeClass('active');
        $(this).addClass('active');
        var inythis = $(this);
        $("#page-content .iframe-content[data-url='" + inythis.data("url") + "'][data-value='" + inythis.data("value") + "']").addClass('active');
    });
})

