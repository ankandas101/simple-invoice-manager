<?php
/**
 * Configuration for balance manager.
 * This file returns an 'array factory' compatible definition for {@link \Illuminatech\Balance\BalanceDb} object.
 *
 * @see https://github.com/illuminatech/balance
 * @see \Illuminatech\Balance\BalanceDb
 * @see \Illuminatech\ArrayFactory\FactoryContract
 */

return [
    'accountTable'              => 'customers',
    'transactionTable'          => 'transactions',
    'accountBalanceAttribute'   => 'balance',
    'accountLinkAttribute'      => 'customer_id',
    'extraAccountLinkAttribute' => 'customer_id',
    'dataAttribute'             => 'data',
    //'newBalanceAttribute' => 'new_balance',
    'dbTransactionEnabled'       => true,
    'dbTransactionNestedEnabled' => false,
];
