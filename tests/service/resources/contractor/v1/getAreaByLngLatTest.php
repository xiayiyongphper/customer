<?php
namespace tests\service\resources\contractor\v1;

use framework\message\Message;
use service\message\contractor\GetAreaByLngLatRequest;
use service\resources\contractor\v1\getAreaByLngLat;
use tests\service\ContractorTest;

class  getAreaByLngLatTest extends ContractorTest
{

    public function _initData()
    {
        $this->header->setRoute('contractor.getAreaByLngLat');
    }

    public function testRequest()
    {
        $this->assertInstanceOf('service\message\contractor\GetAreaByLngLatRequest', getAreaByLngLat::request());
    }


    public function testRun()
    {
        /** @var GetAreaByLngLatRequest $request */
        $request = new GetAreaByLngLatRequest();
        $request->setContractorId($this->contractor_id);
        $request->setAuthToken($this->contractor_auth_token);
        $request->setLat(22);
        $request->setLng(22);

        $rawBody = Message::pack($this->header, $request);
        $this->request->setRawBody($rawBody);
        $response = $this->application->handleRequest($this->request);
        /** @var \service\message\common\ResponseHeader $header */
        list($header, $data) = $response;
        $this->assertNotEmpty($response);
        $this->assertEquals(0, $header->getCode());
    }

}