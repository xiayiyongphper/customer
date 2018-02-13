<?php
namespace tests\service\resources\contractor\v1;

use framework\message\Message;
use service\message\contractor\ContractorAuthenticationRequest;
use service\message\contractor\ContractorOrderDetailRequest;
use service\message\contractor\GetAreaByLngLatRequest;
use service\message\contractor\GetStoreInfoRequest;
use service\message\contractor\MarkPriceRequest;
use service\message\contractor\QuickRegisterCustomerRequest;
use service\message\contractor\SaveStoreRequest;
use service\message\contractor\StoresListRequest;
use service\message\contractor\visitedRecordsRequest;
use service\message\customer\GetSmsRequest;
use service\resources\contractor\v1\getAreaByLngLat;
use service\resources\contractor\v1\getStoreInfo;
use service\resources\contractor\v1\home;
use service\resources\contractor\v1\markPrice;
use service\resources\contractor\v1\orderDetail;
use service\resources\contractor\v1\quickCreateCustomer;
use service\resources\contractor\v1\saveStoreInfo;
use service\resources\contractor\v1\saveStoreIntention;
use service\resources\contractor\v1\storeList;
use service\resources\contractor\v1\visitedRecords;
use service\resources\customers\v1\sms;
use tests\service\ContractorTest;

class  visitedRecordsTest extends ContractorTest
{

    public function _initData()
    {
        $this->header->setRoute('contractor.visitedRecords');
    }

    public function testRequest()
    {
        $this->assertInstanceOf('service\message\contractor\visitedRecordsRequest', visitedRecords::request());
    }

    public function testRun()
    {
        /** @var visitedRecordsRequest $request */
        $request = new visitedRecordsRequest();
        $request->setContractorId($this->contractor_id);
        $request->setAuthToken($this->contractor_auth_token);
        $request->setCustomerId($this->customer_id);

        $rawBody = Message::pack($this->header, $request);
        $this->request->setRawBody($rawBody);
        $response = $this->application->handleRequest($this->request);
        /** @var \service\message\common\ResponseHeader $header */
        list($header, $data) = $response;
        $this->assertNotEmpty($response);
        $this->assertEquals(0, $header->getCode());
    }

}