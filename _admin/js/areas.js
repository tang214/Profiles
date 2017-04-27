function area_change(control)
{
    var val = $(control).val();
    if(val != '')
    {
        $('#area_details').show();
        $('#submit').unbind('click');

        if(val == '_new')
        {
            $('#submit').on('click', add_area);
            $('#area_name').html("New Area");
        }
        else
        {
            $('#submit').on('click', update_area);
            $('#area_name').html(val);
            var area = $('#area_select :selected').data('area');
            $('#short_name').val(area.short_name);
            $('#name').val(area.name);
        }
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
}

function areas_post_done()
{
    location.reload();
}

function form_vars()
{
    return {
        short_name: $('#short_name').val(),
        name: $('#name').val()
    };
}

function add_area()
{
    $.ajax({
        url: '../api/v1/areas',
        data: JSON.stringify(form_vars()),
        type: 'POST',
        processData: false,
        dataType: 'json',
        success: areas_post_done});
}

function update_area()
{
    $.ajax({
        url: '../api/v1/areas/'+$('#area_name').html(),
        data: JSON.stringify(form_vars()),
        type: 'PATCH',
        processData: false,
        dataType: 'json',
        success: areas_post_done});
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
