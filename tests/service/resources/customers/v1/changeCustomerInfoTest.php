<?php
namespace tests\service\resources\customers\v1;

use framework\message\Message;
use service\message\customer\FillCustomerInfoRequest;
use service\resources\customers\v1\changeCustomerInfo;
use tests\service\CustomersTest;

class changeCustomerInfoTest extends CustomersTest
{

    public function _initData()
    {
        $this->header->setRoute('customers.changeCustomerInfo');

    }

    public function testRequest()
    {
        $this->assertInstanceOf('service\message\customer\FillCustomerInfoRequest', changeCustomerInfo::request());
    }

    public function testRun()
    {
        /** @var FillCustomerInfoRequest $request */
        $request = new FillCustomerInfoRequest();
        $request->setCustomerId($this->customer_id);
        $request->setAuthToken($this->auth_token);
        $request->setStoreName('测试');
        $request->setStoreKeeper('测试');
        $request->setStoreArea(50);
        $request->setBusinessLicenseImg('测试');
        $rawBody = Message::pack($this->header, $request);
        $this->request->setRawBody($rawBody);
        $response = $this->application->handleRequest($this->request);
        /** @var \service\message\common\ResponseHeader $header */
        list($header, $data) = $response;
        $this->assertNotEmpty($response);
        $this->assertEquals(0, $header->getCode());
    }

}