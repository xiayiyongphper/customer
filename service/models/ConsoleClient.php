<?php
namespace service\models;

use service\components\Logger;
use service\message\common\Command;
use service\message\common\ExtensionHeader;
use service\message\common\Header;
use service\message\common\Order;
use service\message\core\HomeRequest;
use service\message\core\HomeResponse;
use service\message\customer\LoginRequest;
use service\message\customer\LoginResponse;
use service\message\customer\ResponseExtension;
use service\message\sales\CreateOrdersRequest;
use service\message\sales\CreateOrdersResponse;
use service\message\sales\OrderCollectionRequest;
use service\message\sales\OrderCollectionResponse;
use service\message\sales\OrderDetailRequest;
use service\message\sales\OrderDetailResponse;
use service\message\sales\OrderReviewRequest;
use service\message\sales\OrderReviewResponse;
use service\message\sales\OrderStatusHistoryRequest;
use service\message\sales\OrderStatusHistoryResponse;
use service\models\client\ClientAbstract;
use framework\message\Message;

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/1/8
 * Time: 12:01
 */
class ConsoleClient extends ClientAbstract
{
    public $cmd;
    protected $_responseClass;

    protected function ping()
    {
        $header = new Header();
        $header->setRoute('console.cmd');
        $header->setVersion(1);
        $header->setEncrypt('des');
        $header->setProtocol('pb');
        $command = new Command();
        $command->setCmd('ping');
        $command->setScope('*');
        $this->send(Message::pack($header, $command));
    }

    protected function stats()
    {
        $header = new Header();
        $header->setRoute('console.cmd');
        $header->setVersion(1);
        $header->setEncrypt('des');
        $header->setProtocol('pb');
        $command = new Command();
        $command->setCmd('stats');
        $command->setScope('*');
        $this->send(Message::pack($header, $command));
    }

    protected function reload()
    {
        $header = new Header();
        $header->setRoute('console.cmd');
        $header->setVersion(1);
        $header->setEncrypt('des');
        $header->setProtocol('pb');
        $command = new Command();
        $command->setCmd('reload');
        $command->setScope('*');
        $this->send(Message::pack($header, $command));
    }

    protected function shutdown()
    {
        $header = new Header();
        $header->setRoute('console.cmd');
        $header->setVersion(1);
        $header->setEncrypt('des');
        $header->setProtocol('pb');
        $command = new Command();
        $command->setCmd('shutdown');
        $command->setScope('*');
        $this->send(Message::pack($header, $command));
    }

    public function onConnect(\swoole_client $client)
    {
        echo "client connected" . PHP_EOL;
        call_user_func([$this, $this->cmd]);
        $client->close();
    }

    public function onReceive($client, $data)
    {
        $message = new Message();
        $message->unpackResponse($data);
        $responseClass = $this->_responseClass;
        if ($message->getHeader()->getCode() > 0) {
            echo sprintf('程序执行异常：%s', $message->getHeader()->getMsg()) . PHP_EOL;
        } else {
            $response = new $responseClass();
            $response->parseFromString($message->getPackageBody());
            print_r($response->toArray());
        }
    }
}
