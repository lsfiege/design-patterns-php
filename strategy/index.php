<?php

// php artisan taxatron:load /path/to/some/ledger.txt --format=csv

class Load extends Command
{
    // ...

    public function handle()
    {
        $reader = new LedgerReader($this->option('format'));
        $transactions = $reader->parse($this->argument('input'));

        foreach ($transactions as $transaction) {
            $transaction->categorize();
            $transaction->save();
        }
    }
}

interface Parser
{
    public function parse(string $line): Transaction;
}

class LedgerReader
{
    private $parser;

    public function __construct($format)
    {
        $this->parser = $this->makeParser($format);
    }

    public function parse($path)
    {
        $reader = new SplFileObject(realpath($path));
        
        return $this->readTransactions($reader);
    }

    private function readTransactions($reader)
    {
        $transactions = [];
        foreach ($reader as $line) {
            if (empty($line)) {
                continue;
            }

            $transactions[] = $this->parser->parse($line);
        }

        return $transactions;
    }

    private function makeParser(string $format): Parser
    {
        $factory = new ParserFactory;

        return $factory->make($format);
    }
}

class ParserFactory
{
    const RAW = 'raw';
    const CSV = 'csv';

    public function make(string $format): Parser
    {
        switch ($format) {
            case self::RAW:
                return new RawParser;
            case self::CSV:
                return new CsvParser;
            default:
                throw new \RuntimeException('Unsupported format: ' . $format);
        }
    }
}

class RawParser implements Parser
{
    public function parse($line): Transaction
    {
        $record = str_getcsv($line, "\t");
        $type = $this->getType(array_slice($record, -3));

        return new Transaction([
            'date' => Carbon::parse($record[0]),
            'description' => $record[1],
            'type' => $type,
        ]);
    }

    private function getType($attributes)
    {
        [$debit, $credit] = $attributes;

        if ($credit == TransactionType::NOT_APPLICABLE) {
            return new Debit($this->toCents($debit));
        }

        return new Credit($this->toCents($credit));
    }
}

class CsvParser implements Parser
{
    public function parse($line): Transaction
    {
        $record = str_getcsv($line, "\t");
        [$date, $description] = $record;

        return new Transaction([
            'date' => Carbon::parse($date),
            'description' => $description,
            'type' => $this->getType(array_slice($record, -2)),
        ]);
    }

    private function getType($attributes)
    {
        [$debit, $credit] = $attributes;

        return $credit ? new Credit($credit) : new Debit($debit);
    }
}
