<?php
include __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../vendor/mgp25/instagram-php/src/Instagram.php';

/////// CONFIG ///////
include "account.php";

error_reporting(E_ALL);

$debug = false;

$i = new \InstagramAPI\Instagram($username, $password, $debug);

try {
    $i->login();
} catch (Exception $e) {
    echo $e->getMessage();
    exit();
}


$photos = array();


foreach ($photos as $photo) {

    print_r($photo);
    try {
        $i->uploadPhoto($photo["file"], $photo["caption"]);
    } catch (Exception $e) {
        echo $e->getMessage();
    }
    sleep(10);
}


$i->logout();