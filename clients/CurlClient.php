<?php

namespace app\clients;

use Exception;

class CurlClient implements HttpClientInterface
{
    /**
     * Настройки Curl, заданные по умолчанию
     *
     * @return array
     */
    private static function getDefaultCurlOptions(): array
    {
        return [
            'CURLOPT_RETURNTRANSFER' => true,
            'CURLOPT_USERAGENT' => 'Mozilla/4.0 (compatible; PHP client; ' . php_uname('s') . '; PHP/' . phpversion() . ')',
            'CURLOPT_POST' => true,
            'CURLOPT_SSL_VERIFYPEER' => false,
        ];
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public static function sendQuery(string $path, array $payload = [], array $headers = [], array $params = [])
    {
        static $curl = null;
        $defaultOptions = self::getDefaultCurlOptions();

        if ($curl === null) {
            $curl = curl_init();

            curl_setopt($curl, CURLOPT_RETURNTRANSFER,
                $params['CURLOPT_RETURNTRANSFER'] ?? $defaultOptions['CURLOPT_RETURNTRANSFER']);

            curl_setopt($curl, CURLOPT_USERAGENT,
                $params['CURLOPT_USERAGENT'] ?? $defaultOptions['CURLOPT_USERAGENT']);
        }

        curl_setopt($curl, CURLOPT_URL, $path);

        curl_setopt($curl, CURLOPT_POST,
            $params['CURLOPT_POST'] ?? $defaultOptions['CURLOPT_POST']);

        if (curl_getinfo($curl, CURLOPT_POST) === true) {
            curl_setopt($curl, CURLOPT_POSTFIELDS, $payload);
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        }

        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER,
            $params['CURLOPT_SSL_VERIFYPEER'] ?? $defaultOptions['CURLOPT_SSL_VERIFYPEER']);

        $result = curl_exec($curl);

        if ($result === false) {
            throw new Exception('Не удалось получить ответ: ' . curl_error($curl));
        }

        return $result;
    }
}