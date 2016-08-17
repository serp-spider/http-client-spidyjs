<?php
/**
 * @license see LICENSE
 */

namespace Serps\HttpClient;

use Psr\Http\Message\RequestInterface;
use Serps\Core\Cookie\CookieJarInterface;
use Serps\Core\Http\HttpClientInterface;
use Serps\Core\Http\ProxyInterface;
use Serps\Core\Http\SearchEngineResponse;
use Serps\Core\UrlArchive;
use Serps\Exception;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class SpidyJsClient implements HttpClientInterface
{

    protected $bin;

    public function __construct($spidyJsBinary = 'spidyjs')
    {
        $this->bin = $spidyJsBinary;
    }

    public function sendRequest(
        RequestInterface $request,
        ProxyInterface $proxy = null,
        CookieJarInterface $cookieJar = null
    ) {

        $commandOptions = [];



        $initialUrl = (string)$request->getUri();

        $commandArg = [
            'method' => $request->getMethod(),
            'url'    => $initialUrl,
            'headers'=> []
        ];

        if ($proxy) {
            $proxyString = $proxy->getHost() . ':' . $proxy->getPort();
            // TODO: proxy auth

            if ($proxyType = $proxy->getType()) {
                $proxyString = $proxyType . '://' . $proxyString;
            } else {
                $proxyString = 'http://' . $proxyString;
            }

            $commandArg['proxy'] = $proxyString;
        }

        // TODO COOKIE JAR

        foreach ($request->getHeaders() as $headerName => $headerValues) {
            $commandArg['headers'][$headerName] = implode(',', $headerValues);
        }

        $data = (string)$request->getBody();
        if ($data) {
            $commandArg['data'] = $data;
        }

        $scriptFile = __DIR__ . '/spidyAdapter.js';
        $commandOptions = implode(' ', $commandOptions);
        $commandArg = json_encode($commandArg);

        $process = new Process("$this->bin $commandOptions $scriptFile '$commandArg'");
        $process->run();
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        $dataResponse = json_decode($process->getOutput(), true);
        if (!$dataResponse) {
            throw new Exception('Unable to parse SpidyJS response: ' . json_last_error_msg());
        }

        $response = new SearchEngineResponse(
            $dataResponse['headers'],
            $dataResponse['status'],
            $dataResponse['content'],
            true,
            UrlArchive::fromString($initialUrl),
            UrlArchive::fromString($dataResponse['url']),
            $proxy
        );

        return $response;
    }
}
