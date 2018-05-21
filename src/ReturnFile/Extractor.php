<?php
/**
 * This Software is part of aryelgois/bank-interchange and is provided "as is".
 *
 * @see LICENSE
 */

namespace aryelgois\BankInterchange\ReturnFile;

use aryelgois\BankInterchange\Models;

/**
 * Extracts useful data from parsed Return Files
 *
 * @author Aryel Mota Góis
 * @license MIT
 * @link https://www.github.com/aryelgois/bank-interchange
 */
abstract class Extractor
{
    /**
     * Date format used in the Return file
     *
     * @const string
     */
    const DATE_FORMAT = 'dmY';

    /**
     * Types of charging the Bank provides
     *
     * @const string[]
     */
    const CHARGING_TYPES = [];

    /**
     * Bank model
     *
     * @var Models\Bank
     */
    protected $bank;

    /**
     * CNAB layout found in the Parser
     *
     * @var int
     */
    protected $cnab;

    /**
     * Config used by the parser
     *
     * @var mixed[]
     */
    protected $config;

    /**
     * Parsed registries
     *
     * @var mixed[]
     */
    protected $registries;

    /**
     * Extracted data
     *
     * @var array[]
     */
    protected $result;

    /**
     * Creates a new Extractor object
     *
     * @param Parser $return_file Contains parsed registries to be extracted
     */
    public function __construct(Parser $return_file)
    {
        $parsed = $return_file->output();

        $this->bank = Models\Bank::getInstance([
            'code' => $parsed['bank_code'],
        ]);

        $this->cnab = $parsed['cnab'];

        $this->config = $return_file->getConfig();

        $this->registries = $parsed['registries'];

        $this->result = [
            'return_file' => $this->extractReturnFile(),
            'titles' => $this->extractTitles(),
        ];
    }

    /**
     * Outputs extracted data
     *
     * @return array[]
     */
    public function output()
    {
        return $this->result;
    }

    /*
     * Extractors
     * =========================================================================
     */

    /**
     * Extracts data about the Return File
     *
     * @return mixed[]
     */
    protected function extractReturnFile()
    {
        $header = $this->registries[0];

        return [
            'sequence' => (int) $header->file_sequence,
            'emission' => static::parseDate($header->record_date),
            'charging' => $this->extractCharging(),
        ];
    }

    /**
     * Extracts data about the titles charging
     *
     * @return mixed[]
     */
    abstract protected function extractCharging();

    /**
     * Extracts data about each Title
     *
     * @return array[]
     */
    abstract protected function extractTitles();

    /*
     * Helper
     * =========================================================================
     */

    /**
     * Detects an Assignment in the database using registry's data
     *
     * @param Registry $title Registry with title's data
     *
     * @return Models\Assignment On success
     * @return null              On failure
     */
    protected function detectAssignment(Registry $title)
    {
        return Models\Assignment::getInstance([
            'bank' => $this->bank->id,
            'agency' => $title->assignment_agency,
            'account' => $title->assignment_account,
            'cnab' => (string) $this->cnab,
        ]);
    }

    /**
     * Computes a human readable occurrence from a title
     *
     * NOTE:
     * - The default behavior is to do nothing. It is not abstract because some
     *   children classes may not use it.
     *
     * @param Registry $title Registry with title's data
     *
     * @return string When defined by a child class
     * @return null   When disabled. You should fallback to the occurrence code
     */
    protected function occurrence(Registry $title)
    {
        return;
    }

    /**
     * Parses a date to Y-m-d
     *
     * @param string $date   Date string
     * @param string $format Expected date $format (defaults to DATE_FORMAT)
     * @param string $output Iutput format (defaults to Y-m-d)
     *
     * @return string On success
     * @return null   On failure
     */
    protected static function parseDate(
        string $date,
        string $format = null,
        string $output = null
    ) {
        $d = \DateTime::createFromFormat($format ?? static::DATE_FORMAT, $date);
        return ($d ? $d->format($output ?? 'Y-m-d') : null);
    }
}
