<?php
declare(strict_types=1);

namespace Unit;

use PHPUnit\Framework\TestCase;
use VkontakeOSINT\Models\Error;

class ErrorTest extends TestCase
{
    /**
     * get ban for bot
     */
    public function test_get_ban_error(): void
    {
        $json1 = '{
            "error": {
                "error_code": 5,
                "error_msg": "USER_AUTHORIZATION_FAILED."
            }
        }';

        $json2 = '{
            "error": {
                "error_code": 7,
                "error_msg": "NOT_HAVE_PERMISSION_FOR_THIS_ACTION."
            }
        }';

        $json3 = '{
            "error": {
                "error_code": 15,
                "error_msg": "ACCESS_DENIED."
            }
        }';

        $json4 = '{
            "error": {
                "error_code": 27,
                "error_msg": "PASSKEY_INVALID."
            }
        }';

        $json5 = '{
            "error": {
                "error_code": 28,
                "error_msg": "APP_PASSKEY_INVALID."
            }
        }';

        $json6 = '{
            "error": {
                "error_code": 29,
                "error_msg": "ACCESS_RATE_LIMIT."
            }
        }';

        $error1 = Error::get(json_decode($json1, true, 512, JSON_THROW_ON_ERROR));
        $error2 = Error::get(json_decode($json2, true, 512, JSON_THROW_ON_ERROR));
        $error3 = Error::get(json_decode($json3, true, 512, JSON_THROW_ON_ERROR));
        $error4 = Error::get(json_decode($json4, true, 512, JSON_THROW_ON_ERROR));
        $error5 = Error::get(json_decode($json5, true, 512, JSON_THROW_ON_ERROR));
        $error6 = Error::get(json_decode($json6, true, 512, JSON_THROW_ON_ERROR));

        self::assertEquals(1, $error1->getCode(), 'wrong code for 5');
        self::assertEquals(1, $error2->getCode(), 'wrong code for 7');
        self::assertEquals(1, $error3->getCode(), 'wrong code for 15');
        self::assertEquals(1, $error4->getCode(), 'wrong code for 27');
        self::assertEquals(1, $error5->getCode(), 'wrong code for 28');
        self::assertEquals(1, $error6->getCode(), 'wrong code for 29');
    }

    /**
     * get not ban code for bot
     */
    public function test_get_not_ban_exception(): void
    {
        $json = '{
            "error": {
                "error_code": 6,
                "error_msg": "User authorization failed: invalid session."
            }
        }';

        $error = Error::get(json_decode($json, true, 512, JSON_THROW_ON_ERROR));
        self::assertEquals(6, $error->getCode(), 'wrong code for 6');
    }

    public function test_get_not_valid_response(): void
    {
        $json = '{"message":"Page not found"}';

        $error1 = Error::get(json_decode($json, true, 512, JSON_THROW_ON_ERROR));
        self::assertEquals(0, $error1->getCode(), 'wrong code for not valid response');
        self::assertEquals($json, $error1->getMessage(), 'wrong message for not valid response');

    }
}
