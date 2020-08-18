<?php
declare(strict_types=1);

namespace Unit;

use PHPUnit\Framework\TestCase;
use VkontakeOSINT\Client\VkApiClient;
use VkontakeOSINT\Exceptions\VkException;

class VkApiClientTest extends TestCase
{
    private VkApiClient $client;

    public function setUp(): void
    {
        $this->client = new VkApiClient('');
    }

    /**
     * throw exception if too many profiles in request
     */
    public function test_too_many_profiles(): void
    {
        $profiles = [];
        for ($i = 0; $i <= 800; $i++) {
            $profiles[] = $i;
        }
        $this->expectException(VkException::class);
        $this->expectExceptionCode(VkException::TOO_MUCH_PROFILES_COUNT);
        $this->client->getUsers($profiles);
    }

    /**
     * throw exception if invalid auth key
     */
    public function test_invalid_auth_key(): void
    {
        $this->expectException(VkException::class);
        $this->expectExceptionCode(VkException::BANNED_EXCEPTION);
        $this->client->getUsers([]);
    }
}