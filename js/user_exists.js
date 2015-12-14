function add_auth_header(jqXHR)
{
    var user = getParameterByName('uid');
    var pass = $('#link_pass').val();
    jqXHR.setRequestHeader('Authorization', 'Basic '+btoa(user+':'+pass));
}

function finish_link_accounts(jqXHR)
{
    if(jqXHR.status != 200)
    {
        if(jqXHR.responseJSON !== undefined)
        {
            alert(jqXHR.responseJSON.message);
        }
        else
        {
            alert('Failed to link accounts');
        }
    }
    else
    {
        window.location.href = 'index.php';
    }
    console.log(jqXHR);
}

function link_accounts()
{
    var obj = {};
    var prov = getParameterByName('src');
    switch(prov)
    {
        case 'google':
            obj.provider = 'google.com';
            break;
        default:
            obj.provider = prov;
            break;
    }
    $.ajax({
        'url': 'api/v1/users/me/Actions/link',
        'beforeSend': add_auth_header,
        'data': JSON.stringify(obj),
        'processData': false,
        'type': 'POST',
        'complete': finish_link_accounts 
    });
}
