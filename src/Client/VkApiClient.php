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
    private const API_VERSION = '5.130';

    private const VK_CONNECT_TIMEOUT_SEC = 1;
    private const VK_TIMEOUT_SEC         = 2;
    private const PROFILES_LIMIT_COUNT   = 800;

    private VKSdk $vkClient;

    /**
     * @param string $authKey
     * @param array  $curlConfig
     *
     * @throws ReflectionException
     */
    public function __construct(private string $authKey, array $curlConfig = [])
    {
        $this->vkClient = new VKSdk(self::API_VERSION);

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
     * @return User[]
     * @throws VkException
     */
    public function getUsers(array $profiles): array
    {
        $count = count($profiles);
        if ($count > self::PROFILES_LIMIT_COUNT) {
            throw new VkException("Too much profiles $count", VkException::TOO_MUCH_PROFILES_COUNT);
        }

        $users = [];
        try {
            $nodes = $this->vkClient->users()->get($this->authKey, [
                'user_ids' => $profiles,
                'fields' => [
                    'id',
                    'last_seen',
                    'online',
                    'photo_100',
                ],
            ]);

            foreach ($nodes as $node) {
                $user = User::get($node);
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

