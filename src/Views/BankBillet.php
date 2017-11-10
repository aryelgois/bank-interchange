<?php
/**
 * This Software is part of aryelgois\BankInterchange and is provided "as is".
 *
 * @see LICENSE
 */

namespace aryelgois\BankInterchange\Abstracts\Views;

use aryelgois\Utils;
use aryelgois\BankInterchange as BankI;
use FPDF;

/**
 * Generates Bank Billets to be sent to clients/payers
 *
 * Extends FPDF by Olivier Plathey
 *
 * NOTES:
 * - Every occurency of '{{ total_pages }}' in the pdf will be replaced
 *
 * @author Aryel Mota Góis
 * @license MIT
 * @link https://www.github.com/aryelgois/bank-interchange
 */
abstract class BankBillet extends FPDF
{
    /**
     * Length used to zero-pad "Our Number"
     */
    const ONUM_LEN = 8;

    /**
     * Length used to zero-pad the account (WITHOUT the checkdigit)
     */
    const ACCOUNT_LEN = 11;

    /**
     * Temporary way to set the document specie
     */
    const SPECIE_DOC = '11';

    /**
     * Information on page header about printing
     *
     * @const string[]
     */
    const HEADER_INSTRUCTIONS = [
        'title' => 'Instruções de Impressão',
        'body' => "- Imprima em impressora jato de tinta (ink jet) ou laser em qualidade normal ou alta (Não use modo econômico).\n"
                . "- Utilize folha A4 (210 x 297 mm) ou Carta (216 x 279 mm) e margens mínimas à esquerda e à direita do formulário.\n"
                . "- Corte na linha indicada. Não rasure, risque, fure ou dobre a região onde se encontra o código de barras.\n"
                . "- Caso não apareça o código de barras no final, clique em F5 para atualizar esta tela.\n"
                . '- Caso tenha problemas ao imprimir, copie a seqüencia numérica abaixo e pague no caixa eletrônico ou no internet banking:'
    ];

    /**
     * Path to directory with logos
     *
     * @const string
     */
    const PATH_LOGOS = __DIR__ . '/../../../res/logos';

    /**
     * Font presets of family, weight, size and color
     *
     * @const array[]
     */
    const FONTS = [
        'digitable'  => ['Arial', 'B',  8, [0, 0,  0]],
        'digitable1' => ['Arial', 'B', 10, [0, 0,  0]],
        'billhead'   => ['Arial', '',   6, [0, 0,  0]],
        'bank_code'  => ['Arial', 'B', 14, [0, 0,  0]],
        'cell_title' => ['Arial', '',   6, [0, 0, 51]],
        'cell_data'  => ['Arial', 'B',  7, [0, 0,  0]],
        'footer'     => ['Arial', '',   9, [0, 0,  0]]
    ];

    /**
     * Size of dashes: black, white
     *
     * @const integer[]
     */
    const DASH_STYLE = [2, 1];

    /**
     * Default line width for borders
     *
     * @const integer
     */
    const DEFAULT_LINE_WIDTH = 0.2;

    /**
     * Dictionary of terms used in the billet
     *
     * @var string[]
     */
    protected $dictionary = [
        'accept'        => 'Aceite',
        'addition'      => '(+) Outros acréscimos',
        'agency_code'   => 'Agência/Código do Cedente',
        'amount'        => 'Quantidade',
        'assignor'      => 'Cedente',
        'bank_use'      => 'Uso do banco',
        'charged'       => '(=) Valor cobrado',
        'cod_down'      => 'Cód. baixa',
        'compensation'  => 'Ficha de Compensação',
        'cpf_cnpj'      => 'CPF/CNPJ',
        'cut_here'      => 'Corte na linha pontilhada',
        'date_due'      => 'Vencimento',
        'date_document' => 'Data do documento',
        'date_process'  => 'Data processameto',
        'deduction'     => '(-) Outras deduções',
        'demonstrative' => 'Demonstrativo',
        'discount'      => '(-) Desconto / Abatimentos',
        'doc_number'    => 'Número do documento',
        'doc_number_sh' => 'Nº documento',
        'doc_value'     => 'Valor documento',
        'doc_value='    => '(=) Valor documento',
        'fine'          => '(+) Mora / Multa',
        'guarantor'     => 'Sacador/Avalista',
        'header_info'   => "    Linha Digitável:  %s\n    Valor:   %s",
        'instructions'  => 'Instruções (Texto de responsabilidade do cedente)',
        'mech_auth'     => 'Autenticação mecânica',
        'onum'          => 'Nosso número',
        'payer'         => 'Sacado',
        'payer_receipt' => 'Recibo do Sacado',
        'payment_place' => 'Local de pagamento',
        'specie'        => 'Espécie',
        'specie_doc'    => 'Espécie doc.',
        'wallet'        => 'Carteira'
    ];

    /**
     * Holds data from database and manipulates some tables
     *
     * @var Model
     */
    protected $model;

    /**
     * Contains data for the billet
     *
     * @var mixed[]
     */
    protected $billet = [];

    /**
     * Creates a new Billet View object
     *
     * @param Models\BankBillet $model Holds data for the bank billet
     * @param mixed[]           $data  Data for the bank billet
     */
    public function __construct(BankI\BankBillet\Models\Model $model, $data)
    {
        parent::__construct();
        $this->AliasNbPages('{{ total_pages }}');

        $this->SetLineWidth(static::DEFAULT_LINE_WIDTH);

        $this->model = $model;

        $this->beforeDraw($data);

        $this->drawBillet();
    }


    /*
     * Before Drawing / Helper
     * =========================================================================
     */

    /**
     * Prepare some data to be used during Draw
     *
     * @param mixed[] $data Data for the bank billet
     */
    protected function beforeDraw($data)
    {
        $this->billet['value'] = (float)($this->model->title->value + $this->model->bank->tax);
        $this->generateBarcode();

        $this->billet = array_merge($this->billet, $data);
    }

    /**
     * Calculates Our number's check digit
     *
     * @return string
     */
    protected function checkDigitOnum()
    {
        $onum = BankI\Utils::padNumber($this->model->assignor->agency['number'], 3)
              . BankI\Utils::padNumber($this->model->title->onum, 8);

        return BankI\Utils::checkDigitOnum($onum);
    }

    /**
     * Calculates the check digit for Barcode
     *
     * @param string $code 43 digits
     * @return string
     */
    protected static function checkDigitBarcode($code)
    {
        $tmp = Utils\Validation::mod11($code);
        $cd = ($tmp == 0 || $tmp == 1 || $tmp == 10)
            ? 1
            : 11 - $tmp;
        return $cd;
    }

    /**
     * Calculate the amount of days since 1997-10-07
     *
     * @return string Four digits
     */
    protected function dueFactor()
    {
        $date = \DateTime::createFromFormat('Y-m-d', $this->model->title->due);
        $epoch = new \DateTime('1997-10-07');
        if ($date && $date > $epoch) {
            return str_pad(substr($date->diff($epoch)->format('%a'), -4), 4, '0', STR_PAD_LEFT);
        }
        return '0000';
    }

    protected function generateBarcode()
    {
        $barcode = [
            $this->model->bank->code,
            $this->model->title->specie['cnab' . $this->model->title->cnab],
            '', // Check digit
            $this->dueFactor(),
            BankI\Utils::padNumber(number_format($this->billet['value'], 2, '', ''), 10),
            $this->generateFreeSpace()
        ];
        $barcode[2] = self::checkDigitBarcode(implode('', $barcode));

        $this->billet['barcode'] = implode('', $barcode);

        $this->billet['digitable'] = static::formatDigitable(...$barcode);
    }

    /**
     * Free space, defined by Bank.
     *
     * Here: Our number . Agency/Assignor's code
     */
    protected function generateFreeSpace()
    {
        return $this->formatOnum(false) . $this->formatAgencyCode(false);
    }


    /*
     * Drawing
     * =========================================================================
     */


    /**
     * Procedurally draws the bank billet using FPDF methods
     */
    protected abstract function drawBillet();

    protected function drawPageHeader()
    {
        $this->billetSetFont('cell_data');
        $this->Cell(177, 3, utf8_decode(static::HEADER_INSTRUCTIONS['title']), 0, 1, 'C');
        $this->Ln(2);
        $this->MultiCell(177, 3, utf8_decode(static::HEADER_INSTRUCTIONS['body']));
        $this->Ln(2);
        $this->billetSetFont('digitable');
        $this->MultiCell(177, 3.5, utf8_decode(sprintf($this->dictionary['header_info'], $this->billet['digitable'], $this->formatMoney($this->billet['value']))));
        $this->Ln(4);
    }

    protected function drawBillhead()
    {
        $this->Ln(2);
        $y = $this->GetY();
        $this->Image(self::PATH_LOGOS . '/assignors/' . $this->model->assignor->logo, null, null, 40, 0, '', $this->model->assignor->url);
        $y1 = $this->GetY();
        $this->billetSetFont('billhead');
        $this->SetXY(50, $y);
        $this->MultiCell(103.2, 2.5, utf8_decode($this->model->assignor->name . "\n" . $this->model->assignor->formatDocument() . "\n" . $this->model->assignor->address[0]->outputLong()));
        $this->SetY(max($y1, $this->GetY()));
    }

    /**
     * Inserts a dashed line, with optional text before or after
     *
     * The text uses previous font
     *
     * @param string  $text       An optional text
     * @param boolean $text_first If the text comes first
     * @param string  $align      Aligns the text (L|C|R)
     */
    protected function drawDash($text = '', $text_first = false, $align = 'R')
    {
        $cell = function ($text, $align)
        {
            $this->Cell(177, 4, utf8_decode($text), 0, 1, $align);
        };

        if ($text_first) {
            $cell($text, $align);
        }

        $this->SetLineWidth(static::DEFAULT_LINE_WIDTH * 0.625);
        $y = $this->GetY();
        $this->SetDash(...static::DASH_STYLE);
        $this->Line(10, $y, 187, $y);
        $this->SetDash();
        $this->SetLineWidth(static::DEFAULT_LINE_WIDTH);

        if (!$text_first) {
            $cell($text, $align);
        }
    }

    /**
     * Inserts the Bank header
     *
     * @param string  $digitable_align   Aligns the digitable line (L|C|R)
     * @param integer $line_width_factor Multiplier for the line width
     */
    protected function drawBankHeader($digitable_align = 'R', $line_width_factor = 2)
    {
        $bank = $this->model->bank;
        $logo = self::PATH_LOGOS . '/banks/' . $bank->logo;

        $this->Ln(3);
        if (is_file($logo)) {
            $this->Image($logo, null, null, 40);
            $this->SetXY(50, $this->GetY() - 7);
        } else {
            $this->billetSetFont('cell_data');
            $this->Cell(40, 7, utf8_decode($bank->name));
        }

        $this->SetLineWidth(static::DEFAULT_LINE_WIDTH * $line_width_factor);
        $this->billetSetFont('bank_code');
        $this->Cell(15, 7, $this->formatBankCode(), 'LR', 0, 'C');
        $this->billetSetFont('digitable1');
        $this->Cell(122, 7, $this->billet['digitable'], 0, 1, $digitable_align);
        $y = $this->GetY();
        $this->Line(10, $y, 187, $y);
        $this->SetLineWidth(static::DEFAULT_LINE_WIDTH);
    }

    /**
     * Inserts row of cells
     *
     * @param mixed[] $cells Data to be written
     * @param boolean $close If the border should be closed in the left
     */
    protected function drawTableRow($cells, $close = false)
    {
        $write_row = function ($field, $border) use ($cells, $close)
        {
            $count = count($cells);
            foreach ($cells as $cell) {
                $this->Cell($cell['w'], 3.5, utf8_decode($cell[$field]), $border . ($close && $count == 1 ? 'R' : ''), ($count == 1 ? 1 : 0), $cell[$field . '_align'] ?? 'L');
                $count--;
            }
        };
        $this->billetSetFont('cell_title');
        $write_row('title', 'L');
        $this->billetSetFont('cell_data');
        $write_row('data', 'LB');
    }

    /**
     * Inserts column of cells
     *
     * @param mixed[] $cells Data to be written
     * @param integer $x     Abscissa offset from page border
     * @param integer $w     Column width
     * @param boolean $close If the border should be closed in the left
     */
    protected function drawTableColumn($cells, $x, $w, $close = false)
    {
        $fields = [
            'title' => ['font' => 'cell_title', 'border' => 'L'],
            'data' => ['font' => 'cell_data', 'border' => 'LB']
        ];
        foreach ($cells as $cell) {
            foreach ($fields as $field => $config) {
                $this->SetX($x);
                $this->billetSetFont($config['font']);
                $this->Cell($w, 3.5, utf8_decode($cell[$field]), $config['border'] . ($close ? 'R' : ''), 1, $cell[$field . '_align'] ?? 'L');
            }
        }
    }

    /**
     * Produces a bar code from string of digits in style "2 of 5 intercalated"
     *
     * @param string $data     Data to be encoded
     * @param float  $baseline Corresponds to the width of a wide bar
     * @param float  $height   Bar height
     * @return
     */
    protected function drawBarCode($baseline = 0.8, $height = 13)
    {
        $data = $this->billet['barcode'];
        $wide = $baseline;
        $narrow = $baseline / 3;
        $map = [
            '00110',
            '10001',
            '01001',
            '11000',
            '00101',
            '10100',
            '01100',
            '00011',
            '10010',
            '01010'
        ];

        // If odd $data
        if ((strlen($data) % 2) != 0) {
            $data = '0' . $data;
        }
        // Generate bits to be draw
        $code = '0000'; // Leading value
        for ($i = 0, $l = strlen($data); $i < $l; $i += 2) {
            $code .= implode('', Utils\Utils::arrayInterpolate($map[$data[$i]], $map[$data[$i + 1]]));
        }
        $code .= '100'; // Trailing value

        // Draw
        $this->SetFillColor(0);
        $x = $this->GetX();
        $y = $this->GetY();
        $draw = true;
        foreach (str_split($code, 1) as $bit) {
            $width = ($bit == '0')
                ? $narrow
                : $wide;
            if ($draw) {
                $this->Rect($x, $y, $width, $height, 'F');
            }
            $x += $width;
            $draw = !$draw;
        }
        $this->Ln($height);
    }


    /*
     * Formatting
     * =========================================================================
     */

    /**
     * Formats Agency/Assignor's code
     *
     * @todo Calculate Check digit
     *
     * @param boolean $full If shoud return the full formatting
     *
     * @return string
     */
    protected function formatAgencyCode($full = true)
    {
        $assignor = $this->model->assignor;
        $tmp = [
            BankI\Utils::padNumber($assignor->agency['number'], 4),
            BankI\Utils::padNumber($assignor->account['number'], static::ACCOUNT_LEN)
        ];

        // @todo check digit
        $cd = $assignor->account['cd'];

        if ($full) {
            return implode(' / ', $tmp) . '-' . $cd;
        }
        return implode('', $tmp) . $cd;
    }

    /**
     * Calculates Bank code's check digit and formats it
     *
     * @return string
     */
    protected function formatBankCode()
    {
        $code = $this->model->bank->code;

        $checksum = Utils\Validation::mod11Pre($code);
        $digit = $checksum * 10 % 11;
        if ($digit == 10) {
            $digit = 0;
        }

        return $code . '-' . $digit;
    }

    /**
     * Formats a date from Y-m-d to d/m/Y
     *
     * @param string $date Date string
     *
     * @return string
     */
    protected static function formatDate($date)
    {
        $d = \DateTime::createFromFormat('Y-m-d', $date);
        if (!$d) {
            $d = \DateTime::createFromFormat('Y-m-d H:i:s', $date);
        }
        return ($d ? $d->format('d/m/Y') : $date);
    }

    /**
     * Formats the barcode into a Digitable line
     *
     * @param string $bank   Bank code (3 digits)
     * @param string $specie Specie Code (1 digit)
     * @param string $cd     Check digit (1 digit)
     * @param string $due    Due Factor (4 digits)
     * @param string $value  Document Value (10 digits: 8 integers and 2 decimals)
     * @param string $free   Free space (25 digits, defined by each bank)
     * @return string
     */
    protected static function formatDigitable($bank, $specie, $cd, $due, $value, $free)
    {
        $fields = [];

        // Field #0 - Bank code, Specie code, 5 first digits from Free space, Check digit for this field
        $tmp = $bank . $specie . substr($free, 0, 5);
        $tmp .= Utils\Validation::mod10($tmp);
        $fields[] = implode('.', str_split($tmp, 5));

        // Field #1 - Digits 6 to 15 from Free space, Check digit for this field
        $tmp = substr($free, 5, 10);
        $fields[] = implode('.', str_split($tmp, 5)) . Utils\Validation::mod10($tmp);

        // Field #2 - Digits 16 to 25 from Free space, Check digit for this field
        $tmp = substr($free, 15, 10);
        $fields[] = implode('.', str_split($tmp, 5)) . Utils\Validation::mod10($tmp);

        // Field #3 - Digitable line Check digit
        $fields[] = $cd;

        // Field #4 - Due factor, Document value
        $fields[] = $due . $value;

        return implode(' ', $fields);
    }

    /**
     * Formats a numeric value as monetary value
     *
     * @param number  $value  Value to be formated
     * @param boolean $symbol If should prepend the specie symbol
     * @return string
     */
    protected function formatMoney($value, $symbol = true)
    {
        $specie = $this->model->title->specie;
        return ($symbol ? $specie['symbol'] . ' ' : '') . number_format($value, 2, $specie['decimal'], $specie['thousand']);
    }

    /**
     * Calculates Our number's check digit and formats it
     *
     * @param boolean $dash If should add a dash between number and check digit
     *
     * @return string
     */
    protected function formatOnum($dash = true)
    {
        $result = BankI\Utils::padNumber($this->model->title->onum, static::ONUM_LEN)
                . ($dash ? '-' : '')
                . $this->checkDigitOnum();
        return $result;
    }


    /*
     * Internal
     * =========================================================================
     */


    /**
     * Allows an easy way to set current font
     *
     * @param string $font Key for FONTS
     */
    protected function billetSetFont($font)
    {
        $f = static::FONTS[$font];
        $this->SetFont($f[0], $f[1], $f[2]);
        if (count($f) > 3) {
            $this->SetTextColor(...$f[3]);
        }
    }

    /**
     * This extension allows to set a dash pattern and draw dashed lines or rectangles.
     *
     * Call the function without parameter to restore normal drawing.
     *
     * @link http://www.fpdf.org/en/script/script33.php
     * @author yukihiro_o <yukihiro_o@infoseek.jp>
     * @license FPDF
     * @param float black Length of dashes
     * @param float white Length of gaps
     */
    protected function SetDash($black = null, $white = null)
    {
        if ($black !== null) {
            $s = sprintf('[%.3F %.3F] 0 d', $black * $this->k, $white * $this->k);
        } else {
            $s = '[] 0 d';
        }
        $this->_out($s);
    }

    /**
     * Page footer
     *
     * Used internally by FPDF
     */
    public function Footer()
    {
        $this->SetY(-15);
        $this->billetSetFont('footer');
        $this->Cell(88.5, 5, $this->PageNo() . ' / {{ total_pages }}');
        $this->Cell(88.5, 5, date('d/m/Y H:i:s T'), 0, 0, 'R');
    }
}
