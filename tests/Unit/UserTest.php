<?php
declare(strict_types=1);

namespace Unit;

use PHPUnit\Framework\TestCase;
use VkontakeOSINT\Models\User;

class UserTest extends TestCase
{
    /**
     * get status USER::DELETED_STATUS if profile is deactivated
     */
    public function test_get_delete_status(): void
    {
        $node = '{
            "id": 3,
            "first_name": "DELETED",
            "last_name": "",
            "deactivated": "deleted",
            "online": 0,
            "photo_100": "https://vk.com/images/deactivated_100.png"
        }';

        $user = User::get(json_decode($node, true, 512, JSON_THROW_ON_ERROR));
        self::assertEquals(3, $user->getStatus(), 'Getting wrong status for deleted profile');

    }

    /**
     * get status USER::HIDE_STATUS if profile hide status
     */
    public function test_get_hide_status(): void
    {
        $node = '{
            "id": 521118799,
            "first_name": "Test",
            "last_name": "Testov",
            "is_closed": true,
            "can_access_closed": false,
            "photo_100": "https://vk.com/images/camera_100.png?ava=1",
            "online": 0
        }';

        $user = User::get(json_decode($node, true, 512, JSON_THROW_ON_ERROR));
        self::assertEquals(2, $user->getStatus(), 'Getting wrong status for profile with hide status');
    }

    /**
     * get status USER::ONLINE_STATUS if profile is online
     */
    public function test_get_online_status(): void
    {
        $node = '{
            "id": 521118799,
            "first_name": "Test",
            "last_name": "Testov",
            "is_closed": true,
            "can_access_closed": false,
            "photo_100": "https://vk.com/images/camera_100.png?ava=1",
            "online": 1,
            "online_app": 6146827,
            "last_seen": {
                "time": 1597701903,
                "platform": 7
            }
        }';

        $user = User::get(json_decode($node, true, 512, JSON_THROW_ON_ERROR));
        self::assertEquals(1, $user->getStatus(), 'Getting wrong status for profile with online status');
    }

    /**
     * get status USER::OFFLINE_STATUS if profile is online
     */
    public function test_get_offline_status(): void
    {
        $node = '{
            "id": 521118799,
            "first_name": "Test",
            "last_name": "Testov",
            "is_closed": true,
            "can_access_closed": false,
            "photo_100": "https://vk.com/images/camera_100.png?ava=1",
            "online": 0,
            "online_app": 6146827,
            "last_seen": {
                "time": 1597701903,
                "platform": 7
            }
        }';

        $user = User::get(json_decode($node, true, 512, JSON_THROW_ON_ERROR));
        self::assertEquals(0, $user->getStatus(), 'Getting wrong status for profile with offline status');
    }

    /**
     * get status USER::UNKNOWN_STATUS if profile status is unknown or there is no status
     */
    public function test_get_unknown_status(): void
    {
        $node1 = '{
            "id": 521118799,
            "first_name": "Test",
            "last_name": "Testov",
            "is_closed": true,
            "can_access_closed": false,
            "photo_100": "https://vk.com/images/camera_100.png?ava=1",
            "online": 6,
            "online_app": 6146827,
            "last_seen": {
                "time": 1597701903,
                "platform": 7
            }
        }';

        $node2 = '{
            "id": 521118799,
            "first_name": "Test",
            "last_name": "Testov",
            "is_closed": true,
            "can_access_closed": false,
            "photo_100": "https://vk.com/images/camera_100.png?ava=1",
            "online_app": 6146827,
            "last_seen": {
                "time": 1597701903,
                "platform": 7
            }
        }';


        $user1 = User::get(json_decode($node1, true, 512, JSON_THROW_ON_ERROR));
        $user2 = User::get(json_decode($node2, true, 512, JSON_THROW_ON_ERROR));
        self::assertEquals(6, $user1->getStatus(), 'Getting wrong status for profile with unknown status');
        self::assertEquals(4, $user2->getStatus(), 'Getting wrong status for profile that not have status');
    }

    /**
     * get null if id not found
     */
    public function test_get_null_for_user_without_id(): void
    {
        $node = '{
            "first_name": "Test",
            "last_name": "Testov",
            "is_closed": true,
            "can_access_closed": false,
            "photo_100": "https://vk.com/images/camera_100.png?ava=1",
            "online": 0,
            "online_app": 6146827,
            "last_seen": {
                "time": 1597701903,
                "platform": 7
            }
        }';

        $user = User::get(json_decode($node, true, 512, JSON_THROW_ON_ERROR));
        self::assertNull($user, 'Getting wrong response for user without id');
    }
}
