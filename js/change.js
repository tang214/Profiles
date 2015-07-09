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

function forget_submit()
{
    var form = $('#form');
    $.post('change.php', form.serializeArray(), form_submit_done, 'json');
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

function change_password(e)
{
    var obj = {};
    if($('#hash').length !== 0)
    {
        obj.hash = $('#hash').val();
    }
    else
    {
        obj.current = $('#current').val();
    }
    obj.password = $('#password').val();
    if(obj.password.length < 4)
    {
        $('#password').parent().addClass('has-error');
        alert('Passwords must be at least 4 characters long!');
        return false;
    }
    else if((/[a-z]/.test(obj.password)) === false)
    {
        $('#password').parent().addClass('has-error');
        alert('Passwords have at least one lower case character!');
        return false;
    }
    else if((/[A-Z]/.test(obj.password)) === false)
    {
        $('#password').parent().addClass('has-error');
        alert('Passwords have at least one upper case character!');
        return false;
    }
    else if((/[0-9]/.test(obj.password)) === false)
    {
        $('#password').parent().addClass('has-error');
        alert('Passwords have at least one number!');
        return false;
    }
    else
    {
        $('#password').parent().removeClass('has-error');
    }
    var pass2 = $('#password2').val();
    if(obj.password !== pass2)
    {
        $('#password2').parent().addClass('has-error');
        alert('Passwords must match!');
        return false;
    }
    else
    {
        $('#password2').parent().removeClass('has-error');
    }
    console.log(obj);
    return false;
}

function init_register_page()
{
    $('#form').submit(change_password);
}

$(init_register_page);
