<?php
namespace tests\service\resources\driver\v1;

use framework\message\Message;
use service\message\driver\AddOrder;
use service\resources\driver\v1\deleteOrder;
use tests\service\DriverTest;

class deleteOrderTest extends DriverTest
{

    public function _initData()
    {
        $this->header->setRoute('driver.deleteOrder');

    }

    public function testRequest()
    {
        $this->assertInstanceOf('service\message\driver\DeleteOrder', deleteOrder::request());
    }

    public function testAddOrderNew()
    {
        $request = new AddOrder();
        $request->setDriverId($this->driver_id);
        $request->setAuthToken($this->driver_auth_token);
        $request->setFlag(1);
        $request->setIncrementId('16032509014825927');
        $this->header->setRoute('driver.addOrder');
        $rawBody = Message::pack($this->header, $request);
        $this->request->setRawBody($rawBody);
        $response = $this->application->handleRequest($this->request);
        /** @var \service\message\common\ResponseHeader $header */
        /** @var \service\message\driver\OrderActionResponse $data */
        list($header, $data) = $response;
        $this->assertNotEmpty($response);
        $this->assertEquals(0, $header->getCode());
        $driver_order_id = $data->getOrder()->getDriverOrderId();
        return $driver_order_id;
    }

    /**
     * @depends testAddOrderNew
     * @param $driver_order_id
     */
    public function testDelete($driver_order_id)
    {
        /** @var DeleteOrder $request */
        $request = new \service\message\driver\DeleteOrder();
        $request->setDriverId($this->driver_id);
        $request->setAuthToken($this->driver_auth_token);
        $request->setDriverOrderId($driver_order_id);
        $rawBody = Message::pack($this->header, $request);
        $this->request->setRawBody($rawBody);
        $response = $this->application->handleRequest($this->request);
        $this->assertNotEmpty($response);
    }

}