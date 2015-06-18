var cropper;

function update_city_state(data)
{
    if(data.city !== undefined && $("#l").val() == '')
    {
        $("#l").val(data.city);
    }
    if(data.state_short !== undefined && $("#st").val() == '')
    {
        $("#st").val(data.state_short);
    }
}

function finish_populate_form(jqXHR, textStatus)
{
    if(textStatus === 'success')
    {
        var json = jqXHR.responseJSON;
        $('#uid_label').html(json.uid);
        $('#uid').val(json.uid);
        $('#givenName').val(json.givenName);
        $('#sn').val(json.sn);
        $('#displayName').val(json.displayName);
        $('#mail').val(json.mail);
        $('#mobile').val(json.mobile);
        $('#postalAddress').val(json.postalAddress);
        $('#postalCode').val(json.postalCode);
        $('#l').val(json.l);
        cropper.reset();
        if(json.jpegPhoto != undefined && json.jpegPhoto.length > 0)
        {
            cropper.obj.append('<img src="data:image/jpeg;base64,'+json.jpegPhoto+'">');
        }
        else
        {
            //Use gravatar
            $('#gravatar').append('If no profile photo is set. Your <a href="http://www.gravatar.com">Gravatar</a> image will be used instead<br/><img src="//www.gravatar.com/avatar/'+CryptoJS.MD5(json.mail.toLowerCase())+'?d=identicon" style="width:64px; height: 64px;"/>');
        }
        if(json.c != undefined && json.c.length > 0)
        {
            $('#c').val(json.c);
        }
        else
        {
            //Default to the US
            $('#c').val('US');
        }
        $('#st').val(json.st);
        //window.console.error(json);
    }
    else
    {
        window.console.error('Ajax returned: '+textStatus);
    }
}

function start_user_population()
{
    $.ajax({
        url: 'api/v1/users/me',
        type: 'GET',
        dataType: 'json',
        complete: finish_populate_form
    });
}

function populate_user_data()
{
    setTimeout(start_user_population, 300);
}

function start_populate_form()
{
    populate_user_data();
}

function profile_submit_done(jqXHR)
{
    if(jqXHR.status == 200)
    {
        if($('#content .alert').length == 0)
        {
            add_notification($('#content'), 'Successfully applied changes', NOTIFICATION_SUCCESS);
        }
        else
        {
            add_notification($('#content'), 'Successfully applied changes yet again!', NOTIFICATION_SUCCESS);
        }
        window.scrollTo(0, 0);
    }
    else(data.error)
    {
         alert(jqXHR.responseJSON.message);
         console.log(jqXHR);
    }
}

function update_profile()
{
    var obj = $('#profile').serializeObject();
    if($('#jpegPhoto img').length > 0)
    {
        obj['jpegPhoto'] = $('#jpegPhoto img').attr('src').substring(23);
    }
    $.ajax({
        url: 'api/v1/users/me',
        data: obj,
        type: 'PATCH',
        dataType: 'json',
        processData: false,
        complete: profile_submit_done});
}

function delete_user()
{
    location = '/delete.php';
}

function do_init()
{
    cropper = new Croppic('jpegPhoto', {
         modal: true,
         uploadUrl: '/ajax/upload.php',
         cropUrl: '/ajax/save.php'
    });
    start_populate_form();
    $("#profile").validate({
        debug: true,
        rules: {
            email: { required: true, email: true},
            postalCode: {
                remote: {
                    url: 'api/v1/zip',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        c: function() { return $('#c').val(); }
                    }
                }
            }
        }
    });
}

$(do_init);
// vim: set tabstop=4 shiftwidth=4 expandtab:
