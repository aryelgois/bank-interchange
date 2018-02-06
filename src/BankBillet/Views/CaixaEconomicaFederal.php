<?php
/**
 * This Software is part of aryelgois/bank-interchange and is provided "as is".
 *
 * @see LICENSE
 */

namespace aryelgois\BankInterchange\BankBillet\Views;

use aryelgois\BankInterchange;

/**
 * Generates bank billets for Caixa Econômica Federal
 *
 * @author Aryel Mota Góis
 * @license MIT
 * @link https://www.github.com/aryelgois/bank-interchange
 */
class CaixaEconomicaFederal extends BankInterchange\BankBillet\View
{
    /**
     * Procedurally draws the bank billet using FPDF methods
     */
    protected function drawBillet()
    {
        $fields = $this->fields;

        $this->AddPage();

        $this->drawPageHeader();

        $this->billetSetFont('cell_data');
        $this->drawDash($fields['client_receipt']['text']);

        $this->drawBillhead();

        $this->drawBankHeader();

        $this->drawTable1();

        $this->billetSetFont('cell_title');
        $this->drawDash($fields['cut_here']['text'], true);

        $this->drawBankHeader();

        $this->drawTable2();

        $this->drawBarCode();

        $this->billetSetFont('cell_title');
        $this->drawDash($fields['cut_here']['text'], true);
    }

    /**
     * Table 1, stays with the Client
     */
    protected function drawTable1()
    {
        $fields = $this->fields;

        /*
         * Structure:
         *
         * assignor | agency_code | currency       | amount | our_number
         * doc_number | cpf_cnpj | date_due        | doc_value
         * discount | deduction | fine | additions | charged
         * client
         */
        $table = [
            [
                ['width' =>  80.8, 'field' => 'assignor'],
                ['width' =>  35.4, 'field' => 'agency_code'],
                ['width' =>  11,   'field' => 'currency'],
                ['width' =>  16,   'field' => 'amount'],
                ['width' =>  33.8, 'field' => 'our_number', 'align' => 'R'],
            ],
            [
                ['width' =>  52.8, 'field' => 'doc_number'],
                ['width' =>  37,   'field' => 'cpf_cnpj'],
                ['width' =>  37.4, 'field' => 'date_due'],
                ['width' =>  49.8, 'field' => 'doc_value', 'align' => 'R'],
            ],
            [
                ['width' =>  32,   'field' => 'discount',  'align' => 'R'],
                ['width' =>  32,   'field' => 'deduction', 'align' => 'R'],
                ['width' =>  32,   'field' => 'fine',      'align' => 'R'],
                ['width' =>  31.2, 'field' => 'addition',  'align' => 'R'],
                ['width' =>  49.8, 'field' => 'charged',   'align' => 'R'],
            ],
            [
                ['width' => 177,   'field' => 'client'],
            ],
        ];
        foreach ($table as $row) {
            $this->drawRow($row, 'LB');
        }

        // Demonstrative
        $this->billetSetFont('cell_title');
        $this->Cell(151, 3.5, $fields['demonstrative']['text'], 0, 0);
        $this->Cell(26, 3.5, $fields['mech_auth']['text'], 0, 1);
        $this->billetSetFont('cell_data');
        $y = $this->GetY();
        $this->MultiCell(151, 3.5, $fields['demonstrative']['value']);
        $y1 = $this->GetY();
        $this->SetXY(161, $y);
        $this->Cell(26, 3.5, '', 0, 1);
        $y2 = $this->GetY();
        $this->SetY(max($y + 14, $y1, $y2));
        $this->Ln(12);
    }

    /**
     * Table 2, stays in payment place
     */
    protected function drawTable2()
    {
        $fields = $this->fields;
        $models = $this->models;

        /*
         * Structure:
         *
         * payment_place                                                      | date_due
         * assignor                                                           | agency_code
         * date_document | doc_number_sh | specie_doc | accept | date_process | our_number
         * bank_use | wallet | currency | amount | doc_valueU                 | doc_value=
         * demonstrative or instructions                                      | discount
         *                                                                    | deduction
         *                                                                    | fine
         *                                                                    | additions
         *                                                                    | charged
         */
        $table = [
            [
                ['width' => 127.2, 'field' => 'payment_place'],
                ['width' =>  49.8, 'field' => 'date_due', 'align' => 'R'],
            ],
            [
                ['width' => 127.2, 'field' => 'assignor'],
                ['width' =>  49.8, 'field' => 'agency_code', 'align' => 'R'],
            ],
            [
                ['width' =>  32,   'field' => 'date_document'],
                ['width' =>  42.2, 'field' => 'doc_number_sh'],
                ['width' =>  18,   'field' => 'specie_doc'],
                ['width' =>  11,   'field' => 'accept'],
                ['width' =>  24,   'field' => 'date_process'],
                ['width' =>  49.8, 'field' => 'our_number', 'align' => 'R'],
            ],
            [
                ['width' =>  32,   'field' => 'bank_use'],
                ['width' =>  24,   'field' => 'wallet'],
                ['width' =>  16,   'field' => 'currency'],
                ['width' =>  34.2, 'field' => 'amount'],
                ['width' =>  21,   'field' => 'doc_valueU'],
                ['width' =>  49.8, 'field' => 'doc_value=', 'align' => 'R'],
            ],
            [
                ['width' => 127.2, 'field' => 'instructions'],
                [
                    'width' => 49.8,
                    'field' => [
                        'discount',
                        'deduction',
                        'fine',
                        'addition',
                        'charged',
                    ],
                    'align' => 'R'
                ],

            ],
        ];
        foreach ($table as $row) {
            $this->drawRow($row, 'LB');
        }

        // Client
        $this->billetSetFont('cell_title');
        $this->Cell(127.2, 7, $fields['client']['text'], 'L', 1);
        $this->billetSetFont('cell_data');
        $this->MultiCell(127.2, 3.5, $fields['client']['value'] . "\n" . utf8_decode($models['client.address']->outputLong()), 'LB');
        $this->SetXY(137.2, $this->GetY() - 3.5);
        $this->billetSetFont('cell_title');
        $this->Cell(49.8, 3.5, $fields['cod_down']['text'], 'LB', 1);

        // Guarantor
        $this->Cell(17, 3.5, $fields['guarantor']['text']);
        $this->billetSetFont('cell_data');
        $this->Cell(93, 3.5, $fields['guarantor']['value']);

        // Mechanical authentication
        $this->billetSetFont('cell_title');
        $this->Cell(39.5, 3.5, $fields['mech_auth']['text'] . ' - ', 0, 0, 'R');
        $this->billetSetFont('cell_data');
        $this->Cell(27.5, 3.5, $fields['compensation']['text'], 0, 1, 'R');
    }
}
