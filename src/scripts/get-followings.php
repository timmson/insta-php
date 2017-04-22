<?php
include __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../vendor/mgp25/instagram-php/src/Instagram.php';

//Username and password
include "account.php";

$debug = false;

$inst = new \InstagramAPI\Instagram($username, $password, $debug);

try {
    $inst->login();
} catch (Exception $e) {
    echo 'something went wrong ' . $e->getMessage() . "\n";
    exit(0);
}

try {
    $helper = null;

    $i = 0;


    $followingCount = $inst->getSelfUsernameInfo()->getFollowingCount();

    print_r("Following count = ".$followingCount);

    if ($followingCount > 172/*magic number*/) {

        $helper = $inst->getSelfUsersFollowing();

        foreach ($helper->getFollowings() as $following) {
            $userId = $following->getUsernameId();
            echo $following->getUsername() . " " . $userId . "\n";
            $inst->unfollow($userId);
            sleep(2);
        }
    }


} catch (Exception $e) {
    echo $e->getMessage();
}

$inst->logout();

?>
