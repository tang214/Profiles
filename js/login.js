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

function do_login_init()
{
    init_dialog();
    var login_link = $(".links a[href*='login']");
    login_link
        .button()
        .click(open_dialog)
}

$(do_login_init);
