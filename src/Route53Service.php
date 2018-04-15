<?php

namespace DnsStore;

use Aws\Route53\Route53Client;
use DnsStore\Verifiers\HostedZoneExistsVerifier;
use DnsStore\Verifiers\TxtRecordsVerifier;

class Route53Service
{
    private $domain;
    private $client;
    private $hostedZoneId;


    public function __construct(string $domain)
    {
        $this->domain       = $domain;
        $this->client       = new Route53Client([
            'version'     => 'latest',
            'region'      => getenv('AWS_REGION'),
            'credentials' => [
                'key'    => getenv('AWS_KEY'),
                'secret' => getenv('AWS_SECRET'),
            ],
        ]);
        $this->hostedZoneId = $this->getHostedZoneIdByDomain($domain);
    }


    private function getHostedZoneIdByDomain(string $domain)
    {
        $foundZone = $this->client->listHostedZonesByName([
            'DNSName'  => $domain,
            'MaxItems' => '1',
        ])->get('HostedZones')[0];

        new HostedZoneExistsVerifier($domain, $foundZone);

        return $foundZone['Id'];
    }


    public function createRecordSets(string $subDomain, array $txtRecords, array $metadata)
    {
        new TxtRecordsVerifier($txtRecords);

        print "\nUploading:\n";

        // store metadata in subdomain
        $encodedMetadata = '"' . base64_encode(json_encode($metadata)) . '"';
        $this->storeRecordInRoute53($subDomain . "." . $this->domain . '.', $encodedMetadata);

        // store payload in suffixed subdomains
        $i = 1;
        foreach ($txtRecords as $record) {
            print ".";
            if ($i % 50 === 0) {
                print " (" . $i . "/" . count($txtRecords) . ")\n";
            }
            $suffix = "-" . $i++;
            $this->storeRecordInRoute53($subDomain . $suffix . "." . $this->domain . '.', $record);
        }
        print " Done!\n";
    }


    public function deleteRecords(string $subDomain)
    {
        $nextRecordName = "";
        while ($nextRecordName !== null) {
            $list = $this->client->listResourceRecordSets([
                'HostedZoneId' => $this->hostedZoneId,
                'DnsName'      => $subDomain . "." . $this->domain . ".",
            ]);

            foreach ($list->get('ResourceRecordSets') as $recordSet) {
                if (strpos($recordSet['Name'], $subDomain) !== 0 or $recordSet['Type'] !== 'TXT') {
                    continue;
                }
                $this->deleteRecordInRoute53($recordSet);
            }
            $nextRecordName = $list->get('NextRecordName');
        }
    }


    private function deleteRecordInRoute53(array $recordSet)
    {
        $this->client->changeResourceRecordSets([
            'HostedZoneId' => $this->hostedZoneId,
            'ChangeBatch'  => [
                'Changes' => [
                    [
                        'Action'            => 'DELETE',
                        'ResourceRecordSet' => $recordSet,
                    ],
                ],
            ],
        ]);
    }


    private function storeRecordInRoute53(string $domain, string $record)
    {
        $this->client->changeResourceRecordSets([
            'HostedZoneId' => $this->hostedZoneId,
            'ChangeBatch'  => [
                'Comment' => 'created by DnsStore',
                'Changes' => [
                    [
                        'Action'            => 'CREATE',
                        'ResourceRecordSet' => [
                            'Name'            => $domain,
                            'Type'            => 'TXT',
                            'TTL'             => 3600,
                            'ResourceRecords' => [
                                [
                                    'Value' => $record,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);
    }


}