/**
 * Created by rawraw on 2017/1/22.
 */

function tabSwitch(a,b,fn,url){
	$(a).off('click').on('click',function(){
		var this_ = this ;
		var box = $(b).parent();
		var index = $(this_ ).index();
		var ww = $(b).parent().width();
		$(this_ ).addClass('active' );
		$(this_ ).siblings(a).removeClass('active');
		$(b).removeClass('hidden');
		ww = ww * index;
		loadScroll();
		$(b).eq(index).siblings(b).addClass('hidden');
		box.stop().animate({left: -ww +'px'},300,function(){
			// $(b).eq(index).siblings(b).addClass('hidden');
			setCookie( 'tab', index );
			if(fn){
				var tab = $('.active' ).index() + 1;
				fn(tab,url,7,5);
			}
		});

	})
}
function tabRecord(a,b){
	var tab = getCookie('tab');
	if(!tab){
		tab = 0
	}
	var index = tab;
	var box = $(b).parent();
	var ww = $(b).parent().width();
	$(a).eq(index).addClass('active');
	$(a).eq(index).siblings(a).removeClass('active');
	$(b).removeClass('hidden');
	ww = ww * index;
	box.css({left: -ww +'px'});
	setTimeout(function(){
		$(b).eq(index).siblings(b).addClass('hidden');
	},100);
	//清除tab值
	// pushHistory();
	// window.addEventListener( "popstate", function(){
	// 	delCookie( 'tab' );
	// 	window.history.go( -1 );
	// }, false );
}

function tagSwitch(a,b,fn,url){
	$(a).off('click').on('click',function(){
		var this_ = this ;
		var box = $(b).parent();
		var index = $(this_ ).index();
		var ww = $(b).parent().width();
		$(this_ ).addClass('active' );
		$(this_ ).siblings(a).removeClass('active');
		$(b).removeClass('hidden');
		ww = ww * index;
		loadScroll();
		$(b).eq(index).siblings(b).addClass('hidden');
		box.stop().animate({left: -ww +'px'},300,function(){
			// $(b).eq(index).siblings(b).addClass('hidden');
			setCookie( 'tag', index );
			if(fn){
				var tab = $('.active' ).index() + 1;
				fn(tab,url,7,5);
			}
		});

	})
}
function tagRecord(a,b){
	var tab = getCookie('tag');
	if(!tab){
		tab = 0
	}
	var index = tab;
	var box = $(b).parent();
	var ww = $(b).parent().width();
	$(a).eq(index).addClass('active');
	$(a).eq(index).siblings(a).removeClass('active');
	$(b).removeClass('hidden');
	ww = ww * index;
	box.css({left: -ww +'px'});
	setTimeout(function(){
		$(b).eq(index).siblings(b).addClass('hidden');
	},100);
	//清除tab值
	pushHistory();
	window.addEventListener( "popstate", function( e ){
		delCookie( 'tag' );
		window.history.go( -1 );
	}, false );
}
function pushHistory(){
	var sta = {
		title: "title"
	};
	if( window.history.state === null ){
		window.history.pushState( sta, "title" );
	}
}
function setCookie( name, value ){
	var Days = 30;
	var exp = new Date();
	exp.setTime( exp.getTime() + Days * 24 * 60 * 60 * 1000 );
	document.cookie = name + "=" + value + ";expires=" + exp.toGMTString();
}
function getCookie( name ){
	var arr, reg = new RegExp( "(^| )" + name + "=([^;]*)(;|$)" );
	if( arr = document.cookie.match( reg ) )
		return arr[ 2 ];
	else
		return null;
}
function delCookie( name ){
	var exp = new Date();
	exp.setTime( exp.getTime() - 1 );
	var cval = getCookie( name );
	if( cval != null )
		document.cookie = name + "=" + cval + ";expires=" + exp.toGMTString();
}
function moveanyway(){
	var oW,oH,touch_start,touch_end;
	var block = document.getElementById("publish");
	block.addEventListener("touchstart", function(e) {
		var touches = e.touches[0];
		touch_start = touches.clientX;
		oW = touches.clientX - block.offsetLeft;
		oH = touches.clientY - block.offsetTop;
		//阻止页面的滑动默认事件
		document.addEventListener("touchmove",defaultEvent,false);
	},false);
	block.addEventListener("touchmove", function(e) {
		var touches = e.touches[0];
		var oLeft = touches.clientX - oW;
		var oTop = touches.clientY - oH;
		touch_end = touches.clientX;
		if(oLeft < 0) {
			oLeft = 0;
		}else if(oLeft > document.documentElement.clientWidth - block.offsetWidth) {
			oLeft = (document.documentElement.clientWidth - block.offsetWidth);
		}
		if(oTop < 0) {
			oTop = 0;
		}else if(oTop > document.documentElement.clientHeight - block.offsetHeight) {
			oTop = (document.documentElement.clientHeight - block.offsetHeight);
		}
		block.style.left = oLeft+ "px";
		block.style.top = oTop + "px";
		e.preventDefault();
	},false);
	block.addEventListener("touchend",function() {
		document.removeEventListener("touchmove",defaultEvent,false);
		if(touch_start != touch_end && touch_end){
			var d = document.documentElement.clientWidth - block.offsetWidth;
			if(block.offsetLeft<(document.documentElement.clientWidth - block.offsetWidth)/2) {
				d = 0 ;
			}
			$(block).animate({
				left:d+'px'
			},300)
		}
	},false);
	function defaultEvent(e) {
		e.preventDefault();
	}
}
function imgresize(){
	setTimeout(function(){
		var img = $('.img img' );
		img.each(function(){
			if($(this).width() == $(this).height()){
				$(this).height('78px');
				$(this).width('78px');
			}else if($(this).width() > $(this).height()){
				$(this).height('78px' ).css({'left':-$(this).width()/2+78/2});
			}else{
				$(this).width('78px').css({'top':-$(this).height()/2+78/2});
			}
		});
	},100);
}
function imgfit(){
	var ups = $('.up' );
	var len = ups.length;
	for(var i = 0 ; i <len ;i++){
		var up = ups.eq(i);
		var path = up.attr('data-tab');
		var image = new Image();
		var ww = up.width();
		image.src = path ;
		up.css({"background-image":'url('+path+')'});
		image.onload = function(){
			if(image.width > image.height){
				up.css({"background-size":'auto '+ww +'px'});
			}else{
				up.css({"background-size":ww +'px'+' auto '});
			}
		};
	}

}
//禁用浏览器返回
function fobidden_back() {
	//防止页面后退
	history.pushState(null, null, document.URL);
	window.addEventListener('popstate',back_common)
}
//启用浏览器返回
function enable_back() {
	history.go(-1);
	window.removeEventListener('popstate',back_common)
}
function back_common() {
	history.pushState(null, null, document.URL);
}