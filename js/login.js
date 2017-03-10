function open_dialog(event)
{
    $("#login-form").dialog("open");
    if(event != undefined && event != null)
    {
        event.preventDefault();
    }
}

function login_submit_done(data)
{
    if(data.error)
    {
         var failed = getParameterByName('failed')*1;
         failed++;
         window.location = window.loginUrl+'?failed='+failed;
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
        url: window.profilesUrl+'/ajax/login.php',
        data: $(form).serialize(),
        type: 'post',
        dataType: 'json',
        xhrFields: {withCredentials: true},
        success: login_submit_done});
}

function do_login_init()
{
    if($('#login_main_form').length > 0)
    {
        $("#login_main_form").validate({
            debug: true,
            submitHandler: login_submitted
        });
    }
    if($('#login_dialog_form').length > 0)
    {
        var login_link = $(".links a[href*='login']");
        login_link.attr('data-toggle','modal');
        login_link.attr('data-target','#login-dialog');
        login_link.removeAttr('href');
        login_link.css('cursor', 'pointer');
        $("#login_dialog_form").validate({
            debug: true,
            submitHandler: login_submitted
        });
    }
    if($(window).width() <= 340)
    {
        $('.login-container').css('max-width', $(window).width()-50);
    }
}

$(do_login_init);
