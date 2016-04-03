<?php
/**
 * @license see LICENSE
 */
namespace Serps\Test\HttpClient;

use Serps\Core\Http\HttpClientInterface;
use Serps\Core\Http\SearchEngineResponse;
use Serps\HttpClient\PhantomJsClient;
use Serps\HttpClient\SpidyJsClient;
use Serps\Test\HttpClient\HttpClientTestsCase;
use Zend\Diactoros\Request;

use Zend\Diactoros\Response;

/**
 * @covers Serps\HttpClient\SpidyJsClient
 */
class SpidyJsClientTest extends HttpClientTestsCase
{
    public function getHttpClient()
    {
        return new SpidyJsClient();
    }

    public function testCookies()
    {
        $this->markTestSkipped('Cookies not supported');
    }

    public function testSetCookies()
    {
        $this->markTestSkipped('Cookies not supported');
    }
}
