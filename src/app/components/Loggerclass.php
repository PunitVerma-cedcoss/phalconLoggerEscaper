<?php

namespace App\Components;

use Phalcon\Logger\Adapter\Stream;
use Phalcon\Logger;

class Loggerclass
{
    public $adapter1;
    public $adapter2;
    public $logger;
    public function __construct()
    {
        $this->adapter1 = new Stream(APP_PATH . '/logs/login.log');
        $this->adapter2 = new Stream(APP_PATH . '/logs/register.log');
        $this->logger  = new Logger(
            'messages',
            [
                'login' => $this->adapter1,
                'register' => $this->adapter2,
            ]
        );
    }
    public function LoginLog($type, $msg)
    {
        switch ($type) {
            case "error":
                $this->logger->excludeAdapters(['register'])->error($msg);
                break;
            case "critial":
                $this->logger->excludeAdapters(['register'])->critical($msg);
                break;
            case "info":
                $this->logger->excludeAdapters(['register'])->info($msg);
                break;
            default:
                echo "invalid choice";
                break;
        }
    }
    public function RegisterLog($type, $msg)
    {
        switch ($type) {
            case "error":
                $this->logger->excludeAdapters(['login'])->error($msg);
                break;
            case "critial":
                $this->logger->excludeAdapters(['login'])->critical($msg);
                break;
            case "info":
                $this->logger->excludeAdapters(['login'])->info($msg);
                break;
            default:
                echo "invalid choice";
                break;
        }
    }
}
