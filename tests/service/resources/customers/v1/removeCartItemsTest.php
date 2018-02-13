<?php
namespace tests\service\resources\customers\v1;

use framework\message\Message;
use service\message\common\Product;
use service\message\customer\RemoveCartItemsRequest;
use service\resources\customers\v1\removeCartItems;
use tests\service\CustomersTest;

class  removeCartItemsTest extends CustomersTest
{

    public function _initData()
    {
        $this->header->setRoute('customers.removeCartItems');
    }

    public function testRequest()
    {
        $this->assertInstanceOf('service\message\customer\RemoveCartItemsRequest', removeCartItems::request());
    }


    public function testRun()
    {
        /** @var RemoveCartItemsRequest $request */
        $request = new RemoveCartItemsRequest();
        $request->setCustomerId($this->customer_id);
        $request->setAuthToken($this->auth_token);
        $product = new Product();
        $product->setProductId(1);
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