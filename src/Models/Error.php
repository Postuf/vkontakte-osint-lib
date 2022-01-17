<?php
declare(strict_types=1);

namespace VkontakeOSINT\Models;

use JsonException;
use VkontakeOSINT\Exceptions\VkException;

class Error
{
    private const UNKNOWN_RESPONSE_CODE = 0;

    /**
     * Error constructor.
     *
     * @param int    $code
     * @param string $message
     */
    private function __construct(private int $code, private string $message) {}

    /**
     * @param array $node
     *
     * @return static
     * @throws JsonException
     */
    public static function get(array $node): self
    {
        if (isset($node['error'])) {
            $code = VkException::BANNED_EXCEPTIONS[$node['error']['error_code']] ?? $node['error']['error_code'];
            $message = "Message: {$node['error']['error_msg']} Vk code: {$node['error']['error_code']}";
            $error = new self($code, $message);
        }

        return $error ?? new self(self::UNKNOWN_RESPONSE_CODE, json_encode($node, JSON_THROW_ON_ERROR));
    }

    /**
     * @return int
     */
    public function getCode(): int
    {
        return $this->code;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }
}
