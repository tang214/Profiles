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

function validate_zip(e)
{
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
        if(json.jpegPhoto != undefined && json.jpegPhoto.length > 0)
        {
            var img = new Image();
            img.id = 'jpegPhoto';
            img.src = 'data:image/jpeg;base64,'+json.jpegPhoto;
            $('#jpegPhotoBtn').hide().after(img);
            $(img).on('click', image_click);
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
    if($('#jpegPhoto').length > 0)
    {
        obj['jpegPhoto'] = $('#jpegPhoto').attr('src').substring(23);
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

var jcrop_api;

function crop()
{
    var select = jcrop_api.tellSelect();
    var canvas = document.createElement('canvas');
    canvas.height = select.h;
    canvas.width = select.w;
    var context = canvas.getContext('2d');
    var image = $('#cropPhoto')[0];
    context.drawImage(image, select.x, select.y, select.w, select.h, 0, 0, select.w, select.h);
    var dataURL = canvas.toDataURL('image/jpeg');
    if($('#jpegPhoto').length === 0)
    {
        var img = new Image();
        img.id = 'jpegPhoto';
        img.src = dataURL;
        $('#jpegPhotoBtn').hide().after(img);
        $(img).on('click', image_click);
    }
    else
    {
        $('#jpegPhoto')[0].src = dataURL;
    }
}

function crop_change(e)
{
    $('.btn-crop').removeAttr('disabled');
}

function image_click(e)
{
    $('#jpegPhotoBtn').click();
}

function process_file(e)
{
    var reader = e.target;
    var image = new Image();
    image.src = reader.result;
    if(image.width > 400 || image.height > 400)
    {
        //Show crop dialog
        image.style.width = '100%';
        image.id = 'cropPhoto';
        bootbox.dialog({
            'title': 'Image Cropper',
            'size': 'large',
            'className': 'crop_modal',
            'onEscape': false,
            'backdrop': null,
            'message': image.outerHTML,
            'buttons': {
                success: {
                    label: 'Crop',
                    className: 'btn-success btn-crop',
                    callback: crop
                },
                danger: {
                    label: 'Cancel',
                    className: 'btn-danger'
                }
            }
        });
        var width = $('.modal-dialog').width()-30;
        var height = $(window).height()-90;
        var newW, newH;
        if(image.width > image.height)
        {
            newH = image.height * (width / image.width);
            newW = width;
        }
        else
        {
            newH = height;
            newW = image.width * (height / image.height);
        }
        $('#cropPhoto').Jcrop({onChange: crop_change, boxWidth: newW, boxHeight: newH, maxSize: [400,400]}, function(){jcrop_api = this;});
        $('.btn-crop').attr('disabled', true);
    }
    else
    {
        if($('#jpegPhoto').length === 0)
        {
            image.id = 'jpegPhoto';
            $('#jpegPhotoBtn').hide().after(image);
            $(image).on('click', image_click);
        }
        else
        {
            $('#jpegPhoto')[0].src = image.src;
        }
    }
}

function read_file(file)
{
    var reader = new FileReader();
    reader.onload = process_file;
    reader.readAsDataURL(file);
}

function file_sent(e)
{
    var file = e.target.files[0];
    if(file)
    {
        if(/^image\//i.test(file.type))
        {
            read_file(file);
        }
        else
        {
            alert('Not an image!');
        }
    }
}

function do_init()
{
    if(!browser_supports_image_upload())
    {
        $('#jpegPhotoBtn').hide();
    }
    else if(window.File === undefined || window.FileReader === undefined || window.FormData === undefined)
    {
        $('#jpegPhotoBtn').hide();
    }
    else
    {
        $('#jpegPhotoBtn').on('change', file_sent);
    }
    start_populate_form();
    $('#postalCode').on('change', validate_zip);
}

$(do_init);
// vim: set tabstop=4 shiftwidth=4 expandtab:
