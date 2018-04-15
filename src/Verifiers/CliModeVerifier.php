<?php

namespace DnsStore\Verifiers;

use DnsStore\Exceptions\WrongInterfaceException;

class CliModeVerifier extends Verifier
{


    protected function denied(): bool
    {
        // allow only executions via CLI
        return php_sapi_name() !== "cli";
    }


    protected function getExceptionClass(): string
    {
        return WrongInterfaceException::class;
    }


    protected function getExceptionMessage(): string
    {
        return "This program can only be run in CLI mode.";
    }
}