<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once('class.ProfilesPage.php');
$page = new ProfilesPage('Burning Flipside Org');

$page->add_js_from_src('https://www.google.com/jsapi', false);

$page->body .= '
<div id="content">
    <div id="people"></div>
</div>
<script>
google.load("visualization", "1", {packages:["orgchart"]});
google.setOnLoadCallback(drawChart);

var data;

function drawChart()
{
        data = new google.visualization.DataTable();
        data.addColumn("string", "Name");
        data.addColumn("string", "Manager");
        data.addColumn("string", "ToolTip");

        $.ajax({
            url: "../api/v1/areas?$filter=short_name%20ne%20%27AAR%27",
            type: "GET",
            dataType: "json",
            complete: gotAreas,
            context: data
        });
}

function gotAreas(jqXHR)
{
    var deferreds = [];
    this.addRow([{v: "AAR", f:"Austin Artistic Reconstruction, LLC<div id=\"lead_AAR\"></div>"}, "", "The Board of Directors"]);
    for(i = 0; i < jqXHR.responseJSON.length; i++)
    {
        var boxText = jqXHR.responseJSON[i].name+"<div id=\"lead_"+jqXHR.responseJSON[i].short_name+"\"></div>";
        this.addRow([{v: jqXHR.responseJSON[i].short_name, f: boxText}, "AAR", jqXHR.responseJSON[i].name]);
        deferreds.push($.ajax({url: "../api/v1/areas/"+jqXHR.responseJSON[i].short_name+"/leads", type: "GET", dataType: "json", context: this}));
    }
    $.when.apply($, deferreds).done(gotLeads);
}

function gotLeads()
{
    for(i = 0; i < arguments.length; i++)
    {
        var boxText = "<div style=\"vertical-align: top; display: block;\">Leads<hr/><hr/>";
        for(j = 0; j < arguments[i][0].length; j++)
        {
            if(arguments[i][0][j].short_name.endsWith("AF") || arguments[i][0][j].short_name === "VC")
            {
                continue;
            }
            boxText+=arguments[i][0][j].name+"<br/>";
            boxText+="<div id=\"lead_"+arguments[i][0][j].short_name+"\"></div><hr/>";
        }
        boxText+="</div>";
        data.addRow([boxText, arguments[i][0][0].area, ""]);
    }
    var chart = new google.visualization.OrgChart(document.getElementById("people"));
    chart.draw(data, {allowHtml: true});
    $("#people table").css("border-collapse", "separate");
}
</script>';

$page->print_page();
?>
