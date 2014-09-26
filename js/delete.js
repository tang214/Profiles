function allow_delete()
{
    $('.btn-danger').removeAttr('disabled');
    $('.btn-danger').click(really_really_delete_user);
}

function really_really_delete_user()
{
    $.ajax({
        url: '/ajax/user.php',
        data: 'action=delete',
        type: 'post',
        dataType: 'json',
        success: delete_done
    });
}

function delete_done(data)
{
    if(data.error !== undefined)
    {
        alert(data.error);
    }
    else
    {
        location = '/logout.php';
    }
}
