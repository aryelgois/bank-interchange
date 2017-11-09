<?php

use aryelgois\BankInterchange as BankI;

require_once __DIR__ . '/../autoload.php';

$config = [
    'assignor' => 1, // use assignor #1 from Database
    'title' => [
        'cnab' => '400',
        'payer' => 1, // client id from session
        'wallet' => 2,
        'doc_type' => 1, // not used
        'kind' => 19,
        'specie' => 1,
        'value' => 20, // you have to sum every item represented by the title
        //'iof' => 0,
        //'rebate' => 0,
        'fine_type' => 3,
        'discount_type' => 3,
        'description' => 'Teste de Boleto',
        'due' => date('Y-m-d', strtotime('+ 5 days')) // 'Contra apresentação'
    ],
    'billet' => [
        'payment_place' => 'Pagável em qualquer Banco até o vencimento',
        'demonstrative' => "Pagamento de boleto - Teste de boleto\nTaxa bancária - {{ tax }}\nBankInterchange - https://www.github.com/aryelgois/bank-interchange",
        'instructions'  => "- Sr. Caixa, cobrar multa de 2% após o vencimento\n- Receber até 10 dias após o vencimento\n- Em caso de dúvidas entre em contato conosco: suporte@exemplo.com.br\n  Emitido pelo sistema BankInterchange - https://www.github.com/aryelgois/bank-interchange"
    ]
];

// new controler
$controller = new BankI\BankBillet\Controllers\Controller($db_address, $db_banki, $config);

//output result
if ($controller->execute()) {
    $path = __DIR__ . '/../data/bank_billets';
    $filename = $controller->saveFile($path);
    if ($filename !== false) {
        $controller->output();
    } else {
        echo '<p>Error saving file. Remember to give write permission to apache at data/*</p>';
    }
} else {
    echo '<p>Error: could not generate the bank billet</p>';
}
echo '<p>END</p>';
