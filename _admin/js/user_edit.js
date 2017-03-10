var _uid = null;

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

function usersDone(jqXHR)
{
    if(jqXHR.status !== 200)
    {
        alert('Unable to obtain user list!');
        console.log(jqXHR);
        return;
    }
    var users = jqXHR.responseJSON;
    for(i = 0; i < users.length; i++)
    {
        $('#user_select').append('<option value="'+users[i].uid+'">'+users[i].uid+'</option>');
    }
    var uid = getUID();
    if(uid != null)
    {
        $('#user_select').val(uid);
    }
}

var leads = null;
var user = null;

function areasDone(jqXHR)
{
    if(jqXHR.status !== 200)
    {
        alert('Unable to obtain area list!');
        console.log(jqXHR);
        return;
    }
    var areas = jqXHR.responseJSON;
    for(i = 0; i < areas.length; i++)
    {
        $('#ou').append('<option value="'+areas[i].short_name+'">'+areas[i].name+'</option>');
        $.ajax({
            url: '../api/v1/areas/'+areas[i].short_name+'/leads',
            type: 'get',
            dataType: 'json',
            context: areas[i].short_name,
            success: leadsDone});
    }
    if(user != null)
    {
        $('#ou').val(user.ou);
        area_change($('#ou'));
    }
}

function leadsDone(data)
{
    leads = data;
    if(leads === null)
    {
        leads = {};
    }
    leads[this] = data;
    area_change($('#ou'));
}

function area_change(control)
{
    var val = $(control).val();
    if(val == '')
    {
        return;
    }
    if(leads != null)
    {
        $('#title').html('<option></option>');
        var areaLeads = leads[val];
        if(areaLeads === undefined) return;
        for(i = 0; i < areaLeads.length; i++)
        {
            var option = $('<option value="'+areaLeads[i].short_name+'">'+areaLeads[i].name+'</option>');
            if(user !== null && user.title[0] == areaLeads[i].short_name)
            {
                option.attr('selected', 'true');
            }
            $('#title').append(option);
        }
    }
}

function userDataDone(jqXHR)
{
    if(jqXHR.status !== 200)
    {
        alert('Unable to obtain user data!');
        console.log(jqXHR);
        return;
    }
    user = jqXHR.responseJSON;
    $('#uid').html(user.uid);
    $('#uid_x').val(user.uid);
    $('#old_uid').val(user.uid);
    $('#dn').html(user.dn);
    $('#givenName').val(user.givenName);
    $('#sn').val(user.sn);
    $('#displayName').val(user.displayName);
    $('#mail').val(user.mail);
    $('#mobile').val(user.mobile);
    $('#postalAddress').val(user.postalAddress);
    $('#postalCode').val(user.postalCode);
    $('#l').val(user.l);
    $('#st').val(user.st);
    $('#ou').val(user.ou);
    area_change($('#ou'));
    $('#title').val(user.title[0]);
    $('#user_data').show(); 
}

function populateUserDropdown()
{
    //Turn off events on the dropdown
    $('#user_select').change(null);
    $('#user_select').empty();
    $.ajax({
        url: '../api/v1/users?$select=uid',
        type: 'get',
        dataType: 'json',
        complete: usersDone});
    //Enable events on the dropdown
    $('#user_select').change(userSelectChange);
}

function populateAreaDropdown()
{
    $.when(
        $.ajax({
            url: '../api/v1/areas',
            type: 'get',
            dataType: 'json',
            complete: areasDone})
    ).done(populateUserData);
}

function populateUserData()
{
    var uid = getUID();
    if(($('#user_data').length > 0) && (uid != null))
    {
        $.ajax({
            url: '../api/v1/users/'+uid,
            type: 'get',
            dataType: 'json',
            complete: userDataDone});
    }
}

function userSelectChange()
{
    _uid = $(this).val();
    populateUserData(); 
}

function userSubmitDone(jqXHR)
{
    if(jqXHR.status !== 200)
    {
        alert('Unable to set user data!');
        console.log(jqXHR);
        return;
    }
    alert("Success!");
    location = 'user_edit.php?uid='+getUID();
}

function userDataSubmitted(e)
{
    e.preventDefault();
    var obj = $(e.target).serializeObject();
    $.ajax({
        url: '../api/v1/users/'+getUID(),
        data: JSON.stringify(obj),
        type: 'PATCH',
        dataType: 'json',
        processData: false,
        complete: userSubmitDone});
    return false;
}

function do_user_edit_init()
{
    populateUserDropdown();
    populateAreaDropdown();
    $("#form").submit(userDataSubmitted);
}

$(do_user_edit_init);
