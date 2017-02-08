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

function draw_done()
{
    $('td.removeControl').html('<span class="glyphicon glyphicon-minus"></span>');
    $('td.addControl').html('<span class="glyphicon glyphicon-plus"></span>');
}

function add_clicked()
{
    var tr = $(this).closest('tr');
    var non_members = $('#non-members').DataTable();
    var members = $('#members').DataTable();
    var row = non_members.row(tr);
    var data = row.data();
    row.remove().draw(false);
    try
    {
        members.row.add(data).draw(false);
    }
    catch(TypeError)
    {
        /*Ignore Type errors when adding aata*/
    }
}

function remove_clicked()
{
    var tr = $(this).closest('tr');
    var non_members = $('#non-members').DataTable();
    var members = $('#members').DataTable();
    var row = members.row(tr);
    var data = row.data();
    row.remove().draw(false);
    try
    {
        non_members.row.add(data).draw(false);
    }
    catch(TypeError)
    {
        /*Ignore Type errors when adding aata*/
    }
}

function non_member_groups_done(data)
{
    var tbody = $('#non_members tbody');
    var groups = {'count':0};
    for(var i = 0; i < data.data.length; i++)
    {
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

function groupSubmitDone(jqXHR)
{
    if(jqXHR.status !== 200)
    {
        alert('Unable to update group!');
        return;
    }
    alert('Success!');
    location = 'group_edit.php?gid='+getGID();
}

function groupDataSubmitted(e)
{
    e.preventDefault();
    var group = $('#form :input:not(select)').serializeObject();
    var members = $('#members').DataTable().data();
    group.member = [];
    for(var i = 0; i < members.length; i++)
    {
       var child = {};
       child.type = members[i].type;
       if(members[i].type === 'Group')
       {
           child.cn = members[i].cn;
       }
       else
       {
           child.uid = members[i].uid;
       }
       group.member.push(child);
    }
    $.ajax({
        url: '../api/v1/groups',
        data: JSON.stringify(group),
        type: 'POST',
        dataType: 'json',
        processData: false,
        complete: groupSubmitDone});
    return false;
}

function renderID(data, type, row)
{
    if(row.uid !== undefined)
    {
        return row.uid;
    }
    else
    {
        return row.cn;
    }
}

function renderName(data, type, row)
{
    if(row.sn !== undefined)
    {
        return row.givenName+' '+row.sn;
    }
    else
    {
        return row.description;
    }
}

function do_group_edit_init()
{
    $('#members').on('draw.dt', draw_done);
    $('#non-members').on('draw.dt', draw_done);
    $('#members').dataTable({
        'columns': [
            {'className':'removeControl','data':null,'defaultContent':'','orderable':false},
            {'data': 'uid', 'defaultContent':'', 'render': renderID},
            {'data': 'mail', 'defaultContent':'N/A'},
            {'data': 'name', 'defaultContent':'', 'render': renderName}],
        'order': [[1, 'asc']]
    });
    $('#non-members').dataTable({
        'columns':[
            {'className':'addControl','data':null,'defaultContent':'','orderable':false},
            {'data': 'uid', 'defaultContent':'', 'render': renderID},
            {'data': 'mail', 'defaultContent':'N/A'},
            {'data': 'name', 'defaultContent':'', 'render': renderName}],
        'order': [[1, 'asc']],
        'ajax': '../api/v1/groups/none/non-members?$select=cn,mail,description,givenName,sn,uid,type&fmt=data-table'
    });
    $("#form").submit(groupDataSubmitted);
    $('#members tbody').on('click', 'td.removeControl', remove_clicked);
    $('#non-members tbody').on('click', 'td.addControl', add_clicked);
    $('#submit').removeAttr("disabled");
}

$(do_group_edit_init);
