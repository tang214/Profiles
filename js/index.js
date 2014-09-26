var stage = 0;
var times = 0;

function clear_konami()
{
    $('#konami').remove();
    times++;
}

function on_key(event)
{
    if(stage == 0 && event.keyCode == 38)
    {
        stage = 1;
    }
    if(stage == 1 && event.keyCode == 38)
    {
        stage = 2;
    }
    if(stage == 2 && event.keyCode == 40)
    {
        stage = 3;
    }
    if(stage == 3 && event.keyCode == 40)
    {
        stage = 4;
    }
    if(stage == 4 && event.keyCode == 37)
    {
        stage = 5;
    }
    if(stage == 5 && event.keyCode == 39)
    {
        stage = 6;
    }
    if(stage == 6 && event.keyCode == 37)
    {
        stage = 7;
    }
    if(stage == 7 && event.keyCode == 39)
    {
        stage = 8;
    }
    if(stage == 8 && event.keyCode == 66)
    {
        stage = 9;
    }
    if(stage == 9 && event.keyCode == 65)
    {
        var marquee = $('<marquee/>', {behavior: 'slide', direction: 'left', id: 'konami'});
        var img = $('<img/>', {src: '/img/logo.svg'});
        if(times >= 1)
        {
            marquee.append("Here we go again...");
        }
        img.appendTo(marquee);
        var audio = $('<audio/>', {autoplay: true});
        $('<source/>', {src: '/media/contra.ogg', type: 'audio/ogg'}).appendTo(audio);
        audio.appendTo(marquee);
        marquee.appendTo($('#content'));
        setTimeout(clear_konami, 5000);
    }
}

$(document).keydown(on_key);
