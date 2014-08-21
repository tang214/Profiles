function renderGroupName(data, type, row)
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
        var cols = [
            {"render": renderGroupName, "targets": 0}
        ];

        $("#group_table").dataTable({
            "ajax": 'ajax/groups.php',
            "columnDefs": cols
        });

        $("#group_table tbody").on('click', 'tr', onGroupTableBodyClick);
    }
}

$(do_groups_init);
