<?php
namespace tests\service\resources\driver\v1;

use common\models\driver\Order;
use framework\message\Message;
use service\message\driver\AddOrder;
use service\message\driver\OrderAction;
use service\resources\driver\v1\deliverySuccess;
use tests\service\DriverTest;

class deliverySuccessTest extends DriverTest
{

    public function _initData()
    {
        $this->header->setRoute('driver.deliverySuccess');

    }

    public function testRequest()
    {
        $this->assertInstanceOf('service\message\driver\OrderAction', deliverySuccess::request());
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


    public function testRun()
    {
        /** @var OrderAction $request */
        $request = new OrderAction();
        $request->setDriverId($this->driver_id);
        $request->setAuthToken($this->driver_auth_token);
        $request->setIncrementId('16032509014825927');
        $rawBody = Message::pack($this->header, $request);
        $this->request->setRawBody($rawBody);
        $response = $this->application->handleRequest($this->request);
        $this->assertNotEmpty($response);
        //删除该订单
        Order::getOrderByIncrementId('16032509014825927')->delete();
    }
}