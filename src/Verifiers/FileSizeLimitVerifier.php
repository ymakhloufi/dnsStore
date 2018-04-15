<?php

namespace DnsStore\Verifiers;

use DnsStore\Exceptions\FileSizeException;

class FileSizeLimitVerifier extends Verifier
{

    /** @var string */
    private $pathToFile;

    /** @var int */
    private $maxSizeInBytes;


    public function __construct(string $pathToFile, int $maxSizeInBytes)
    {
        $this->pathToFile     = $pathToFile;
        $this->maxSizeInBytes = $maxSizeInBytes;

        new FileExistenceVerifier($pathToFile);
        parent::__construct();
    }


    protected function denied(): bool
    {
        return filesize($this->pathToFile) > $this->maxSizeInBytes;
    }


    protected function getExceptionClass(): string
    {
        return FileSizeException::class;
    }


    protected function getExceptionMessage(): string
    {
        return "The specified file is too large. The file has " .
               filesize($this->pathToFile) / 1024 .
               "KB, but only a maximum of " .
               $this->maxSizeInBytes / 1024 .
               "KB is allowed";
    }
}