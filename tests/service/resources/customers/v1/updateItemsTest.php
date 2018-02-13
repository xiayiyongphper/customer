<?php
namespace tests\service\resources\customers\v1;

use framework\message\Message;
use service\message\common\Product;
use service\message\customer\GetAppVersionRequest;
use service\message\customer\PostDeviceTokenRequest;
use service\message\customer\RemoveCartItemsRequest;
use service\message\customer\UpdateCartItemsRequest;
use service\resources\customers\v1\getAppVersion;
use service\resources\customers\v1\postDeviceToken;
use service\resources\customers\v1\removeCartItems;
use service\resources\customers\v1\updateItems;
use tests\service\CustomersTest;

class  updateItemsTest extends CustomersTest
{

    public function _initData()
    {
        $this->header->setRoute('customers.updateItems');
    }

    public function testRequest()
    {
        $this->assertInstanceOf('service\message\customer\UpdateCartItemsRequest', updateItems::request());
    }

    public function testResponse()
    {
        $this->assertEquals(true,updateItems::response());
    }

    public function testRun()
    {
        /** @var UpdateCartItemsRequest $request */
        $request = new UpdateCartItemsRequest();
        $request->setCustomerId($this->customer_id);
        $request->setAuthToken($this->auth_token);
        $product = new Product();
        $product->setProductId(6);
        $request->appendProducts($product);

        $rawBody = Message::pack($this->header, $request);
        $this->request->setRawBody($rawBody);
        $response = $this->application->handleRequest($this->request);
        /** @var \service\message\common\ResponseHeader $header */
        list($header, $data) = $response;
        $this->assertNotEmpty($response);
        $this->assertEquals(0, $header->getCode());
    }

}