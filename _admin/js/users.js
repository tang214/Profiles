function renderUID(data, type, row)
{
    return '<a href="user_edit.php?uid='+data+'">'+data+'</a>';
}

function renderName(data, type, row, meta)
{
    if(row['givenName'] !== false)
    {
        return row['givenName']+' '+data;
    }
    return data;
}

function onUserTableBodyClick()
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

function do_users_init()
{
    if($("#user_table").length > 0)
    {
        $('#user_table').dataTable({
            'ajax': '../api/v1/users?fmt=data-table&$select=uid,displayName,sn,mail,givenName',
            'columns': [
                {'data': 'uid', 'render': renderUID},
                {'data': 'displayName'},
                {'data': 'sn'},
                {'data': 'mail'},
                {'data': 'givenName', 'visible': false}
            ]
        });

        $("#user_table tbody").on('click', 'tr', onUserTableBodyClick);
    }
}

$(do_users_init);
