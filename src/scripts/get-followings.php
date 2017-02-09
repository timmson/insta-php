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

    $i = 0;

    //include 'shlak.php';
    //echo count($shlak);


   do {
        if (is_null($helper)) {
            $helper = $inst->getSelfUsersFollowing();
        } else {
            $helper = $inst->getSelfUsersFollowing($helper->getNextMaxId());
        }


        foreach ($helper->getFollowings() as $following) {
            sleep(2);
	    if (!in_array($following->getUsername(), $shlak, true)) {
                $userId = $following->getUsernameId();
                echo ($i++) . '- ' . $following->getUsername() . " " . $userId . "\n";
                $inst->unfollow($userId);
            }
        }

        /*        if (is_null($helper)) {
                    $helper = $inst->getSelfUserFollowers();
                } else {
                    $helper = $inst->getSelfUserFollowers($helper->getNextMaxId());
                }



                foreach ($helper->getFollowers() as $following) {
                    $userId = $following->getUsernameId();
                    //echo ($i++).'- ' . $following->getUsername() . " " . $userId . "\n";
                    echo '"'.$following->getUsername(). '", ' . "\n";
                }*/

   } while (!is_null($helper->getNextMaxId()));

} catch (Exception $e) {
    echo $e->getMessage();
}

$inst->logout();

?>
