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
use aryelgois\BankInterchange\FilePack;
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
abstract class View extends FPDF implements FilePack\ViewInterface
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
     * Dictionary of terms used in the billet
     *
     * @var string[]
     */
    protected $dictionary = [
        'accept'         => 'Aceite',
        'addition'       => '(+) Outros acréscimos',
        'agency_code'    => 'Agência/Código do Beneficiário',
        'amount'         => 'Quantidade',
        'assignor'       => 'Beneficiário',
        'bank_use'       => 'Uso do banco',
        'charged'        => '(=) Valor cobrado',
        'client'         => 'Pagador',
        'client_receipt' => 'Recibo do Pagador',
        'cod_down'       => 'Cód. baixa',
        'compensation'   => 'Ficha de Compensação',
        'cpf_cnpj'       => 'CPF/CNPJ',
        'currency'       => 'Espécie',
        'cut_here'       => 'Corte na linha pontilhada',
        'date_document'  => 'Data do documento',
        'date_due'       => 'Vencimento',
        'date_process'   => 'Data processameto',
        'deduction'      => '(-) Outras deduções',
        'demonstrative'  => 'Demonstrativo',
        'discount'       => '(-) Desconto / Abatimentos',
        'doc_number'     => 'Número do documento',
        'doc_number_sh'  => 'Nº documento',
        'doc_value'      => 'Valor documento',
        'doc_value='     => '(=) Valor documento',
        'doc_valueU'     => 'Valor documento',
        'fine'           => '(+) Mora / Multa',
        'guarantor'      => 'Sacador/Avalista',
        'header_info'    => "    Linha Digitável:  %s\n    Valor:   %s",
        'instructions'   => 'Instruções (Texto de responsabilidade do beneficiário)',
        'kind'           => 'Espécie doc.',
        'mech_auth'      => 'Autenticação mecânica',
        'our_number'     => 'Nosso número',
        'payment_place'  => 'Local de pagamento',
        'wallet'         => 'Carteira',
    ];

    /**
     * Contains fields to be drawn in the billet
     *
     * @var array[]
     */
    protected $fields = [];

    /**
     * Paths to directories with logos
     *
     * @var string[]
     */
    protected $logos = [];

    /**
     * Holds data from database and manipulates some tables
     *
     * @var BankInterchange\Models\Title
     */
    protected $title;

    /**
     * Creates a new Billet View object
     *
     * @param Models\Title $title Holds data for the bank billet
     * @param string[]     $data  Additional data for the bank billet
     * @param string[]     $logos Paths to directories with logos
     */
    public function __construct(
        BankInterchange\Models\Title $title,
        array $data,
        array $logos
    ) {
        $this->updateDictionary();
        $this->dictionary = array_map('utf8_decode', $this->dictionary);

        $this->title = $title;

        $value = $title->value
            + ($title->tax_included ? 0 : $title->tax_value);

        $this->data = array_merge(
            $data,
            ['value' => (float) $value],
            $this->generateBarcode($value)
        );

        $this->fields = $this->generateFields();
        $this->logos = $logos;

        parent::__construct();
        $this->AliasNbPages('{{ total_pages }}');
        $this->SetLineWidth(static::DEFAULT_LINE_WIDTH);
        $this->drawBillet();
    }

    /*
     * FilePack\ViewInterface
     * =========================================================================
     */

    /**
     * Generates a filename (without extension)
     *
     * @return string
     */
    public function filename()
    {
        $title = $this->title;
        return $title->assignment->id . '-' . $title->doc_number;
    }

    /**
     * Returns the View contents
     *
     * @return string
     */
    public function getContents()
    {
        return $this->Output('S');
    }

    /**
     * Outputs the View with appropriated headers
     *
     * @param string $filename File name to be outputed
     */
    public function outputFile(string $filename)
    {
        $this->Output('I', $filename);
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
        $data = $this->data;
        $dict = $this->dictionary;

        $title = utf8_decode($data['header_title']);
        $body = utf8_decode($data['header_body']);
        $info = sprintf(
            $dict['header_info'],
            $data['digitable'],
            $this->formatMoney($data['value'])
        );

        $this->billetSetFont('cell_data');

        if (strlen($title)) {
            $this->Cell(177, 3, $title, 0, 1, 'C');
            $this->Ln(2);
        }

        if (strlen($body)) {
            $this->MultiCell(177, 3, $body);
            $this->Ln(2);
        }

        $this->billetSetFont('digitable');
        $this->MultiCell(177, 3.5, $info);
        $this->Ln(4);
    }

    /**
     * Draws the Billhead
     */
    protected function drawBillhead()
    {
        $assignment = $this->title->assignment;
        $assignor = $assignment->assignor;
        $person = $assignor->person;

        $this->Ln(2);

        $logo = self::findFile("assignors/$person->id.*", $this->logos);
        if ($logo !== null) {
            $y = $this->GetY();
            $this->Image($logo, null, null, 40, 0, '', $assignor->url);
            $y1 = $this->GetY();
            $this->SetXY(50, $y);
        }

        $text = $person->name . "\n"
            . $person->getFormattedDocument() . "\n"
            . $assignment->address->outputLong();

        $this->billetSetFont('billhead');
        $this->MultiCell(103.2, 2.5, utf8_decode($text));
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
            $this->Cell(177, 4, $text, 0, 1, $align);
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
        $bank = $this->title->assignment->bank;
        $this->Ln(3);

        $logo = self::findFile("banks/$bank->id.*", $this->logos);
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
     * Produces a bar code from string of digits in style "2 of 5 intercalated"
     *
     * @param float  $baseline Corresponds to the width of a wide bar
     * @param float  $height   Bar height
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
            $code .= implode('', Utils\Utils::arrayInterpolate(
                $map[$data[$i]],
                $map[$data[$i + 1]]
            ));
        }
        $code .= '100'; // Trailing value

        // Draw
        $this->SetFillColor(0);
        $x = $this->GetX();
        $y = $this->GetY();
        $draw = true;
        foreach (str_split($code, 1) as $bit) {
            $width = ($bit == '0' ? $narrow : $wide);
            if ($draw) {
                $this->Rect($x, $y, $width, $height, 'F');
            }
            $x += $width;
            $draw = !$draw;
        }
        $this->Ln($height);
    }

    /**
     * Draws a generic table
     *
     * Structure:
     *
     * ```
     * assignor | agency_code | currency       | amount | our_number
     * doc_number | cpf_cnpj | date_due        | doc_value
     * discount | deduction | fine | additions | charged
     * client
     * ```
     *
     * @param mixed   $border Applied on each row @see FPDF::Cell() border
     * @param float[] $widths List of widths for each cell (total: 15 cells)
     */
    protected function drawGenericTable1($border, array $widths)
    {
        $table = [
            [
                ['width' => $widths[0], 'field' => 'assignor'],
                ['width' => $widths[1], 'field' => 'agency_code'],
                ['width' => $widths[2], 'field' => 'currency'],
                ['width' => $widths[3], 'field' => 'amount'],
                ['width' => $widths[4], 'field' => 'our_number', 'align' => 'R'],
            ],
            [
                ['width' => $widths[5], 'field' => 'doc_number'],
                ['width' => $widths[6], 'field' => 'cpf_cnpj'],
                ['width' => $widths[7], 'field' => 'date_due'],
                ['width' => $widths[8], 'field' => 'doc_value', 'align' => 'R'],
            ],
            [
                ['width' => $widths[9],  'field' => 'discount',  'align' => 'R'],
                ['width' => $widths[10], 'field' => 'deduction', 'align' => 'R'],
                ['width' => $widths[11], 'field' => 'fine',      'align' => 'R'],
                ['width' => $widths[12], 'field' => 'addition',  'align' => 'R'],
                ['width' => $widths[13], 'field' => 'charged',   'align' => 'R'],
            ],
            [
                ['width' => $widths[14], 'field' => 'client'],
            ],
        ];
        foreach ($table as $row) {
            $this->drawRow($row, $border);
        }
    }

    /**
     * Draws a generic table
     *
     * Structure:
     *
     * ```
     * payment_place                                                | date_due
     * assignor                                                     | agency_code
     * date_document | doc_number_sh | kind | accept | date_process | our_number
     * bank_use | wallet | currency | amount | doc_valueU           | doc_value=
     * demonstrative or instructions                                | discount
     *                                                              | deduction
     *                                                              | fine
     *                                                              | additions
     *                                                              | charged
     * ```
     *
     * @param string  $big_cell Tells which information goes in the big cell
     *                          Domain: 'demonstrative' or 'instructions'
     * @param mixed   $border   Applied on each row @see FPDF::Cell() border
     * @param float[] $widths   List of widths for each cell (total: 18 cells)
     */
    protected function drawGenericTable2(
        string $big_cell,
        $border,
        array $widths
    ) {
        $table = [
            [
                ['width' => $widths[0], 'field' => 'payment_place'],
                ['width' => $widths[1], 'field' => 'date_due', 'align' => 'R'],
            ],
            [
                ['width' => $widths[2], 'field' => 'assignor'],
                ['width' => $widths[3], 'field' => 'agency_code', 'align' => 'R'],
            ],
            [
                ['width' => $widths[4], 'field' => 'date_document'],
                ['width' => $widths[5], 'field' => 'doc_number_sh'],
                ['width' => $widths[6], 'field' => 'kind'],
                ['width' => $widths[7], 'field' => 'accept'],
                ['width' => $widths[8], 'field' => 'date_process'],
                ['width' => $widths[9], 'field' => 'our_number', 'align' => 'R'],
            ],
            [
                ['width' => $widths[10], 'field' => 'bank_use'],
                ['width' => $widths[11], 'field' => 'wallet'],
                ['width' => $widths[12], 'field' => 'currency'],
                ['width' => $widths[13], 'field' => 'amount'],
                ['width' => $widths[14], 'field' => 'doc_valueU'],
                ['width' => $widths[15], 'field' => 'doc_value=', 'align' => 'R'],
            ],
            [
                ['width' => $widths[16], 'field' => $big_cell],
                [
                    'width' => $widths[17],
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
            $this->drawRow($row, $border);
        }
    }

    /**
     * Inserts row of cells
     *
     * Each cell has a field (or multiple fields) and a width. Each field is a
     * key in $this->fields, and optionally tells its alignment.
     *
     * @param array[] $cells      List of cells to bew draw
     * @param mixed   $row_border Row border @see FPDF::Cell() border
     */
    protected function drawRow($cells, $row_border = 1)
    {
        $origin = ['x' => $this->GetX(), 'y' => $this->GetY()];
        $coords = [];
        $x = $origin['x'];
        foreach ($cells as $cell) {
            $fields = (array) $cell['field'];
            $count = count($fields);
            $align = $cell['align'] ?? 'L';
            $width = $cell['width'];
            foreach ($fields as $field) {
                $border = (--$count > 0 ? 'B' : 0);
                $field = $this->fields[$field];
                $title = $field['text'] ?? '';
                $data = $field['value'] ?? '';
                $this->billetSetFont('cell_title');
                $this->Cell($width, 3.5, $title, 0, 2);
                $this->billetSetFont('cell_data');
                if (strpos($data, "\n")) {
                    $this->MultiCell($width, 3.5, $data, $border, $align);
                } else {
                    $this->Cell($width, 3.5, $data, $border, 2, $align);
                }
            }
            $x += $width;
            $coords[] = ['x' => $x, 'y' => $this->GetY()];
            $this->SetXY($x, $origin['y']);
        }

        $height = max(array_column($coords, 'y')) - $origin['y'];
        $width = $coords[count($coords) - 1]['x'] - $origin['x'];

        for ($i = count($cells) - 2; $i >= 0; $i--) {
            $x = $coords[$i]['x'];
            $this->Line($x, $origin['y'], $x, $origin['y'] + $height);
        }

        $this->SetXY($origin['x'], $origin['y']);
        $this->Cell($width, $height, '', $row_border, 1);
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
        return $this->title->assignment->formatAgencyAccount(
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
        $code = $this->title->assignment->bank->code;

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
     * @param number  $value  Value to be formatted
     * @param string  $format @see Models/Currency::format()
     *
     * @return string
     */
    protected function formatMoney($value, $format = 'symbol')
    {
        return $this->title->currency->format($value, $format);
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
            $this->title->our_number,
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
        $title = $this->title;
        $assignment = $title->assignment;

        $our_number = BankInterchange\Utils::padNumber($assignment->agency, 3)
            . BankInterchange\Utils::padNumber($title->our_number, 8);

        return $title->checkDigitOurNumberAlgorithm($our_number);
    }

    /**
     * Calculate the amount of days since 1997-10-07
     *
     * @return string Four digits
     */
    protected function dueFactor()
    {
        $date = \DateTime::createFromFormat('Y-m-d', $this->title->due);
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
     * @param string $file  glob pattern to search inside each path
     * @param array  $paths List of paths to search
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
            $files = glob("$path/$file");
            if (!empty($files)) {
                return $files[0];
            }
        }
    }

    /**
     * Generates the barcode data and its digitable line
     *
     * @param numeric $value Billet value
     *
     * @return string[]
     */
    protected function generateBarcode($value)
    {
        $title = $this->title;

        $value = $title->currency->format($value, 'nomask');
        $barcode = [
            $title->assignment->bank->code,
            $title->getCurrencyCode()->billet,
            '', // Check digit
            $this->dueFactor(),
            BankInterchange\Utils::padNumber($value, 10),
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
        $title = $this->title;
        $assignment = $title->assignment;
        $assignor_person = $title->assignment->assignor->person;

        $doc_number = BankInterchange\Utils::padNumber($title->doc_number, 10);
        $value = $this->formatMoney($data['value']);

        $demonstrative = $this->simpleTemplate($data['demonstrative'] ?? '');
        $instructions = $this->simpleTemplate($data['instructions'] ?? '');

        $guarantor = ($title->guarantor !== null)
            ? $title->guarantor->person->name . '     '
            . $title->guarantor->address->outputShort()
            : '';

        $fields = [
            'accept'        => $title->accept,
            'addition'      => $data['addition'] ?? '',
            'agency_code'   => $this->formatAgencyAccount(true),
            'amount'        => $data['amount'] ?? '',
            'assignor'      => $assignor_person->name,
            'bank_use'      => $data['bank_use'] ?? '',
            'charged'       => $data['charged'] ?? '',
            'client'        => $title->client->person->name,
            'cpf_cnpj'      => $assignor_person->getFormattedDocument(),
            'currency'      => $title->currency->symbol,
            'date_document' => self::formatDate($title->emission),
            'date_due'      => self::formatDate($title->due),
            'date_process'  => date('d/m/Y'),
            'deduction'     => $data['deduction'] ?? '',
            'demonstrative' => $demonstrative,
            'discount'      => $data['discount'] ?? '',
            'doc_number'    => $doc_number,
            'doc_number_sh' => $doc_number,
            'doc_value'     => $value,
            'doc_value='    => $value,
            'doc_valueU'    => $data['doc_valueU'] ?? '',
            'fine'          => $data['fine'] ?? '',
            'guarantor'     => $guarantor,
            'instructions'  => $instructions,
            'kind'          => $title->kind->symbol,
            'our_number'    => $this->formatOurNumber(true),
            'payment_place' => $data['payment_place'] ?? '',
            'wallet'        => $assignment->wallet->symbol,
        ];

        $result = [];
        foreach ($fields as $field_key => $field_value) {
            $result[$field_key] = [
                'text' => $this->dictionary[$field_key],
                'value' => utf8_decode($field_value),
            ];
        }
        return $result;
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

    /**
     * Expands a template tag to a $title column
     *
     * NOTE:
     * - Some columns have specific formatting that is applied automatically
     * - If the tag points to a Medools\Model, its primary key is returned
     *
     * @param string[] $match Match from preg_replace_callback()
     *
     * @return string
     */
    protected function parseTemplateTag(array $match)
    {
        $keys = explode('->', $match[1]);

        $previous = null;
        $model = $this->title;

        foreach ($keys as $key) {
            $previous = $model;
            $model = $model->{$key};
        }

        if ($model instanceof Medools\Model) {
            return implode('-', $model->getPrimaryKey());
        }

        switch ($key) {
            case 'billet_tax':
            case 'discount1_value':
            case 'discount2_value':
            case 'discount3_value':
            case 'fine_value':
            case 'interest_value':
            case 'ioc_iof':
            case 'rebate':
            case 'tax_value':
            case 'value_paid':
            case 'value':
                $model = $this->formatMoney($model);
                break;

            case 'discount1_date':
            case 'discount2_date':
            case 'discount3_type_date':
            case 'due':
            case 'emission':
            case 'fine_date':
            case 'interest_date':
                $model = $this->formatDate($model);
                break;

            case 'document':
                $model = $previous->getFormattedDocument();
                break;

            case 'stamp':
            case 'update':
                $model = date('H:i:s d/m/Y', strtotime($model));
                break;

            case 'zipcode':
                $model = Utils\Validation::cep($model);
                break;
        }

        return $model;
    }

    /**
     * Searches and replaces {{ key }} tags
     *
     * The key must be a valid $title column. Nested Models can be accessed with
     * {{ key->key }}
     *
     * @param string $subject The string to search and replace
     *
     * @return string
     */
    protected function simpleTemplate(string $subject)
    {
        if ($subject === '') {
            return '';
        }

        $result = preg_replace_callback(
            '/{{ ?(\w+(?:->\w+)*) ?}}/',
            [$this, 'parseTemplateTag'],
            $subject
        );

        return trim($result);
    }

    /*
     * Hooks
     * =========================================================================
     */

    /**
     * Modifies $dictionary before its UTF8 decoding
     */
    protected function updateDictionary()
    {
        return;
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
        $this->Cell(88.5, 5, $this->PageNo() . " / $this->AliasNbPages");
        $this->Cell(88.5, 5, date('Y-m-d H:i:s O'), 0, 0, 'R');
    }
}
