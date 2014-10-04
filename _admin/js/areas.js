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
    if(data.areas != false)
    {
        for(i = 0; i < data.areas.length; i++)
        {
            var opt = $('<option/>', {value: data.areas[i].short_name}).html(data.areas[i].name);
            opt.appendTo($('#area_select'));
            opt.data('area', data.areas[i]);
        }
    }
}

function areas_post_done(data)
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

function submit_areas(event)
{
    var short_name = $('#short_name').val();
    var name = $('#name').val();
    $.ajax({
        url: 'ajax/areas.php',
        data: 'short_name='+short_name+'&name='+name,
        type: 'post',
        dataType: 'json',
        success: areas_post_done});
}

function init_page()
{
    $('#submit').on('click', submit_areas);
    $.ajax({
        url: 'ajax/areas.php',
        type: 'get',
        dataType: 'json',
        success: areas_done});
}

$(init_page);
