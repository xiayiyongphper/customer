<?php
namespace tests\service\resources\customers\v1;

use framework\message\Message;
use ProtocolBuffers\Enum;
use service\message\common\SourceEnum;
use service\message\customer\ResetPasswordRequest;
use service\resources\customers\v1\resetPassword;
use tests\service\CustomersTest;

class  resetPasswordTest extends CustomersTest
{

    public function _initData()
    {
        $this->header->setRoute('customers.resetPassword');
    }

    public function testRequest()
    {
        $this->assertInstanceOf('service\message\customer\ResetPasswordRequest', resetPassword::request());
    }

    public function testForgetPassword()
    {
        /** @var ResetPasswordRequest $request */
        $request = new ResetPasswordRequest();
        $request->setCustomerId($this->customer_id);
        $request->setAuthToken($this->auth_token);
        $request->setCode('95239523');
        $request->setNewPassword('111111');
        $request->setPhone('18682002348');
        $request->setType(1);

        $rawBody = Message::pack($this->header, $request);
        $this->request->setRawBody($rawBody);
        $response = $this->application->handleRequest($this->request);
        /** @var \service\message\common\ResponseHeader $header */
        list($header, $data) = $response;
        $this->assertNotEmpty($response);
        $this->assertEquals(0, $header->getCode());
    }

    public function testModifyPassword()
    {
        /** @var ResetPasswordRequest $request */
        $request = new ResetPasswordRequest();
        $request->setCustomerId($this->customer_id);
        $request->setAuthToken($this->auth_token);
        $request->setCode('95239523');
        $request->setNewPassword('e10adc3949ba59abbe56e057f20f883e');
        $request->setPhone('18682002348');
        $request->setType(2);

        $rawBody = Message::pack($this->header, $request);
        $this->request->setRawBody($rawBody);
        $response = $this->application->handleRequest($this->request);
        /** @var \service\message\common\ResponseHeader $header */
        list($header, $data) = $response;
        $this->assertNotEmpty($response);
        $this->assertEquals(0, $header->getCode());
    }

    public function testModifyPasswordPC()
    {
        /** @var ResetPasswordRequest $request */
        $request = new ResetPasswordRequest();
        $request->setCustomerId($this->customer_id);
        $request->setAuthToken($this->auth_token);
        $request->setCode('95239523');
        $request->setNewPassword('e10adc3949ba59abbe56e057f20f883e');
        $request->setOriginalPassword('e10adc3949ba59abbe56e057f20f883e');
        $request->setPhone('18682002348');
        $request->setType(2);
        $this->header->setSource(SourceEnum::PCWEB);
        $rawBody = Message::pack($this->header, $request);
        $this->request->setRawBody($rawBody);
        $response = $this->application->handleRequest($this->request);
        /** @var \service\message\common\ResponseHeader $header */
        list($header, $data) = $response;
        $this->assertNotEmpty($response);
        $this->assertEquals(0, $header->getCode());
    }

}