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

function validate_zip(value, element)
{
    if(value.length > 0)
    {
        if($('#c').val() == 'US')
        {
            //Make sure this is either a 5 or 5+4 zip code
            if(/^\d{5}(?:-\d{4})?$/.test(value) == false)
            {
                return this.optional(element);
            }
            try{
                $.ajax({
                    url: '/ajax/zip_proxy.php',
                    data: 'zip='+value,
                    type: 'get',
                    dataType: 'json',
                    async: false,
                    success: function(data){is_valid = (data.city !== undefined); update_city_state(data)}});
                return is_valid;
            } catch(err) {
                return true;
            }
        }
        else
        {
            return true;
        }
    }
    else
    {
        return this.optional(element);
    }
}

function finish_populate_form(data, textStatus, jqXHR)
{
    if(textStatus === 'success')
    {
        var json = eval('('+data+')');
        $('#uid_label').html(json.uid);
        $('#uid').val(json.uid);
        $('#givenName').val(json.givenName);
        $('#sn').val(json.sn);
        $('#displayName').val(json.displayName);
        $('#mail').val(json.mail);
        $('#mobile').val(json.mobile);
        $('#street').val(json.postalAddress);
        $('#zip').val(json.postalCode);
        $('#l').val(json.l);
        cropper.reset();
        if(json.jpegPhoto != undefined && json.jpegPhoto.length > 0)
        {
            cropper.obj.append('<img src="data:image/jpeg;base64,'+json.jpegPhoto+'">');
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
    $.ajax('./ajax/user.php').done(finish_populate_form);
}

function populate_user_data()
{
    setTimeout(start_user_population, 300);
}

function start_populate_form()
{
    populate_user_data();
}

function profile_submit_done(data)
{
    if(data.error)
    {
         alert(data.error);
         console.log(data.error);
    }
    else
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
    return false;
}

function profile_data_submitted(form)
{
    var jpegPhoto = '';
    if($('#jpegPhoto img').length > 0)
    {
        jpegPhoto = '&jpegPhoto='+encodeURIComponent($('#jpegPhoto img').attr('src').substring(23));
    }
    $.ajax({
        url: '/ajax/user.php',
        data: $(form).serialize()+jpegPhoto,
        type: 'post',
        dataType: 'json',
        success:profile_submit_done});
}

function delete_user()
{
    location = '/delete.php';
}

function do_init()
{
    jQuery.validator.addMethod("zip", validate_zip, "Please provide a valid zipcode.");

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
            zip: "zip"
        },
        submitHandler: profile_data_submitted
    });
}

$(do_init);
// vim: set tabstop=4 shiftwidth=4 expandtab:
