function area_change(control)
{
    var val = $(control).val();
    if(val != '')
    {
        $('#area_details').show();
        if(val == '_new')
        {
            $('#area_name').html("New Area");
        }
        else
        {
            $('#area_name').html(val);
            var area = $('#area_select :selected').data('area');
            $('#short_name').val(area.short_name);
            $('#name').val(area.name);
        }
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
}

function areas_post_done(data)
{
    location.reload();
}

function submit_areas(event)
{
    var obj = {};
    var old_name = $('#area_name').html();
    obj.short_name = $('#short_name').val();
    obj.name = $('#name').val();
    if(old_name == obj.short_name)
    {
        $.ajax({
            url: '../api/v1/areas/'+obj.short_name,
            data: JSON.stringify(obj),
            type: 'PATCH',
            processData: false,
            dataType: 'json',
            success: areas_post_done});
            method = 'PATCH';
    }
    else
    {
        $.ajax({
            url: '../api/v1/areas',
            data: JSON.stringify(obj),
            type: method,
            processData: false,
            dataType: 'json',
            success: areas_post_done});
    }
}

function init_page()
{
    $('#submit').on('click', submit_areas);
    $.ajax({
        url: '../api/v1/areas',
        type: 'get',
        dataType: 'json',
        success: areas_done});
}

$(init_page);
