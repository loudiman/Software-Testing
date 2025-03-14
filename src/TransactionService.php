<?php

namespace App;

class TransactionService
{
    public function processTransfer(array $input): string
    {
        // Validate required fields
        if (!isset($input['from']) || !isset($input['to']) || !isset($input['amount'])) {
            return 'error';
        }

        // Extract values
        $fromAccount = $input['from'];
        $toAccount = $input['to'];
        $amount = $input['amount'];

        // Validate empty accounts
        if (empty($fromAccount) || empty($toAccount)) {
            return 'error';
        }

        // Validate amount
        if ($amount <= 0) {
            return 'error';
        }

        // Validate same account transfer
        if ($fromAccount === $toAccount) {
            return 'error';
        }

        // At this point, all validations have passed
        // In a real application, we would perform the actual transfer here
        return 'success';
    }
}