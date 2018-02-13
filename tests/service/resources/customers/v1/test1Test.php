<?php
namespace tests\service\resources\customers\v1;

use framework\message\Message;
use service\message\customer\TestReportRequest;
use service\resources\customers\v1\test1;
use tests\service\CustomersTest;

class  test1Test extends CustomersTest
{

    public function _initData()
    {
        $this->header->setRoute('customers.test1');
    }

    public function testRequest()
    {
        $this->assertInstanceOf('service\message\customer\TestReportRequest', test1::request());
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