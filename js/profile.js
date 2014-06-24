var cropper;

function validate_zip(value, element)
{
    if(value.length > 0)
    {
        //Make sure this is either a 5 or 5+4 zip code
        if(/^\d{5}(?:-\d{4})?$/.test(value) == false)
        {
            return this.optional(element);
        }
        try{
            var val = $.ajax('/ajax/zip_proxy.php?zip='+value, {async: false});
            var city = val.responseJSON.city;
            var state = val.responseJSON.state_short;
            $("#l").val(city);
            $("#st").val(state);
            return true;
        } catch(err) {
            return this.optional(element);
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
        $('#uid').html(json.uid);
        $('#givenName').val(json.givenName);
        $('#sn').val(json.sn);
        $('#displayName').val(json.displayName);
        $('#mail').val(json.mail);
        $('#mobile').val(json.mobile);
        $('#street').val(json.street);
        $('#zip').val(json.postalCode);
        $('#l').val(json.l);
        $('#st').val(json.st);
        cropper.reset();
        cropper.obj.append('<img src="data:image/jpeg;base64,'+json.jpegPhoto+'">');
        //window.console.error(json);
    }
    else
    {
        window.console.error('Ajax returned: '+textStatus);
    }
}

function start_populate_form()
{
    $.ajax('./ajax/user.php').done(finish_populate_form);
}

function do_init()
{
    jQuery.validator.addMethod("zip", validate_zip, "Please provide a valid zipcode.");

    cropper = new Croppic('jpegPhoto', {
         modal: true,
         uploadUrl: 'upload.php',
         cropUrl: 'save.php'
    });
    start_populate_form();
    $("#profile").validate({
        debug: true,
        rules: {
            email: { required: true, email: true},
            zip: "zip"
        }
    });
    $("#reset").click(start_populate_form);
}

$(do_init);
// vim: set tabstop=4 shiftwidth=4 expandtab:
