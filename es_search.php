<?php
/**
 * Created by Jason.
 * Author: Jason Y. Wang
 * Date: 2016/2/1
 * Time: 16:35
 */
ob_start();

//$url = 'http://172.16.10.205:9200/products/440300/1';
$url = 'http://172.16.10.205:9200/products/440300/2';

$curl = curl_init($url);
//curl_setopt($curl,CURLOPT_CUSTOMREQUEST,'DELETE');
$result = curl_exec($curl);
curl_close($curl);
$result = ob_get_contents();
ob_end_clean();

var_dump(json_decode($result,true));

