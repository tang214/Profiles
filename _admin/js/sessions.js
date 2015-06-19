function session_del_done(data)
{
    if(data.error === undefined)
    {
        $("#sessions").dataTable().api().ajax.reload();
    }
    else
    {
        alert("Session operation failed: "+data.error);
        console.log(data);
    }
}

function sessionExecute()
{
    var selected_rows = $('#sessions tr.selected');
    if(selected_rows.length < 1)
    {
        return;
    }
    switch($("#session_action")[0].value)
    {
        case "none":
            return;
        case "del":
            for(i = 0; i < selected_rows.length; i++)
            {
                $.ajax({
                    url: '../api/v1/sessions/'+selected_rows[i].childNodes[0].innerHTML,
                    type: 'DELETE',
                    success: session_del_done
                });
            }
            break;
    }
}

function renderSID(data, type, row, meta)
{
    if(row['current'] === true)
    {
        return 'This Session';
    }
    return row['sid'];
}

function renderUID(data, type, row, meta)
{
    if(row['AuthData'] !== undefined && row['AuthData']['extended'] !== undefined)
    {
        if(row['AuthData']['extended']['uid'] !== undefined)
        {
            return row['AuthData']['extended']['uid'];
        }
    }
    return 'Anonymous';
}

function renderIP(data, type, row, meta)
{
    if(row['ip_address'] !== undefined)
    {
        return row['ip_address'];
    }
    return 'Unknown';
}

function renderTime(data, type, row, meta)
{
    if(row['init_time'] !== undefined)
    {
        var date = new Date(row['init_time']);
        return date.toLocaleString();
    }
    return 'Unknown';
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
        $("#sessions").dataTable({
            'ajax': '../api/v1/sessions?fmt=data-table',
            'columns': [
                {'render': renderSID, 'data': null},
                {'render': renderUID, 'data': null},
                {'render': renderIP, 'data': null},
                {'render': renderTime, 'data': null}
            ]
        });

        $("#sessions tbody").on('click', 'tr', onSessionTableBodyClick);
    }
}

$(do_sessions_init);
