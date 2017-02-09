<?php
include __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../vendor/mgp25/instagram-php/src/Instagram.php';

//Username and password
include "account.php";

$debug = false;

$inst = new \InstagramAPI\Instagram($username, $password,$debug);

try {
    $inst->login();
} catch (Exception $e) {
    echo 'something went wrong ' . $e->getMessage() . "\n";
    exit(0);
}

try {
    $helper = null;

    $usernameOfTarget = $argv[1];
    echo "Get followers of " . $usernameOfTarget. "\n";
    $userId = $inst->getUsernameId($usernameOfTarget);
    //print_r($userId);

    //$followers = $inst->getUserFollowers($userId);
    //print_r($followers->getFollowers());

    do {
        if (is_null($helper)) {
            $helper = $inst->getUserFollowers($userId);
        } else {
            $helper = $inst->getUserFollowers($userId, $helper->getNextMaxId());
        }

        foreach ($helper->getFollowers() as $follower) {
            $userId = $follower->getUsernameId();
            echo '- ' . $follower->getUsername() . " " . $userId . "\n";
            sleep(2);
	    $inst->follow($userId);
        }

        echo "Next round [" . $helper->getNextMaxId() . "] \n";

    } while (!is_null($helper->getNextMaxId()));

} catch (Exception $e) {
    echo $e->getMessage();
}

$inst->logout();

?>
