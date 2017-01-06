<?php
require '/var/www/common/Autoload.php';
require '/var/www/common/libs/aws/aws-autoloader.php';
use Aws\S3\S3Client;

function on_new_socket_cb(ZMQSocket $socket, $persistent_id = null)
{
    if ($persistent_id === 'server')
    {
        $socket->bind("tcp://*:55555");
    } 
    else 
    {
        $socket->connect("tcp://127.0.0.1:55555");
    }
}

$context = new ZMQContext();
$socket = $context->getSocket(ZMQ::SOCKET_REP, 'server', 'on_new_socket_cb');
while(true)
{
    $function = $socket->recv();
    $message = unserialize(base64_decode($socket->recv()));
    $socket->send('ACK');
    switch($function)
    {
        case 'testX:':
            testX($message);
            break;
        case 'processListServMessage:':
            processListServMessage($message);
            break;
        default:
            echo "$function\n";
            print_r($message);
    }
}

function testX($message)
{
    $router = \Email\EmailRouter::getInstance();
    $ret = $router->routeSingle($message['dest'], $message['msg']);
    echo json_encode($ret);
}

function endswith($string, $test)
{
    $strlen = strlen($string);
    $testlen = strlen($test);
    if ($testlen > $strlen) return false;
    return substr_compare($string, $test, $strlen - $testlen, $testlen) === 0;
}

function getDestinationsForID($id)
{
    if(strcasecmp($id,'pboyd') === 0)
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
    $credentials = \Aws\Common\Credentials\Credentials::fromIni('default', '/var/www/secure_settings/aws.ini');

    $s3Client = S3Client::factory([
            'version' => 'latest',
            'region'  => 'us-west-2',
            'credentials' => $credentials
         ]);
    $object = $s3Client->getObject(array('Bucket'=>$action->bucketName, 'Key'=>$action->objectKey));
    return $object['Body']->__toString();
}

function encodeRecipients($recipient)
{
    if(is_array($recipient))
    {
        return join(', ', array_map(array($this, 'encodeRecipients'), $recipient));
    }
    if(preg_match("/(.*)<(.*)>/", $recipient, $regs))
    {
        $recipient = '=?UTF-8?B?'.base64_encode($regs[1]).'?= <'.$regs[2].'>';
    }
    return $recipient;
}

function fixMessage($message)
{
    $output = array();
    $array = explode("\n",$message);
    foreach($array as $arr)
    {
        if((preg_match('/^Return-Path:/',$arr)))
        {
            //Strip...
        }
        else if((preg_match('/^From:/',$arr)))
        {
            $from = trim(substr($arr, 5));
            if(preg_match("/(.*)<(.*)>/", $from, $regs))
            {
                $output[] = 'From: '.encodeRecipients('BurningFlipside Mailer On Behalf Of '.$regs[1].' <mailer@profiles.burningflipside.com>');
            }
            else
            {
                $output[] = 'From: '.encodeRecipients('BurningFlipside Mailer On Behalf Of '.$from.' <mailer@profiles.burningflipside.com>');
            }
            $output[] = 'Reply-To: '.$from;
        }
        else
        {
            $output[] = $arr;
        }
    }
    return implode("\n", $output);
}

function processListServMessage($message)
{
    $message = json_decode($message);
    $dests = getActualDestinations($message->mail->destination);
    if($dests === false)
    {
        return;
    }
    //print_r($dests);
    //print_r($message);
    $rawMessage = getRawMessage($message->receipt->action);
    //print_r($rawMessage);
    $rawMessage = fixMessage($rawMessage);
    $credentials = \Aws\Common\Credentials\Credentials::fromIni('default', '/var/www/secure_settings/aws.ini');
    $smtp = \Email\SMTPServer::getInstance();
    $ret = $smtp->connect('email-smtp.us-west-2.amazonaws.com');
    if(!$ret)
    {
        echo "Error connecting to SMTP server!\n";
        return;
    }
    $ret = $smtp->authenticate('AKIAIZT5BYBMPJVWJH3A', 'AhUgHVGrfyi0XErOwMGBDwwJzoVlop77py8AUNHYJScJ');
    if(!$ret)
    {
        echo "Error authenticating!\n";
        return;
    }
    foreach($dests as $dest)
    {
        $tmpRawMessage = "X-Original-To: $dest\nDelivered-To: mailer@profiles.burningflipside.com\nReturn-Path: mailer@profiles.burningflipside.com\n".$rawMessage;
        $ret = $smtp->sendOne('mailer@tickets.burningflipside.com', $dest, $tmpRawMessage);
        echo "Send = $ret\n";
    }
    $smtp->disconnect();
    echo "processListServMessage: Exited\n";
}
