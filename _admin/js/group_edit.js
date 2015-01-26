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

function group_data_done(data)
{
    $('#gid').html(data.group.cn);
    $('#gid_edit').val(data.group.cn);
    $('#old_gid').val(data.group.cn);
    $('#dn').html(data.group.dn);
    $('#description').val(data.group.description);
    var members = $('#members').DataTable();
    var non_members = $('#non-members').DataTable();
    members.clear();
    members.rows.add(data.group.member).draw();
    non_members.ajax.url('ajax/groups.php?gid='+data.group.cn+'&nonMembersOnly=true').load();
    $('#group_data').show();
    $('#members tbody').on('click', 'td.removeControl', remove_clicked);
    $('#non-members tbody').on('click', 'td.addControl', add_clicked);
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
            url: 'ajax/groups.php?fullMember=true&gid='+gid,
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

function group_data_submitted(form)
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

function do_group_edit_init()
{
    $('#members').on('draw.dt', draw_done);
    $('#non-members').on('draw.dt', draw_done);
    $('#members').dataTable({
        'columns': [
            {'className':'removeControl','data':null,'defaultContent':'','orderable':false},
            {'data': "username", 'defaultContent':''},
            {'data': 'email', 'defaultContent':''},
            {'data': 'name', 'defaultContent':''}],
        'order': [[1, 'asc']]
    });
    $('#non-members').dataTable({
        'columns':[{'className':'addControl','data':null,'defaultContent':'','orderable':false},{'data': "username"},{'data': 'email'},{'data': 'name'}],
        'order': [[1, 'asc']]
    });
    populate_group_dropdown();
    populate_group_data();
    $("#form").validate({
        debug: true,
        submitHandler: group_data_submitted
    });
    $('#submit').removeAttr("disabled");
}

$(do_group_edit_init);
