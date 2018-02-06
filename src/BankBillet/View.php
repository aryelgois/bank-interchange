<?php
/**
 * This Software is part of aryelgois/bank-interchange and is provided "as is".
 *
 * @see LICENSE
 */

namespace aryelgois\BankInterchange\BankBillet;

use aryelgois\Utils;
use aryelgois\Medools;
use aryelgois\BankInterchange;
use FPDF;

/**
 * Generates Bank Billets to be sent to clients
 *
 * Extends FPDF by Olivier Plathey
 *
 * NOTE:
 * - Every occurency of '{{ total_pages }}' in the pdf will be replaced
 *
 * @author Aryel Mota Góis
 * @license MIT
 * @link https://www.github.com/aryelgois/bank-interchange
 */
abstract class View extends FPDF
{
    /**
     * Length used to zero-pad "Our Number"
     *
     * @const integer
     */
    const OUR_NUMBER_LENGTH = 8;

    /**
     * Length used to zero-pad the assignor's agency (WITHOUT the checkdigit)
     *
     * @const integer
     */
    const AGENCY_LENGTH = 4;

    /**
     * Length used to zero-pad the assignor's account (WITHOUT the checkdigit)
     *
     * @const integer
     */
    const ACCOUNT_LENGTH = 11;

    /**
     * Temporary way to set the document specie
     *
     * @const string
     */
    const SPECIE_DOC = '11';

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
     * @const numeric
     */
    const DEFAULT_LINE_WIDTH = 0.2;

    /**
     * Contains some data for the billet
     *
     * @var mixed[]
     */
    protected $data = [];

    /**
     * Contains fields to be drawn in the billet
     *
     * @var array[]
     */
    protected $fields = [];

    /**
     * Path to directory with logos
     *
     * @var string
     */
    protected $logos;

    /**
     * Holds model instances from different tables
     *
     * @var Medools\Model[]
     */
    protected $models = [];

    /**
     * Creates a new Billet View object
     *
     * @param Models\Title $title Holds data for the bank billet
     * @param string[]     $data  Extra data for the bank billet
     * @param string       $logos Path to directory with logos
     */
    public function __construct(
        BankInterchange\Models\Title $title,
        $data,
        $logos
    ) {
        $this->logos = (array) $logos;

        $models = [];
        $models['assignment']       = $title->assignment;
        $models['assignor']         = $models['assignment']->assignor;
        $models['assignor.address'] = $models['assignor']->address;
        $models['assignor.person']  = $models['assignor']->person;
        $models['bank']             = $models['assignment']->bank;
        $models['client']           = $title->client;
        $models['client.address']   = $models['client']->address;
        $models['client.person']    = $models['client']->person;
        $models['currency']         = $title->currency;
        $models['currency_code']    = Medools\ModelManager::getInstance(
            BankInterchange\Models\CurrencyCode::class,
            [
                'currency' => $models['currency']->id,
                'bank' => $models['bank']->id
            ]
        );
        $models['guarantor']        = $title->guarantor;
        $models['guarantor.person'] = $models['guarantor']->person ?? null;
        $models['title']            = $title;
        $models['wallet']           = $models['assignment']->wallet;
        $this->models = $models;

        $value = $models['title']->value + $models['bank']->tax;
        $data = array_merge(
            $data,
            ['value' => (float) $value],
            $this->generateBarcode($value)
        );
        $this->data = $data;

        $this->fields = $this->generateFields();

        parent::__construct();
        $this->AliasNbPages('{{ total_pages }}');
        $this->SetLineWidth(static::DEFAULT_LINE_WIDTH);
        $this->drawBillet();
    }

    /*
     * Drawing
     * =========================================================================
     */

    /**
     * Procedurally draws the bank billet using FPDF methods
     */
    protected abstract function drawBillet();

    /**
     * Draws the Page Header
     */
    protected function drawPageHeader()
    {
        $fields = $this->fields;
        $this->billetSetFont('cell_data');
        $this->Cell(177, 3, $fields['header_title']['text'], 0, 1, 'C');
        $this->Ln(2);
        $this->MultiCell(177, 3, $fields['header_body']['text']);
        $this->Ln(2);
        $this->billetSetFont('digitable');
        $this->MultiCell(177, 3.5, $fields['header_info']['text']);
        $this->Ln(4);
    }

    /**
     * Draws the Billhead
     */
    protected function drawBillhead()
    {
        $this->Ln(2);
        $logo = self::findFile('assignors/' . $this->models['assignor']->logo, $this->logos);

        if ($logo !== null) {
            $y = $this->GetY();
            $this->Image($logo, null, null, 40, 0, '', $this->models['assignor']->url);
            $y1 = $this->GetY();
            $this->SetXY(50, $y);
        }
        $this->billetSetFont('billhead');
        $this->MultiCell(103.2, 2.5, utf8_decode($this->models['assignor.person']->name . "\n" . $this->models['assignor.person']->documentFormat() . "\n" . $this->models['assignor.address']->outputLong()));
        $this->SetY(max($y1 ?? 0, $this->GetY()));
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
        $cell = function ($text, $align) {
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
    protected function drawBankHeader(
        $digitable_align = 'R',
        $line_width_factor = 2
    ) {
        $bank = $this->models['bank'];
        $logo = self::findFile('banks/' . $bank->logo, $this->logos);

        $this->Ln(3);
        if ($logo !== null) {
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
        $this->Cell(122, 7, $this->data['digitable'], 0, 1, $digitable_align);
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
        $data = $this->data['barcode'];
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
     * Formats Agency/Account
     *
     * @param boolean $symbol If shoud include symbols
     *
     * @return string
     */
    protected function formatAgencyAccount($symbol = false)
    {
        return $this->models['assignment']->formatAgencyAccount(
            static::AGENCY_LENGTH,
            static::ACCOUNT_LENGTH,
            $symbol
        );
    }

    /**
     * Calculates Bank code's check digit and formats it
     *
     * @return string
     */
    protected function formatBankCode()
    {
        $code = $this->models['bank']->code;

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
     * @param string $currency Currency Code (1 digit)
     * @param string $cd     Check digit (1 digit)
     * @param string $due    Due Factor (4 digits)
     * @param string $value  Document Value (10 digits: 8 integers and 2 decimals)
     * @param string $free   Free space (25 digits, defined by each bank)
     * @return string
     */
    protected static function formatDigitable(
        $bank_code,
        $currency_code,
        $check_digit,
        $due_factor,
        $value,
        $free_space
    ) {
        $fields = [];

        /*
         * Field #0
         *
         * - $bank_code
         * - $currency_code
         * - 5 first digits from $free_space
         * - Check digit for this field
         */
        $tmp = $bank_code . $currency_code . substr($free_space, 0, 5);
        $tmp .= Utils\Validation::mod10($tmp);
        $fields[] = implode('.', str_split($tmp, 5));

        /*
         * Field #1
         *
         * - Digits 6 to 15 from $free_space
         * - Check digit for this field
         */
        $tmp = substr($free_space, 5, 10);
        $fields[] = implode('.', str_split($tmp, 5))
            . Utils\Validation::mod10($tmp);

        /*
         * Field #2
         *
         * - Digits 16 to 25 from $free_space
         * - Check digit for this field
         */
        $tmp = substr($free_space, 15, 10);
        $fields[] = implode('.', str_split($tmp, 5))
            . Utils\Validation::mod10($tmp);

        /*
         * Field #3
         *
         * - Digitable line $check_digit
         */
        $fields[] = $check_digit;

        /*
         * Field #4
         *
         * - $due_factor
         * - Document $value
         */
        $fields[] = $due_factor . $value;

        return implode(' ', $fields);
    }

    /**
     * Formats a numeric value as monetary value
     *
     * @param number  $value  Value to be formated
     * @param string  $format @see Models/Currency::format()
     *
     * @return string
     */
    protected function formatMoney($value, $format = 'symbol')
    {
        return $this->models['currency']->format($value, $format);
    }

    /**
     * Calculates Our number's check digit and formats it
     *
     * @param boolean $mask If should add a dash between number and check digit
     *
     * @return string
     */
    protected function formatOurNumber($mask = false)
    {
        $our_number = BankInterchange\Utils::padNumber(
            $this->models['title']->our_number,
            static::OUR_NUMBER_LENGTH
        );

        return $our_number . ($mask ? '-' : '') . $this->checkDigitOurNumber();
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
     * Calculates Our number's check digit
     *
     * @return string
     */
    protected function checkDigitOurNumber()
    {
        $our_number = BankInterchange\Utils::padNumber($this->models['assignment']->agency, 3)
            . BankInterchange\Utils::padNumber($this->models['title']->our_number, 8);

        return $this->models['title']->checkDigitOurNumberAlgorithm($our_number);
    }

    /**
     * Calculate the amount of days since 1997-10-07
     *
     * @return string Four digits
     */
    protected function dueFactor()
    {
        $date = \DateTime::createFromFormat('Y-m-d', $this->models['title']->due);
        $epoch = new \DateTime('1997-10-07');
        if ($date && $date > $epoch) {
            $diff = substr($date->diff($epoch)->format('%a'), -4);
            return str_pad($diff, 4, '0', STR_PAD_LEFT);
        }
        return '0000';
    }

    /**
     * Finds a file in a list of paths
     *
     * @param string $file  [description]
     * @param array  $paths [description]
     *
     * @return string If file was found
     * @return null   If file was not found
     */
    protected static function findFile($file, array $paths)
    {
        if ($file === null) {
            return null;
        }
        foreach ($paths as $path) {
            $test_file = "$path/$file";
            if (is_file($test_file)) {
                return $test_file;
            }
        }
    }

    /**
     * Generates the barcode data and it's digitable line
     *
     * @param numeric $value Billet value
     *
     * @return string[]
     */
    protected function generateBarcode($value)
    {
        $barcode = [
            $this->models['bank']->code,
            $this->models['currency_code']->billet,
            '', // Check digit
            $this->dueFactor(),
            BankInterchange\Utils::padNumber(number_format($value, 2, '', ''), 10),
            $this->generateFreeSpace()
        ];
        $barcode[2] = self::checkDigitBarcode(implode('', $barcode));

        return [
            'barcode' => implode('', $barcode),
            'digitable' => self::formatDigitable(...$barcode),
        ];
    }

    /**
     * Generates fields to be drawn in the billet
     *
     * @return array[]
     */
    protected function generateFields()
    {
        $data = $this->data;
        $models = $this->models;
        $doc_number = BankInterchange\Utils::padNumber($models['title']->id, 10);
        $value = $this->formatMoney($data['value']);

        $fields = [
            'accept' => [
                'text' => 'Aceite',
                'value' => $data['accept'] ?? 'A',
            ],
            'addition' => [
                'text' => '(+) Outros acréscimos',
                'value' => $data['addition'] ?? '',
            ],
            'agency_code' => [
                'text' => 'Agência/Código do Beneficiário',
                'value' => $this->formatAgencyAccount(true),
            ],
            'amount' => [
                'text' => 'Quantidade',
                'value' => $data['amount'] ?? '',
            ],
            'assignor' => [
                'text' => 'Beneficiário',
                'value' => $models['assignor.person']->name,
            ],
            'bank_use' => [
                'text' => 'Uso do banco',
                'value' => $data['bank_use'] ?? '',
            ],
            'charged' => [
                'text' => '(=) Valor cobrado',
                'value' => $data['charged'] ?? '',
            ],
            'client' => [
                'text' => 'Pagador',
                'value' => $models['client.person']->name,
            ],
            'client_receipt' => [
                'text' => 'Recibo do Pagador',
            ],
            'cod_down' => [
                'text' => 'Cód. baixa',
            ],
            'compensation' => [
                'text' => 'Ficha de Compensação',
            ],
            'cpf_cnpj' => [
                'text' => 'CPF/CNPJ',
                'value' => $models['assignor.person']->documentFormat(),
            ],
            'currency' => [
                'text' => 'Espécie',
                'value' => $models['currency']->symbol,
            ],
            'cut_here' => [
                'text' => 'Corte na linha pontilhada',
            ],
            'date_document' => [
                'text' => 'Data do documento',
                'value' => self::formatDate($models['title']->stamp),
            ],
            'date_due' => [
                'text' => 'Vencimento',
                'value' => self::formatDate($models['title']->due),
            ],
            'date_process' => [
                'text' => 'Data processameto',
                'value' => date('d/m/Y'),
            ],
            'deduction' => [
                'text' => '(-) Outras deduções',
                'value' => $data['deduction'] ?? '',
            ],
            'demonstrative' => [
                'text' => 'Demonstrativo',
                'value' => $data['demonstrative'] ?? '',
            ],
            'discount' => [
                'text' => '(-) Desconto / Abatimentos',
                'value' => $data['discount'] ?? '',
            ],
            'doc_number' => [
                'text' => 'Número do documento',
                'value' => $doc_number,
            ],
            'doc_number_sh' => [
                'text' => 'Nº documento',
                'value' => $doc_number,
            ],
            'doc_value' => [
                'text' => 'Valor documento',
                'value' => $data['doc_value'] ?? '',
                'value' => $value,
            ],
            'doc_value=' => [
                'text' => '(=) Valor documento',
                'value' => $value,
            ],
            'fine' => [
                'text' => '(+) Mora / Multa',
                'value' => $data['fine'] ?? '',
            ],
            'guarantor' => [
                'text' => 'Sacador/Avalista',
                'value' => ($models['guarantor'] !== null)
                    ? $models['guarantor.person']->name . '     '
                    . $models['guarantor.address']->outputShort()
                    : '',
            ],
            'header_body' => [
                'text' => $data['header_body'] ?? '',
            ],
            'header_info' => [
                'text' => sprintf(
                    "    Linha Digitável:  %s\n    Valor:   %s",
                    $data['digitable'],
                    $value
                ),
            ],
            'header_title' => [
                'text' => $data['header_title'] ?? '',
            ],
            'instructions' => [
                'text' => 'Instruções (Texto de responsabilidade do beneficiário)',
                'value' => $data['instructions'] ?? '',
            ],
            'mech_auth' => [
                'text' => 'Autenticação mecânica',
            ],
            'our_number' => [
                'text' => 'Nosso número',
                'value' => $this->formatOurNumber(true),
            ],
            'payment_place' => [
                'text' => 'Local de pagamento',
                'value' => $data['payment_place'] ?? '',
            ],
            'specie_doc' => [
                'text' => 'Espécie doc.',
                'value' => static::SPECIE_DOC,
            ],
            'wallet' => [
                'text' => 'Carteira',
                'value' => $models['wallet']->symbol,
            ],
        ];

        foreach ($fields as &$field) {
            $field['text'] = utf8_decode($field['text']);
            if (array_key_exists('value', $field)) {
                $field['value'] = utf8_decode($field['value']);
            }
        }
        unset($field);

        return $fields;
    }

    /**
     * Free space, defined by Bank.
     *
     * Here: Our number . Agency/Assignor
     */
    protected function generateFreeSpace()
    {
        return $this->formatOurNumber() . $this->formatAgencyAccount();
    }

    /*
     * For FPDF
     * =========================================================================
     */

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
        $this->Cell(88.5, 5, date('Y-m-d H:i:s O'), 0, 0, 'R');
    }
}
