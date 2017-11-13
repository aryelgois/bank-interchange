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
     * Contains the Return File, with rows splitted
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
        /*
         * Splits rows and removes empty rows
         */
        $return_file = array_filter(
            explode("\n", str_replace("\r", '', $return_file))
        );
        if (empty($return_file)) {
            throw new \InvalidArgumentException('Return File is empty');
        }

        /*
         * Rows length
         */
        $lengths = array_map('strlen', $return_file);

        /*
         * Defines CNAB by longest row
         */
        $cnab = max($lengths);
        if (!in_array($cnab, Cnab::STANDARDS)) {
            throw new \InvalidArgumentException(
                'Invalid CNAB: ' . $cnab . ' positions'
            );
        }

        /*
         * Pads shorter rows (maybe are missing ' '. if not, will fail latter)
         */
        $shorter = array_filter($lengths, function ($len) use ($cnab) {
            return $len != $cnab;
        });
        foreach (array_keys($shorter) as $row) {
            $return_file[$row] = str_pad($return_file[$row], $cnab);
        }

        /*
         * Store data
         */
        $this->return_file = $return_file;
        $this->cnab = $cnab;
    }

    /**
     * Validates the Return File
     *
     * @return array[] With validation data
     * @return null    For a not implemented CNAB
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

        $patterns = [
            'file_header'  => '/^(\d{3})(\d{4})0 {9}(\d{1})(\d{14})(\d{20})(\d{5})(.)(\d{12})(.)(.)(.{30})(.{30}) {10}2(\d{8})(\d{6})(\d{6})(\d{3})(\d{5}) {20}( {20}) {29}$/',
            'lot_header'   => '/^(\d{3})(\d{4})1(.)(\d{2}) {2}(\d{3}) (\d{1})(\d{15})(\d{20})(\d{5})(.)(\d{12})(.)(.)(.{30})(.{40})(.{40})(\d{8})(\d{8})(\d{8}) {33}$/',
            'title_t'      => '/^(\d{3})(\d{4})3(\d{5})T (\d{2})(\d{5})(.)(\d{12})(.)(.)(\d{20})(\d)(.{15})(\d{8})(\d{15})(\d{3})(\d{5})(\d)(.{25})(\d{2})(\d)(\d{15})(.{40})(\d{10})(\d{15})(.{10}) {17}$/',
            'title_u'      => '/^(\d{3})(\d{4})3(\d{5})U (\d{2})(\d{15})(\d{15})(\d{15})(\d{15})(\d{15})(\d{15})(\d{15})(\d{15})(\d{8})(\d{8})(.{4})(.{8})(\d{15})(.{30})(\d{3})(\d{20}) {7}$/',
            'lot_trailer'  => '/^(\d{3})(\d{4})5 {9}(\d{6})(\d{6})(\d{17})(\d{6})(\d{17})(\d{6})(\d{17})(\d{6})(\d{17})(.{8}) {117}$/',
            'file_trailer' => '/^(\d{3})(\d{4})9 {9}(\d{6})(\d{6})(\d{6}) {205}$/',
        ];
        $registries = [];

        foreach ($this->return_file as $row => $registry) {
            $matched = false;
            foreach ($patterns as $pattern) {
                if (preg_match($pattern, $registry, $matches)) {
                    $registries[] = array_map('trim', $matches);
                    $matched = true;
                    break;
                }
            }
            if (!$matched) {
                # code...
            }
        }
        return $registries;

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

        $patterns = [
            'file_header'  => '/^02RETORNO(\d{2})(.{15})(\d{20})(.{30})(\d{3})(.{15})(\d{6})(\d{2})(\d{14}) {273}(\d{5})(\d{6})$/',
            'title'        => '/^1(\d{2})(\d{14})(\d{20})(.{25})(\d{20}) {25}(\d{1})(\d{2})(\d{6})(.{10}) {20}(\d{6})(\d{13})(\d{3})(\d{5})(\d{2})(\d{13})(\d{13})(\d{13})(\d{13})(\d{13})(\d{13})(\d{13})(\d{13})(\d{13}) {9}(.)(\d{12})(\d{6})(\d{13})(\d{2})(\d{2}) {54}(\d{2})(\d)(\d{6})$/',
            'file_trailer' => '/^92(\d{2})(\d{3}) {10}(\d{8})(\d{14})(\d{8}) {10}(\d{8})(\d{14})(\d{8}) {10}(\d{8})(\d{14})(\d{8}) {10}(\d{8})(\d{14})(\d{8}) {10}(\d{14})(\d{3})(\d{14})(\d{14})(\d{14})(\d{14}) {144}(\d{6})$/',
        ];
        $registries = [];

        foreach ($this->return_file as $row => $registry) {
            $matched = false;
            foreach ($patterns as $pattern) {
                if (preg_match($pattern, $registry, $matches)) {
                    $registries[] = array_map('trim', $matches);
                    $matched = true;
                    break;
                }
            }
            if (!$matched) {
                # code...
            }
        }
        return $registries;

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
