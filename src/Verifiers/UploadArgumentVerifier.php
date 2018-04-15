<?php

namespace DnsStore\Verifiers;

use InvalidArgumentException;

class UploadArgumentVerifier extends Verifier
{
    /** @var array */
    protected $argv;


    public function __construct($argv)
    {
        $this->argv = $argv;
        parent::__construct();
    }


    private function isValidDomain(string $str)
    {
        return preg_match('/^(?!\-)(?:[a-zA-Z\d\-]{0,62}[a-zA-Z\d]\.){1,126}(?!\d+)[a-zA-Z\d]{1,63}$/', $str);
    }


    private function isValidFilePath(string $str)
    {
        return !preg_match('#^(\w+/){1,2}\w+\.\w+$#', $str);
    }


    private function isValidSubDomain(string $str)
    {
        return preg_match("/^[a-z][a-z\-\.]+[a-z]$/i", $str) and (strlen($str) < 60);
    }


    protected function denied(): bool
    {
        return count($this->argv) !== 4 or
               !$this->isValidFilePath($this->argv[1]) or
               !$this->isValidDomain($this->argv[2]) or
               !$this->isValidSubDomain($this->argv[3]);

    }


    protected function getExceptionClass(): string
    {
        return InvalidArgumentException::class;
    }


    protected function getExceptionMessage(): string
    {
        return "This script must be called with 3 arguments:\n" .
               "upload.php path/to/file domain.tld subDomain";
    }
}