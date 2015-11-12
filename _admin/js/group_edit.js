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
        console.log(jqXHR);
        return;
    }
    var groups = jqXHR.responseJSON;
    for(i = 0; i < groups.length; i++)
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
    try{
        members.row.add(data).draw(false);
    } catch(TypeError) {}
}

function remove_clicked()
{
    var tr = $(this).closest('tr');
    var non_members = $('#non-members').DataTable();
    var members = $('#members').DataTable();
    var row = members.row(tr);
    var data = row.data();
    row.remove().draw(false);
    try{
        non_members.row.add(data).draw(false);
    } catch(TypeError) {}
}

function groupDataDone(jqXHR)
{
    if(jqXHR.status !== 200)
    {
        alert('Unable to obtain group list!');
        console.log(jqXHR);
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
    members.rows.add(group.member).draw();
    non_members.ajax.url('../api/v1/groups/'+group.cn+'/non-members?$select=cn,mail,description,givenName,sn,uid&fmt=data-table').load();
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
            url: '../api/v1/groups/'+gid+'?$expand=member',
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

function group_submit_done(data)
{
    if(data.error)
    {
         alert(data.error);
         console.log(data.error);
    }
    else
    {
        if(data.unset.length == 0)
        {
            alert('Success!');
        }
        else
        {
            var str = 'Did not set: \n';
            for(i = 0; i < data.unset.length; i++)
            {
                str += data.unset[i]+'\n';
            }
        }
        console.log(data);
    }
}

function groupDataSubmitted(e)
{
    var members = $('#members').DataTable().data();
    var members_str = "";
    for(i = 0; i < members.length; i++)
    {
        members_str += "&members[]="+members[i].dn;
    }
    $.ajax({
        url: 'ajax/groups.php',
        data: $('#form :input:not(select)').serialize()+members_str+"&action=edit",
        type: 'post',
        dataType: 'json',
        success: group_submit_done});
}

function renderName(data, type, row)
{
    return row.givenName+' '+row.sn;
}

function do_group_edit_init()
{
    $('#members').on('draw.dt', draw_done);
    $('#non-members').on('draw.dt', draw_done);
    $('#members').dataTable({
        'columns': [
            {'className':'removeControl','data':null,'defaultContent':'','orderable':false},
            {'data': 'uid', 'defaultContent':''},
            {'data': 'mail', 'defaultContent':''},
            {'data': 'name', 'defaultContent':'', 'render': renderName}],
        'order': [[1, 'asc']]
    });
    $('#non-members').dataTable({
        'columns':[
            {'className':'addControl','data':null,'defaultContent':'','orderable':false},
            {'data': 'cn'},
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
