<?php
namespace tests\service\resources\contractor\v1;

use framework\message\Message;
use service\message\contractor\addVisitRecordBriefRequest;
use service\message\contractor\VisitRecord;
use service\resources\contractor\v1\addVisitRecordBrief;
use tests\service\ContractorTest;

class  addVisitRecordBriefTest extends ContractorTest
{

    public function _initData()
    {
        $this->header->setRoute('contractor.addVisitRecordBrief');
    }

    public function testRequest()
    {
        $this->assertInstanceOf('service\message\contractor\addVisitRecordBriefRequest', addVisitRecordBrief::request());
    }


    public function testRun()
    {
        /** @var addVisitRecordBriefRequest $request */
        $request = new addVisitRecordBriefRequest();
        $request->setContractorId($this->contractor_id);
        $request->setAuthToken($this->contractor_auth_token);
        $visitRecord = new VisitRecord();
        $visitRecord->setCustomerId($this->customer_id);
        $visitRecord->setLocateAddress('测试');
        $visitRecord->setFeedback('测试');
        $visitRecord->setLat(22);
        $visitRecord->setLng(22);
        $visitRecord->setStoreName('测试');
        $visitRecord->setVisitWay('测试');
        $request->setRecord($visitRecord);

        $rawBody = Message::pack($this->header, $request);
        $this->request->setRawBody($rawBody);
        $response = $this->application->handleRequest($this->request);
        /** @var \service\message\common\ResponseHeader $header */
        list($header, $data) = $response;
        $this->assertNotEmpty($response);
        $this->assertEquals(0, $header->getCode());
    }

}