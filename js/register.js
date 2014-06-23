function validate_pass(value, element, params)
{
    if(this.optional(element))
    {
        return true;
    }
    var password = value;
    var email = $('#email').val();
    var uid = $('#uid').val();
    var res = zxcvbn(password, [email, uid]);
    var msg,color;
    switch(res.score)
    {
       case 0:
           msg = "Incredibly weak password";
           color = "red";
           break;
       case 1:
           msg = "Weak password";
           color = "orange";
           break;
       case 2:
           msg = "Average password";
           color = "yellow";
           break;
       case 3:
           msg = "Strong password";
           color = "green";
           break;
       case 4:
           msg = "Very strong password";
           color = "green";
           break;
    }
    var label_elem = $("[for='password']");
    label_elem.html(msg);
    label_elem.css("color", color);
    $("[for='password']").tooltip("option", "content", "Estimated password crack time is "+res.crack_time+"s");
    if(res.score < 1)
    {
        return false;
    }
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
    jQuery.validator.addMethod("pass", validate_pass, '');
    jQuery.validator.addMethod("pass2", validate_pass2, 'Passwords are not the same');

    $('#form').validate({
        debug: true,
        rules: { 
            email: { required: true, email: true, remote: 'ajax/valid_email.php'},
            uid: { required: true, remote: 'ajax/valid_uid.php'},
            password: { required: true, pass: true },
            password2: { required: true, pass2: true }
        },
        messages: {
            email: { remote: jQuery.validator.format('Email address {0} is already registered. Please click <a href=\"/reset.php\">here</a> to request a password reset.') },
            uid: { remote: jQuery.validator.format('The username {0} is already registered. Please try another.') }
        },
        submitHandler: submit_registration_form
    });
    
    $("[for='password']").tooltip();
}

$(init_register_page);
