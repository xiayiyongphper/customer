<?php
namespace tests\service\resources\contractor\v1;

use framework\message\Message;
use service\message\contractor\SmsLoginRequest;
use service\message\driver\LoginRequest;
use service\resources\contractor\v1\smsLogin;
use service\resources\driver\v1\Login;
use tests\service\ContractorTest;

class  smsLoginTest extends ContractorTest
{

    public function _initData()
    {
        $this->header->setRoute('contractor.smsLogin');
    }

    public function testRequest()
    {
        $this->assertInstanceOf('service\message\contractor\SmsLoginRequest', smsLogin::request());
    }


    public function testRun()
    {
        /** @var SmsLoginRequest $request */
        $request = new SmsLoginRequest();
        $request->setPhone('18682002348');
        $request->setCode('4851');

        $rawBody = Message::pack($this->header, $request);
        $this->request->setRawBody($rawBody);
        $response = $this->application->handleRequest($this->request);
        /** @var \service\message\common\ResponseHeader $header */
        list($header, $data) = $response;
        $this->assertNotEmpty($response);
        $this->assertEquals(0, $header->getCode());
    }

}