var time_left = 5;

function time_dec()
{
    time_left--;
    $('#secs').html(time_left);
    if(time_left == 0)
    {
        window.location = window.profilesUrl+'/login.php';
    }
}

setInterval(time_dec, 1000);
