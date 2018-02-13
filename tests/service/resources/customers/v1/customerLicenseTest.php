<?php
namespace tests\service\resources\customers\v1;

use framework\message\Message;
use service\message\customer\CustomerAuthenticationRequest;
use service\resources\customers\v1\customerLicense;
use tests\service\CustomersTest;

class customerLicenseTest extends CustomersTest
{

    public function _initData()
    {
        $this->header->setRoute('customers.customerLicense');
    }

    public function testRequest()
    {
        $this->assertInstanceOf('service\message\customer\CustomerAuthenticationRequest', customerLicense::request());
    }


    public function testRun()
    {
        /** @var CustomerAuthenticationRequest $request */
        $request = new CustomerAuthenticationRequest();
        $request->setCustomerId($this->customer_id);
        $request->setAuthToken($this->auth_token);
        $rawBody = Message::pack($this->header, $request);
        $this->request->setRawBody($rawBody);
        $response = $this->application->handleRequest($this->request);
        /** @var \service\message\common\ResponseHeader $header */
        list($header, $data) = $response;
        $this->assertNotEmpty($response);
        $this->assertEquals(0, $header->getCode());
    }

}