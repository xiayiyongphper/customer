<?php
namespace tests\service\resources\contractor\v1;

use framework\message\Message;
use service\message\contractor\CreateCustomerIntentionRequest;
use service\resources\contractor\v1\createCustomersIntention;
use tests\service\ContractorTest;

class  createCustomersIntentionTest extends ContractorTest
{

    public function _initData()
    {
        $this->header->setRoute('contractor.createCustomersIntention');
    }

    public function testRequest()
    {
        $this->assertInstanceOf('service\message\contractor\CreateCustomerIntentionRequest', createCustomersIntention::request());
    }


    public function testRun()
    {
        /** @var CreateCustomerIntentionRequest $request */
        $request = new CreateCustomerIntentionRequest();
        $request->setContractorId($this->contractor_id);
        $request->setAuthToken($this->contractor_auth_token);
        $request->setCity('441800');
        $request->setAreaId(44);
        $request->setAddress('测试');
        $request->setDetailAddress('测试');
        $request->setStoreName('测试');
        $request->setStorekeeper('测试');
        $request->setBusinessLicenseNo('61561561615');
        $request->setLat(22);
        $request->setLng(22);
        $request->appendType(1);
        $request->appendType(2);
        $request->appendType(3);
        $request->setStorekeeperInstoreTimes('测试');

        $rawBody = Message::pack($this->header, $request);
        $this->request->setRawBody($rawBody);
        $response = $this->application->handleRequest($this->request);
        /** @var \service\message\common\ResponseHeader $header */
        list($header, $data) = $response;
        $this->assertNotEmpty($response);
        $this->assertEquals(0, $header->getCode());
    }

}