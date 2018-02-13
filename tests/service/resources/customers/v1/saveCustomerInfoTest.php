<?php
namespace tests\service\resources\customers\v1;

use framework\message\Message;
use service\message\customer\FillCustomerInfoRequest;
use service\resources\customers\v1\saveCustomerInfo;
use tests\service\CustomersTest;

class  saveCustomerInfoTest extends CustomersTest
{

    public function _initData()
    {
        $this->header->setRoute('customers.saveCustomerInfo');
    }

    public function testRequest()
    {
        $this->assertInstanceOf('service\message\customer\FillCustomerInfoRequest', saveCustomerInfo::request());
    }

    public function testRun()
    {
        /** @var FillCustomerInfoRequest $request */
        $request = new FillCustomerInfoRequest();
        $request->setCustomerId($this->customer_id);
        $request->setAuthToken($this->auth_token);
        $request->setAddress('测试');
        $request->setDetailAddress('测试');
        $request->setProvince('440000');
        $request->setCity('441800');
        $request->setStoreKeeper('测试');
        $request->setStoreArea(51);
        $request->setStoreName('测试');

        $rawBody = Message::pack($this->header, $request);
        $this->request->setRawBody($rawBody);
        $response = $this->application->handleRequest($this->request);
        /** @var \service\message\common\ResponseHeader $header */
        list($header, $data) = $response;
        $this->assertNotEmpty($response);
        $this->assertEquals(0, $header->getCode());
    }

}