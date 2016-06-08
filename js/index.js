var times = 0;

function clear_konami()
{
    $('#konami').remove();
    times++;
}

function konami()
{
    var marquee = $('<marquee/>', {behavior: 'slide', direction: 'left', id: 'konami'});
    var img = $('<img/>', {src: '/img/common/logo.svg'});
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

function pageInit()
{
    cheet('up up down down left right left right b a', konami);
}

$(pageInit);
