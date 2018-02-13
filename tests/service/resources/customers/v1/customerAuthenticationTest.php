<?php
namespace tests\service\resources\customers\v1;

use framework\message\Message;
use service\message\customer\CustomerAuthenticationRequest;
use service\resources\customers\v1\customerAuthentication;
use tests\service\CustomersTest;

class customerAuthenticationTest extends CustomersTest
{

    public function _initData()
    {
        $this->header->setRoute('customers.customerAuthentication');

    }

    public function testRequest()
    {
        $this->assertInstanceOf('service\message\customer\CustomerAuthenticationRequest', customerAuthentication::request());
    }

    public function testResponse()
    {
        $this->assertInstanceOf('service\message\customer\CustomerResponse', customerAuthentication::response());
    }

    /**
     * @covers service\resources\customers\v1\customerAuthentication::run
     */
    public function testRun()
    {
        /** @var CustomerAuthenticationRequest $request */
        $request = new CustomerAuthenticationRequest();
        $request->setCustomerId($this->customer_id);
        $request->setAuthToken($this->auth_token);
        $rawBody = Message::pack($this->header, $request);
        $this->request->setRawBody($rawBody);
        $response = $this->application->handleRequest($this->request);
        $this->assertNotEmpty($response);
    }

    /**
     * @covers service\resources\customers\v1\customerAuthentication::run
     */
    public function testRunNocache()
    {
        /** @var CustomerAuthenticationRequest $request */
        $request = new CustomerAuthenticationRequest();
        $request->setCustomerId('1057');
        $request->setAuthToken('nvgUjDzOTcSXXbVp');
        $request->setNoCache(0);
        $rawBody = Message::pack($this->header, $request);
        $this->request->setRawBody($rawBody);
        $response = $this->application->handleRequest($this->request);
        $this->assertNotEmpty($response);
    }

    /**
     * @covers service\resources\customers\v1\customerAuthentication::run
     */
    public function testCustomerNotExist()
    {
        /** @var CustomerAuthenticationRequest $request */
        $request = new CustomerAuthenticationRequest();
        $request->setCustomerId('99999999');
        $request->setAuthToken('nvgUjDzOTcSXXbVp');
        $rawBody = Message::pack($this->header, $request);
        $this->request->setRawBody($rawBody);
        $response = $this->application->handleRequest($this->request);
        /** @var \service\message\common\ResponseHeader $header */
        list($header, $data) = $response;
        $this->assertNotEmpty($response);
        $this->assertEquals(12001, $header->getCode());
    }
}