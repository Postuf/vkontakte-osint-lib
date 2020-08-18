<?php
declare(strict_types=1);

namespace VkontakeOSINT\Models;

class User
{
    private const USER_OFFLINE_STATUS = 0;
    private const USER_ONLINE_STATUS = 1;
    private const USER_HIDE_STATUS = 2;
    private const USER_DELETE_STATUS = 3;
    private const USER_UNKNOWN_STATUS = 4;

    private int $profileId;
    private int $timestamp;
    private int $status;
    private string $photo;

    /**
     * User constructor.
     *
     * @param int    $profileId
     * @param int    $timestamp
     * @param int    $status
     * @param string $photo
     */
    private function __construct(int $profileId, int $timestamp, int $status, string $photo)
    {
        $this->profileId = $profileId;
        $this->timestamp = $timestamp;
        $this->status = $status;
        $this->photo = $photo;
    }

    /**
     * @param array $node
     *
     * @return null|static
     */
    public static function getUser(array $node): ?self
    {
        if (isset($node['id'])) {

            $profileId = (int)$node['id'];
            $timestamp = (int) ($node['last_seen']['time'] ?? 0);
            $photo = $node['photo_100'] ?? '';
            $isDelete = isset($node['deactivated']);
            $isHidden = !isset($node['last_seen']['time']);
            $isOnline = isset($node['online']) ? (int)$node['online'] : null;

            if ($isDelete) {
                $status = self::USER_DELETE_STATUS;
            } elseif($isHidden) {
                $status = self::USER_HIDE_STATUS;
            } elseif (self::USER_OFFLINE_STATUS === $isOnline ||
                self::USER_ONLINE_STATUS === $isOnline ||
                !is_null($isOnline)
            ) {
                $status = $isOnline;
            } else {
                $status = self::USER_UNKNOWN_STATUS;
            }

            $user = new self($profileId, $timestamp, $status, $photo);
        }

        return $user ?? null;
    }

    /**
     * @return int
     */
    public function getProfileId(): int
    {
        return $this->profileId;
    }

    /**
     * @return int
     */
    public function getTimestamp(): int
    {
        return $this->timestamp;
    }

    /**
     * @return int
     */
    public function getStatus(): int
    {
        return $this->status;
    }

    /**
     * @return string
     */
    public function getPhoto(): string
    {
        return $this->photo;
    }
}