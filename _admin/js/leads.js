function area_change(control)
{
    $.ajax({
        url: '../api/v1/areas/'+$('#area_select').val()+'/leads',
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
    for(i = 0; i < data.length; i++)
    {
        var opt = $('<option/>', {value: data[i].short_name}).html(data[i].name);
            opt.appendTo($('#lead_select'));
            opt.data('lead', data[i]);
    }
}

function areas_done(data)
{
    for(i = 0; i < data.length; i++)
    {
        var opt = $('<option/>', {value: data[i].short_name}).html(data[i].name);
        opt.appendTo($('#area_select'));
        opt.data('area', data[i]);
    }
    $.ajax({
        url: '../api/v1/areas/'+$('#area_select').val()+'/leads',
        type: 'get',
        dataType: 'json',
        success: leads_done});
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
    var obj = {};
    obj.short_name = $('#short_name').val();
    obj.name = $('#name').val();
    obj.area = $('#area_select').val();
    $.ajax({
        url: '../api/v1/leads',
        data: JSON.stringify(obj),
        type: 'POST',
        processData: false,
        dataType: 'json',
        success: leads_post_done});
}

function init_page()
{
    $('#submit').on('click', submit_lead);
    $.ajax({
        url: '../api/v1/areas',
        type: 'get',
        dataType: 'json',
        success: areas_done});
}

$(init_page);
