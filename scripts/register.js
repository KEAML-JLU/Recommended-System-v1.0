////////////////////////////////////////// 注册部分 ////////////////////////////////////////

$("#register-button-submit").on('click',function(){
   var username = $(".register #username");
   var usernameValue = $(".register #username").val();
   var password = $(".register #password");
   var passwordValue = $(".register #password").val();
   var password_confirm = $(".register #password_confirm");
   var password_confirmValue = $(".register #password_confirm").val();
   var name = $(".register #name");
   var nameValue = $(".register #name").val();
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
 }else if(passwordValue != password_confirmValue){
	 alert("Passwords are not consistent");
	 password_confirm.focus();
	 return false;
 }else if(nameValue == ""){
	 alert("nickname empty");
	 name.focus();
	 return false;
 }else{
  succ = false;
  $.ajax({
		type : "post",
		url : "register.php",
		data : {"userName" : usernameValue, "password" : passwordValue, "name" : nameValue},
			async : false,
			success : function(result) {
			
				if (result == 'Register successfully') {
					succ = true;
					alert("register successfully");
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
		$("#register-button-submit").click();
	} 
}); 