<?php
namespace tests\service\resources\customers\v1;

use framework\message\Message;
use service\message\customer\CartItemsRequest;
use service\resources\customers\v1\cartItems;
use tests\service\CustomersTest;

class cartItemsTest extends CustomersTest
{

    public function _initData()
    {
        $this->header->setRoute('customers.cartItems');

    }

    public function testRequest()
    {
        $this->assertInstanceOf('service\message\customer\CartItemsRequest', cartItems::request());
    }

    public function testResponse()
    {
        $this->assertInstanceOf('service\message\customer\CartItemsResponse', cartItems::response());
    }

    /**
     * @covers service\resources\customers\v1\cartItems::run
     */
    public function testRun()
    {
        /** @var CartItemsRequest $request */
        $request = new CartItemsRequest();
        $request->setCustomerId($this->customer_id);
        $request->setAuthToken($this->auth_token);
        $rawBody = Message::pack($this->header, $request);
        $this->request->setRawBody($rawBody);
        $response = $this->application->handleRequest($this->request);
        $this->assertNotEmpty($response);
    }

    /**
     * @covers service\resources\customers\v1\cartItems::run
     */
    public function testCustomerExpire()
    {
        /** @var CartItemsRequest $request */
        $request = new CartItemsRequest();
        $request->setCustomerId('1057');
        $request->setAuthToken('3516516156');
        $rawBody = Message::pack($this->header, $request);
        $this->request->setRawBody($rawBody);
        $response = $this->application->handleRequest($this->request);
        /** @var \service\message\common\ResponseHeader $header */
        list($header, $data) = $response;
        $this->assertNotEmpty($response);
        $this->assertEquals(12002, $header->getCode());
    }

}