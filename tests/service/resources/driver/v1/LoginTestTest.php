<?php
namespace tests\service\resources\driver\v1;

use framework\message\Message;
use service\message\driver\LoginRequest;
use service\resources\driver\v1\loginTest;
use tests\service\DriverTest;

class  LoginTestTest extends DriverTest
{

    public function _initData()
    {
        $this->header->setRoute('driver.loginTest');
    }

    public function testRequest()
    {
        $this->assertInstanceOf('service\message\driver\LoginRequest', loginTest::request());
    }


    public function testRun()
    {
        /** @var LoginRequest $request */
        $request = new LoginRequest();

        $rawBody = Message::pack($this->header, $request);
        $this->request->setRawBody($rawBody);
        $response = $this->application->handleRequest($this->request);
        /** @var \service\message\common\ResponseHeader $header */
        list($header, $data) = $response;
        $this->assertNotEmpty($response);
        $this->assertEquals(0, $header->getCode());
    }

}