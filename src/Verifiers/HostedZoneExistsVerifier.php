<?php

namespace DnsStore\Verifiers;

use DnsStore\Exceptions\HostedZoneNotFoundException;

class HostedZoneExistsVerifier extends Verifier
{
    private $requestedDomain;
    private $foundHostedZone;


    public function __construct(string $requestedDomain, array $foundHostedZone)
    {
        $this->requestedDomain = $requestedDomain;
        $this->foundHostedZone = $foundHostedZone;

        parent::__construct();
    }


    protected function denied(): bool
    {
        // domain will be suffixed with a dot.
        return ($this->foundHostedZone['Name'] ?? null) !== $this->requestedDomain . ".";

    }


    protected function getExceptionClass(): string
    {
        return HostedZoneNotFoundException::class;
    }


    protected function getExceptionMessage(): string
    {
        return "No HostedZone for the domain " . $this->requestedDomain . " was found!";
    }
}