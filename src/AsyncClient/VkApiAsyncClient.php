<?php
declare(strict_types=1);

namespace VkontakeOSINT\AsyncClient;

use React\EventLoop\StreamSelectLoop;
use React\HttpClient\Client;
use React\HttpClient\Response;
use VkontakeOSINT\Models\Error;
use VkontakeOSINT\Models\User;

class VkApiAsyncClient
{
    private const GET_INFO_URL = 'https://api.vk.com/method/users.get?user_ids={profileId}&fields=last_seen,online,id,photo_100&v=5.130&access_token={accessToken}';

    private Client $asyncClient;
    private StreamSelectLoop $loop;

    /**
     * VkApiAsyncClient constructor.
     *
     * @param string $authKey
     */
    public function __construct(private string $authKey)
    {
        $this->loop = new StreamSelectLoop();
        $this->asyncClient = new Client($this->loop);
    }

    /**
     * @param string   $profileId
     * @param callable $onSuccess
     * @param callable $onError
     *
     */
    public function getUserInfo(string $profileId, callable $onError, callable $onSuccess): void
    {
        $uri = str_replace(['{profileId}', '{accessToken}'], [$profileId, $this->authKey], self::GET_INFO_URL);
        $request = $this->asyncClient->request('GET', $uri);

        $request->on(
            'response',
            static function (Response $response) use ($onError, $onSuccess) {
                $response->on(
                    'data',
                    static function (?string $serializedResponseBody) use ($onError, $onSuccess) {
                        $response = json_decode($serializedResponseBody, true, 512, JSON_THROW_ON_ERROR);
                        if (isset($response['response'][0])) {
                            $onSuccess(User::get($response['response'][0]));
                        } else {
                            $onError(Error::get($response));
                        }
                    }
                );
            }
        );

        $request->end();

    }

    public function pollMessage(): void
    {
        $this->loop->run();
    }

}
