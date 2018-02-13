<?php
namespace tests\service\resources\contractor\v1;

use framework\message\Message;
use service\message\contractor\searchStoresRequest;
use service\message\customer\GetSmsRequest;
use service\resources\contractor\v1\searchStores;
use tests\service\ContractorTest;

class  searchStoresTest extends ContractorTest
{

    public function _initData()
    {
        $this->header->setRoute('contractor.searchStores');
    }

    public function testRequest()
    {
        $this->assertInstanceOf('service\message\contractor\searchStoresRequest', searchStores::request());
    }


    public function testRun()
    {
        /** @var searchStoresRequest $request */
        $request = new searchStoresRequest();
        $request->setContractorId($this->contractor_id);
        $request->setAuthToken($this->contractor_auth_token);
        $request->setKeyword('测试');

        $rawBody = Message::pack($this->header, $request);
        $this->request->setRawBody($rawBody);
        $response = $this->application->handleRequest($this->request);
        /** @var \service\message\common\ResponseHeader $header */
        list($header, $data) = $response;
        $this->assertNotEmpty($response);
        $this->assertEquals(0, $header->getCode());
    }

}