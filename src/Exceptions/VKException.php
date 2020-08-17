<?php
namespace App\Impl\VK\Exceptions;

use Exception;

class VKException extends Exception
{
    public const USER_AUTHORIZATION_FAILED = 5;
    public const NOT_HAVE_PERMISSION_FOR_THIS_ACTION = 7;
    public const ACCESS_DENIED = 15;
    public const PASSKEY_INVALID = 27;
    public const APP_PASSKEY_INVALID = 28;
    public const ACCESS_RATE_LIMIT = 29;
}