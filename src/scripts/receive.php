<?php
require_once __DIR__ . '/../vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;

include "account.php";

$connection = new AMQPStreamConnection($amqp['host'], $amqp['port'], $amqp['user'], $amqp['password']);
$channel = $connection->channel();

//$channel->exchange_declare('mbt-bot.message.send', 'fanout', false, false, false);

//list($queue_name, ,) = $channel->queue_declare("", false, false, true, false);

$channel->queue_bind($amqp['queue'], $amqp['exchange'], '#');

echo ' [*] Waiting for logs. To exit press CTRL+C', "\n";

$callback = function($msg){
    echo ' [x] ', $msg->body, "\n";
};

$channel->basic_consume($amqp['queue'], '', false, true, false, false, $callback);

while(count($channel->callbacks)) {
    $channel->wait();
}

$channel->close();
$connection->close();
