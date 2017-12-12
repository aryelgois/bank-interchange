<?php
/**
 * This Software is part of aryelgois\BankInterchange and is provided "as is".
 *
 * @see LICENSE
 */

namespace aryelgois\BankInterchange\Views\BankBillets;

use aryelgois\Utils\Validation;
use aryelgois\BankInterchange as BankI;

/**
 * Generates Bank Billets in Banese's layout
 *
 * @author Aryel Mota Góis
 * @license MIT
 * @link https://www.github.com/aryelgois/bank-interchange
 */
class Banese extends BankI\Views\BankBillet
{
    /**
     * Font presets of family, weight, size and color
     *
     * @const array[]
     */
    const FONTS = [
        'digitable'  => ['Arial', 'B',  8, [ 0,  0,  0]],
        'digitable1' => ['Arial', 'B', 10, [ 0,  0,  0]],
        'billhead'   => ['Arial', '',   6, [ 0,  0,  0]],
        'bank_code'  => ['Arial', 'B',  9, [ 0,  0,  0]],
        'cell_title' => ['Arial', '',   6, [20, 20, 20]],
        'cell_data'  => ['Arial', 'B',  7, [ 0,  0,  0]],
        'footer'     => ['Arial', '',   9, [ 0,  0,  0]]
    ];

    /**
     * Size of dashes: black, white
     *
     * @const integer[]
     */
    const DASH_STYLE = [0.625, 0.75];

    /**
     * Default line width for borders
     *
     * @const integer
     */
    const DEFAULT_LINE_WIDTH = 0.3;

    /**
     * Free space: Asbace key
     *
     * Here: Agency . Account . Our number . Bank code . 2 check digits
     */
    protected function generateFreeSpace()
    {
        $key = BankI\Utils::padNumber($this->ref['assignor']->agency, 2, true)
             . BankI\Utils::padNumber($this->ref['assignor']->account, 9, true)
             . $this->formatOurNumber(false)
             . BankI\Utils::padNumber($this->ref['bank']->code, 3, true);
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

    /**
     * Procedurally draws the bank billet using FPDF methods
     */
    protected function drawBillet()
    {
        $this->dictionary = array_replace(
            $this->dictionary,
            [
                'agency_code'   => 'Agência/Cod. Beneficiário',
                'date_process'  => 'Data do processameto',
                'doc_number_sh' => 'Nº do documento',
                'discount'      => '(-) Desconto/ Abatimento',
                'doc_value'     => 'Valor',
                'doc_value='    => '(=) Valor do documento',
                'fine'          => '(+) Mora/Multa',
                'guarantor'     => 'Sacador/Avalista: ',
                'instructions'  => 'Instruções',
                'specie'        => 'Moeda',
                'specie_doc'    => 'Espécie doc',
            ]
        );
        $keys = [
            'accept', 'addition', 'agency_code', 'amount', 'assignor',
            'bank_use', 'charged', 'date_due', 'date_document', 'date_process',
            'deduction', 'demonstrative', 'discount', 'doc_number_sh',
            'doc_value', 'doc_value=', 'fine', 'guarantor', 'instructions',
            'mech_auth', 'our_number', 'payer', 'payment_place', 'specie',
            'specie_doc', 'wallet'
        ];
        foreach ($keys as $key) {
            $this->dictionary[$key] = mb_strtoupper($this->dictionary[$key]);
        }

        $dict = $this->dictionary;

        $this->AddPage();

        $this->drawPageHeader();

        $this->billetSetFont('cell_data');
        $this->drawDash($dict['payer_receipt']);

        $this->drawBillhead();

        $this->drawBankHeader('L', 1);

        $this->drawTable('demonstrative');

        $this->Ln(4);

        $this->billetSetFont('cell_title');
        $this->drawDash($dict['compensation']);

        $this->SetY($this->GetY() - 3);

        $this->drawBankHeader('L', 1);

        $this->drawTable('instructions');

        $this->SetY($this->GetY() - 3);
        $this->drawBarCode();
    }

    /**
     * Generic Table
     *
     * NOTES:
     * - '{{ tax }}' is replaced by the money-formated tax in the big cell, if
     *   setted to demonstrative
     *
     * @param string $big_cell demonstrative|instructions Tells which information
     *                         goes in the big cell
     */
    protected function drawTable($big_cell = 'instructions')
    {
        $dict = $this->dictionary;
        $title = $this->title;
        $assignor = $this->ref['assignor'];
        $assignor_person = $this->ref['assignor.person'];
        $bank = $this->ref['bank'];
        $payer_person = $this->ref['payer.person'];
        $wallet = $this->ref['wallet'];

        $y = $this->GetY(); // get Y to come back and add the aside column

        /*
         * Structure:
         *
         * Payment place
         * Assignor
         * Document Date | Document number | Document specie | Accept | Processing Date
         * Bank's use | Wallet | Specie | Amount | Document value UN
         * Payer
         */
        $table = [
            [
                ['w' => 127.2, 'title' => $dict['payment_place'], 'data' => $this->billet['payment_place'] ?? '']
            ],
            [
                ['w' => 127.2, 'title' => $dict['assignor'],      'data' => $assignor_person->name . '     ' . $assignor_person->documentFormat(true)]
            ],
            [
                ['w' =>  32,   'title' => $dict['date_document'], 'data' => self::formatDate($title->stamp)],
                ['w' =>  27,   'title' => $dict['doc_number_sh'], 'data' => BankI\Utils::padNumber($title->id, 10)],
                ['w' =>  20,   'title' => $dict['specie_doc'],    'data' => '5'],                                           //$data['misc']['specie_doc']
                ['w' =>  12,   'title' => $dict['accept'],        'data' => 'A'],                                           //$data['misc']['accept']
                ['w' =>  36.2, 'title' => $dict['date_process'],  'data' => date('d/m/Y')]
            ],
            [
                ['w' =>  32,   'title' => $dict['bank_use'],      'data' => ''],                                            //$data['misc']['bank_use']
                ['w' =>  16,   'title' => $dict['wallet'],        'data' => $wallet->symbol],
                ['w' =>  11,   'title' => $dict['specie'],        'data' => $this->ref['specie']->symbol],
                ['w' =>  32,   'title' => $dict['amount'],        'data' => ''],                                            //$data['misc']['amount']
                ['w' =>  36.2, 'title' => $dict['doc_value'],     'data' => '']                                             //$data['misc']['value_un']
            ]
        ];
        foreach ($table as $row) {
            $this->drawTableRow($row);
        }

        // Big cell: Instructions or Demonstrative
        $big_cell_text = $this->billet[$big_cell] ?? '';
        if ($big_cell == 'demonstrative') {
            $big_cell_text = str_replace('{{ tax }}', $this->formatMoney($bank->tax), $big_cell_text);
        }
        $y1 = $this->GetY();
        $this->billetSetFont('cell_title');
        $this->Cell(127.2, 7, utf8_decode($dict[$big_cell]), 0, 1);
        $this->billetSetFont('cell_data');
        $this->MultiCell(127.2, 3.5, utf8_decode($big_cell_text));
        $y2 = $this->GetY();

        /**
         * Aside column:
         *
         * Structure:
         *
         * Due
         * Agency/Assignor's code
         * Our number
         * (=) Document value
         * (-) Discount/Rebates
         * (-) Other deductions
         * (+) "Mora"/Fine
         * (+) Other additions
         * (=) Amount charged
         */
        $this->SetY($y);
        $table = [
            ['title' => $dict['date_due'],    'data' => self::formatDate($title->due),       'data_align' => 'R'],
            ['title' => $dict['agency_code'], 'data' => $this->formatAgencyAccount(),               'data_align' => 'R'],
            ['title' => $dict['our_number'],  'data' => $this->formatOurNumber(),                   'data_align' => 'R'],
            ['title' => $dict['doc_value='],  'data' => $this->formatMoney($this->billet['value']), 'data_align' => 'R'],
            ['title' => $dict['discount'],    'data' => '',                                         'data_align' => 'R'], //$data['misc']['discount']
            ['title' => $dict['deduction'],   'data' => '',                                         'data_align' => 'R'], //$data['misc']['deduction']
            ['title' => $dict['fine'],        'data' => '',                                         'data_align' => 'R'], //$data['misc']['fine']
            ['title' => $dict['addition'],    'data' => '',                                         'data_align' => 'R'], //$data['misc']['addition']
            ['title' => $dict['charged'],     'data' => '',                                         'data_align' => 'R']  //$data['misc']['charged']
        ];
        $this->drawTableColumn($table, 137.2, 49.8, true);

        // Instructions border
        $y = $this->GetY();
        $y3 = max($y, $y2);
        $this->Line(10, $y1, 10, $y3);
        $this->Line(10, $y3, 137.2, $y3);
        if ($y3 > $y) {
            $this->Line(137.2, $y3, 137.2, $y);
            $this->SetY($y3);
        }

        // Payer
        $y = $this->GetY();
        $this->billetSetFont('cell_title');
        $this->Cell(10, 3.5, $dict['payer']);
        $this->SetXY($this->GetX() + 5, $y);
        $this->billetSetFont('cell_data');
        $this->MultiCell(112.2, 3.5, utf8_decode($payer_person->name . "\n" . $this->ref['payer.address']->outputLong()));
        $y1 = $this->GetY();
        $this->SetXY(119.2, $y);
        $this->Cell(36, 3.5, $payer_person->documentFormat(true), 0, 0, 'C');
        $this->setY($y1);

        // Guarantor

        $guarantor = ($this->ref['guarantor'] !== null)
                   ? $this->ref['guarantor.person']->name . '     '
                   . $this->ref['guarantor.address']->outputShort()
                   : '';
        $this->billetSetFont('cell_title');
        $this->Cell(24, 3.5, $dict['guarantor']);
        $this->billetSetFont('cell_data');
        $this->Cell(153, 3.5, utf8_decode($guarantor), 0, 1);
        $x = $this->GetX();
        $y1 = $this->GetY();

        // Payer / Guarantor border
        $this->Line($x, $y, $x, $y1);
        $this->Line($x, $y1, 187, $y1);
        $this->Line(187, $y, 187, $y1);

        // Mechanical authentication
        $this->SetX(119.2);
        $this->billetSetFont('cell_title');
        $this->Cell(67.8, 3.5, utf8_decode($dict['mech_auth'] . '/' . $wallet->name));
        $this->Ln(3.5);
    }
}
