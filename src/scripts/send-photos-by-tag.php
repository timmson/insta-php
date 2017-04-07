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
    $i = 0;
    $j = 0;
    while ($i < count($items) && $j < 5) {
        if ($items[$i]->isPhoto()) {
            $maxWidth = 0;
            $maxWidthUrl = '';
            $imageVersions = $items[$i]->getImageVersions();
            for ($k = 0; $k < count($imageVersions); $k++) {
                if ($imageVersions[$k]->getWidth() > $maxWidth) {
                    $maxWidth = $imageVersions[$k]->getWidth();
                    $maxWidthUrl = $imageVersions[$k]->getUrl();
                }
            }
            $msg = getMessage($to, $maxWidthUrl, "https://instagram.com/" . $items[$i]->getUser()->getUserName() . "/");
            $channel->basic_publish($msg, $amqp['exchange']);
            $j++;

        }
        $i++;
    }

    $channel->close();
    $connection->close();

} catch (Exception $e) {
    echo $e->getMessage();
}

$inst->logout();

function getMessage($to, $imageUrl, $profileUrl)
{
    $message = array(
        "to" => $to,
        "version" => 2,
        "type" => 'image_link',
        "text" => "Memes",
        "image" => $imageUrl,
        "url" => $profileUrl
    );

    $data = json_encode($message);

    return new AMQPMessage($data);
}

?>