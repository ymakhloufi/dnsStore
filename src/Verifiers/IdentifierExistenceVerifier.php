<?php

namespace DnsStore\Verifiers;

use DnsStore\Exceptions\IdentifierNotFoundException;

class IdentifierExistenceVerifier extends Verifier
{
    protected $identifier;
    private   $domain;


    public function __construct(string $identifier, string $domain)
    {
        $this->identifier = $identifier;
        $this->domain     = $domain;
        parent::__construct();
    }


    protected function denied(): bool
    {
        // first one has metadata, second one is first block with payload
        return empty(dns_get_record($this->identifier . "." . $this->domain, DNS_TXT)) or
               empty(dns_get_record($this->identifier . "-1." . $this->domain, DNS_TXT));
    }


    protected function getExceptionClass(): string
    {
        return IdentifierNotFoundException::class;
    }


    protected function getExceptionMessage(): string
    {
        return "The specified identifier was not found: " . $this->identifier;
    }
}