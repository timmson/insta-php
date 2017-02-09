<?php
include __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../vendor/mgp25/instagram-php/src/Instagram.php';

//Username and password
include __DIR__ ."/account.php";

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

    $hashTag = $argv[1];

    $limit = 20;

    $cnt = 0;

    do {
        if (is_null($helper)) {
            $helper = $inst->getHashtagFeed($hashTag);
        } else {
            $helper = $inst->getHashtagFeed($hashTag, $helper->getNextMaxId());
        }

        $images = array();
        $items = $helper->getItems();//$helper->getRankedItems();

        foreach ($helper->getItems() as $item) {
            $mediaId = $item->getMediaId();
            $userId = $item->getUserNameId();
            //$user = $item->getUserName();
            echo "[".$cnt."]: id = " . $mediaId . " by " . $userId . " \n";
            $inst->like($mediaId);
            sleep(5);
            $inst->follow($userId);
            sleep(5);
            $cnt++;
        }

        //echo "Next round [" . $helper->getNextMaxId() . "] \n";

    } while ($cnt < $limit);

} catch (Exception $e) {
    echo $e->getMessage();
}

$inst->logout();

?>