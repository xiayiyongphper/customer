<?php
namespace tests\service\resources\customers\v1;

use framework\message\Message;
use service\message\customer\CheckCustomerRequest;
use service\resources\customers\v1\checkCustomerIfExist;
use tests\service\CustomersTest;

class checkCustomerIfExistTest extends CustomersTest
{

    public function _initData()
    {
        $this->header->setRoute('customers.checkCustomerIfExist');
    }

    public function testRequest()
    {
        $this->assertInstanceOf('service\message\customer\CheckCustomerRequest', checkCustomerIfExist::request());
    }

    public function testUsername()
    {
        /** @var CheckCustomerRequest $request */
        $request = new CheckCustomerRequest();
        $request->setUsername('wofengletian');
        $request->setField(1);
        $rawBody = Message::pack($this->header, $request);
        $this->request->setRawBody($rawBody);
        $response = $this->application->handleRequest($this->request);
        /** @var \service\message\common\ResponseHeader $header */
        list($header, $data) = $response;
        $this->assertNotEmpty($response);
        $this->assertEquals(0, $header->getCode());
    }

    public function testPhone()
    {
        /** @var CheckCustomerRequest $request */
        $request = new CheckCustomerRequest();
        $request->setPhone('18682002348');
        $request->setField(2);
        $rawBody = Message::pack($this->header, $request);
        $this->request->setRawBody($rawBody);
        $response = $this->application->handleRequest($this->request);
        /** @var \service\message\common\ResponseHeader $header */
        list($header, $data) = $response;
        $this->assertNotEmpty($response);
        $this->assertEquals(0, $header->getCode());
    }

}