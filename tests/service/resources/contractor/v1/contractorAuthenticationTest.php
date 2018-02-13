<?php
namespace tests\service\resources\contractor\v1;

use framework\message\Message;
use service\message\contractor\ContractorAuthenticationRequest;
use service\resources\contractor\v1\contractorAuthentication;
use tests\service\ContractorTest;

class  contractorAuthenticationTest extends ContractorTest
{

    public function _initData()
    {
        $this->header->setRoute('contractor.contractorAuthentication');
    }

    public function testRequest()
    {
        $this->assertInstanceOf('service\message\contractor\ContractorAuthenticationRequest', contractorAuthentication::request());
    }


    public function testRun()
    {
        /** @var ContractorAuthenticationRequest $request */
        $request = new ContractorAuthenticationRequest();
        $request->setContractorId($this->contractor_id);
        $request->setAuthToken($this->contractor_auth_token);

        $rawBody = Message::pack($this->header, $request);
        $this->request->setRawBody($rawBody);
        $response = $this->application->handleRequest($this->request);
        /** @var \service\message\common\ResponseHeader $header */
        list($header, $data) = $response;
        $this->assertNotEmpty($response);
        $this->assertEquals(0, $header->getCode());
    }

}