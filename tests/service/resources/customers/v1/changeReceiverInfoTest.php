<?php
namespace tests\service\resources\customers\v1;

use framework\message\Message;
use service\message\customer\ChangeReceiverInfoRequest;
use service\resources\customers\v1\changeReceiverInfo;
use tests\service\CustomersTest;

class changeReceiverInfoTest extends CustomersTest
{

    public function _initData()
    {
        $this->header->setRoute('customers.changeReceiverInfo');
    }

    public function testRequest()
    {
        $this->assertInstanceOf('service\message\customer\ChangeReceiverInfoRequest', changeReceiverInfo::request());
    }

    public function testRun()
    {
        /** @var ChangeReceiverInfoRequest $request */
        $request = new ChangeReceiverInfoRequest();
        $request->setCustomerId($this->customer_id);
        $request->setAuthToken($this->auth_token);
        $request->setReceiverName('王洋');
        $request->setReceiverPhone('18682002348');
        $rawBody = Message::pack($this->header, $request);
        $this->request->setRawBody($rawBody);
        $response = $this->application->handleRequest($this->request);
        /** @var \service\message\common\ResponseHeader $header */
        list($header, $data) = $response;
        $this->assertNotEmpty($response);
        $this->assertEquals(0, $header->getCode());
    }

    public function testRunReceiveNone()
    {
        /** @var ChangeReceiverInfoRequest $request */
        $request = new ChangeReceiverInfoRequest();
        $request->setCustomerId('99999999');
        $request->setAuthToken('nvgUjDzOTcSXXbVp');
        $request->setReceiverName('王洋');
        $request->setReceiverPhone('18682002348');
        $rawBody = Message::pack($this->header, $request);
        $this->request->setRawBody($rawBody);
        $response = $this->application->handleRequest($this->request);
        /** @var \service\message\common\ResponseHeader $header */
        list($header, $data) = $response;
        $this->assertNotEmpty($response);
        $this->assertEquals(12001, $header->getCode());
    }

}