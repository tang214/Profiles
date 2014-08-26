var pendingTable;

function pending_submit_done(data)
{
    if(data.overall == true)
    {
        pendingTable.ajax.reload();
    }
    else
    {
        alert("One or more delete operation failed");
        console.log(data);
    }
}

function pendingExecute()
{
    var script;
    var selected = [];
    switch($("#pending_action")[0].value)
    {
        case "none":
            return;
        case "del":
            script = "ajax/del_pending.php";
            break;
    }
    var selected_rows = pendingTable.$('tr.selected');
    if(selected_rows.length < 1)
    {
        return;
    }
    for(i = 0; i < selected_rows.length; i++)
    {
        selected.push(selected_rows[i].childNodes[0].innerHTML);
    }
    $.ajax({
        url: script, 
        data: {'uids':selected}, 
        type: 'post',
        dataType: 'json',
        success: pending_submit_done});
}

function onPendingTableBodyClick()
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

function tableDrawComplete()
{
    if(pendingTable.api().data().length == 0)
    {
        $("#pending_set").empty();
        $("#pending_set").append("No Pending Registrations at this time.");
    }
}

function do_pending_init()
{
    if($("#pending_table").length > 0)
    {
        pendingTable = $("#pending_table").dataTable({
            "ajax": 'ajax/pending_users.php'
        });

        $("#pending_table tbody").on('click', 'tr', onPendingTableBodyClick);

        $("#pending_table").on('draw.dt', tableDrawComplete);
    }
}

$(do_pending_init);
