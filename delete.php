<?php

use DnsStore\Route53Service;
use DnsStore\Verifiers\CliModeVerifier;
use DnsStore\Verifiers\DeleteArgumentVerifier;

require_once "vendor/autoload.php";
$dotenv = new Dotenv\Dotenv(__DIR__);
$dotenv->load();

new CliModeVerifier();                // Make sure we're running this in the CLI
new DeleteArgumentVerifier($argv);    // Make sure CLI arguments match delete.php domain.tld subDomain


$domain    = $argv[1];
$subDomain = $argv[2];

$dnsService = new Route53Service($domain);
$dnsService->deleteRecords($subDomain);
