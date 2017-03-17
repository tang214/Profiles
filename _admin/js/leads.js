function area_change()
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
        $('#submit').unbind('click');

        if(val == '_new')
        {
            $('#submit').on('click', add_lead);
            $('#lead_name').html("New Lead");
            $('#short_name').val('');
            $('#name').val('');
        }
        else
        {
            $('#submit').on('click', update_lead);
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
    for(var i = 0; i < data.length; i++)
    {
        opt = $('<option/>', {value: data[i].short_name}).html(data[i].name);
        opt.appendTo($('#lead_select'));
        opt.data('lead', data[i]);
    }
}

function areas_done(data)
{
    for(var i = 0; i < data.length; i++)
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

function form_vars(){
    return {
        short_name: $('#short_name').val(),
        name: $('#name').val(),
        area: $('#area_select').val()
    };
}

function add_lead()
{
    $.ajax({
        url: '../api/v1/leads',
        data: JSON.stringify(form_vars()),
        type: 'POST',
        processData: false,
        dataType: 'json',
        success: leads_post_done});
}

function update_lead()
{
    $.ajax({
        url: '../api/v1/leads/'+$('#lead_name').html(),
        data: JSON.stringify(form_vars()),
        type: 'PATCH',
        processData: false,
        dataType: 'json',
        success: leads_post_done});
}

function init_page()
{
    $.ajax({
        url: '../api/v1/areas',
        type: 'get',
        dataType: 'json',
        success: areas_done});
}

$(init_page);
