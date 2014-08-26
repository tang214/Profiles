var _gid = null;

function getParameterByName(name) {
    name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
    var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
        results = regex.exec(location.search);
    return results == null ? null : decodeURIComponent(results[1].replace(/\+/g, " "));
}

function getGID()
{
    if(_gid != null)
    {
        return _gid;
    }
    else
    {
        return getParameterByName('gid');
    }
}

function groups_done(data)
{
    var groups = data.data;
    for(i = 0; i < groups.length; i++)
    {
        $('#group_select').append('<option value="'+groups[i][0]+'">'+groups[i][0]+'</option>');
    }
    var gid = getGID();
    if(gid != null)
    {
        $('#group_select').val(gid);
    }
}

function strncmp(str1, str2, n)
{
    str1 = str1.substring(0, n);
    str2 = str2.substring(0, n);
    if(str1 == str2)
    {
        return 0;
    }
    else if(str1 > str2)
    {
        return 1;
    }
    else
    {
        return -1;
    }
}

function tooltip_data_done(data)
{
    $(this).attr("title", data.description);
    $(this).tooltip({content: data.description});
}

function user_tooltip_data_done(data)
{
    $(this).attr("title", data.displayName);
    $(this).tooltip({content: data.displayName});
    if(data.jpegPhoto.length > 0)
    {
        alert("TODO: Implement real photo");
    }
}

function start_drag()
{
    $('.snap-to:empty').css("background-color", "azure");
}

function stop_drag()
{
    $('.snap-to').css("background-color", "transparent");
}

function user_to_html(uid)
{
    var div = $('<div>');
    div.draggable({
        cursor: 'move',
        snap: '.snap-to',
        snapMode: 'inner',
        snapTolerance: 60,
        revert: "invalid",
        start: start_drag,
        stop: stop_drag
    });
    var img = $('<img>');
    img.attr("src", "images/user.svg");
    img.attr("height", '60');
    img.attr("width", '60');
    div.css('text-align', 'center');
    div.append(img);
    div.append('<br/>');
    div.append(uid);
    div.attr("id", "user="+uid);
    $.ajax({
        url: '/ajax/user.php?uid='+uid,
        type: 'get',
        context: div,
        dataType: 'json',
        success: user_tooltip_data_done});
    return div;
}

function group_open(eventData)
{
    _gid = eventData.data;
    $('#group_select').val(_gid);
    populate_group_data();
}

function group_to_html(gid)
{
    var div = $('<div>');
    div.draggable({
        cursor: 'move',
        snap: '.snap-to',
        snapMode: 'inner',
        snapTolerance: 60,
        revert: "invalid",
        start: start_drag,
        stop: stop_drag
    });
    var img = $('<img>');
    img.attr("src", "images/group.svg");
    img.attr("height", '60');
    img.attr("width", '60');
    div.css('text-align', 'center');
    div.append(img);
    div.append('<br/>');
    div.append(gid);
    div.dblclick(gid, group_open);
    div.attr("id", "group="+gid);
    $.ajax({
        url: 'ajax/groups.php?gid='+gid,
        type: 'get',
        context: div,
        dataType: 'json',
        success: tooltip_data_done});
    return div;
}

function get_user_or_group_html(dn)
{
    if(strncmp(dn, "uid=", 4) == 0)
    {
        //is User
        var uid = dn.split(",")[0].substring(4);
        return user_to_html(uid);
    }
    else if(strncmp(dn, "cn=", 3) == 0)
    {
        //is Group
        var gid = dn.split(",")[0].substring(3);
        return group_to_html(gid);
    }
    else if(dn.indexOf(',' != -1))
    {
        //posixUser
        return user_to_html(dn);
    }
    else
    {
        //Unknown type!
        return dn;
    }
}

function add_row_to_table(tbody)
{
    var row = $('<tr>');
    for(i = 0; i < 4; i++)
    {
        new_user_cell().appendTo(row)
    }
    tbody.append(row);
}

function check_tables_full()
{
    if($('#non_members .snap-to:empty').length == 0)
    {
        add_row_to_table($('#non_members tbody'));
    }
    if($('#group_members .snap-to:empty').length == 0)
    {
        add_row_to_table($('#group_members tbody'));
    }
}

function check_members_empty()
{
    if($('#group_members .snap-to :not(:empty)').length == 0)
    {
        alert('Groups require one or more members!');
        $('#submit').attr("disabled", "disabled");
    }
    else
    {
        $('#submit').removeAttr("disabled");
    }
}

function drop_user_or_group(event, ui)
{
    var element = ui.draggable.detach();
    element.css('top', 'auto');
    element.css('left', 'auto');
    $(event.target).append(element);
    check_tables_full();
    check_members_empty();
}

function new_user_cell()
{
    var cell = $('<td>');
    cell.attr("class", "snap-to");
    cell.css("width", "60");
    cell.css("height", "90");
    cell.droppable({
        drop: drop_user_or_group
    });
    return cell;
}

function add_users_to_table(tbody, users, clear, empty_row)
{
    if(clear === undefined || clear == true)
    {
        tbody.empty();
    }
    if(users === undefined || users.count === undefined)
    {
        if(empty_row === undefined || empty_row == true)
        {
            add_row_to_table(tbody);
        }
    }
    else
    {
        for(i = 0; i < users.count; i+=4)
        {
            var row = $('<tr>');
            for(j = 0; j < 4; j++)
            {
                if(i+j >= users.count)
                {
                    new_user_cell().appendTo(row);
                }
                else
                {
                    new_user_cell().html(get_user_or_group_html(users[i+j])).appendTo(row);
                }
            }
            tbody.append(row);
        }
    }
    if(empty_row === undefined || empty_row == true)
    {
        check_tables_full();
    }
}

function group_data_done(data)
{
    $('#gid').html(data.cn);
    $('#gid_edit').val(data.cn);
    $('#old_gid').val(data.cn);
    $('#dn').html(data.dn);
    $('#description').val(data.description);
    var tbody = $('#group_members tbody');
    add_users_to_table(tbody, data.member);
    $.ajax({
            url: 'ajax/groups.php',
            type: 'get',
            dataType: 'json',
            success: non_member_groups_done});
    $('#group_data').show();
}

function groupAlreadyMember(gid)
{
    var divs = $("#group_members tbody tr td div:contains('"+gid+"')");
    return divs.length >= 1;
}

function userAlreadyMember(uid)
{
    var divs = $("#group_members tbody tr td div:contains('"+uid+"')");
    return divs.length >= 1;
}

function non_member_groups_done(data)
{
    tbody = $('#non_members tbody');
    var groups = {'count':0};
    for(i = 0; i < data.data.length; i++)
    {
        if(data.data[i][0] == getGID())
        {
            continue;
        }
        else if(groupAlreadyMember(data.data[i][0]))
        {
            continue;
        }
        groups[groups.count] = "cn="+data.data[i][0];
        groups.count++;
    }
    add_users_to_table(tbody, groups, true, false);
    $.ajax({
            url: 'ajax/users.php',
            type: 'get',
            dataType: 'json',
            success: non_member_users_done});
}

function non_member_users_done(data)
{
    var tbody = $('#non_members tbody');
    var users = {'count':0};
    for(i = 0; i < data.data.length; i++)
    {
        if(userAlreadyMember(data.data[i][0]))
        {
            continue;
        }
        users[users.count] = "uid="+data.data[i][0];
        users.count++;
    }
    add_users_to_table(tbody, users, false);
}

function populate_group_dropdown()
{
    //Turn off events on the dropdown
    $('#group_select').change(null);
    $.ajax({
        url: 'ajax/groups.php',
        type: 'get',
        dataType: 'json',
        success: groups_done});
    //Enable events on the dropdown
    $('#group_select').change(groupSelectChange);
}

function populate_group_data()
{
    var gid = getGID();
    if(($('#group_data').length > 0) && (gid != null))
    {
        $.ajax({
            url: 'ajax/groups.php?gid='+gid,
            type: 'get',
            dataType: 'json',
            success: group_data_done});
    }
}

function groupSelectChange()
{
    _gid = $(this).val();
    populate_group_data(); 
}

function group_submit_done(data)
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

function group_data_submitted(form)
{
    var member_divs = $("#group_members tbody tr td div");
    var members_str = "";
    for(i = 0; i < member_divs.length; i++)
    {
        members_str += "&members[]="+member_divs[i].id;
    } 
    $.ajax({
        url: 'ajax/groups.php',
        data: $(form).serialize()+members_str,
        type: 'post',
        dataType: 'json',
        success: group_submit_done});
}

function do_group_edit_init()
{
    populate_group_dropdown();
    populate_group_data();
    $("#form").validate({
        debug: true,
        submitHandler: group_data_submitted
    });
    $('#submit').removeAttr("disabled");
}

$(do_group_edit_init);