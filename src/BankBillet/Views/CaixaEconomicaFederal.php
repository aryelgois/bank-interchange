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

        $this->drawTable1();

        $this->billetSetFont('cell_title');
        $this->drawDash($fields['cut_here']['text'], true);

        $this->drawTable2();

        $this->drawBarCode();

        $this->billetSetFont('cell_title');
        $this->drawDash($fields['cut_here']['text'], true);
    }

    /**
     * Table 1, stays with the Client
     *
     * Extends drawGenericTable1()
     */
    protected function drawTable1()
    {
        $this->drawBankHeader();

        $this->drawGenericTable1(
            'LB',
            [
                 80.8, 35.4, 11  , 16  , 33.8,
                 52.8, 37  , 37.4, 49.8,
                 32  , 32  , 32  , 31.2, 49.8,
                177
            ]
        );

        $fields = $this->fields;

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
     *
     * Extends drawGenericTable2()
     */
    protected function drawTable2()
    {
        $this->drawBankHeader();

        $this->drawGenericTable2(
            'instructions',
            'LB',
            [
                127.2, 49.8,
                127.2, 49.8,
                 32  , 42.2, 18, 11  , 24, 49.8,
                 32  , 24  , 16, 34.2, 21, 49.8,
                127.2, 49.8
            ]
        );

        $fields = $this->fields;
        $models = $this->models;

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
