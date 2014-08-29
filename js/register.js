function validate_pass_len(value, element, params)
{
    if(value.length < 4)
    {
        return false;
    }
    return true;
}

function validate_pass_lower(value, element, params)
{
    return (/[a-z]/.test(value));
}

function validate_pass_upper(value, element, params)
{
    return (/[A-Z]/.test(value));
}

function validate_pass_number(value, element, params)
{
    return (/[0-9]/.test(value));
}

function validate_complexity(value, element, params)
{
    if(this.optional(element))
    {
        return true;
    }
    var password = value;
    var email = $('#email').val();
    var uid = $('#uid').val();
    var res = zxcvbn(password, [email, uid]);
    var msg;
    switch(res.score)
    {
       case 0:
           msg = "Incredibly weak password";
           break;
       case 1:
           msg = "Weak password";
           break;
       case 2:
           msg = "Average password";
           break;
       case 3:
           msg = "Strong password";
           break;
       case 4:
           msg = "Very strong password";
           break;
    }
    $("#password").tooltip("option", "content", msg+". Estimated password crack time is "+res.crack_time+"s");
    return true;
}

function validate_pass2(value, element, params)
{
    var pass2 = value;
    var pass  = $('#password').val();
    if(pass2 === pass)
    {
        return true;
    }
    else
    {
        return false;
    }
}

function form_submit_done(data, textStatus, jqXHR)
{
    var json = eval(data);
    if(json.status == 0)
    {
        window.location.replace('index.php');
    }
    else
    {
        alert(json.msg);
        console.log(json);
    }
}

function submit_registration_form()
{
    var form = $('#form');
    $.post('register.php', form.serializeArray(), form_submit_done, 'json');
}

function init_register_page()
{
    jQuery.validator.addMethod("pass_length", validate_pass_len, 'Passwords must be at least 4 characters long');
    jQuery.validator.addMethod("pass_lower", validate_pass_lower, 'Passwords must contain at least one lower case letter');
    jQuery.validator.addMethod("pass_upper", validate_pass_upper, 'Passwords must contain at least one upper case letter');
    jQuery.validator.addMethod("pass_number", validate_pass_number, 'Passwords must contain at least one number');
    jQuery.validator.addMethod("pass_complex", validate_complexity, 'Password is not complex enough');
    jQuery.validator.addMethod("pass2", validate_pass2, 'Passwords are not the same');

    jQuery.validator.addClassRules("pass", {pass_length: true, pass_lower: true, pass_upper: true, pass_number: true, pass_complex:true});

    $('#form').validate({
        debug: true,
        rules: { 
            email: { required: true, email: true, remote: 'ajax/valid_email.php'},
            uid: { required: true, remote: 'ajax/valid_uid.php'},
            password: { required: true },
            password2: { required: true, pass2: true }
        },
        messages: {
            email: { remote: jQuery.validator.format('Email address {0} is already registered. Please click <a href=\"/reset.php\">here</a> to request a password reset.') },
            uid: { remote: jQuery.validator.format('The username {0} is already registered. Please try another.') }
        },
        submitHandler: submit_registration_form
    });
    
    $("#password").tooltip();
}

$(init_register_page);
