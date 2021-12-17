$(function(){
	$(".kbin_tab .tabTit li").click(function(){
	    $(this).addClass('curr').siblings().removeClass('curr');
	    var index = $(this).index();
	    var ip = $(this).parents('.kbin_tab');	    
	    ip.find('.tabBody .tim').hide();
	    ip.find('.tabBody .tim:eq('+index+')').show();
	});

	$('body').on('click', '.balance .eye', function() {
		var num = '1561465.00';
		if ($(this).hasClass('closeEye')) {
			$(this).removeClass('closeEye');
			$(this).addClass('openEye');
			$('#numbr').text(num).addClass('show');
		}else{
			$(this).addClass('closeEye');
			$(this).removeClass('openEye');
			$('#numbr').text('******').removeClass('show');
		}		

	});	

	$('.eyepw').click(function(){
	  	if($(this).hasClass('closeEye')){
	  		$(this).removeClass('closeEye').addClass('openEye');
	  		$(this).parents('.pwtro').find('.pw1').attr('type','text');
	  	}else{
	  		$(this).removeClass('openEye').addClass('closeEye');
	  		$(this).parents('.pwtro').find('.pw1').attr('type','password');
	  	}
	  });	

	$('body').on('click','.highqy',function(){
		$(this).parents('.ul_search').find('.advQuery').addClass('liflex');
		$(this).text('隐藏高级查询').addClass('SimpleBtn').removeClass('highqy');
	});
	$('body').on('click','.SimpleBtn',function(){
		$(this).parents('.ul_search').find('.advQuery').removeClass('liflex');
		$(this).text('显示高级查询').addClass('highqy').removeClass('SimpleBtn');
	})

	$('.tabul li').click(function(){
		$(this).addClass('curr').siblings().removeClass('curr');
	});

	$('.regForm .intro input').focus(function(){
		$(this).parents('.intro').find('.tips_group').show();
		$(this).parents('.intro').find('.tip_alone').hide();
	});
	$('.regForm .intro input').blur(function(){
		if($(this).val() == ''){
			$(this).parents('.intro').find('.tips_group').hide();
			$(this).parents('.intro').find('.tip_alone').show();
			$(this).parents('.intro').find('.pass').remove();
		}else{
			if($(this).parents('.intro').find('.pass').length >= 1){
				return false;
			}else{
				$(this).parents('.intro').append('<em class="pass"></em>');				
			}
		}
		
	});

	
})

function outpg(url,id,tit){
	var page = '<div class="layui-tab-item layui-show" lay-item-id="'+ id +'"><iframe class="iniframe" src="' + url + '"></iframe></div>';
	var tab = '<li class="cyn-new layui-this" lay-id="'+id+'">' + '<i class="fa fa-user" aria-hidden="true"></i>' + tit + '<i class="layui-icon layui-unselect layui-tab-close">&#x1006;</i>' + '</li>';
	
	$(window.parent.document).find('.layui-tab-title li').removeClass('layui-this');
	$(window.parent.document).find('.layui-tab-title').append(tab);
	$(window.parent.document).find('.layui-tab-content').find('.layui-tab-item').removeClass('layui-show');
	$(window.parent.document).find('.layui-tab-content').append(page);
}