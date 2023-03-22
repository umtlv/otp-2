<?php

namespace src;

use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

class Otp
{
    /**
     * @throws Exception
     */
    public static function create(string $key)
    {
        $expires = self::getTokenLifetime();

        $data = [
            'key'          => $key,
            'ip_address'   => user_ip(),
            'verify_token' => create_token(),
            'verify_code'  => create_otp(),
            'expires_at'   => $expires,
            'attempts'     => 0,
            'verified'     => false
        ];

        self::save($data['verify_token'], $data);
    }

    /**
     * @throws Exception
     */
    public static function getTokenLifetime(): Carbon
    {
        return self::getLifetime('token_lifetime', 'Token lifetime');
    }

    /**
     * @throws Exception
     */
    public function getResendingTime(): Carbon
    {
        return $this->getLifetime("resending_time", 'Resending time');
    }

    /**
     * @throws Exception
     */
    private static function getLifetime(string $key, string $title): Carbon
    {
        $lifetime = self::getConfig($key, $title);
        return now()->addMinutes($lifetime);
    }

    /**
     * @throws Exception
     */
    private static function getConfig(string $key, string $title)
    {
        $data = config("otp.$key");
        if (is_null($data)) {
            throw new Exception("$title does not set");
        }

        return $data;
    }

    private static function save(string $key, array $data)
    {
        Cache::put($key, $data, $data['expires_at']);
    }
}