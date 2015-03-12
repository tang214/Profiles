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

function non_member_groups_done(data)
{
    tbody = $('#non_members tbody');
    var groups = {'count':0};
    for(i = 0; i < data.data.length; i++)
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

function group_submit_done(data)
{
    if(data.error)
    {
         if(data.invalid)
         {
             var label = $('[for="'+data.invalid+'"]');
             if(label.length > 0)
             {
                 label.html(data.error);
                 label.show();
             }
             else
             {
                 alert(data.error);
             }
         }
         else
         {
             alert(data.error);
         }
         console.log(data.error);
    }
    else
    {
        console.log(data);
        window.location = 'index.php';
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
        data: $(form).serialize()+members_str+'&action=new',
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
        'order': [[1, 'asc']],
        'ajax': 'ajax/groups.php?gid=null&nonMembersOnly=true'
    });
    $("#form").validate({
        debug: true,
        submitHandler: group_data_submitted
    });
    $('#members tbody').on('click', 'td.removeControl', remove_clicked);
    $('#non-members tbody').on('click', 'td.addControl', add_clicked);
    $('#submit').removeAttr("disabled");
}

$(do_group_edit_init);
