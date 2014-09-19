function isValidEmailAddress(emailAddress) 
{
    var pattern = new RegExp(/^((([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*)|((\x22)((((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(([\x01-\x08\x0b\x0c\x0e-\x1f\x7f]|\x21|[\x23-\x5b]|[\x5d-\x7e]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(\\([\x01-\x09\x0b\x0c\x0d-\x7f]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))))*(((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(\x22)))@((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?$/i);
    return pattern.test(emailAddress);
};

function verifyAllFields()
{
	var allFieldsValid=true;

	if(!isValidEmailAddress($('#input-email').val()))
		allFieldsValid=false;
	if($('#input-password').val()=="")
		allFieldsValid=false;
	
	if($('#input-nombre').val()=="")
		allFieldsValid=false;
	
	if(allFieldsValid)
		$('.div-login-submit').addClass('div-email-submit-active',250);
	else
		$('.div-login-submit').removeClass('div-email-submit-active',250);		
	return allFieldsValid;
}

$('#input-email').on('input',function()
{
  verifyAllFields();
});


$('#input-email').bind('keyup',function(event)
{
  verifyAllFields();
  if(event.which==13) //Intro 
  	$('.div-login-submit').trigger("click");
});

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


$('#input-nombre').on('input',function()
{
  verifyAllFields();
});


$('#input-nombre').bind('keyup',function(event)
{
  verifyAllFields();
  if(event.which==13) //Intro 
  	$('.div-login-submit').trigger("click");
});

$('.div-login-submit').click(function()
{
	if(verifyAllFields())
	{
		$.post( "register.php", { email: $('#input-email').val(), nombre: $('#input-nombre').val(), password: $('#input-password').val()})
	    .done(function(data) 
	    {
		  loadOverlay("register-end.html",true);	    	
	    });
	}
});

$('.login').click(function()
{
	console.log('Login');
	loadOverlay("login.html",true);
});

$('#input-email').focus();