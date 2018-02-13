<?php
namespace tests\service\resources\customers\v1;

use framework\message\Message;
use service\message\customer\CustomerDetailRequest;
use service\resources\customers\v1\customerDetail;
use tests\service\CustomersTest;

class customerDetailTest extends CustomersTest
{

    public function _initData()
    {
        $this->header->setRoute('customers.customerDetail');
    }

    public function testRequest()
    {
        $this->assertInstanceOf('service\message\customer\CustomerDetailRequest', customerDetail::request());
    }

    public function testResponse()
    {
        $this->assertInstanceOf('service\message\customer\CustomerResponse', customerDetail::response());
    }

    public function testRun()
    {
        /** @var CustomerDetailRequest $request */
        $request = new CustomerDetailRequest();
        $request->setCustomerId($this->customer_id);
        $request->setAuthToken($this->auth_token);
        $rawBody = Message::pack($this->header, $request);
        $this->request->setRawBody($rawBody);
        $response = $this->application->handleRequest($this->request);
        /** @var \service\message\common\ResponseHeader $header */
        list($header, $data) = $response;
        $this->assertNotEmpty($response);
        $this->assertEquals(0, $header->getCode());
    }

    public function testCustomerExpire()
    {
        /** @var CustomerDetailRequest $request */
        $request = new CustomerDetailRequest();
        $request->setCustomerId($this->customer_id);
        $request->setAuthToken('1111111');
        $rawBody = Message::pack($this->header, $request);
        $this->request->setRawBody($rawBody);
        $response = $this->application->handleRequest($this->request);
        /** @var \service\message\common\ResponseHeader $header */
        list($header, $data) = $response;
        $this->assertNotEmpty($response);
        $this->assertEquals(12002, $header->getCode());
    }

}