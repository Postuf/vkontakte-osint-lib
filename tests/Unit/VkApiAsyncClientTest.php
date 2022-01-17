<?php
declare(strict_types=1);

namespace Unit;

use PHPUnit\Framework\TestCase;
use VkontakeOSINT\AsyncClient\VkApiAsyncClient;
use VkontakeOSINT\Models\Error;
use VkontakeOSINT\Models\User;

class VkApiAsyncClientTest extends TestCase
{
    private VkApiAsyncClient $client;

    public function setUp(): void
    {
        $this->client = new VkApiAsyncClient('');
    }

    public function test_get_not_valid_token_error(): void
    {
        $code = 0;
        $run = true;
        $onError = static function (Error $error) use (&$code, &$run) {
           $code = $error->getCode();
           $run = false;
        };

        $onSuccess = static function (?User $user) {
        };

        $this->client->getUserInfo('1', $onError, $onSuccess);

        while ($run) {$this->client->pollMessage();}
        self::assertEquals(1, $code);
    }
}
