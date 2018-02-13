<?php
namespace tests\service\resources\customers\v1;

use framework\message\Message;
use service\message\customer\ConfirmVerifyCodeRequest;
use service\resources\customers\v1\confirmVerifyCode;
use tests\service\CustomersTest;

class confirmVerifyCodeTest extends CustomersTest
{

    public function _initData()
    {
        $this->header->setRoute('customers.confirmVerifyCode');
    }

    public function testRequest()
    {
        $this->assertInstanceOf('service\message\customer\ConfirmVerifyCodeRequest', confirmVerifyCode::request());
    }

    public function testResponse()
    {
        $this->assertInstanceOf('service\message\common\UniversalResponse', confirmVerifyCode::response());
    }

    public function testVerifyCodeError()
    {
        /** @var ConfirmVerifyCodeRequest $request */
        $request = new ConfirmVerifyCodeRequest();
        $request->setType(1);
        $request->setPhone('18682002348');
        $request->setCode('95239523');
        $rawBody = Message::pack($this->header, $request);
        $this->request->setRawBody($rawBody);
        $response = $this->application->handleRequest($this->request);
        /** @var \service\message\common\ResponseHeader $header */
        list($header, $data) = $response;
        $this->assertNotEmpty($response);
        $this->assertEquals(12011, $header->getCode());
    }

}