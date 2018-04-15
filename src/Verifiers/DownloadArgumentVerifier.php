<?php

namespace DnsStore\Verifiers;

use InvalidArgumentException;

class DownloadArgumentVerifier extends Verifier
{
    /** @var array */
    protected $argv;


    public function __construct($argv)
    {
        $this->argv = $argv;
        parent::__construct();
    }


    private function isValidSubDomain(string $str)
    {
        return preg_match("/^[a-z][a-z\-\.]+[a-z]$/i", $str) and (strlen($str) < 60);
    }


    private function isValidDomain(string $str)
    {
        return preg_match('/^(?!\-)(?:[a-zA-Z\d\-]{0,62}[a-zA-Z\d]\.){1,126}(?!\d+)[a-zA-Z\d]{1,63}$/', $str);
    }


    protected function denied(): bool
    {
        return count($this->argv) !== 3 or
               !$this->isValidSubDomain($this->argv[2]) or
               !$this->isValidDomain($this->argv[1]);

    }


    protected function getExceptionClass(): string
    {
        return InvalidArgumentException::class;
    }


    protected function getExceptionMessage(): string
    {
        return "This script must be called with 2 arguments:\n" .
               "download.php fileIdentifier outputFile.ext";
    }
}