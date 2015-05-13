function field_populated(id)
{
    if($(id).val().length === 0)
    {
        $(id).parents('.form-group').addClass('has-error');
        return false;
    }
    else
    {
        $(id).parents('.form-group').removeClass('has-error');
        return true;
    }
}

function fields_equal(id1, id2)
{
    if($(id1).val() != $(id2).val())
    {
        $(id1).parents('.form-group').addClass('has-error');
        return false;
    }
    else
    {
        $(id1).parents('.form-group').removeClass('has-error');
        return true;
    }
}

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
    else
    {
        console.log(jqXHR);
    }
}

function change_password()
{
    var pass = true;
    if(field_populated('#oldpass') === false)
    {
        pass = false;
    }
    if(field_populated('#newpass') === false)
    {
        pass = false;
    }
    if(field_populated('#confirm') === false || fields_equal('#confirm', '#newpass') === false)
    {
        pass = false;
    }
    if(pass)
    {
        var obj = {};
        obj.oldpass = $('#oldpass').val();
        obj.password = $('#newpass').val();
        $.ajax({
            url: 'api/v1/users/me',
            type: 'PATCH',
            data: JSON.stringify(obj),
            processData: false,
            complete: change_password_done
        });
    }
}

function forget_submit(form)
{
    form.submit();
}

function do_init()
{
    $("#forgot_form").validate({
        submitHandler: forget_submit
    });
}

$(do_init);
// vim: set tabstop=4 shiftwidth=4 expandtab:
