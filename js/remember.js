// JavaScript Document
$(document).ready(function(){
	var strName = localStorage.getItem('keyName');
	var strPass = localStorage.getItem('keyPass');
	if(strName){
		$('#user').val(strName);
        }if(strPass){
            $('#pass').val(strPass);
        }
 
    });
	function loginBtn_click(){
		var strName = $('#user').val();
		var strPass = $('#pass').val();
		localStorage.setItem('keyName',strName);
		if($('#remember').is(':checked')){
			localStorage.setItem('keyPass',strPass);
            }else{
                localStorage.removeItem('keyPass');
            }
            window.location.reload();
}