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
    for ($i = 0; $i < 10; $i++) {
        foreach ($items[$i]->getImageVersions() as $imageVersion) {
            if ($imageVersion->getWidth() == '1080') {
                $msg = getMessage($to, $imageVersion->getUrl());
                $channel->basic_publish($msg, $amqp['exchange']);
            }
        }
    }

    $channel->close();
    $connection->close();

} catch (Exception $e) {
    echo $e->getMessage();
}

$inst->logout();

function getMessage($to, $url)
{
    $message = array(
        "to" => $to,
        "version" => 2,
        "type" => 'image_link',
        "text" => "Memes",
        "image" => $url,
        "url" => $url
    );

    $data = json_encode($message);

    return new AMQPMessage($data);
}

?>