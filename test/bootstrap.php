<?php

require __DIR__ . '/../vendor/autoload.php';


$server = new \Serps\Test\HttpClient\WebServer(__DIR__ . '/webres');
$server->start();