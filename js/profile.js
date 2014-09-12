var cropper;

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

function country_value_changed()
{
    var country = $(this).val();
    $.ajax({
            url: '/ajax/states.php?c='+country,
            type: 'get',
            dataType: 'json',
            success: populate_states});
}

function populate_countries(data)
{
    var countries = data.countries;
    var dropdown = $('#c');
    for(var propertyName in countries)
    {
        $('<option\>', {value: propertyName, text: countries[propertyName]}).appendTo(dropdown);
    }
    dropdown.on('change', country_value_changed);
}

function populate_states(data)
{
    if(data.states == undefined)
    {
        //We don't know how to handle this country. Just let the user input the state freeform
        $('#st').replaceWith($('<input/>', {id: 'st', name: 'st', type: 'text'}));
    }
    else
    {
        var states = data.states;
        $('[for=st]').html(states.states_label+':');
        $('#st').replaceWith($('<select/>', {id: 'st', name: 'st'}));
        var dropdown = $('#st');
        for(var state in states.states)
        {
            $('<option/>', {value: state, text: states.states[state]}).appendTo(dropdown);
        }
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
    $.when(
        $.ajax({
            url: '/ajax/countries.php',
            type: 'get',
            dataType: 'json',
            success: populate_countries}),
        $.ajax({
            url: '/ajax/states.php?c=US',
            type: 'get',
            dataType: 'json',
            success: populate_states})
    ).done(populate_user_data);
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
        console.log(data.unset);
        location.reload();
    }
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
    $("#reset").click(start_populate_form);
}

$(do_init);
// vim: set tabstop=4 shiftwidth=4 expandtab:
