<?php
namespace tests\service\resources\customers\v1;

use framework\message\Message;
use service\message\customer\PushQueueRequest;
use service\resources\customers\v1\pushQueue;
use tests\service\CustomersTest;

class  pushQueueTest extends CustomersTest
{

    public function _initData()
    {
        $this->header->setRoute('customers.pushQueue');
    }

    public function testRequest()
    {
        $this->assertInstanceOf('service\message\customer\PushQueueRequest', pushQueue::request());
    }

    public function testRun()
    {
        /** @var PushQueueRequest $request */
        $request = new PushQueueRequest();
        $request->setToken('1111111');
        $request->setTypequeue(1);
        $request->setChannel(1000);
        $request->setGroupId(1);
        $request->setMessage('测试');
        $request->setPlatForm(1);

        $rawBody = Message::pack($this->header, $request);
        $this->request->setRawBody($rawBody);
        $response = $this->application->handleRequest($this->request);
        /** @var \service\message\common\ResponseHeader $header */
        list($header, $data) = $response;
        $this->assertNotEmpty($response);
        $this->assertEquals(0, $header->getCode());
    }

}