<?php

namespace DnsStore;

use DnsStore\Verifiers\IdentifierExistenceVerifier;

class DnsDownloadService
{
    private $identifier;
    private $domain;
    private $originalFile;
    private $rawContent = "";


    public function __construct(string $identifier, string $domain)
    {
        $this->identifier = $identifier;
        $this->domain     = $domain;
        $this->fetchData();
    }


    private function fetchData()
    {
        new IdentifierExistenceVerifier($this->identifier, $this->domain);

        $metadata           = base64_decode(dns_get_record($this->getIdentifierByIterator(null), DNS_TXT)[0]['txt']);
        $metadata           = json_decode($metadata, true);
        $this->originalFile = $metadata['originalFileName'];


        print "\nFetching:\n";
        for ($i = 1; $i <= $metadata['blockCount']; $i++) {
            print ".";
            if ($i % 50 === 0) {
                print " ($i/{$metadata['blockCount']})\n";
            }

            // allow for up to 10 retries if a record cannot be found!
            $record = dns_get_record($this->getIdentifierByIterator($i), DNS_TXT);
            for ($retry = 0; $retry < getenv("DNS_DOWNLOAD_ROBUSTNESS") and !isset($record[0]); $retry++) {
                sleep(1);
                $record = dns_get_record($this->getIdentifierByIterator($i), DNS_TXT);
            }
            $this->rawContent .= $record[0]['txt'];
        }
        print " Done!\n";
    }


    private function getIdentifierByIterator($i = null)
    {
        if ($i === null) {
            return $this->identifier . "." . $this->domain;
        }

        return $this->identifier . "-" . $i . "." . $this->domain;
    }


    public function getDecodedFileContents()
    {
        return base64_decode($this->rawContent);
    }


    public function getOriginalFileNAme()
    {
        return $this->originalFile;
    }


}