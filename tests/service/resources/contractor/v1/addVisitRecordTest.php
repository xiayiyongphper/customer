<?php
namespace tests\service\resources\contractor\v1;

use framework\message\Message;
use service\message\contractor\addVisitRecordRequest;
use service\message\contractor\VisitRecord;
use service\resources\contractor\v1\addVisitRecord;
use tests\service\ContractorTest;

class  addVisitRecordTest extends ContractorTest
{

    public function _initData()
    {
        $this->header->setRoute('contractor.addVisitRecord');
    }

    public function testRequest()
    {
        $this->assertInstanceOf('service\message\contractor\addVisitRecordRequest', addVisitRecord::request());
    }


    public function testSave()
    {
        /** @var addVisitRecordRequest $request */
        $request = new addVisitRecordRequest();
        $request->setContractorId($this->contractor_id);
        $request->setAuthToken($this->contractor_auth_token);
        $request->setAction(2);
        $visitRecord = new VisitRecord();
        $visitRecord->setContractorId($this->contractor_id);
        $visitRecord->setCustomerId($this->contractor_id);
        $visitRecord->setContractorName('王洋');
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

    public function testNew()
    {
        /** @var addVisitRecordRequest $request */
        $request = new addVisitRecordRequest();
        $request->setContractorId($this->contractor_id);
        $request->setAuthToken($this->contractor_auth_token);
        $request->setAction(1);
        $visitRecord = new VisitRecord();
        $visitRecord->setContractorId($this->contractor_id);
        $visitRecord->setCustomerId($this->contractor_id);
        $visitRecord->setContractorName('王洋');
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