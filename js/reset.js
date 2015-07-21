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

function reminder_post_done(jqXHR)
{
    console.log(jqXHR);
}

function reset_post_done(jqXHR)
{
    if(jqXHR.status === 404)
    {
        bootbox.dialog({
            message: "Did you forget your username?",
            title: "Invalid User Name",
            buttons: {
                success: {
                    label: "Yes",
                    callback: remind_user_name
                },
                danger: {
                    label: "No, Let me try again.",
                    callback: reset_password_not_logged_in
                }
            }
        });
    }
    else
    {
        console.log(jqXHR);
    }
}

function got_email(result)
{
    if(result !== null)
    {
        $.ajax({
            url: 'api/v1/users/Actions/remind_uid',
            data: 'email='+encodeURIComponent(result),
            type: 'POST',
            complete: reminder_post_done
        });
    }
}

function got_uid(result)
{
    if(result !== null)
    {
        $.ajax({
            url: 'api/v1/users/'+encodeURIComponent(result)+'/Actions/reset_pass',
            type: 'POST',
            complete: reset_post_done
        });
    }
}

function remind_user_name()
{
    bootbox.prompt({
        title: 'What is your email address?',
        callback: got_email
    });
}

function reset_password_not_logged_in()
{
    bootbox.prompt({
        title: 'What is your user name?',
        callback: got_uid
    });
}

function what_did_they_forget()
{
    var forgot = $('input[name=forgot]:checked').val();
    if(forgot === undefined)
    {
        alert('Must select one!');
        return;
    }
    switch(forgot)
    {
        case 'user':
            remind_user_name();
            break;
        case 'pass':
            reset_password_not_logged_in();
            break;
        default:
            alert('BUGBUG: I don\'t know how to reset '+forgot);
            return;
    }
}

// vim: set tabstop=4 shiftwidth=4 expandtab:
