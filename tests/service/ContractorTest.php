<?php
namespace tests\service;

use common\models\driver\Driver;
use common\models\driver\Order;
use common\models\LeContractor;
use common\models\LeCustomers;
use framework\Application;
use framework\Request;
use service\message\common\Header;
use service\message\common\SourceEnum;
use tests\AbstractTest;

abstract class ContractorTest extends AbstractTest
{
    protected $customer_id = 1027;
    protected $contractor_id = 17;
    protected $contractor_auth_token;

    /** @var Header $header*/
    protected $header;
    /** @var Request $request*/
    protected $request;

    /** @var Application $application*/
    protected $application;

    abstract protected function _initData();

    public function setUp()
    {
        parent::setUp();

        //构造header
        $this->header = new Header();
        $this->header->setSource(SourceEnum::CUSTOMER);
        $this->header->setAppVersion(2.6);
        $this->header->setEncrypt('des');
        $this->header->setVersion(1);
        //构造request
        $this->request = new Request();
        $this->request->setRemote(true);
        //加入请求数据
        $this->_initData();
        $this->application = new Application($this->config);
        $contractor = LeContractor::findByContractorId($this->contractor_id);
        $this->contractor_auth_token = $contractor->auth_token;
    }

    public function tearDown()
    {
        parent::tearDown();
        $this->header = null;
        $this->request = null;
    }
}