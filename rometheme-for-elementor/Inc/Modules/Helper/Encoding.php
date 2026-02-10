<?php

namespace RTMKit\Modules\Helper;

class Encoding
{

    private static $instance;

    public static function instance(): Encoding
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    function encodeSHA1(string $text): string
    {
        $checksum = hash('sha1', $text);

        $jsonData = json_encode([
            'key' => $text,
            'chk' => $checksum
        ]);

        // Base64 URL-safe
        $base64 = rtrim(strtr(base64_encode($jsonData), '+/', '-_'), '=');

        return $base64;
    }
}
