<?php

class ParserController
{
    const USER_AGENT = "User-Agent: Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/31.0.1650.63 Safari/537.36\r\n";

    private static $stream_context = null;

    /**
     * @param $address
     * @return array|mixed
     */
    public static function getDecodeJsonContent($address)
    {
        try {
            if (is_null(self::$stream_context)) {
                self::setStreamContext();
            }

            $json = @file_get_contents($address, false, self::$stream_context);
            return self::isJSON($json)
                ? json_decode($json, true)
                : [];
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Создаёт контекст потока
     */
    private static function setStreamContext()
    {
        $opts = array(
            'http' => array(
                'method' => "GET",
                'header' => self::USER_AGENT
            )
        );

        self::$stream_context = stream_context_create($opts);
    }

    /**
     * @param $string
     * @return bool
     */
    public static function isJSON($string){
        return (bool)(
            is_string($string)
            && is_array(json_decode($string, true))
            && json_last_error() == JSON_ERROR_NONE
        );
    }
}