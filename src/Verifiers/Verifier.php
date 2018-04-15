<?php

namespace DnsStore\Verifiers;

abstract class Verifier
{
    public function __construct()
    {
        $this->verify();
    }


    public function verify()
    {
        if ($this->denied()) {
            $exceptionClass = $this->getExceptionClass();
            throw new $exceptionClass($this->getExceptionMessage());
        }
    }


    protected abstract function denied(): bool;


    protected abstract function getExceptionClass(): string;


    protected abstract function getExceptionMessage(): string;
}