<?php
require '/var/www/common/libs/aws/aws-autoloader.php';
use Aws\Sns\MessageValidator\Message;
use Aws\Sns\MessageValidator\MessageValidator;
use Aws\S3\S3Client;
use Guzzle\Http\Client;

function aws()
{
    global $app;
    $app->post('/snsEndpoint(/)', 'snsEndpoint');
    $app->post('/test(/)', 'testX');
}

function sendToBackend($function, $payload)
{
    $context = new ZMQContext();
    $queue = new ZMQSocket($context, ZMQ::SOCKET_REQ);

    $queue->connect('tcp://127.0.0.1:55555');
    $message = base64_encode(serialize($payload));
    $queue->send($function.':', ZMQ::MODE_SNDMORE);
    $queue->send($message);
    return $queue->recv();
}

function testX()
{
    global $app;
    $array = $app->getJSONBody(true);

    echo sendToBackend('testX', $array);
    die();
}

function snsEndpoint()
{
    global $app;
    $array = $app->getJSONBody(true);

    try
    {
        $message = Message::fromArray($array);

        // Validate the message
        $validator = new MessageValidator();
        $validator->validate($message);
    }
    catch(\Exception $e)
    {
        $app->notFound();
    }

    $type = $message->get('Type');
    switch($type)
    {
        case 'SubscriptionConfirmation':
            (new Client)->get($message->get('SubscribeURL'))->send();
            break;
        case 'Notification':
            $arn = $message->get('TopicArn');
            if($arn !== false)
            {
                $pos = strpos($arn, 'Listserv');
                if($pos !== false)
                {
                    sendToBackend('processListServMessage', $message->get('Message'));
                    echo 'true';
                    return;
                }
            }
        default:
            file_put_contents('/var/www/profiles/tmp/log.log', print_r($message, true), FILE_APPEND);
            break;
    }
}

function endswith($string, $test)
{
    $strlen = strlen($string);
    $testlen = strlen($test);
    if($testlen > $strlen)
    {
        return false;
    }
    return substr_compare($string, $test, $strlen - $testlen, $testlen) === 0;
}

function getDestinationsForID($id)
{
    if(strcasecmp($id, 'pboyd') === 0)
    {
        return array('pboyd04@gmail.com');
    }
    else
    {
        return false;
    }
}

function getActualDestinations($originals)
{
    $ret = array();
    $count = count($originals);
    for($i = 0; $i < $count; $i++)
    {
        $dest = $originals[$i];
        if(endswith($dest, 'burningflipside.com'))
        {
            $parts = explode('@', $dest);
            if(count($parts) === 2)
            {
                $dests = getDestinationsForID($parts[0]);
                if($dests === false)
                {
                    file_put_contents('/var/www/profiles/tmp/log.log', "getActualDestinations: Invalid destination id $parts[0]\n", FILE_APPEND);
                }
                else
                {
                    $ret = array_merge($ret, $dests);
                }
            }
            else
            {
                file_put_contents('/var/www/profiles/tmp/log.log', "getActualDestinations: Invalid destination format $dest\n", FILE_APPEND);
            }
        }
    }
    return $ret;
}

function getRawMessage($action)
{
    $s3Client = S3Client::factory();
    $object = $s3Client->getObject(array('Bucket'=>$action['bucketName'], 'Key'=>$action['objectKey']));
    file_put_contents('/var/www/profiles/tmp/log.log', print_r($object, true), FILE_APPEND);
    return $object;
}

function processListServMessage($message)
{
    $message = json_decode($message);
    $dests = getActualDestinations($message->mail->destination);
    file_put_contents('/var/www/profiles/tmp/log.log', print_r($dests, true), FILE_APPEND);
    $rawMessage = getRawMessage($message['action']);
    //file_put_contents('/var/www/profiles/tmp/log.log', print_r($message, true), FILE_APPEND);
    file_put_contents('/var/www/profiles/tmp/log.log', "processListServMessage: Exited\n", FILE_APPEND);
}

?>
