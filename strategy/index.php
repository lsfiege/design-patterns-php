<?php

// php artisan taxatron:load /path/to/some/ledger.txt

class Load extends Command
{
    // ...

    public function handle(LedgerReader $reader)
    {
        $transactions = $reader->parse($this->argument('input'));

        foreach ($transactions as $transaction) {
            $transaction->categorize();
            $transaction->save();
        }
    }
}

class LedgerReader
{
    public function parse($path, $format = 'raw')
    {
        $reader = new SplFileObject(realpath($path));
        $reader->setFlags(SplFileObject::READ_CSV);
        
        if ($format == 'raw') {
            $reader->setCsvControl("\t");
        } elseif ($format == 'csv') {
            $reader->setCsvControl(',');
        } else {
            throw new \RuntimeException('Unknown format: ' . $format);
        }

        return $this->readTransactions($reader, $format);
    }

    private function readTransactions($reader, $format)
    {
        // Here we cant do this since the upper parse method is doing the same branching
        // So let's move to new next git branch
        if ($format == 'raw') {
            $parser = (new ParseRawStrategy);
        } else {
            $parse = (new ParseCsvStrategy);
        }

        $transactions = [];
        foreach ($reader as $record) {
            if ($record[0] == null) {
                continue;
            }

            $transactions[] = $parser->parse($record);
        }

        return $transactions;
    }

    public function parseRawRecord($record)
    {
        $type = $this->getRawType(array_slice($record, -3));

        return new Transaction([
            'date' => Carbon::parse($record[0]),
            'description' => $record[1],
            'type' => $type,
        ]);
    }

    private function getRawType($attributes)
    {
        [$debit, $credit] = $attributes;

        if ($credit == TransactionType::NOT_APPLICABLE) {
            return new Debit($this->toCents($debit));
        }

        return new Credit($this->toCents($credit));
    }

    public function parseCsvRecord($record)
    {
        $type = $this->getCsvType(array_slice($record, -2));

        return new Transaction([
            'date' => Carbon::parse($record[0]),
            'description' => $record[1],
            'type' => $type,
        ]);
    }

    private function getCsvType($attributes)
    {
        [$debit, $credit] = $attributes;

        if (empty($credit)) {
            return new Debit($this->toCents($debit));
        }

        return new Credit($this->toCents($credit));
    }

    private function toCents($dollarsAndCents)
    {
        return str_replace(['$', '.', ','], '', $dollarsAndCents);
    }
}

/*
 * Things got over complicated huh?, let's fix this
 */
