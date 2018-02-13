<?php
namespace tests\service\resources\driver\v1;

use framework\message\Message;
use service\message\customer\TestReportRequest;
use service\resources\driver\v1\test2;
use tests\service\DriverTest;

class  test2Test extends DriverTest
{

    public function _initData()
    {
        $this->header->setRoute('driver.test2');
    }

    public function testRequest()
    {
        $this->assertInstanceOf('service\message\customer\TestReportRequest', test2::request());
    }

    public function testRun()
    {
        /** @var TestReportRequest $request */
        $request = new TestReportRequest();
        $request->setIp('127.0.0.1');

        $rawBody = Message::pack($this->header, $request);
        $this->request->setRawBody($rawBody);
        $response = $this->application->handleRequest($this->request);
        /** @var \service\message\common\ResponseHeader $header */
        list($header, $data) = $response;
        $this->assertNotEmpty($response);
        $this->assertEquals(0, $header->getCode());
    }

}