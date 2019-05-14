/*
 *
 *@author Donghui.Wang
 */



//////////////////////////////////////// 登陆部分 ////////////////////////////////////////

$("#login-button-submit").on('click',function(){
	  var username = $(".login #username");
	  var usernameValue = $(".login #username").val();
	  var password = $(".login #password");
	  var passwordValue = $(".login #password").val();
	if(usernameValue == ""){
		alert("Username empty");
		username.focus();
		return false;
	}else if(usernameValue.length > 20){
		alert("Length of username should not more than 20 characters.");
		username.focus();
		return false;
	}else if(passwordValue == ""){
		alert("Password empty");
		password.focus();
		return false;
	}else if(passwordValue.length < 6 || passwordValue.length > 30){
		alert("Length of password should not more than 30 characters or less than 6 characters.");
		password.focus();
		return false;
	}else{
		succ = false;
		$.ajax({
			type : "POST",
			url : "login.php",
			data : {"userName": usernameValue, "password": passwordValue},
			async : true,
			success : function(result) {
				if (result == 'admin') {
					succ = true;
				}else if (result == 'user') {
					succ = true;
					location.href = '../index.html';
				}else{
					alert(result);
				}
			}
		});
		return succ;

	}
});

// 点击回车登陆
$(document).keydown(function(event){ 
	if(event.keyCode == 13){
		$("#login-button-submit").click();
	} 
}); 
    
//    // 点击注册按钮事件
//    $("#register-link-click").on('click',function(){
//    	$(".login").hide();
//    	$(".register").show();
//    });



//退出登录
function logout(){
	// 登出
	$.ajax({
		url : "logout.php",
		async : true,
		success : function(){
			location.href = './';
		}
	});
}