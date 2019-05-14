$(document).ready(function(){
	
  // 1. set Navi animation
  setNaviAnim();
  
  // 2. set Navi click event
  setNaviEvent();

  // 3. set search button event  
  setSrhEvent();
  
  // 4. get the visited num
  setVisitedNum();
  
  // 5. check logged or not
  checkLogged();
  
  // 6. check mobile or PC
  checkMobile();
});


// 1. set Navi animation
function setNaviAnim(){
	/*	简化版示例	*/
	
	$("#navi li a").wrapInner( '<span class="out"></span>' );
	
	$("#navi li a").each(function() {
		$('<span class="over">' +  $(this).text() + '</span>' ).appendTo( this );
	});

	$("#navi li a").hover(function() {
		$(".out",this).stop().animate({'top':'29px'},200); // 向下滑动 - 隐藏
		$(".over",this).stop().animate({'top':'0px'},200); // 向下滑动 - 显示

	}, function() {
		$(".out",this).stop().animate({'top':'0px'},200); // 向上滑动 - 显示
		$(".over",this).stop().animate({'top':'-29px'},200); // 向上滑动 - 隐藏
	});
}


// 2. set Navi click event
function setNaviEvent(){
	$("#login").click(function(){
		location.href = 'users/login.html';
	});
	
	
	$("#register").click(function(){
		location.href = 'users/register.html';
	});	
	
	$("#logout").click(function(){
		$.ajax({
			url : "users/logout.php",
			success : function(response){
				location.href = './';
			}
		});
		
		
	});		
}


// 3. set search button event  
function setSrhEvent(){
  $(".search-button").click(function(){
//  htmlobj=$.ajax({url:"search.php?abstract="+ $("#abstract").val(),async:false});
//  	$(".content").html(htmlobj.responseText);
//  	
	// check input length
	var abs_content =  $("#abstract").val();
	if(abs_content.length < 100){
		alert('Please input appropriate abstract!')
		return false;
	}
	var rank_n =  $("input[name='rank']:checked").val();
	
	var info = [];	// conf info returned

	// show abs	
	$("#abstract-content").html(abs_content);
	$("#conf-info").html('<li>Recommending...</li>');	// init		
	$('.content').show();	
	
	abs_content = abs_content.replace(/update/gi, "updates");

	// get first three conf id and information
	$.ajax({
		type : "POST",
		url : "search/search.php",
		data : {"abs":abs_content, "rank_num":rank_n},
		success : function(response){
			//alert(response);
			//$("body").html(response);
			info = eval('(' + response + ')');
			$("#conf-info").html('');	// clear
			// show conf info
			$("#conf-info").append('<h4>Journals:</h4>');
			for(i = 0; i < rank_n; i++){
				$("#conf-info").append('<li>' + (i+1) + '&nbsp <a href="' + info[i].link + '" target="_blank">' + info[i].name + '</a>&nbsp' + '</li>');
			}
			$("#conf-info").append('<h4>Conferences:</h4>');
			for(i = rank_n; i < rank_n*2; i++){
				$("#conf-info").append('<li>' + (i-9) + '&nbsp <a href="' + info[i].link + '" target="_blank">' + info[i].name + '</a>&nbsp' + '</li>');
			}
		}
	});
	

	
  });
  	
	// 点击回车搜索
	$(document).keydown(function(event){ 
	if(event.keyCode == 13){
		$(".btn_srh").click();
	} 
	}); 		
}

// 4. get the visited num
function setVisitedNum(){
	// get first three conf id and information
	$.ajax({
		url : "footer/visitedNum.php",
		success : function(response){
			//alert(response);
			$('#visited-num').html(response);
		}
	});	
}

// 5. check logged or not
function checkLogged(){
	// 检查用户是否登录
	$.ajax({
		url : "users/getInfo.php",
		async : true,
		success : function(result) {
			// 未登录
			if(result == 'not logged in'){
				return;
			}
			
			// 已登录
			var user = eval('(' + result + ')');	// result为string类型，需要转换
			if(user.Type != "admin"){
				
				$('#logged #user-name').html(user.Name);
				
				$('#function').hide();
				$('#logged').show();
							
			}else{
				$('#logout').click();
			}
		}
	});	
	
}

// 6. check mobile or PC
function checkMobile(){
	if ((navigator.userAgent.match(/(phone|pad|pod|iPhone|iPod|ios|iPad|Android|Mobile|BlackBerry|IEMobile|MQQBrowser|JUC|Fennec|wOSBrowser|BrowserNG|WebOS|Symbian|Windows Phone)/i))){
		$('#site-name').hide();
		$('#abstract-title').hide();
		$('#abstract-content').hide();
	}
	
}