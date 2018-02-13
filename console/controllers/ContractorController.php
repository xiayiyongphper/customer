<?php

namespace console\controllers;

use service\components\Tools;
use service\message\contractor\addVisitRecordRequest;
use service\message\contractor\visitedRecordsRequest;
use service\resources\contractor\v1\addVisitRecord;
use service\resources\contractor\v1\visitedRecords;
use yii\console\Controller;

/**
 * Created by Jason.
 * Author: Jason Y. Wang
 * Date: 2016/2/1
 * Time: 14:40
 */
class ContractorController extends Controller
{
    protected $contractorId = 3;
    protected $authToken = '123456';

    public function actionIndex()
    {
        $request = new visitedRecordsRequest();
        $requestData = [
            'contractor_id' => $this->contractorId,
            'auth_token' => $this->authToken,
        ];
        $request->setFrom(Tools::pb_array_filter($requestData));
        $response = (new visitedRecords())->run($request->serializeToString());
        print_r($response->toArray());
    }

    public function test()
    {

    }
}
