/**
 * 
 * @authors Korbin 280674094@qq.com
 * @date    2018-12-27 18:43:11
 * @version $Id$
 */

$(function(){
	//滑动验证码 
	$("#slidverify").slider({
		width: 360, // width
		height: 40, // height
		sliderBg: "rgb(232, 232, 232)", // 滑块背景颜色
		color: "#999", // 文字颜色
		fontSize: 14, // 文字大小
		bgColor: "#33CC00", // 背景颜色
		textMsg: "按住滑块，拖拽验证", // 提示文字
		successMsg: "验证成功", // 验证成功提示文字
		successColor: "#fff", // 滑块验证成功提示文字颜色
		time: 400, // 返回时间
		callback: function(result) { // 回调函数，true(成功),false(失败)
			
		}
	});

	
})