<?php
if($_SERVER["HTTPS"] != "on")
{
    header("Location: https://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"]);
    exit();
}
require_once("class.FlipSession.php");
if(!FlipSession::is_logged_in())
{
    echo json_encode(array('error' => "Not logged in!"));
    die();
}

if(strtoupper($_SERVER['REQUEST_METHOD']) == 'POST')
{
    $response = array();
    if(!isset($_FILES['img']))
    {
        $response['status'] = 'error';
        $response['message'] = 'Error! Invalid upload!';
    }
    else if($_FILES['img']['error'] > 0)
    {
        $response['status'] = 'error';
        $response['message'] = 'Error! Upload return code: '.$_FILES['img']['error'];
    }
    else
    {
        $user = FlipSession::get_user();
        $filename = $_FILES["img"]["tmp_name"];
        list($width, $height) = getimagesize($filename);
        $newname = "./tmp/".$user->uid.$_FILES["img"]["name"];
        move_uploaded_file($filename,  '.'.$newname);

        $response["status"] = 'success';
        $response[ "url"]   = $newname;
        $response["width"]  = $width;
        $response["height"] = $height;
    }
    echo json_encode($response);
}
else
{
    echo json_encode(array('error' => "Unrecognized Operation ".$_SERVER['REQUEST_METHOD']));
    die();
}
/* vim: set tabstop=4 shiftwidth=4 expandtab: */
?>
