<?php
namespace tests\service\resources\customers\v1;

use framework\message\Message;
use service\message\customer\ChangeBindingPhoneRequest;
use service\resources\customers\v1\changeBindingPhone;
use tests\service\CustomersTest;

class changeBindingPhoneTest extends CustomersTest
{

    public function _initData()
    {
        $this->header->setRoute('customers.changeBindingPhone');

    }

    public function testRequest()
    {
        $this->assertInstanceOf('service\message\customer\ChangeBindingPhoneRequest', changeBindingPhone::request());
    }

    /**
     * @covers service\resources\customers\v1\changeBindingPhone::run
     */
    public function testRunVerifyCodeError()
    {
        /** @var ChangeBindingPhoneRequest $request */
        $request = new ChangeBindingPhoneRequest();
        $request->setCustomerId($this->customer_id);
        $request->setAuthToken($this->auth_token);
        $request->setPhone('18682002348');
        $request->setCode('1111');
        $rawBody = Message::pack($this->header, $request);
        $this->request->setRawBody($rawBody);
        $response = $this->application->handleRequest($this->request);
        $this->assertNotEmpty($response);
        /** @var \service\message\common\ResponseHeader $header */
        list($header, $data) = $response;
        $this->assertNotEmpty($response);
        $this->assertEquals(12011, $header->getCode());
    }

    /**
     * @covers service\resources\customers\v1\changeBindingPhone::run
     */
    public function testRun()
    {
        /** @var ChangeBindingPhoneRequest $request */
        $request = new ChangeBindingPhoneRequest();
        $request->setCustomerId($this->customer_id);
        $request->setAuthToken($this->auth_token);
        $request->setPhone('18682002348');
        $request->setCode('11521');
        $rawBody = Message::pack($this->header, $request);
        $this->request->setRawBody($rawBody);
        $response = $this->application->handleRequest($this->request);
        $this->assertNotEmpty($response);
        /** @var \service\message\common\ResponseHeader $header */
        list($header, $data) = $response;
        $this->assertNotEmpty($response);
        $this->assertEquals(0, $header->getCode());
    }

}