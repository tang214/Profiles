function groupExecute()
{
    var action;
    var selected = [];
    switch($("#group_action")[0].value)
    {
        default:
        case "none":
            return;
        case "del":
            action = "delete";
            break;
        case "new":
            window.location = "group_new.php";
            break;
    }
    var selected_rows = $('#group_table tr.selected');
    if(selected_rows.length < 1)
    {
        return;
    }
    for(var i = 0; i < selected_rows.length; i++)
    {
        selected.push(selected_rows[i].childNodes[0].innerHTML);
    }
    $.ajax({
        url: 'ajax/sessions.php',
        data: {'sids':selected,'action':action},
        type: 'post',
        dataType: 'json',
        success: session_exec_done});
}

function renderGroupName(data)
{
    return '<a href="group_edit.php?gid='+data+'">'+data+'</a>';
}

function onGroupTableBodyClick()
{
    if($(this).hasClass('selected')) 
    {
        $(this).removeClass('selected');
    }
    else 
    {
        $(this).addClass('selected');
    }
}

function do_groups_init()
{
    if($("#group_table").length > 0)
    {
        $("#group_table").dataTable({
            'ajax': '../api/v1/groups?fmt=data-table',
            'columns': [
                {'data': 'cn', 'render': renderGroupName},
                {'data': 'description'}
            ]
        });

        $("#group_table tbody").on('click', 'tr', onGroupTableBodyClick);
    }
}

$(do_groups_init);
