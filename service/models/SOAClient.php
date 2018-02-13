<?php
namespace service\models;

use framework\components\TStringFuncFactory;
use framework\message\Message;
use service\message\common\Header;
use service\message\common\SourceEnum;
use service\message\contractor\addVisitRecordRequest;
use service\message\contractor\ContractorAuthenticationRequest;
use service\message\contractor\searchStoresRequest;
use service\message\contractor\visitedRecordsRequest;
use service\message\customer\CustomerBalanceLogRequest;
use service\message\customer\CustomerDetailRequest;
use service\message\customer\LoginRequest;
use service\message\customer\TestReportRequest;
use service\models\client\ClientAbstract;

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/1/8
 * Time: 12:01
 */
class SOAClient extends ClientAbstract
{

    protected $_responseClass;
    protected $_customerId = 35;
    protected $_authToken = 'KBovpuxTtPUbhq28';

    protected $_contractorId = 17;
    protected $_contractor_authToken = 'BDEbhnbGNUTbReEG';

    public function systemMessage()
    {
        swoole_timer_tick(1, function () {
            $data = [
                'class' => 'service',
                'method' => 'name',
                'time' => time(),
            ];
            $this->send(Message::packJson($data));
        });
    }

	public function login(){
		$this->_responseClass = 'service\message\customer\CustomerResponse';
		$request = new LoginRequest();
		$request->setUsername('zgr0629');
		$request->setPassword(md5('123456'));
		$header = new Header();
		$header->setSource(SourceEnum::CUSTOMER);
		$header->setVersion(1);
		$header->setRoute('customers.login');
		$this->send(Message::pack($header, $request));
	}

    public function visitedRecords(){
        $this->_responseClass = 'service\message\contractor\visitedRecordsResponse';
        $request = new visitedRecordsRequest();
        $request->setContractorId(3);
        $request->setAuthToken('123456');
        $header = new Header();
        $header->setSource(SourceEnum::CUSTOMER);
        $header->setVersion(1);
        $header->setRoute('contractor.visitedRecords');
        $this->send(Message::pack($header, $request));
    }

    public function addVisitRecord(){
        $this->_responseClass = 'service\message\contractor\VisitRecord';
        $request = new addVisitRecordRequest();
        $request->setContractorId(3);
        $request->setAuthToken('123456');
        $header = new Header();
        $header->setSource(SourceEnum::CUSTOMER);
        $header->setVersion(1);
        $header->setRoute('contractor.addVisitRecord');
        $this->send(Message::pack($header, $request));
    }

    public function searchStores()
    {
        $this->_responseClass = 'service\message\contractor\StoresResponse';
        $request = new searchStoresRequest();
        $request->setContractorId(3);
        $request->setAuthToken('123456');
        $request->setKeyword('测试');
        $header = new Header();
        $header->setSource(SourceEnum::CUSTOMER);
        $header->setVersion(1);
        $header->setRoute('contractor.searchStores');
        $this->send(Message::pack($header, $request));
    }


	public function authentication()
	{
		$this->_responseClass = 'service\message\customer\CustomerResponse';
		$request = new CustomerAuthenticationRequest();
		$request->setCustomerId(16429);
		$request->setAuthToken('ceqDtCwO7yahRslk');
		$header = new Header();
		$header->setSource(SourceEnum::CUSTOMER);
		$header->setVersion(1);
		$header->setRoute('customers.customerAuthentication');
		$this->send(Message::pack($header, $request));
	}

	public function getAppVersion()
	{
		$this->_responseClass = 'service\message\customer\GetAppVersionResponse';
		$request = new GetAppVersionRequest();
		$request->setCustomerId(15372);
		$request->setAuthToken('ZJcsTJaw3k5WiNls');
		$request->setSystem(1);
		$request->setPlatform(1);
		$request->setChannel(100000);
		$request->setVersion(1.0);

		$header = new Header();
		$header->setSource(SourceEnum::CUSTOMER);
		$header->setVersion(1);
		$header->setRoute('customers.getAppVersion');
		$this->send(Message::pack($header, $request));
	}

	public function customerBalanceLog()
	{
		$this->_responseClass = 'service\message\customer\CustomerBalanceLogResponse';
		$request = new CustomerBalanceLogRequest();
		$request->setCustomerId($this->_customerId);
		$request->setAuthToken($this->_authToken);
		$header = new Header();
		$header->setSource(SourceEnum::CUSTOMER);
		$header->setRoute('customers.customerBalanceLog');
		$header->setCustomerId($this->_customerId);
		$this->send(Message::pack($header, $request));
	}


    public function customerManageEntry2()
    {
        $this->_responseClass = 'service\message\contractor\ManageResponse2';
        $request = new ContractorAuthenticationRequest();
        $request->setContractorId($this->_contractorId);
        $request->setAuthToken($this->_contractor_authToken);
        $request->setCity(441800);
        $header = new Header();
        $header->setSource(SourceEnum::CONTRACTOR);
        $header->setRoute('customers.customerManageEntry2');
        $this->send(Message::pack($header, $request));
    }

    public function Test()
    {
        $request = new TestReportRequest();
        $request->setIp('111');
        $header = new Header();
        $header->setSource(SourceEnum::IOS_SHOP);
        $header->setRoute('customers.test');
        $this->send(Message::pack($header, $request));
    }

    public function onConnect($client)
    {
        echo "client connected" . PHP_EOL;
//        $this->createOrders();
//        $this->home();
//        $this->config();
        //$this->orderDetail();
//        $this->orderStatusHistory();
//        $this->orderCancel();
//        $this->revokeCancel();
//        $this->decline();
//        $this->reorder();
//        $this->systemMessage();
//        $this->authentication();
        //$this->getAppVersion();
//        $this->customerManageEntry2();
//        $this->login();
        $this->Test();

    }

    public function onReceive($client, $data)
    {
        $message = new Message();
        //print_r($data);
        $message->unpackResponse($data);
        $responseClass = $this->_responseClass;
        if ($message->getHeader()->getCode() > 0) {
			echo '程序执行异常：'. PHP_EOL;
			echo sprintf('code : %s', $message->getHeader()->getCode()) . PHP_EOL;
			echo sprintf('msg：%s', $message->getHeader()->getMsg()) . PHP_EOL;
        } else {
            if (TStringFuncFactory::create()->strlen($message->getPackageBody()) > 0) {
                $response = new $responseClass();
                $response->parseFromString($message->getPackageBody());
                print_r($responseClass);
                print_r($message->getPackageBody());
                echo PHP_EOL;
                print_r($response->toArray());
            } else {
                print_r('返回值为空');
            }
        }
    }
}