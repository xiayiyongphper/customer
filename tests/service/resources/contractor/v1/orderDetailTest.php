<?php
namespace tests\service\resources\contractor\v1;

use framework\message\Message;
use service\message\contractor\ContractorAuthenticationRequest;
use service\message\contractor\ContractorOrderDetailRequest;
use service\message\contractor\GetAreaByLngLatRequest;
use service\message\contractor\GetStoreInfoRequest;
use service\message\contractor\MarkPriceRequest;
use service\resources\contractor\v1\getAreaByLngLat;
use service\resources\contractor\v1\getStoreInfo;
use service\resources\contractor\v1\home;
use service\resources\contractor\v1\markPrice;
use service\resources\contractor\v1\orderDetail;
use tests\service\ContractorTest;

class  orderDetailTest extends ContractorTest
{

    public function _initData()
    {
        $this->header->setRoute('contractor.orderDetail');
    }

    public function testRequest()
    {
        $this->assertInstanceOf('service\message\contractor\ContractorOrderDetailRequest', orderDetail::request());
    }


    public function testRun()
    {
        /** @var ContractorOrderDetailRequest $request */
        $request = new ContractorOrderDetailRequest();
        $request->setContractorId($this->contractor_id);
        $request->setAuthToken($this->contractor_auth_token);
        $request->setOrderId(193);

        $rawBody = Message::pack($this->header, $request);
        $this->request->setRawBody($rawBody);
        $response = $this->application->handleRequest($this->request);
        /** @var \service\message\common\ResponseHeader $header */
        list($header, $data) = $response;
        $this->assertNotEmpty($response);
        $this->assertEquals(0, $header->getCode());
    }

}