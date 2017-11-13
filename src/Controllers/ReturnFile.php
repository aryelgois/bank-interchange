<?php
/**
 * This Software is part of aryelgois\BankInterchange and is provided "as is".
 *
 * @see LICENSE
 */

namespace aryelgois\BankInterchange\Controllers;

use aryelgois\BankInterchange as BankI;

/**
 * Interprets Return Files sent by banks
 *
 * @author Aryel Mota Góis
 * @license MIT
 * @link https://www.github.com/aryelgois/bank-interchange
 */
class ReturnFile
{
    /**
     * Contains the Return File, with lines splitted
     *
     * @var string[]
     */
    protected $return_file;

    /**
     * Which Cnab the Return File might be
     *
     * @var string
     */
    protected $cnab;

    /**
     * Commands to update the Database, after validation()
     *
     * @var array[]
     */
    protected $apply_data;

    /**
     * Creates a new ReturnFile Controller object
     *
     * @param string $return_file The Return File to be processed
     *
     * @throws \InvalidArgumentException If $return_file is invalid
     */
    public function __construct($return_file)
    {
        $return_file = explode("\n", str_replace("\r", '', $return_file));

        $return_file = array_filter($return_file);
        if (empty($return_file)) {
            throw new \InvalidArgumentException('Return File is empty');
        }

        $length = array_count_values(array_map('strlen', $return_file));
        if (count($length) !== 1) {
            throw new \InvalidArgumentException(
                'Return File has lines with different length'
            );
        }

        $cnab = array_keys($length)[0];
        if (!in_array($cnab, Cnab::STANDARDS)) {
            throw new \InvalidArgumentException('Invalid CNAB');
        }

        $this->return_file = $return_file;
        $this->cnab = $cnab;
    }

    /**
     * Validates the Return File
     *
     * @return array[] With validation data
     * @return null    For a not implemented Cnab
     */
    public function validate()
    {
        switch ($this->cnab) {
            case 240:
                return $this->validateCnab240();
                break;

            case 400:
                return $this->validateCnab400();
                break;
        }
    }

    /**
     * Validates the CNAB240 Return File
     *
     * @return array[] Notes about the validation
     */
    protected function validateCnab240()
    {
        $result = [
            'errors' => [],
            'info' => [],
            'warnings' => [],
        ];

        foreach ($this->return_file as $registry) {
            # code...
        }

        return $result;
    }

    /**
     * Validates the CNAB400 Return File
     *
     * @return array[] Notes about the validation
     */
    protected function validateCnab400()
    {
        $result = [
            'errors' => [],
            'info' => [],
            'warnings' => [],
        ];

        foreach ($this->return_file as $registry) {
            # code...
        }

        return $result;
    }

    /**
     * Runs commands to update the Database
     */
    public function apply()
    {
        foreach($this->apply_data as $command) {
            # code...
        }
    }
}
