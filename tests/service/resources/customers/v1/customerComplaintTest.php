<?php
namespace tests\service\resources\customers\v1;

use framework\message\Message;
use service\message\customer\CustomerComplaintRequest;
use service\resources\customers\v1\customerComplaint;
use tests\service\CustomersTest;

class customerComplaintTest extends CustomersTest
{

    public function _initData()
    {
        $this->header->setRoute('customers.customerComplaint');
    }

    public function testRequest()
    {
        $this->assertInstanceOf('service\message\customer\CustomerComplaintRequest', customerComplaint::request());
    }

    public function testRun()
    {
        /** @var CustomerComplaintRequest $request */
        $request = new CustomerComplaintRequest();
        $request->setCustomerId($this->customer_id);
        $request->setAuthToken($this->auth_token);
        $request->setContactPhone('18682002348');
        $request->setContactName('王洋');
        $request->setWholesalerId('1');
        $request->setIncrementId('11111111');
        $request->setType('1');
        $request->setContent('测试');
        $rawBody = Message::pack($this->header, $request);
        $this->request->setRawBody($rawBody);
        $response = $this->application->handleRequest($this->request);
        /** @var \service\message\common\ResponseHeader $header */
        list($header, $data) = $response;
        $this->assertNotEmpty($response);
        $this->assertEquals(0, $header->getCode());
    }

}