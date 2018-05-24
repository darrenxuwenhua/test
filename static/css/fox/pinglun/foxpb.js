//蓝狐。。其实你们很坏，所以有时不想干了
//快网
$(function(){
	$('#containerpub').show().BlocksIt({
		numOfCol:4,
		offsetX: 8,
		offsetY: 8,
		blockElement: '.grid'
	});
	$("img.lazy").lazyload();	
	var loading = false;
	$(window).scroll(function(){			
		if ($(document).height() - $(this).scrollTop() - $(this).height()<120){
			loadMore();
			if($('#containerpub').attr('data-page')!=0){
				$('#nole').fadeIn();
				$("#nole").text("加载中……");				
			}
        }
		function loadMore() {
		if (loading === false) {
			loading = true;
			var pubpage=$('#containerpub').attr('data-page');
			var puburl=G_BASE_URL + '/pinglun/ajax/list_read/__page';			
		    var foxurl=puburl + '-' +  (Number(pubpage)+1);
			$.get(foxurl , function (result){
				if ($.trim(result) != ''){
					$('#containerpub').attr('data-page', parseInt($('#containerpub').attr('data-page')) + 1);
					$('#containerpub').append(result);	
					$('#containerpub').show().BlocksIt({
						numOfCol:4,
						offsetX: 8,
						offsetY: 8,
						blockElement: '.grid'
					});
					$("img.lazy").lazyload();	
					loading = false;  
					$('#nole').fadeOut();
				}else{
					$('#containerpub').attr('data-page', 0);
					$("#nole").text("已经没有可显示的啦");
					$('#nole').fadeIn();
				}
			});
			}else{
				return;
			}
		}
	});
	var currentWidth = 1000;
	$(window).resize(function() {
		var winWidth = $(window).width();
		var conWidth;
		if(winWidth < 300) {
			conWidth = 250;
			col = 1
		} else if(winWidth < 600) {
			conWidth = 500;
			col = 2
		} else if(winWidth < 800) {
			conWidth = 750;
			col = 3
		} else {
			conWidth = 1000;
			col = 4;
		}
		
		if(conWidth != currentWidth) {
			currentWidth = conWidth;
			$('#containerpub').width(conWidth);
			$('#containerpub').BlocksIt({
				numOfCol: col,
				offsetX: 8,
				offsetY: 8
			});
		}
	});
});