<?php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use VkontakeOSINT\AsyncClient\VkApiAsyncClient;
use VkontakeOSINT\Models\User;

$asyncClient = new VkApiAsyncClient('auth_key');

$run = true;

$onSuccess = static function (?User $user) use (&$run){
    $run = false;
    echo 'Success' . PHP_EOL;
    if ($user instanceof User) {
        echo '=============================' . PHP_EOL;
        echo 'Profile: ' . $user->getProfileId() . PHP_EOL;
        echo 'FirstName: ' . $user->getFirstName() . PHP_EOL;
        echo 'LastName: ' . $user->getLastName() . PHP_EOL;
        echo 'Status: ' . $user->getStatus() . PHP_EOL;
        echo 'Timestamp: ' . $user->getTimestamp() . PHP_EOL;
        echo 'Photo: ' . $user->getPhoto() . PHP_EOL;
        echo '=============================' . PHP_EOL;
    }

};


$onError = static function (VkontakeOSINT\Models\Error $error) use (&$run) {
    $run = false;
    echo '=============================' . PHP_EOL;
    echo 'Code: ' . $error->getCode() . PHP_EOL;
    echo 'Message: ' . $error->getMessage() . PHP_EOL;
    echo '=============================' . PHP_EOL;
};

$asyncClient->getUserInfo('1', $onError, $onSuccess);

while ($run) {
    $asyncClient->pollMessage();
}

