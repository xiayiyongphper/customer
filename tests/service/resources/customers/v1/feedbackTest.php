<?php
namespace tests\service\resources\customers\v1;

use framework\message\Message;
use service\message\customer\FeedbackRequest;
use service\resources\customers\v1\feedback;
use tests\service\CustomersTest;

class  feedbackTest extends CustomersTest
{

    public function _initData()
    {
        $this->header->setRoute('customers.feedback');
    }

    public function testRequest()
    {
        $this->assertInstanceOf('service\message\customer\FeedbackRequest', feedback::request());
    }


    public function testRun()
    {
        /** @var FeedbackRequest $request */
        $request = new FeedbackRequest();
        $request->setCustomerId($this->customer_id);
        $request->setAuthToken($this->auth_token);
        $request->setUserName('test');
        $request->setPhone('18682002348');
        $request->setTypeId(1);
        $request->setTypevar('测试');
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