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
     * Path to matcher file
     *
     * It contains registry patterns and key maps for each CNAB
     *
     * @const string
     */
    const MATCHER = __DIR__ . '/../../config/return_file_matcher.json';

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
     * All the data to match the CNAB registries
     *
     * @var array[]
     */
    protected $matcher;

    /**
     * Human readable messages post validation
     *
     * @var string[]
     */
    protected $message;

    /**
     * Registries data
     *
     * @var array[]
     */
    protected $registries;

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
     * @throws \RuntimeException         If could not load matcher
     * @throws \InvalidArgumentException If $return_file is invalid
     */
    public function __construct($return_file)
    {
        /*
         * Load matcher
         */
        $matcher = json_decode(file_get_contents(static::MATCHER), true);
        if ($matcher === null) {
            throw new \RuntimeException('Could not load matcher');
        }

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
        if (!array_key_exists($cnab, $matcher)) {
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
        $this->matcher = $matcher[$cnab];
        $this->message = [
            'error' => [],
            'info' => [],
            'warning' => [],
        ];
        $this->registries = [];
    }

    /**
     * Validates the Return File registries
     *
     * @return array[] With validation messages
     * @return null    For a not implemented CNAB
     */
    public function validate()
    {
        foreach ($this->return_file as $row => $registry) {
            $matched = false;
            foreach ($this->matcher as $matcher_name => $matcher_data) {
                if (preg_match($matcher_data['pattern'], $registry, $matches)) {
                    $match = array_combine(
                        $matcher_data['map'],
                        array_map('trim', array_slice($matches, 1))
                    );
                    $matched = $matcher_name;
                    break;
                }
            }

            if ($matched) {
                $this->process($matched, $match);
            } else {
                $this->message['error'][] = "Row $row doesn't match any"
                                          . " CNAB$this->cnab registry";
            }
        }
        return $this->message;
    }

    /**
     * Specific operations
     */
    protected function process($matched, $match)
    {
        switch ($this->cnab) {
            case 240:
                switch ($matched) {
                    case 'file_header':
                        # code...
                        break;

                    case 'lot_header':
                        # code...
                        break;

                    case 'title_t':
                        # code...
                        break;

                    case 'title_u':
                        # code...
                        break;

                    case 'lot_trailer':
                        # code...
                        break;

                    case 'file_trailer':
                        # code...
                        break;
                }
                break;

            case 400:
                switch ($matched) {
                    case 'file_header':
                        # code...
                        break;

                    case 'title':
                        # code...
                        break;

                    case 'file_trailer':
                        # code...
                        break;
                }
                break;
        }
        $this->registries[] = $match;
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
