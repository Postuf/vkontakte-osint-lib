<?php

namespace VkontakeOSINTK\Http;

use ReflectionClass;
use ReflectionException;
use VK\Client\VKApiClient as VKSdk;

class VKApiClient
{
    private const VK_CONNECT_TIMEOUT_SEC = 1;
    private const VK_TIMEOUT_SEC = 2;

    private VKSdk $vkClient;
    private string $authKey;
    private array $profiles;

    /**
     * @param string $authKey
     *
     * @throws ReflectionException
     */
    public function __construct(string $authKey)
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
        $initialOpts->setValue($httpClient, [
          CURLOPT_HEADER         => true,
          CURLOPT_CONNECTTIMEOUT => self::VK_CONNECT_TIMEOUT_SEC ,
          CURLOPT_TIMEOUT => self::VK_TIMEOUT_SEC,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4
        ]);
    }

    public function getClient(): VKApiClient
    {
        return $this->vkClient;
    }

    public function addProfiles(array $profiles): void
    {
        foreach ($profiles as $profile) {
            $this->profiles[$profile] = $profile;
        }
    }

    public function deleteProfiles(array $profiles): void
    {
        foreach ($profiles as $profile) {
            unset($this->profiles[$profile]);
        }
    }

    public function getUsers()
    {
        $this->vkClient->users()->get($this->authKey, [
            'user_ids' => $this->profiles,
            'fields' => [
                'last_seen',
                'online',
                'id',
            ],
        ])
    }
}

