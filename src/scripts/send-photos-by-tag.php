<?php
include __DIR__ . '/../vendor/autoload.php';

use InstagramAPI\Instagram;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

//Username and password
include "account.php";

$debug = false;

$inst = new Instagram($username, $password, $debug);

try {
    $inst->login();
} catch (Exception $e) {
    echo 'something went wrong ' . $e->getMessage() . "\n";
    exit(0);
}

try {

    $connection = new AMQPStreamConnection($amqp['host'], $amqp['port'], $amqp['user'], $amqp['password']);
    $channel = $connection->channel();

    $helper = null;

    $hashTag = $argv[1];

    $helper = $inst->getHashtagFeed($hashTag);

    $items = $helper->getItems(); //$helper->getRankedItems();

    for ($i = 0; $i < 5 && $i < count($items); $i++) {
        $maxWidth = 0;
        $maxWidthUrl = '';
        $versions = $items[$i]->isPhoto() ? $items[$i]->getImageVersions() : $items[$i]->getVideoVersions();
        for ($k = 0; $k < count($versions); $k++) {
            if ($versions[$k]->getWidth() > $maxWidth) {
                $maxWidth = $versions[$k]->getWidth();
                $maxWidthUrl = $versions[$k]->getUrl();
            }
        }
        $profileUrl = "https://instagram.com/" . $items[$i]->getUser()->getUserName() . "/";
        $msg = $items[$i]->isPhoto() ? getPhotoMessage($to, $maxWidthUrl, $profileUrl) : getVideoMessage($to, $maxWidthUrl, $profileUrl);
        $channel->basic_publish($msg, $amqp['exchange']);
    }

    $channel->close();
    $connection->close();

} catch (Exception $e) {
    echo $e->getMessage();
}

$inst->logout();

function getPhotoMessage($to, $imageUrl, $profileUrl)
{
    $message = getMessage($to, $profileUrl);
    $message['type'] = 'image_link';
    $message['image'] = $imageUrl;
    return new AMQPMessage(json_encode($message));
}

function getVideoMessage($to, $videoUrl, $profileUrl)
{
    $message = getMessage($to, $profileUrl);
    $message['type'] = 'video_link';
    $message['video'] = $videoUrl;
    return new AMQPMessage(json_encode($message));
}

function getMessage($to, $profileUrl)
{
    return array(
        "to" => $to,
        "version" => 2,
        "type" => 'image_link',
        "text" => "Memes",
        "url" => $profileUrl
    );
}

?>