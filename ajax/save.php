<?php
if($_SERVER["HTTPS"] != "on")
{
    header("Location: https://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"]);
    exit();
}
require_once("class.FlipSession.php");
$user = FlipSession::get_user();
if($user == FALSE)
{
    echo json_encode(array('error' => "Not logged in!"));
    die();
}

if(strtoupper($_SERVER['REQUEST_METHOD']) == 'POST')
{
    $response = array();
    if(!isset($_POST['imgUrl']))
    {
        $response['status'] = 'error';
        $response['message'] = 'Error! Missing URL!';
    }
    else if(!isset($_POST['imgW'])  || !isset($_POST['imgInitW']) || !isset($_POST['imgH'])  || !isset($_POST['imgInitH']) ||
            !isset($_POST['cropW']) || !isset($_POST['cropH'])    || !isset($_POST['imgX1']) || !isset($_POST['imgY1']))
    {
        $response['status'] = 'error';
        $response['message'] = 'Error! Missing Parameters.';
    }
    else
    {
        $real_name = '.'.$_POST['imgUrl'];
        $src_img = new Imagick($real_name);
        if($_POST['imgW'] != $_POST['imgInitW'] || $_POST['imgH'] != $_POST['imgInitH'])
        {
            $src_img->resizeImage(intval($_POST['imgW']), intval($_POST['imgH']), imagick::FILTER_BOX, 0);
        }
        $src_img->cropImage($_POST['cropW'], $_POST['cropH'], $_POST['imgX1'], $_POST['imgY1']);
        $src_img->setFormat('jpeg');
        $src_img->setCompressionQuality(80);
        $image = $src_img->getImageBlob();
        $user->jpegPhoto = array($image);
        unlink($real_name);
        $response['status'] = 'success';
        $response['url'] = 'data:image/jpeg;base64,'.base64_encode($image);
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
