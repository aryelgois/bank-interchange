<?php
/**
 * This Software is part of aryelgois/bank-interchange and is provided "as is".
 *
 * @see LICENSE
 */

namespace aryelgois\BankInterchange\BankBillet\Views;

use aryelgois\Utils\Validation;
use aryelgois\BankInterchange;

/**
 * Generates bank billets for Banese
 *
 * @author Aryel Mota Góis
 * @license MIT
 * @link https://www.github.com/aryelgois/bank-interchange
 */
class Banese extends BankInterchange\BankBillet\View
{
    const SPECIE_DOC = 5;

    const FONTS = [
        'digitable'  => ['Arial', 'B',  8, [ 0,  0,  0]],
        'digitable1' => ['Arial', 'B', 10, [ 0,  0,  0]],
        'billhead'   => ['Arial', '',   6, [ 0,  0,  0]],
        'bank_code'  => ['Arial', 'B',  9, [ 0,  0,  0]],
        'cell_title' => ['Arial', '',   6, [20, 20, 20]],
        'cell_data'  => ['Arial', 'B',  7, [ 0,  0,  0]],
        'footer'     => ['Arial', '',   9, [ 0,  0,  0]]
    ];

    const DASH_STYLE = [0.625, 0.75];

    const DEFAULT_LINE_WIDTH = 0.3;

    /**
     * Procedurally draws the bank billet using FPDF methods
     */
    protected function drawBillet()
    {
        // Change some fields
        $rename_fields = [
            'agency_code'   => ['text' => 'Agência/Cod. Beneficiário'],
            'date_process'  => ['text' => 'Data do processameto'],
            'doc_number_sh' => ['text' => 'Nº do documento'],
            'discount'      => ['text' => '(-) Desconto/ Abatimento'],
            'doc_value'     => ['text' => 'Valor'],
            'doc_valueU'    => ['text' => 'Valor'],
            'doc_value='    => ['text' => '(=) Valor do documento'],
            'fine'          => ['text' => '(+) Mora/Multa'],
            'guarantor'     => ['text' => 'Sacador/Avalista: '],
            'instructions'  => ['text' => 'Instruções'],
            'currency'      => ['text' => 'Moeda'],
            'specie_doc'    => ['text' => 'Espécie doc'],
        ];
        foreach ($rename_fields as &$field) {
            $field['text'] = utf8_decode($field['text']);
        }
        unset($field);
        $this->fields = array_replace_recursive(
            $this->fields,
            $rename_fields
        );

        // Make most fields upper case
        $keys = [
            'accept', 'addition', 'agency_code', 'amount', 'assignor',
            'bank_use', 'charged', 'client', 'currency', 'date_document',
            'date_due', 'date_process', 'deduction', 'demonstrative',
            'discount', 'doc_number_sh', 'doc_value', 'doc_value=',
            'doc_valueU', 'fine', 'guarantor', 'instructions', 'mech_auth',
            'our_number', 'payment_place', 'specie_doc', 'wallet',
        ];
        foreach ($keys as $key) {
            $text = mb_strtoupper(utf8_encode($this->fields[$key]['text']));
            $this->fields[$key]['text'] = utf8_decode($text);
        }

        // Add document to assignor
        $this->fields['assignor']['value'] .= '     '
            . $this->models['assignor.person']->documentFormat(true);

        // Draw billet
        $fields = $this->fields;

        $this->AddPage();

        $this->drawPageHeader();

        $this->billetSetFont('cell_data');
        $this->drawDash($fields['client_receipt']['text']);

        $this->drawBillhead();

        $this->drawBankHeader('L', 1);

        $this->drawTable('demonstrative');

        $this->Ln(4);

        $this->billetSetFont('cell_title');
        $this->drawDash($fields['compensation']['text']);

        $this->SetY($this->GetY() - 3);

        $this->drawBankHeader('L', 1);

        $this->drawTable('instructions');

        $this->SetY($this->GetY() - 3);
        $this->drawBarCode();
    }

    /**
     * Generic Table
     *
     * @param string $big_cell Tells which information goes in the big cell
     *                         Domain: 'demonstrative', 'instructions'
     */
    protected function drawTable($big_cell)
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
                ['width' =>  27,   'field' => 'doc_number_sh'],
                ['width' =>  20,   'field' => 'specie_doc'],
                ['width' =>  12,   'field' => 'accept'],
                ['width' =>  36.2, 'field' => 'date_process'],
                ['width' =>  49.8, 'field' => 'our_number', 'align' => 'R'],
            ],
            [
                ['width' =>  32,   'field' => 'bank_use'],
                ['width' =>  16,   'field' => 'wallet'],
                ['width' =>  11,   'field' => 'currency'],
                ['width' =>  32,   'field' => 'amount'],
                ['width' =>  36.2, 'field' => 'doc_valueU'],
                ['width' =>  49.8, 'field' => 'doc_value=', 'align' => 'R'],
            ],
            [
                ['width' => 127.2, 'field' => $big_cell],
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
            $this->drawRow($row, 'LBR');
        }

        // Client
        $y = $this->GetY();
        $this->billetSetFont('cell_title');
        $this->Cell(10, 3.5, $fields['client']['text']);
        $this->SetXY($this->GetX() + 5, $y);
        $this->billetSetFont('cell_data');
        $this->MultiCell(112.2, 3.5, $fields['client']['value'] . "\n" . utf8_decode($models['client.address']->outputLong()));
        $y1 = $this->GetY();
        $this->SetXY(119.2, $y);
        $this->Cell(36, 3.5, $models['client.person']->documentFormat(true), 0, 0, 'C');
        $this->setY($y1);

        // Guarantor
        $this->billetSetFont('cell_title');
        $this->Cell(24, 3.5, $fields['guarantor']['text']);
        $this->billetSetFont('cell_data');
        $this->Cell(153, 3.5, $fields['guarantor']['value'], 0, 1);
        $x = $this->GetX();
        $y1 = $this->GetY();

        // Client / Guarantor border
        $this->Line($x, $y, $x, $y1);
        $this->Line($x, $y1, 187, $y1);
        $this->Line(187, $y, 187, $y1);

        // Mechanical authentication
        $this->SetX(119.2);
        $this->billetSetFont('cell_title');
        $this->Cell(67.8, 3.5, $fields['mech_auth']['text'] . '/' . utf8_decode($models['wallet']->name));
        $this->Ln(3.5);
    }

    /**
     * Free space: Asbace key
     *
     * Here: Agency . Account . Our number . Bank code . 2 check digits
     */
    protected function generateFreeSpace()
    {
        $key = BankInterchange\Utils::padNumber($this->models['assignment']->agency, 2, true)
            . BankInterchange\Utils::padNumber($this->models['assignment']->account, 9, true)
            . $this->formatOurNumber()
            . BankInterchange\Utils::padNumber($this->models['bank']->code, 3, true);
        $cd1 = Validation::mod10($key);
        $cd2 = Validation::mod11($key . $cd1, 7);

        if ($cd2 == 1) {
            if ($cd1 < 9) {
                $cd1++;
                $cd2 = Validation::mod11($key . $cd1, 7);
            } elseif ($cd1 == 9) {
                $cd1 = 0;
                $cd2 = Validation::mod11($key . $cd1, 7);
            }
        } elseif ($cd2 > 1) {
            $cd2 = 11 - $cd2;
        }

        return $key . $cd1 . $cd2;
    }
}
