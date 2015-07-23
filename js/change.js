function change_password_done(jqXHR)
{
    if(jqXHR.status === 500)
    {
        if(jqXHR.responseJSON !== undefined && jqXHR.responseJSON.message !== undefined)
        {
            alert(jqXHR.responseJSON.message);
        }
        else
        {
            alert('Unknown error changing password!');
        }
    }
    else if(jqXHR.status === 200)
    {
        window.location = '/index.php';
    }
    else
    {
        console.log(jqXHR);
    }
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
    $.ajax({
        url: 'api/v1/users/me',
        type: 'PATCH',
        data: JSON.stringify(obj),
        processData: false,
        complete: change_password_done
    });
    return false;
}

function init_register_page()
{
    $('#form').submit(change_password);
}

$(init_register_page);
