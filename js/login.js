function init_dialog()
{
    $("#login-form").dialog({
        autoOpen: false,
        modal: true,
    });
}

function open_dialog()
{
    $("#login-form").dialog("open");
    event.preventDefault();
}

function login_submit_done(data)
{
    if(data.error)
    {
         alert('Login failed: '+data.error);
         console.log(data.error);
    }
    else
    {
        if(data.return)
        {
            window.location = data.return;
        }
    }
}

function login_submitted(form)
{
    $.ajax({
        url: '/ajax/login.php',
        data: $(form).serialize(),
        type: 'post',
        dataType: 'json',
        success: login_submit_done});
}

function do_login_init()
{
    init_dialog();
    var login_link = $(".links a[href*='login']");
    login_link
        .button()
        .click(open_dialog);
    if($('#login_main_form').length > 0)
    {
        $("#login_main_form").validate({
            debug: true,
            submitHandler: login_submitted
        });
    }
    if($('#login_dialog_form').length > 0)
    {
        $("#login_dialog_form").validate({
            debug: true,
            submitHandler: login_submitted
        });
    }
}

$(do_login_init);
