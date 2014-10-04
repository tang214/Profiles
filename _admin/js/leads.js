function area_change(control)
{
    $.ajax({
        url: 'ajax/leads.php',
        data: 'area_name='+$('#area_select').val(),
        type: 'get',
        dataType: 'json',
        success: leads_done});
}

function lead_change(control)
{
    var val = $(control).val();
    if(val != '')
    {
        $('#lead_details').show();
        if(val == '_new')
        {
            $('#lead_name').html("New Lead");
            $('#short_name').val('');
            $('#name').val('');
        }
        else
        {
            $('#lead_name').html(val);
            var lead = $('#lead_select :selected').data('lead');
            $('#short_name').val(lead.short_name);
            $('#name').val(lead.name);
        }
    }
}

function leads_done(data)
{
    $('#lead_select').empty();
    var opt = $('<option/>', {value: ''});
    opt.appendTo($('#lead_select'));
    opt = $('<option/>', {value: '_new'}).html('New...');
    opt.appendTo($('#lead_select'));
    if(data.leads != false)
    {
        for(i = 0; i < data.leads.length; i++)
        {
            var opt = $('<option/>', {value: data.leads[i].short_name}).html(data.leads[i].name);
            opt.appendTo($('#lead_select'));
            opt.data('lead', data.leads[i]);
        }
    }
}

function areas_done(data)
{
    if(data.areas != false)
    {
        for(i = 0; i < data.areas.length; i++)
        {
            var opt = $('<option/>', {value: data.areas[i].short_name}).html(data.areas[i].name);
            opt.appendTo($('#area_select'));
            opt.data('area', data.areas[i]);
        }
        $.ajax({
            url: 'ajax/leads.php',
            data: 'area_name='+$('#area_select').val(),
            type: 'get',
            dataType: 'json',
            success: leads_done});
    }
}

function leads_post_done(data)
{
    if(data.success !== undefined)
    {
        location.reload();
    }
    else
    {
        alert(data.error);
    }
}

function submit_lead(event)
{
    var short_name = $('#short_name').val();
    var name = $('#name').val();
    var area = $('#area_select').val();
    $.ajax({
        url: 'ajax/leads.php',
        data: 'short_name='+short_name+'&name='+name+'&area='+area,
        type: 'post',
        dataType: 'json',
        success: leads_post_done});
}

function init_page()
{
    $('#submit').on('click', submit_lead);
    $.ajax({
        url: 'ajax/areas.php',
        type: 'get',
        dataType: 'json',
        success: areas_done});
}

$(init_page);
