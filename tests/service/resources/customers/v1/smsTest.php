<?php
namespace tests\service\resources\customers\v1;

use framework\message\Message;
use service\message\customer\GetSmsRequest;
use service\resources\customers\v1\sms;
use tests\service\CustomersTest;

class smsTest extends CustomersTest
{

    public function _initData()
    {
        $this->header->setRoute('customers.sms');
    }

    public function testRequest()
    {
        $this->assertInstanceOf('service\message\customer\GetSmsRequest', sms::request());
    }

    public function testRun()
    {
        /** @var GetSmsRequest $request */
        $request = new GetSmsRequest();
        $request->setType(1);
        $request->setPhone('18685965874');
        $request->setToken('9gdgtq7eym0579dobesmqm5ze0ig3mpm');

        $rawBody = Message::pack($this->header, $request);
        $this->request->setRawBody($rawBody);
        $response = $this->application->handleRequest($this->request);
        /** @var \service\message\common\ResponseHeader $header */
        list($header, $data) = $response;
        $this->assertNotEmpty($response);
        $this->assertEquals(0, $header->getCode());
    }

}