<?php
declare(strict_types=1);

namespace VkontakeOSINT\Exceptions;

use Exception;

class VkException extends Exception
{
    public const BANNED_EXCEPTION = 1;
    public const TOO_MUCH_PROFILES_COUNT = 100;
    public const BANNED_EXCEPTIONS = [
        VkApiClientException::USER_AUTHORIZATION_FAILED => self::BANNED_EXCEPTION,
        VkApiClientException::NOT_HAVE_PERMISSION_FOR_THIS_ACTION => self::BANNED_EXCEPTION,
        VkApiClientException::ACCESS_DENIED => self::BANNED_EXCEPTION,
        VkApiClientException::PASSKEY_INVALID => self::BANNED_EXCEPTION,
        VkApiClientException::APP_PASSKEY_INVALID => self::BANNED_EXCEPTION,
        VkApiClientException::ACCESS_RATE_LIMIT => self::BANNED_EXCEPTION,
    ];

}