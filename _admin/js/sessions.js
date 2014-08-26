function session_exec_done(data)
{
    if(data.error === undefined)
    {
        console.log(data);
    }
    else
    {
        alert("Session operation failed: "+data.error);
        console.log(data);
    }
}

function sessionExecute()
{
    var action;
    var selected = [];
    switch($("#session_action")[0].value)
    {
        case "none":
            return;
        case "del":
            action = "delete";
            break;
    }
    var selected_rows = $('#sessions tr.selected');
    if(selected_rows.length < 1)
    {
        return;
    }
    for(i = 0; i < selected_rows.length; i++)
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

function renderSID(data, type, row)
{
    if(row[4] == 1)
    {
        return 'This Session';
    }
    return data;
}

function renderTime(data, type, row)
{
    var date = new Date(data);
    return date.toLocaleString();
}

function onSessionTableBodyClick()
{
    if($(this).hasClass('selected')) 
    {
        $(this).removeClass('selected');
    }
    else 
    {
        if($('td:contains("This Session")', this).length > 0)
        {
            alert("This is the current session. You cannot bulk edit this session");
            return;
        }
        $(this).addClass('selected');
    }
}

function do_sessions_init()
{
    if($("#sessions").length > 0)
    {
        var cols = [
            {"render": renderSID, "targets": 0},
            {"render": renderTime, "targets": 3}
        ];

        $("#sessions").dataTable({
            "ajax": 'ajax/sessions.php',
            "columnDefs": cols
        });

        $("#sessions tbody").on('click', 'tr', onSessionTableBodyClick);
    }
}

$(do_sessions_init);
