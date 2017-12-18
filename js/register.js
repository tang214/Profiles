function flagInvalid(item, message)
{
    item.data('valid', false);
    item.parents('.form-group').addClass('has-error');
    item.parents('.form-group').removeClass('has-success');
    if(message !== undefined)
    {
        item.parent().append('<div class="col-sm-10">'+message+'</div>');
    }
}

function flagValid(item)
{
    item.data('valid', true);
    item.parents('.form-group').removeClass('has-error');
    item.parents('.form-group').addClass('has-success');
    item.next('div').remove();
}

function responseIsInvalid(jqXHR)
{
    return (jqXHR.status !== 200 || jqXHR.responseJSON === undefined);
}

function getErrorMessage(text, pending)
{
    if(pending === true)
    {
        return 'The '+text+' is already registered, but the account is not yet active. Please check your email for a confirmation email.';
    }
    return 'The '+text+' is already used. Please go <a href="reset.php">here</a> to reset the password for that account.';
}

function backendCheckDone(jqXHR)
{
    if(responseIsInvalid(jqXHR))
    {
        flagInvalid($(this));
        return;
    }
    if(jqXHR.responseJSON === false || jqXHR.responseJSON.res === false)
    {
        var message = '';
        if(jqXHR.responseJSON.uid !== undefined)
        {
            message = getErrorMessage('username '+jqXHR.responseJSON.uid, jqXHR.responseJSON.pending);
        }
        else if(jqXHR.responseJSON.email !== undefined)
        {
            message = getErrorMessage('email address '+jqXHR.responseJSON.email, jqXHR.responseJSON.pending);
        }
        flagInvalid($(this), message);
        return;
    }
    flagValid($(this));
}

function check_email(e)
{
    var control = e.target;
    if(e.target.willValidate !== true)
    {
        var re = /^([\w-]+(?:\.[\w-]+)*)@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$/i;
        if(re.test(email) === false)
        {
            flagInvalid($(control));
            return;
        }
    }
    $.ajax({
        url: 'api/v1/users/Actions/check_email_available',
        data: 'email='+encodeURIComponent(control.value),
        type: 'POST',
        dataType: 'json',
        context: control,
        complete: backendCheckDone
    });
}

function check_uid(e)
{
    var control = e.target;
    if(control.value.indexOf(',') > -1)
    {
        flagInvalid($(control));
        return;
    }
    if(control.value.indexOf('=') > -1)
    {
        flagInvalid($(control));
        return;
    }
    $.ajax({
        url: 'api/v1/users/Actions/check_uid_available',
        data: 'uid='+encodeURIComponent(control.value),
        type: 'POST',
        dataType: 'json',
        context: control,
        complete: backendCheckDone
    });
}

function check_pass(e)
{
    var control = e.target;
    var value = control.value;
    if(value.length < 4)
    {
        flagInvalid($(control));
        return;
    }
    if(/[a-z]/.test(value) === false)
    {
        flagInvalid($(control));
        return;
    }
    if(/[A-Z]/.test(value) === false)
    {
        flagInvalid($(control));
        return;
    }
    if(/[0-9]/.test(value) === false)
    {
        flagInvalid($(control));
        return;
    }
    flagValid($(control));
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

function form_submit_done(jqXHR)
{
    console.log(jqXHR);
    if(jqXHR.status === 200)
    {
        if(jqXHR.responseJSON === undefined || jqXHR.responseJSON.message === undefined)
        {
            window.location.replace('thanks.php');
        }
        else
        {
            alert(jqXHR.responseJSON.message);
        }
    }
    else
    {
        if(jqXHR.responseJSON !== undefined && jqXHR.responseJSON.message !== undefined)
        {
            alert(jqXHR.responseJSON.message);
        }
        else
        {
            alert(jqXHR.responseJSON);
        }
        console.log(jqXHR);
    }
}

function submit_registration_form(form)
{
    var obj = form.serializeObject();
    $.ajax({
        url: '/api/v1/users',
        contentType: 'application/json',
        data: JSON.stringify(obj),
        type: 'POST',
        dataType: 'json',
        processData: false,
        complete: form_submit_done});
}

function validate_fields(index, value)
{
    if($(value).val().length === 0)
    {
        flagInvalid($(value));
    }
    else if(value.willValidate === true && value.checkValidity() === false)
    {
        flagInvalid($(value));
    }
    else if($(value).data('valid') === false)
    {
        flagInvalid($(value));
    }
    else
    {
        flagValid($(value));
    }
}

function submit_click(e)
{
    var required_fields = $('#form [required]');
    $.each(required_fields, validate_fields);
    var pass  = $('#password').val();
    var pass2 = $('#password2').val();
    if(pass !== pass2)
    {
        flagInvalid($('#password2'));
    }
    else
    {
        flagValid($('#password2'));
    }
    if($('#form .form-group.has-error').length === 0)
    {
        submit_registration_form($('#form'));
    }
    e.preventDefault();
    return false;
}

function init_register_page()
{
    $('[title]').tooltip();
    original_tooltip = $("#password").attr('data-original-title');
    $('#email').on('change', check_email);
    $('#uid').on('change', check_uid);
    $('#password').on('change', check_pass);
    $('#submit').on('click', submit_click);
}

$(init_register_page);
