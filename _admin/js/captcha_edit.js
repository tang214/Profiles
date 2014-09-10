var _cid = null;

function getParameterByName(name) {
    name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
    var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
        results = regex.exec(location.search);
    return results == null ? null : decodeURIComponent(results[1].replace(/\+/g, " "));
}

function getCID()
{
    if(_cid != null)
    {
        return _cid;
    }
    else
    {
        return getParameterByName('cid');
    }
}

function captchas_done(data)
{
    var captchas = data;
    for(i = 0; i < captchas.length; i++)
    {
        $('#captcha_select').append('<option value="'+captchas[i].id+'">'+captchas[i].question+'</option>');
    }
    var cid = getCID();
    if(cid != null)
    {
        $('#captcha_select').val(cid);
    }
    else
    {
        $('#captcha').html('New CAPTCHA');
    }
}

function captcha_data_done(data)
{
    $('#id').html(data.id);
    $('#cid').val(data.id);
    $('#captcha').html(data.id+': '+data.question);
    $('#question').val(data.question);
    $('#hint').val(data.hint);
    $('#answer').val(data.answer);
}

function populate_captcha_dropdown()
{
    //Turn off events on the dropdown
    $('#captcha_select').change(null);
    $.ajax({
        url: 'ajax/captcha.php',
        type: 'get',
        dataType: 'json',
        success: captchas_done});
    //Enable events on the dropdown
    $('#captcha_select').change(captchaSelectChange);
}

function populate_captcha_data()
{
    var cid = getCID();
    if(($('#captcha_data').length > 0) && ((cid == null) || (cid == 'new')))
    {
        $('#id').html('');
        $('#cid').val('NEW');
        $('#captcha').html('New CAPTCHA');
        $('#question').val('');
        $('#hint').val('');
        $('#answer').val('');
    }
    else if(($('#captcha_data').length > 0) && (cid != null))
    {
        $.ajax({
            url: 'ajax/captcha.php?cid='+cid,
            type: 'get',
            dataType: 'json',
            success: captcha_data_done});
    }
}

function captchaSelectChange()
{
    _cid = $(this).val();
    populate_captcha_data(); 
}

function captcha_submit_done(data)
{
    if(data.error)
    {
         alert(data.error);
         console.log(data.error);
    }
    else
    {
        if(data.id != undefined)
        {
            _cid = data.id;
        }
        populate_captcha_dropdown();
        populate_captcha_data();
    }
}

function captcha_data_submitted(form)
{
    $.ajax({
        url: 'ajax/captcha.php',
        data: $(form).serialize(),
        type: 'post',
        dataType: 'json',
        success: captcha_submit_done});
}

function do_captcha_edit_init()
{
    populate_captcha_dropdown();
    populate_captcha_data();
    $("#form").validate({
        debug: true,
        submitHandler: captcha_data_submitted
    });
}

$(do_captcha_edit_init);
