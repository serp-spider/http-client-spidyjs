<?php
/**
 * @license see LICENSE
 */

namespace Serps\Test\HttpClient;

use Symfony\Component\Process\Process;

class WebServer
{

    protected $root;
    protected $host;
    protected $port;

    /**
     * @var Process
     */
    protected $serverProcess;

    public function __construct($root, $host = null, $port = null)
    {
        if (null === $root) {
            $root = __DIR__ . '/../../www';
        }
        if (null === $host) {
            $host = 'localhost';
        }
        if (null === $port) {
            $port = '9850';
        }
        $this->root = $root;
        $this->port = $port;
        $this->host = $host;
    }

    public function isRunning()
    {
        return $this->serverProcess && $this->serverProcess->isRunning();
    }

    public function getPid()
    {
        if ($this->isRunning()) {
            return $this->serverProcess->getPid();
        }
        return null;
    }

    /**
     * Starts the server at the given host and port and waits for the server to be fully started
     * @throws \Exception
     */
    public function start()
    {
        if ($this->isRunning()) {
            throw new \Exception('Server process is already running');
        }

        $scriptString = sprintf(
            'exec php -S %s:%s -t %s',
            $this->host,
            $this->port,
            $this->root
        );


        $this->serverProcess = new Process($scriptString);
        $this->serverProcess->start();
        $this->startWait();
    }

    private function startWait()
    {
        $tryout = 15;
        $timePerTry = 8000;
        $try = 0;

        while ($try < $tryout && $this->serverProcess->isRunning()) {
            $serverResponse = $this->call('/ping.html');
            if ($serverResponse == 'pong') {
                return true;
            }
            usleep($timePerTry);
            $try++;
        }
        $message = 'Unable to start http server';
        if (!$this->serverProcess->isRunning()) {
            $message .= ': ' . $this->serverProcess->getErrorOutput();
        } else {
            $this->serverProcess->stop();
        }
        throw new \Exception($message);
    }

    public function call($uri, $method = 'GET', $data = [])
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->buildUrl($uri));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        switch ($method) {
            case 'GET':
                curl_setopt($ch, CURLOPT_HTTPGET, true);
                curl_setopt($ch, CURLOPT_NOBODY, false);
                break;
            case 'HEAD':
                curl_setopt($ch, CURLOPT_HTTPGET, false);
                curl_setopt($ch, CURLOPT_NOBODY, true);
                break;
            case 'POST':
            case 'CONNECT':
            case 'DELETE':
            case 'PATCH':
            case 'PUT':
            case 'TRACE':
                curl_setopt($ch, CURLOPT_HTTPGET, false);
                curl_setopt($ch, CURLOPT_NOBODY, false);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
                break;
        }
        $response = curl_exec($ch);
        return $response;
    }

    private function buildUrl($uri)
    {
        $uri = ltrim($uri, '/');
        $url = sprintf(
            'http://%s:%s/%s',
            $this->host,
            $this->port,
            $uri
        );
        return $url;
    }

    public function stop()
    {
        unlink($this->iniFile);
        $this->serverProcess->stop();
    }
}
