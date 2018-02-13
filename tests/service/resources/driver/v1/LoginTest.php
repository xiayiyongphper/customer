<?php
namespace tests\service\resources\driver\v1;

use framework\message\Message;
use service\message\driver\LoginRequest;
use service\resources\driver\v1\login;
use tests\service\DriverTest;

class  LoginTest extends DriverTest
{

    public function _initData()
    {
        $this->header->setRoute('driver.login');
    }

    public function testRequest()
    {
        $this->assertInstanceOf('service\message\driver\LoginRequest', login::request());
    }


    public function testLoginByCode()
    {
        /** @var LoginRequest $request */
        $request = new LoginRequest();
        $request->setPhone('18682002348');
        $request->setCode('95239523');
        $request->setLoginType(1);

        $rawBody = Message::pack($this->header, $request);
        $this->request->setRawBody($rawBody);
        $response = $this->application->handleRequest($this->request);
        /** @var \service\message\common\ResponseHeader $header */
        list($header, $data) = $response;
        $this->assertNotEmpty($response);
        $this->assertEquals(0, $header->getCode());
    }

    public function testLoginByPassword()
    {
        /** @var LoginRequest $request */
        $request = new LoginRequest();
        $request->setPhone('18682002348');
        $request->setPassword('e10adc3949ba59abbe56e057f20f883e');
        $request->setLoginType(2);

        $rawBody = Message::pack($this->header, $request);
        $this->request->setRawBody($rawBody);
        $response = $this->application->handleRequest($this->request);
        /** @var \service\message\common\ResponseHeader $header */
        list($header, $data) = $response;
        $this->assertNotEmpty($response);
        $this->assertEquals(0, $header->getCode());
    }

}