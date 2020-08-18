<?php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use VkontakeOSINT\Client\VkApiClient;
use VkontakeOSINT\Exceptions\VkException;

$vkClient = new VkApiClient('275a5d3a0f9a1fd8e455fbe90b8a4a70b6334393a8dda76195bde501d9776756a1e249c763882c942fe41');

try{
    $users = $vkClient->getUsers([1, 3, 56, 521118799]);
} catch (VkException $e) {
    print_r($e); die;
}

foreach ($users as $user) {
    echo '=============================' . PHP_EOL;
    echo 'Profile: ' . $user->getProfileId() . PHP_EOL;
    echo 'FirstName: ' . $user->getFirstName() . PHP_EOL;
    echo 'LastName: ' . $user->getLastName() . PHP_EOL;
    echo 'Status: ' . $user->getStatus() . PHP_EOL;
    echo 'Timestamp: ' . $user->getTimestamp() . PHP_EOL;
    echo 'Photo: ' . $user->getPhoto() . PHP_EOL;
    echo '=============================' . PHP_EOL;
}
