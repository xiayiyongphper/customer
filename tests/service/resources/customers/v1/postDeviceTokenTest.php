<?php
namespace tests\service\resources\customers\v1;

use framework\message\Message;
use service\message\customer\PostDeviceTokenRequest;
use service\resources\customers\v1\postDeviceToken;
use tests\service\CustomersTest;

class  postDeviceTokenTest extends CustomersTest
{

    public function _initData()
    {
        $this->header->setRoute('customers.postDeviceToken');
    }

    public function testRequest()
    {
        $this->assertInstanceOf('service\message\customer\PostDeviceTokenRequest', postDeviceToken::request());
    }


    public function testRun()
    {
        /** @var PostDeviceTokenRequest $request */
        $request = new PostDeviceTokenRequest();
        $request->setChannel(10000);
        $request->setSystem(1);
        $request->setCustomerId($this->customer_id);
        $request->setAuthToken($this->auth_token);
        $request->setTypequeue(1);
        $request->setToken('1111111');
        $request->setWholesalerId(1);

        $rawBody = Message::pack($this->header, $request);
        $this->request->setRawBody($rawBody);
        $response = $this->application->handleRequest($this->request);
        /** @var \service\message\common\ResponseHeader $header */
        list($header, $data) = $response;
        $this->assertNotEmpty($response);
        $this->assertEquals(0, $header->getCode());
    }

}