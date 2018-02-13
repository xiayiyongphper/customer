<?php
namespace tests\service\resources\contractor\v1;

use framework\message\Message;
use service\message\contractor\ContractorAuthenticationRequest;
use service\message\contractor\ContractorOrderDetailRequest;
use service\message\contractor\GetAreaByLngLatRequest;
use service\message\contractor\GetStoreInfoRequest;
use service\message\contractor\MarkPriceRequest;
use service\message\contractor\QuickRegisterCustomerRequest;
use service\resources\contractor\v1\getAreaByLngLat;
use service\resources\contractor\v1\getStoreInfo;
use service\resources\contractor\v1\home;
use service\resources\contractor\v1\markPrice;
use service\resources\contractor\v1\orderDetail;
use service\resources\contractor\v1\quickCreateCustomer;
use tests\service\ContractorTest;

class  quickCreateCustomerTest extends ContractorTest
{

    public function _initData()
    {
        $this->header->setRoute('contractor.quickCreateCustomer');
    }

    public function testRequest()
    {
        $this->assertInstanceOf('service\message\contractor\QuickRegisterCustomerRequest', quickCreateCustomer::request());
    }


    public function testRun()
    {
        /** @var QuickRegisterCustomerRequest $request */
        $request = new QuickRegisterCustomerRequest();
        $request->setContractorId($this->contractor_id);
        $request->setAuthToken($this->contractor_auth_token);
        $request->setUsername('测试');
        $request->setPhone('18682002348');
        $request->setCode(6985);
        $request->setPassword('e10adc3949ba59abbe56e057f20f883e');

        $rawBody = Message::pack($this->header, $request);
        $this->request->setRawBody($rawBody);
        $response = $this->application->handleRequest($this->request);
        /** @var \service\message\common\ResponseHeader $header */
        list($header, $data) = $response;
        $this->assertNotEmpty($response);
        $this->assertEquals(0, $header->getCode());
    }

}