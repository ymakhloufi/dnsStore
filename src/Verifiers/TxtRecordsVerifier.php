<?php

namespace DnsStore\Verifiers;

use DnsStore\Exceptions\RecordTooLongException;

class TxtRecordsVerifier extends Verifier
{
    protected $txtRecords;


    public function __construct(array $txtRecords)
    {
        $this->txtRecords = $txtRecords;
        parent::__construct();
    }


    protected function denied(): bool
    {
        // Route53 supports only up to 10K records per hostedZone.
        // We stop after 9990, because chances are there are
        // some other intended host entries in that zone.
        if (count($this->txtRecords) > 9990) {
            return true;
        }

        foreach ($this->txtRecords as $record) {
            $splitRecord = explode(" ", $record);

            // Each TXT record can have up to 15 lines (on AWS - normally 256 lines should be okay)
            if (count($splitRecord) > 15) {
                return true;
            }

            // Each line in a TXT record can have up to 256 chars
            foreach ($splitRecord as $line) {
                if (strlen($line) > 256) {
                    return true;
                }
            }
        }

        return false;
    }


    protected function getExceptionClass(): string
    {
        return RecordTooLongException::class;
    }


    protected function getExceptionMessage(): string
    {
        return "The intended Record(s) is/are too long.\n" .
               "- Route53 can only store 10K records.\n" .
               "- Each record can have maximum 255 line.\n" .
               "- Each line can have a maximum 255 characters.";
    }
}