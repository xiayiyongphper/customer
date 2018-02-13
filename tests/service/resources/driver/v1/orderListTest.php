<?php
namespace tests\service\resources\driver\v1;

use common\models\driver\Order;
use framework\message\Message;
use service\message\common\OrderAction;
use service\message\driver\AddOrder;
use service\message\driver\LoginRequest;
use service\message\driver\OrderListRequest;
use service\resources\driver\v1\loginTest;
use service\resources\driver\v1\orderDetail;
use service\resources\driver\v1\orderList;
use tests\service\DriverTest;

class orderListTest extends DriverTest
{

    public function _initData()
    {
        $this->header->setRoute('driver.orderList');
    }

    public function testRequest()
    {
        $this->assertInstanceOf('service\message\driver\OrderListRequest', orderList::request());
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
    }

    public function testOrderComplete()
    {
        /** @var OrderListRequest $request */
        $request = new OrderListRequest();
        $request->setDriverId($this->driver_id);
        $request->setAuthToken($this->driver_auth_token);
        $request->setListType(1);

        $rawBody = Message::pack($this->header, $request);
        $this->request->setRawBody($rawBody);
        $response = $this->application->handleRequest($this->request);
        /** @var \service\message\common\ResponseHeader $header */
        list($header, $data) = $response;
        $this->assertNotEmpty($response);
        $this->assertEquals(0, $header->getCode());
    }

    public function testOrderProcessing()
    {
        /** @var OrderListRequest $request */
        $request = new OrderListRequest();
        $request->setDriverId($this->driver_id);
        $request->setAuthToken($this->driver_auth_token);
        $request->setListType(0);

        $rawBody = Message::pack($this->header, $request);
        $this->request->setRawBody($rawBody);
        $response = $this->application->handleRequest($this->request);
        /** @var \service\message\common\ResponseHeader $header */
        list($header, $data) = $response;
        $this->assertNotEmpty($response);
        $this->assertEquals(0, $header->getCode());
        //删除该订单
        Order::getOrderByIncrementId('16032509014825927')->delete();
    }

}