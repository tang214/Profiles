function render_name(data, type, row, meta)
{
    if(data === undefined)
    {
        return '';
    }
    if(data === false)
    {
        return row['sn'];
    }
    else
    {
        return data+' '+row['sn'];
    }
}

function render_email(data, type, row, meta)
{
    return '<a href="mailto:'+data+'">'+data+'</a>';
}

function render_phone(data, type, row, meta)
{
    if(data === null || data === false)
    {
        return '';
    }
    return '<a href="tel:'+data+'">'+data+'</a>';
}

function render_position(data, type, row, meta)
{
    if(data === false)
    {
        return '';
    }
    if(data != null)
    {
        return data+'<span style="display: none;">'+row.area+'</span>';
    }
    else
    {
        return '';
    }
}

var sort_method = null;

function modal_closed(e)
{
    var checkbox = $('#sort_by_area');
    if(checkbox[0].checked)
    {
        sort_method = 'area';
    }
    else
    {
        sort_method = 'alpha';
    }
    $('#directory').DataTable().order([2, 'desc']).draw();
}

function table_sorted(e)
{
    if(sort_method == null)
    {
        var body = 'Sort by area? <input type="checkbox" id="sort_by_area" checked><br/>Else will sort by the position name...';
        var modal = create_modal('Sort Type', body, [{'close': true, 'text': 'OK'}]);
        modal.modal();
        modal.on('hide.bs.modal', modal_closed);
        e.stopImmediatePropagation();
        return false;
    }
}

function get_area(text)
{
    var start = text.indexOf('>');
    var end = text.lastIndexOf('<');
    return text.substring(start+1, end);
}

function sort_position_asc(x, y)
{
    if(sort_method == null || sort_method == 'area')
    {
        var area_x = get_area(x);
        var area_y = get_area(y);
        if(area_x == 'AAR')
        {
            return 1;
        }
        if(area_y == 'AAR')
        {
            return 0;
        }
        if(area_x < area_y)
        {
            return 1;
        }
        return x < y;
    }
    else
    {
        return x < y;
    }
}

function sort_position_desc(x, y)
{
    if(sort_method == null || sort_method == 'area')
    {
        var area_x = get_area(x);
        var area_y = get_area(y);
        if(area_x == 'AAR')
        {
            return 0;
        }
        if(area_y == 'AAR')
        {
            return 1;
        }
        if(area_x > area_y)
        {
            return 1;
        }
        return x > y; 
    }
    else
    {
        return x > y;
    }
}

function table_drawn()
{
    $('#directory tbody tr').filter(
    function(){
        return $(this).find('td').length == $(this).find('td:empty').length;
    }).hide();
    $('#directory tbody tr').on('click', show_details);
}

function show_details(e)
{
    var tr    = $(this).closest('tr');
    var row   = $('#directory').DataTable().row(tr);
    var data  = row.data();
    var html  = '<table><tr>';
    html +=     '<td style="text-align: center; padding: .5em;"><a href="mailto:'+data.mail+'"><span class="glyphicon glyphicon-envelope" style="font-size: 2em"></span><br/>Email</a></td>';
    html +=     '<td style="text-align: center; padding: .5em;"><a href="tel:'+data.mobile+'"><span class="glyphicon glyphicon-earphone" style="font-size: 2em"></span><br/>Call</a></td>';
    html +=     '<td style="text-align: center; padding: .5em;"><a href="sms:'+data.mobile+'"><span class="glyphicon glyphicon-phone" style="font-size: 2em"></span><br/>Text</a></td>';
    html +=     '<td style="text-align: center; padding: .5em;"><a href="../api/v1/users/'+data.uid+'?fmt=vcard"><span class="glyphicon glyphicon-download" style="font-size: 2em"></span><br/>Download</a></td>';
    html +=     '</tr></table>';
    var title = data.givenName+' '+data.sn;
    if(data.jpegPhoto !== null && data.jpegPhoto != '')
    {
        title = '<img src="data:image/jpeg;base64,'+data.jpegPhoto+'" style="width:64px; height: 64px;"/> '+title;
    }
    else
    {
        //Use Gravitar images if no local image
        title = '<img src="//www.gravatar.com/avatar/'+CryptoJS.MD5(data.mail.toLowerCase())+'?d=identicon" style="width:64px; height: 64px;"/> '+title;
    }
    var modal = create_modal(title, html, [{'close': true, 'text': 'OK'}]);
    modal.modal();
    console.log(data);
}

function init_page()
{
    var filter = getParameterByName('filter');
    var data = '?fmt=data-table';
    if(filter != null)
    {
        if(filter == 'aar')
        {
            $('.page-header').html('Board Member Directory');
        }
        else if(filter == 'af')
        {
            $('.page-header').html('Area Facilitator Directory');
        }
        data+= '&type='+filter;
    }
    
    $.fn.dataTableExt.oSort['position-asc'] = sort_position_asc;
    $.fn.dataTableExt.oSort['position-desc'] = sort_position_desc;

    $('#directory th:nth-child(3)').on('click', table_sorted);
    var table = $('#directory').dataTable({
        'ajax': '../api/v1/leads'+data,
        'paging': false,
        'info': false,
        'order': [[0, 'asc']],
        'columns': [
            {'data':'givenName', 'render': render_name},
            {'data':'displayName', 'defaultContent':''},
            {'data':'titlenames', 'type': 'position', 'render': render_position},
            {'data':'ou', 'visible': false, 'defaultContent':''}
        ]
    });
    $('#directory').on('draw.dt', table_drawn);
}

$(init_page);
