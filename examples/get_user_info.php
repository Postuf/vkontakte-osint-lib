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
        echo 'Profile: ' . $user->profileId . PHP_EOL;
        echo 'FirstName: ' . $user->firstName . PHP_EOL;
        echo 'LastName: ' . $user->lastName . PHP_EOL;
        echo 'Status: ' . $user->status . PHP_EOL;
        echo 'Timestamp: ' . $user->timestamp . PHP_EOL;
        echo 'Photo: ' . $user->photo . PHP_EOL;
        echo '=============================' . PHP_EOL;
    }

};


$onError = static function (VkontakeOSINT\Models\Error $error) use (&$run) {
    $run = false;
    echo '=============================' . PHP_EOL;
    echo 'Code: ' . $error->code . PHP_EOL;
    echo 'Message: ' . $error->message . PHP_EOL;
    echo '=============================' . PHP_EOL;
};

$asyncClient->getUserInfo('1', $onError, $onSuccess);

while ($run) {
    $asyncClient->pollMessage();
}

