<?php
namespace tests\service\resources\driver\v1;

use common\models\driver\Order;
use framework\message\Message;
use service\resources\driver\v1\addOrder;
use tests\service\DriverTest;

class addOrderTest extends DriverTest
{

    public function _initData()
    {
        $this->header->setRoute('driver.addOrder');

    }

    public function testRequest()
    {
        $this->assertInstanceOf('service\message\driver\AddOrder', addOrder::request());
    }

    public function testResponse()
    {
        $this->assertInstanceOf('service\message\driver\OrderActionResponse', addOrder::response());
    }

    public function testAddOrderNew()
    {
        $request = new \service\message\driver\AddOrder();
        $request->setDriverId($this->driver_id);
        $request->setAuthToken($this->driver_auth_token);
        $request->setFlag(1);
        $request->setIncrementId('16032509014825927');
        $rawBody = Message::pack($this->header, $request);
        $this->request->setRawBody($rawBody);
        $response = $this->application->handleRequest($this->request);
        /** @var \service\message\common\ResponseHeader $header */
        /** @var \service\message\driver\OrderActionResponse $data */
        list($header, $data) = $response;
        $this->assertNotEmpty($response);
        $this->assertEquals(0, $header->getCode());
        //删除该订单
        Order::getOrderByIncrementId('16032509014825927')->delete();
    }

    public function testAddOrderNone()
    {
        /** @var AddOrder $request */
        $request = new \service\message\driver\AddOrder();
        $request->setDriverId($this->driver_id);
        $request->setAuthToken($this->driver_auth_token);
        $request->setFlag(1);
        $request->setIncrementId('11111');
        $rawBody = Message::pack($this->header, $request);
        $this->request->setRawBody($rawBody);
        $response = $this->application->handleRequest($this->request);
        $this->assertNotEmpty($response);
    }

}