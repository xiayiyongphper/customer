<?php
/**
 * Created by Jason.
 * Author: Jason Y. Wang
 * Date: 2016/1/25
 * Time: 11:31
 */

namespace service\resources\driver\v1;


use framework\components\ProxyAbstract;
use service\message\common\Header;
use service\message\driver\SearchOrderRequest;
use service\message\sales\DriverOrderCollectionRequest;
use service\message\sales\OrderCollectionResponse;
use service\models\common\CustomerException;
use service\models\common\DriverAbstract;

class searchOrder extends DriverAbstract
{
    /**
     * Function: run
     * Author: Jason Y. Wang
     *
     * @param $data
     * @return mixed
     * @throws CustomerException
     */
    public function run($data)
    {
        /** @var SearchOrderRequest $request */
        $request = self::parseRequest($data);
        $driver = $this->initDriver($request);
        $header = new Header();
        $header->setRoute('sales.driverOrderCollection');
        $header->setSource($this->getSource());
        $header->setAppVersion($this->getAppVersion());
        $orderCollectionRequest = new DriverOrderCollectionRequest();
        $orderCollectionRequest->setFuzzyIncrementId($request->getFuzzyIncrementId());
        $orderCollectionRequest->appendWholesalerId($driver->wholesaler_id);
        $message = ProxyAbstract::sendRequest($header, $orderCollectionRequest);
        $response = self::response();
        /** @var OrderCollectionResponse $response */
        if ($message->getPackageBody()) {
            $response->parseFromString($message->getPackageBody());
        } else {
            $response = false;
        }
        return $response;
    }

    public static function request()
    {
        return new SearchOrderRequest();
    }

    public static function response()
    {
        return new OrderCollectionResponse();
    }

}