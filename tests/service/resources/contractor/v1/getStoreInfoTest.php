<?php
namespace tests\service\resources\contractor\v1;

use framework\message\Message;
use service\message\contractor\GetAreaByLngLatRequest;
use service\message\contractor\GetStoreInfoRequest;
use service\resources\contractor\v1\getAreaByLngLat;
use service\resources\contractor\v1\getStoreInfo;
use tests\service\ContractorTest;

class  getStoreInfoTest extends ContractorTest
{

    public function _initData()
    {
        $this->header->setRoute('contractor.getStoreInfo');
    }

    public function testRequest()
    {
        $this->assertInstanceOf('service\message\contractor\GetStoreInfoRequest', getStoreInfo::request());
    }


    public function testRun()
    {
        /** @var GetStoreInfoRequest $request */
        $request = new GetStoreInfoRequest();
        $request->setContractorId($this->contractor_id);
        $request->setAuthToken($this->contractor_auth_token);
        $request->setCustomerId($this->customer_id);
        $request->setCustomerStyle(1);

        $rawBody = Message::pack($this->header, $request);
        $this->request->setRawBody($rawBody);
        $response = $this->application->handleRequest($this->request);
        /** @var \service\message\common\ResponseHeader $header */
        list($header, $data) = $response;
        $this->assertNotEmpty($response);
        $this->assertEquals(0, $header->getCode());
    }

}