<?php
declare(strict_types=1);

namespace VkontakeOSINT\Models;

class User
{
    private const USER_HIDE_STATUS = 2;
    private const USER_DELETE_STATUS = 3;
    private const USER_UNKNOWN_STATUS = 4;

    private function __construct(
        public readonly int $profileId,
        public readonly int $timestamp,
        public readonly int $status,
        public readonly string $photo,
        public readonly string $firstName,
        public readonly string $lastName
    ) {}

    /**
     * @param array $node
     *
     * @return null|static
     */
    public static function get(array $node): ?self
    {
        if (isset($node['id'])) {

            $profileId = (int)$node['id'];
            $firstName = $node['first_name'];
            $lastName = $node['last_name'];
            $timestamp = (int) ($node['last_seen']['time'] ?? 0);
            $photo = $node['photo_100'] ?? '';
            $isDelete = isset($node['deactivated']);
            $isHidden = !isset($node['last_seen']['time']);
            $isOnline = isset($node['online']) ? (int)$node['online'] : null;

            if ($isDelete) {
                $status = self::USER_DELETE_STATUS;
            } elseif($isHidden) {
                $status = self::USER_HIDE_STATUS;
            } elseif (!is_null($isOnline)) {
                $status = $isOnline;
            } else {
                $status = self::USER_UNKNOWN_STATUS;
            }

            $user = new self($profileId, $timestamp, $status, $photo, $firstName, $lastName);
        }

        return $user ?? null;
    }
}
