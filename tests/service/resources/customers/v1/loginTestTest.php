<?php
namespace tests\service\resources\customers\v1;

use framework\message\Message;
use service\message\customer\LoginRequest;
use service\resources\customers\v1\login;
use tests\service\CustomersTest;

class loginTestTest extends CustomersTest
{

    public function _initData()
    {
        $this->header->setRoute('customers.loginTest');
    }

    public function testRequest()
    {
        $this->assertInstanceOf('service\message\customer\LoginRequest', login::request());
    }

    public function testResponse()
    {
        $this->assertInstanceOf('service\message\customer\CustomerResponse', login::response());
    }

    public function testRun()
    {
        /** @var LoginRequest $request */
        $request = new LoginRequest();
        $request->setUsername('18682002348');
        $request->setPassword('e10adc3949ba59abbe56e057f20f883e');
        $rawBody = Message::pack($this->header, $request);
        $this->request->setRawBody($rawBody);
        $response = $this->application->handleRequest($this->request);
        /** @var \service\message\common\ResponseHeader $header */
        list($header, $data) = $response;
        $this->assertNotEmpty($response);
        $this->assertEquals(0, $header->getCode());
    }

}