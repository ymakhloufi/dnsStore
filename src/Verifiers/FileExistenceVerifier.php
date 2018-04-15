<?php

namespace DnsStore\Verifiers;

use DnsStore\Exceptions\FileNotFoundException;

class FileExistenceVerifier extends Verifier
{
    protected $file;


    public function __construct(string $file)
    {
        $this->file = $file;
        parent::__construct();
    }


    protected function denied(): bool
    {
        return !file_exists($this->file);
    }


    protected function getExceptionClass(): string
    {
        return FileNotFoundException::class;
    }


    protected function getExceptionMessage(): string
    {
        return "The specified file was not found: " . $this->file;
    }
}