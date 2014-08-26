function refreshAccorion()
{
    $("#accordian").accordion("refresh");
}

function do_index_init()
{
    if($("#accordian").length > 0)
    {
        $("#accordian").accordion({
            hieghtStyle: "content"
        });

        if($("#pending_table").length > 0)
        {
            $("#pending_table").on('draw.dt', refreshAccorion);
        }
        if($("#user_table").length > 0)
        {
            $("#user_table").on('draw.dt', refreshAccorion);
        }
        if($("#group_table").length > 0)
        {
            $("#group_table").on('draw.dt', refreshAccorion);
        }
    }
}

$(do_index_init);
