<?php

namespace SitPHP\Commands\Helpers;

class PhpHelper
{
    static function isCli()
    {
        return php_sapi_name() === 'cli' || php_sapi_name() === 'phpdbg';
    }

    static function shellExec(string $command, bool $escape = false, &$status = null)
    {
        if ($escape === true) {
            $command = escapeshellcmd($command);
        }
        exec($command, $response, $status);
        if ($status !== 0) {
            return null;
        } else {
            return $response;
        }
    }
}