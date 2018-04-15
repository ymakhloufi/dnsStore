<?php

use DnsStore\FileToTxtDnsConverter;
use DnsStore\Route53Service;
use DnsStore\Verifiers\CliModeVerifier;
use DnsStore\Verifiers\UploadArgumentVerifier;

require_once "vendor/autoload.php";
$dotenv = new Dotenv\Dotenv(__DIR__);
$dotenv->load();

new CliModeVerifier();                // Make sure we're running this in the CLI
new UploadArgumentVerifier($argv);    // Make sure CLI arguments match main.php path/file domain.tld subDomain

$pathToFile = $argv[1];
$domain     = $argv[2];
$subDomain  = $argv[3];

$converter  = new FileToTxtDnsConverter($pathToFile);
$dnsService = new Route53Service($domain);
$dnsService->createRecordSets($subDomain, $converter->getTxtRecords(), $converter->getMetaData());