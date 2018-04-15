<?php

use DnsStore\DnsDownloadService;
use DnsStore\Verifiers\CliModeVerifier;
use DnsStore\Verifiers\DownloadArgumentVerifier;

require_once "vendor/autoload.php";

new CliModeVerifier();                  // Make sure we're running this in the CLI
new DownloadArgumentVerifier($argv);    // Make sure CLI arguments match download.php fileIdentifier outputFile.ext

$domain     = $argv[1];
$identifier = $argv[2];

$dnsService = new DnsDownloadService($identifier, $domain);
file_put_contents("output/" . $dnsService->getOriginalFileNAme(), $dnsService->getDecodedFileContents());
