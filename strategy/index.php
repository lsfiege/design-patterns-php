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
    public function parse($path)
    {
        $reader = new SplFileObject(realpath($path));
        $reader->setFlags(SplFileObject::READ_CSV);
        $reader->setCsvControl("\t");

        return $this->readTransactions($reader);
    }

    private function readTransactions($reader)
    {
        $transactions = [];
        foreach ($reader as $record) {
            if ($record[0] == null) {
                continue;
            }
            $transactions[] = $this->parseRecord($record);
        }

        return $transactions;
    }

    public function parseRecord($record)
    {
        $type = $this->getType(array_slice($record, -3));

        return new Transaction([
            'date' => Carbon::parse($record[0]),
            'description' => $record[1],
            'type' => $type,
        ]);
    }

    private function getType($attributes)
    {
        [$debit, $creedit] = $attributes;

        if ($credt == TransactionType::NOT_APPLICABLE) {
            return new Debit($this->toCents($debit));
        }

        return new Credit($this->toCents($credit));
    }
}

/*
 * What happens if the file format and structure are differents?
 */
