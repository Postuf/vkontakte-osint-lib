<?php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use VkontakeOSINT\Client\VkApiClient;
use VkontakeOSINT\Exceptions\VkException;

$vkClient = new VkApiClient('auth_key');

try{
    $users = $vkClient->getUsers([1, 3, 56, 521118799]);
} catch (VkException $e) {
    print_r($e); die;
}

foreach ($users as $user) {
    echo '=============================' . PHP_EOL;
    echo 'Profile: ' . $user->profileId . PHP_EOL;
    echo 'FirstName: ' . $user->firstName . PHP_EOL;
    echo 'LastName: ' . $user->lastName . PHP_EOL;
    echo 'Status: ' . $user->status . PHP_EOL;
    echo 'Timestamp: ' . $user->timestamp . PHP_EOL;
    echo 'Photo: ' . $user->photo . PHP_EOL;
    echo '=============================' . PHP_EOL;
}
