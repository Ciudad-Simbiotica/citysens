function verifyAllFields()
{
	var allFieldsValid=true;

	if($('#input-password').val()=="")
		allFieldsValid=false;
	
	if($('#input-password-2').val()=="")
		allFieldsValid=false;

	if($('#input-password').val()!=$('#input-password-2').val())
		allFieldsValid=false;

	if(allFieldsValid)
		$('.div-login-submit').addClass('div-email-submit-active',250);
	else
		$('.div-login-submit').removeClass('div-email-submit-active',250);		
	return allFieldsValid;
}



$('#input-password').on('input',function()
{
  verifyAllFields();
});


$('#input-password').bind('keyup',function(event)
{
  verifyAllFields();
  if(event.which==13) //Intro 
  	$('.div-login-submit').trigger("click");
});


$('#input-password-2').on('input',function()
{
  verifyAllFields();
});


$('#input-password-2').bind('keyup',function(event)
{
  verifyAllFields();
  if(event.which==13) //Intro 
  	$('.div-login-submit').trigger("click");
});

$('.div-login-submit').click(function()
{
	if(verifyAllFields())
	{
		$("#login_form").submit();
	}
});
