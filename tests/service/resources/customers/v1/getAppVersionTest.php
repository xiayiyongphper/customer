<?php
namespace tests\service\resources\customers\v1;

use framework\message\Message;
use service\message\customer\GetAppVersionRequest;
use service\resources\customers\v1\getAppVersion;
use tests\service\CustomersTest;

class  getAppVersionTest extends CustomersTest
{

    public function _initData()
    {
        $this->header->setRoute('customers.getAppVersion');
    }

    public function testRequest()
    {
        $this->assertInstanceOf('service\message\customer\GetAppVersionRequest', getAppVersion::request());
    }


    public function testRun()
    {
        /** @var GetAppVersionRequest $request */
        $request = new GetAppVersionRequest();
        $request->setChannel(100000);
        $request->setPlatform(1);
        $request->setVersion('1.0');
        $request->setSystem(2);

        $rawBody = Message::pack($this->header, $request);
        $this->request->setRawBody($rawBody);
        $response = $this->application->handleRequest($this->request);
        /** @var \service\message\common\ResponseHeader $header */
        list($header, $data) = $response;
        $this->assertNotEmpty($response);
        $this->assertEquals(0, $header->getCode());
    }

}