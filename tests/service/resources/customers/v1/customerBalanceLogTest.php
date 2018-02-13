<?php
namespace tests\service\resources\customers\v1;

use framework\message\Message;
use service\message\customer\CustomerBalanceLogRequest;
use service\resources\customers\v1\customerBalanceLog;
use tests\service\CustomersTest;

class customerBalanceLogTest extends CustomersTest
{

    public function _initData()
    {
        $this->header->setRoute('customers.customerBalanceLog');
    }

    public function testRequest()
    {
        $this->assertInstanceOf('service\message\customer\CustomerBalanceLogRequest', customerBalanceLog::request());
    }

    public function testResponse()
    {
        $this->assertInstanceOf('service\message\customer\CustomerBalanceLogResponse', customerBalanceLog::response());
    }

    public function testRun()
    {
        /** @var CustomerBalanceLogRequest $request */
        $request = new CustomerBalanceLogRequest();
        $request->setCustomerId($this->customer_id);
        $request->setAuthToken($this->auth_token);
        $request->setPage(1);
        $rawBody = Message::pack($this->header, $request);
        $this->request->setRawBody($rawBody);
        $response = $this->application->handleRequest($this->request);
        /** @var \service\message\common\ResponseHeader $header */
        list($header, $data) = $response;
        $this->assertNotEmpty($response);
        $this->assertEquals(0, $header->getCode());
    }

}