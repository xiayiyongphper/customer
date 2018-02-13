<?php
namespace service\components;

use common\models\LeCustomers;
use framework\components\ProxyAbstract;
use framework\components\es\Console;
use framework\components\es\Timeline;
use service\message\common\ContractorStatics;
use service\message\common\Header;
use service\message\common\SourceEnum;
use framework\message\Message;
use service\message\contractor\ContractorAuthenticationRequest;
use service\message\contractor\ContractorHomeDataRequest;
use service\models\common\CustomerException;

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/1/25
 * Time: 11:02
 */
class Proxy extends ProxyAbstract
{

    /**
     * Function: initHeader
     * Author: Jason Y. Wang
     *
     * @param $route
     * @return Header
     */
    protected static function initHeader($route)
    {
        if ($route) {
            $header = new Header();
            $header->setVersion(1);
            $header->setRoute($route);
            $header->setSource(SourceEnum::CUSTOMER);
            return $header;
        } else {
            return null;
        }
    }

    /**
     * @param String $route
     * @param $request
     * @param bool $remote
     * @return Message
     * @throws \Exception
     */
    public static function sendRequest($route, $request, $remote = false)
    {
        $timeStart = microtime(true);

        list($ip, $port) = self::getRoute($route, $remote);
        $client = self::getClient($ip, $port);
        $header = self::initHeader($route);
        try {
            $client->send(Message::pack($header, $request));
            $result = $client->recv();
        } catch (\Exception $e) {
            $timeEnd = microtime(true);
            $elapsed = $timeEnd - $timeStart;
            $code = $e->getCode() > 0 ? $e->getCode() : 999;
            Timeline::get()->report($header->getRoute(), 'sendRequest', Logger::SOURCE, $elapsed, $code, $header->getTraceId(), $header->getRequestId());
            Console::get()->logException($e);
            throw $e;
        }
        // swoole 1.8.1有bug,close之后此task也退出了. https://github.com/swoole/swoole-src/issues/522
        //$client->close();
        $message = new Message();
        $message->unpackResponse($result);
        $timeEnd = microtime(true);
        $elapsed = $timeEnd - $timeStart;
        if ($message->getHeader()->getCode() > 0) {
            $e = new \Exception($message->getHeader()->getMsg(), $message->getHeader()->getCode());
            Console::get()->logException($e);
            throw $e;
        }
        Timeline::get()->report($header->getRoute(), 'sendRequest', Logger::SOURCE, $elapsed, 0, $header->getTraceId(), $header->getRequestId());
        return $message;
    }




}
