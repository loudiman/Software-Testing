<?php
namespace Tests;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use App\TransactionService;
/**
 * TransactionServiceTest tests the TransactionService functionality
 * 
 * @covers \App\TransactionService
 */
class TransactionServiceTest extends TestCase
{

    /**
     * Tests the processTransfer method with various input scenarios
     *
     * @dataProvider transferDataProvider
     * @param array $input The transfer input data containing 'from', 'to' and 'amount'
     * @param string $expected Expected result ('success' or 'error')
     * @param string $message Test assertion message
     */

    #[DataProvider('transferDataProvider')]
    public function testProcessTransfer(array $input, string $expected, string $message)
    {
        $transactionService = new TransactionService();
        $this->assertEquals($expected, $transactionService->processTransfer($input), $message);
    }

    /**
     * Data provider for transfer test cases
     * 
     * @return array Test cases with the following structure:
     *               - input: array with 'from', 'to' and 'amount' keys
     *               - expected: string ('success' or 'error')
     *               - message: string (test description)
     */
    public static function transferDataProvider(): array
    {
        return [
            'successful_transfer' => [
                ['from' => '123456', 'to' => '654321', 'amount' => 1000],
                'success',
                'Valid transfer should succeed'
            ],
            'zero_amount' => [
                ['from' => '123456', 'to' => '654321', 'amount' => 0],
                'error',
                'Zero amount should fail'
            ],
            'empty_from_account' => [
                ['from' => '', 'to' => '654321', 'amount' => 1000],
                'error',
                'Empty from account should fail'
            ],
            'empty_to_account' => [
                ['from' => '123456', 'to' => '', 'amount' => 1000],
                'error',
                'Empty to account should fail'
            ],
            'negative_amount' => [
                ['from' => '123456', 'to' => '654321', 'amount' => -100],
                'error',
                'Negative amount should fail'
            ],
            'missing_from_key' => [
                ['to' => '654321', 'amount' => 1000],
                'error',
                'Missing from account should fail'
            ],
            'missing_to_key' => [
                ['from' => '123456', 'amount' => 1000],
                'error',
                'Missing to account should fail'
            ],
            'missing_amount_key' => [
                ['from' => '123456', 'to' => '654321'],
                'error',
                'Missing amount should fail'
            ],
            'same_account_transfer' => [
                ['from' => '123456', 'to' => '123456', 'amount' => 1000],
                'error',
                'Transfer to same account should fail'
            ]
        ];
    }
}