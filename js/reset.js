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
