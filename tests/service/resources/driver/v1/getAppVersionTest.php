<?php
namespace tests\service\resources\driver\v1;

use framework\message\Message;
use service\message\customer\GetAppVersionRequest;
use service\resources\driver\v1\getAppVersion;
use tests\service\DriverTest;

class  getAppVersionTest extends DriverTest
{

    public function _initData()
    {
        $this->header->setRoute('driver.getAppVersion');
    }

    public function testRequest()
    {
        $this->assertInstanceOf('service\message\customer\GetAppVersionRequest', getAppVersion::request());
    }


    public function testRun()
    {
        /** @var GetAppVersionRequest $request */
        $request = new GetAppVersionRequest();
        $request->setChannel(300000);
        $request->setPlatform(3);
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