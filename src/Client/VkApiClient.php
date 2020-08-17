<?php
declare(strict_types=1);

namespace VkontakeOSINT\Client;

use Exception;
use ReflectionClass;
use ReflectionException;
use VK\Client\VKApiClient as VKSdk;
use VkontakeOSINT\Exceptions\VkException;
use VkontakeOSINT\Models\User;

class VkApiClient
{
    private const VK_CONNECT_TIMEOUT_SEC = 1;
    private const VK_TIMEOUT_SEC         = 2;
    private const PROFILES_LIMIT_COUNT   = 800;

    private VKSdk $vkClient;
    private string $authKey;
    private array $profiles = [];

    /**
     * @param string $authKey
     * @param array  $curlConfig
     *
     * @throws ReflectionException
     */
    public function __construct(string $authKey, array $curlConfig = [])
    {
        $this->vkClient = new VKSdk();
        $this->authKey = $authKey;

        $vkRequest = (new ReflectionClass($this->vkClient))->getProperty('request');
        $vkRequest->setAccessible(true);
        $request = $vkRequest->getValue($this->vkClient);

        $curlClient = (new ReflectionClass($request))->getProperty('http_client');
        $curlClient->setAccessible(true);
        $httpClient = $curlClient->getValue($vkRequest->getValue($this->vkClient));

        $initialOpts= (new ReflectionClass($httpClient))->getProperty('initial_opts');
        $initialOpts->setAccessible(true);

        $config = array_replace([
            CURLOPT_HEADER         => true,
            CURLOPT_CONNECTTIMEOUT => self::VK_CONNECT_TIMEOUT_SEC ,
            CURLOPT_TIMEOUT => self::VK_TIMEOUT_SEC,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4
        ], $curlConfig);

        $initialOpts->setValue($httpClient, $config);
    }

    /**
     * @param array $profiles
     *
     * @throws VkException
     */
    public function addProfiles(array $profiles): void
    {
        $profilesCount = count($profiles) + count($this->profiles);
        if ($profilesCount > self::PROFILES_LIMIT_COUNT) {
            throw new VkException("Too much profiles $profilesCount", VkException::TOO_MUCH_PROFILES_COUNT);
        }

        foreach ($profiles as $profile) {
            $this->profiles[$profile] = $profile;
        }
    }

    /**
     * @param array $profiles
     */
    public function deleteProfiles(array $profiles): void
    {
        foreach ($profiles as $profile) {
            unset($this->profiles[$profile]);
        }
    }

    /**
     * @return User[]
     * @throws VkException
     *
     */
    public function getUsers(): array
    {
        $users = [];
        try {
            $nodes = $this->vkClient->users()->get($this->authKey, [
                'user_ids' => $this->profiles,
                'fields' => [
                    'id',
                    'last_seen',
                    'online',
                    'photo_100',
                ],
            ]);

            foreach ($nodes as $node) {
                $user = User::getUser($node);
                if (is_null($user)) {
                    continue;
                }
                $users[] = $user;

            }
        } catch (Exception $e) {
            $code = VkException::BANNED_EXCEPTIONS[$e->getCode()] ?? $e->getCode();
            $message = 'Message: ' . $e->getMessage() . ' Code: ' . $e->getCode();
            throw new VkException($message, $code);
        }

        return $users;
    }
}

