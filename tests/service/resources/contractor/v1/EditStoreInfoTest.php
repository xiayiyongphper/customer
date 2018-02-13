<?php
namespace tests\service\resources\contractor\v1;

use framework\message\Message;
use service\message\contractor\EditStoreInfoRequest;
use service\resources\contractor\v1\EditStoreInfo;
use tests\service\ContractorTest;

class  EditStoreInfoTest extends ContractorTest
{

    public function _initData()
    {
        $this->header->setRoute('contractor.EditStoreInfo');
    }

    public function testRequest()
    {
        $this->assertInstanceOf('service\message\contractor\EditStoreInfoRequest', EditStoreInfo::request());
    }


    public function testStore()
    {
        /** @var EditStoreInfoRequest $request */
        $request = new EditStoreInfoRequest();
        $request->setContractorId($this->contractor_id);
        $request->setAuthToken($this->contractor_auth_token);
        $request->setStoreId(8);
        $request->setStoreStyle(1);

        $rawBody = Message::pack($this->header, $request);
        $this->request->setRawBody($rawBody);
        $response = $this->application->handleRequest($this->request);
        /** @var \service\message\common\ResponseHeader $header */
        list($header, $data) = $response;
        $this->assertNotEmpty($response);
        $this->assertEquals(0, $header->getCode());
    }

    public function testStoreIntention()
    {
        /** @var EditStoreInfoRequest $request */
        $request = new EditStoreInfoRequest();
        $request->setContractorId($this->contractor_id);
        $request->setAuthToken($this->contractor_auth_token);
        $request->setStoreId(8);
        $request->setStoreStyle(2);

        $rawBody = Message::pack($this->header, $request);
        $this->request->setRawBody($rawBody);
        $response = $this->application->handleRequest($this->request);
        /** @var \service\message\common\ResponseHeader $header */
        list($header, $data) = $response;
        $this->assertNotEmpty($response);
        $this->assertEquals(0, $header->getCode());
    }

}