function renderUID(data, type, row)
{
    return '<a href="user_edit.php?uid='+data+'">'+data+'</a>';
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
        var cols = [
            {"render": renderUID, "targets": 0}
        ];

        $("#user_table").dataTable({
            "ajax": 'ajax/users.php',
            "columnDefs": cols
        });

        $("#user_table tbody").on('click', 'tr', onUserTableBodyClick);
    }
}

$(do_users_init);
