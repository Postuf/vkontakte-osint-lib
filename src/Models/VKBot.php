<?php
/** @noinspection PhpUnusedPrivateFieldInspection */
declare(strict_types=1);

namespace App\Impl\VK\Models;

use App\Models\Bot;
use App\Exceptions\BotException;
use App\Impl\VK\Exceptions\VKException;
use App\Impl\VK\Http\VKApiClientCustom;
use App\Models\Order;
use App\Models\TimePoint;
use App\Reporter\MessageType;
use VK\Client\VKApiClient;
use Exception;

class VKBot extends Bot
{
    private const USLEEP_BETWEEN_REQUEST = 250000;

    private const VK_STATUS_OFFLINE = 0;
    private const VK_STATUS_ONLINE = 1;
    private const VK_STATUS_HIDDEN = 2;

    private const BANNED_ERRORS = [
        VKException::USER_AUTHORIZATION_FAILED => 1,
        VKException::NOT_HAVE_PERMISSION_FOR_THIS_ACTION => 1,
        VKException::ACCESS_DENIED => 1,
        VKException::PASSKEY_INVALID => 1,
        VKException::APP_PASSKEY_INVALID => 1,
        VKException::ACCESS_RATE_LIMIT => 1,
    ];

    /**
     * @var VKApiClient|null|bool
     */
    private $connection;

    /**
     * @param Order[] $profiles
     */
    protected function subscribeProfiles(array $profiles): void
    {
        foreach ($profiles as $profile) {
            $this->failedProfiles[$profile->getOrderId()] = $this->clock->time();
        }
    }

    /**
     * @throws BotException
     */
    public function connect(): void
    {
        try{
            $this->connection = (new VKApiClientCustom())->getClient();
            $this->subscribeProfiles($this->profiles);
        }catch (Exception $e){
            $this->handleException($e);
        }
    }


    public function terminate(): void
    {
        $this->connection = null;
    }

    protected function isConnected(): bool
    {
        return $this->connection !== null;
    }

    /**
     * @throws BotException
     */
    protected function pollMessage(): void
    {
        usleep(self::USLEEP_BETWEEN_REQUEST);
        try{
            $timeStart = microtime(true);
            $nodes = $this->connection->users()->get($this->botKey, [
                'user_ids' => array_keys($this->byUserId),
                'fields' => [
                    'last_seen',
                    'online',
                    'id',
                ],
            ]);
            $timePointCount = $this->makeTimePoints($nodes);
            if ($timePointCount < count($this->byUserId) * 0.9) {
                $this->reporter->reportDebug("VK error: got less statuses than expected", [
                    MessageType::_FIELD => MessageType::MSG_VK_LESS_STATUSES,
                    'nodeCount' => count($nodes),
                    'reqCount' => count($this->byUserId),
                    'timePointCount' => $timePointCount,
                ]);
            }
            $timeDiff = microtime(true) - $timeStart;
            if ($timeDiff > 0.5) {
                $timeFormatted = number_format($timeDiff, 2);
                $this->reporter->reportDebug("VK api get took: $timeFormatted s", [
                    MessageType::_FIELD => MessageType::MSG_VK_LONG_REQUEST,
                    'botId' => $this->getId(),
                    'time' => $timeDiff,
                ]);
            }
        }catch (Exception $e){
            $this->handleException($e);
        }
    }

    protected function checkFailedProfiles(): void
    {
        foreach ($this->failedProfiles as $profile => $time) {
            $this->linkMonitor->onFailed((string) $profile);
            $this->reporter->reportDebug("Failed id {$profile} for bot {$this->botId}", [
                'profile' => $profile,
                'botId' => $this->botId,
                MessageType::_FIELD => MessageType::MSG_VK_FAILED_PROFILE_CHECK,
            ]);
            unset($this->failedProfiles[$profile]);
        }
    }

    protected function checkIfReconnectionNeeded(): void
    {
        //Для VK пока нет необходимости в Антифрод поведении бота
    }

    /**
     * @param array $nodes
     * @return int
     * @throws BotException
     */
    private function makeTimePoints(array $nodes): int
    {
        if (count($this->profiles) > 1 && count($nodes) === 1) {
            throw new BotException(
                'Too many profiles in request',
                BotException::TOO_MANY_PROFILES_IN_MONITORING
            );
        }

        $count = 0;
        foreach ($nodes as $node) {
            $profile = $node['id'] ?? null;
            if (!is_null($profile)) {
                if (isset($this->byUserId[(string) $profile])) {
                    $order = $this->byUserId[(string) $profile];
                    $this->linkMonitor->onLinked($order->getOrderId());
                }

                $timestamp = (int)($node['last_seen']['time'] ?? $this->clock->time());
                $deactivated = isset($node['deactivated']);
                $status = isset($node['online']) ? (int)$node['online'] : null;

                if ($deactivated) {
                    $status = TimePoint::DELETED;
                } elseif ($status === self::VK_STATUS_ONLINE){
                    $status = TimePoint::ONLINE;
                } elseif ($status === self::VK_STATUS_OFFLINE){
                    // если у пользователя статус скрыт, то мы не получаем обновления статуса (online/offline)
                    $status = isset($node['last_seen']['time'])
                        ? TimePoint::OFFLINE
                        : TimePoint::HIDDEN;
                } else {
                    // если мы не получили или получили неизвестный статус от вк, то логируем этот факт и не формируем метку
                    $this->reporter->reportDebug("For profile: {$profile} VK did not send status or send status: {$status} unknown to us!", [
                        'profile' => $profile,
                        'botId' => $this->botId,
                        MessageType::_FIELD => MessageType::MSG_VK_UNKNOWN_STATUS,
                    ]);
                    continue;
                }

                $this->timePoints[$profile] = new TimePoint(
                  (string)$profile,
                  $status,
                  $timestamp
                );
                unset($this->failedProfiles[$profile]);

                ++$count;
            }
        }
        return $count;
    }

    /**
     * @param Exception $exception
     * @throws BotException
     */
    private function handleException(Exception $exception): void
    {
        $exceptionCode = $exception->getCode();
        $exceptionMessage = $exception->getMessage();

        if(isset(self::BANNED_ERRORS[$exceptionCode])){
            $botExceptionCode = BotException::BANNED_ERRORS;
        } else {
            $botExceptionCode = BotException::IGNORE_ERRORS;
        }
        throw new BotException("Code = $exceptionCode ; Message--  $exceptionMessage", $botExceptionCode, $exception);
    }
}
