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
use service\resources\contractor\v1\getAreaByLngLat;
use service\resources\contractor\v1\getStoreInfo;
use service\resources\contractor\v1\home;
use service\resources\contractor\v1\markPrice;
use service\resources\contractor\v1\orderDetail;
use service\resources\contractor\v1\quickCreateCustomer;
use service\resources\contractor\v1\saveStoreInfo;
use service\resources\contractor\v1\saveStoreIntention;
use tests\service\ContractorTest;

class  saveStoreIntentionTest extends ContractorTest
{

    public function _initData()
    {
        $this->header->setRoute('contractor.saveStoreIntention');
    }

    public function testRequest()
    {
        $this->assertInstanceOf('service\message\contractor\SaveStoreRequest', saveStoreIntention::request());
    }


    public function testRun()
    {
        /** @var SaveStoreRequest $request */
        $request = new SaveStoreRequest();
        $request->setContractorId($this->contractor_id);
        $request->setAuthToken($this->contractor_auth_token);
        $request->setAreaId(44);
        $request->setAddress('测试');
        $request->setDetailAddress('测试');
        $request->setStoreName('测试');
        $request->setStorekeeper('测试');
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