var _uid = null;

function getParameterByName(name) {
    name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
    var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
        results = regex.exec(location.search);
    return results == null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));
}

function getUID()
{
    if(_uid != null)
    {
        return _uid;
    }
    else
    {
        return getParameterByName('uid');
    }
}

function users_done(data)
{
    var users = data.data;
    for(i = 0; i < users.length; i++)
    {
        $('#user_select').append('<option value="'+users[i][0]+'">'+users[i][0]+'</option>');
    }
    var uid = getUID();
    if(uid != null)
    {
        $('#user_select').val(uid);
    }
}

function user_data_done(data)
{
    $('#uid').html(data.uid);
    $('#uid_edit').val(data.uid);
    $('#old_uid').val(data.uid);
    $('#dn').html(data.dn);
    $('#givenName').val(data.givenName);
    $('#sn').val(data.sn);
    $('#displayName').val(data.displayName);
    $('#mail').val(data.mail);
    $('#mobile').val(data.mobile);
    $('#postalAddress').val(data.postalAddress);
    $('#postalCode').val(data.postalCode);
    $('#l').val(data.l);
    $('#st').val(data.st);
    $('#user_data').show(); 
}

function populate_user_dropdown()
{
    //Turn off events on the dropdown
    $('#user_select').change(null);
    $.ajax({
        url: 'ajax/users.php',
        type: 'get',
        dataType: 'json',
        success: users_done});
    //Enable events on the dropdown
    $('#user_select').change(userSelectChange);
}

function populate_user_data()
{
    var uid = getUID();
    if(($('#user_data').length > 0) && (uid != null))
    {
        $.ajax({
            url: '/ajax/user.php?uid='+uid,
            type: 'get',
            dataType: 'json',
            success: user_data_done});
    }
}

function userSelectChange()
{
    _uid = $(this).val();
    populate_user_data(); 
}

function user_submit_done(data)
{
    if(data.error)
    {
         alert(data.error);
         console.log(data.error);
    }
    else
    {
        console.log(data);
    }
}

function user_data_submitted(form)
{
    $.ajax({
        url: '/ajax/user.php',
        data: $(form).serialize(),
        type: 'post',
        dataType: 'json',
        success:user_submit_done});
}

function do_user_edit_init()
{
    populate_user_dropdown();
    populate_user_data();
    $("#form").validate({
        debug: true,
        submitHandler: user_data_submitted
    });
}

$(do_user_edit_init);
