var _gid = null;

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

function groupsDone(jqXHR)
{
    if(jqXHR.status !== 200)
    {
        alert('Unable to obtain group list!');
        return;
    }
    var groups = jqXHR.responseJSON;
    for(var i = 0; i < groups.length; i++)
    {
        $('#group_select').append('<option value="'+groups[i].cn+'">'+groups[i].cn+'</option>');
    }
    var gid = getGID();
    if(gid != null)
    {
        $('#group_select').val(gid);
    }
}

function draw_done()
{
    $('td.removeControl').html('<span class="fa fa-minus"></span>');
    $('td.addControl').html('<span class="fa fa-plus"></span>');
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

function groupDataDone(jqXHR)
{
    if(jqXHR.status !== 200)
    {
        alert('Unable to obtain group list!');
        return;
    }
    var group = jqXHR.responseJSON;
    $('#gid').html(group.cn);
    $('#gid_edit').val(group.cn);
    $('#dn').html(group.dn);
    $('#description').val(group.description);
    var members = $('#members').DataTable();
    var non_members = $('#non-members').DataTable();
    members.clear();
    for(var i = 0; i < group.member.length; i++)
    {
        if(group.member[i].sn !== undefined)
        {
            group.member[i].type = 'User';
        }
        else
        {
            group.member[i].type = 'Group';
        }
    }
    members.rows.add(group.member).draw();
    non_members.ajax.url('../api/v1/groups/'+group.cn+'/non-members?$select=cn,mail,description,givenName,sn,uid,type&fmt=data-table').load();
    $('#group_data').show();
    $('#members tbody').on('click', 'td.removeControl', remove_clicked);
    $('#non-members tbody').on('click', 'td.addControl', add_clicked);
}

function populateGroupDropdown()
{
    //Turn off events on the dropdown
    $('#group_select').change(null);
    $.ajax({
        url: '../api/v1/groups?$select=cn',
        type: 'get',
        dataType: 'json',
        complete: groupsDone});
    //Enable events on the dropdown
    $('#group_select').change(groupSelectChange);
}

function populateGroupData()
{
    var gid = getGID();
    if(($('#group_data').length > 0) && (gid != null))
    {
        $.ajax({
            url: '../api/v1/groups/'+gid+'?$expand=member&directOnly=true',
            type: 'get',
            dataType: 'json',
            complete: groupDataDone});
    }
}

function groupSelectChange()
{
    _gid = $(this).val();
    populateGroupData(); 
}

function groupSubmitDone(jqXHR)
{
    if(jqXHR.status !== 200)
    {
        alert('Unable to update group!');
        console.log(jqXHR);
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
        url: '../api/v1/groups/'+getGID(),
        data: JSON.stringify(group),
        type: 'PATCH',
        contentType: 'application/json',
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
        'order': [[1, 'asc']]
    });
    populateGroupDropdown();
    populateGroupData();
    $("#form").submit(groupDataSubmitted);
    $('#submit').removeAttr("disabled");
}

$(do_group_edit_init);
