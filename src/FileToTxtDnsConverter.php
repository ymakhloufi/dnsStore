<?php

namespace DnsStore;

use DnsStore\Verifiers\FileExistenceVerifier;
use DnsStore\Verifiers\FileSizeLimitVerifier;

class FileToTxtDnsConverter
{
    /** @var string */
    protected $pathToFile;

    /** @var array */
    protected $txtRecords;


    public function __construct(string $pathToFile)
    {
        new FileExistenceVerifier($pathToFile);          // Make sure file exists
        new FileSizeLimitVerifier($pathToFile, 2097152); // 2MB limit - let's not overdo it
        $this->pathToFile = $pathToFile;
        $this->txtRecords = $this->splitFile();
    }


    private function splitFile()
    {
        $binary  = file_get_contents($this->pathToFile);
        $encoded = base64_encode($binary);

        // Each line in a TXT entry can be 256 bytes long
        $splitIn255ByteParts = str_split($encoded, 253); // 253 + two quotation marks = 255
        $splitInBlocks       = [];

        // AWS Route53 only allows 3839 bytes per TXT entry (even though the RFC says 64K should be possible)
        for ($i = 0; $i < count($splitIn255ByteParts); $i += 15) {
            $splitInBlocks[] = "\"" . implode("\" \"", array_slice($splitIn255ByteParts, $i, 15)) . "\"";
        }

        return $splitInBlocks;
    }


    public function getTxtRecords()
    {
        return $this->txtRecords;
    }


    public function getMetaData()
    {
        return [
            'originalFileName' => basename($this->pathToFile),
            'blockCount'       => count($this->txtRecords),
        ];
    }


}