<?php
/**
 * This Software is part of aryelgois\BankInterchange and is provided "as is".
 *
 * @see LICENSE
 */

namespace aryelgois\BankInterchange\Controllers;

use aryelgois\Utils;
use aryelgois\BankInterchange as BankI;
use VRia\Utils\NoDiacritic;

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
     * Contains the Return File, with lines splitted
     *
     * @var string[]
     */
    protected $return_file;

    /**
     * Which CNAB the Return File might be
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
     * Which registries are enabled in the matcher
     *
     * @var string[]
     */
    protected $matcher_enabled;

    /**
     * All the data matched from Return File
     *
     * @var array[]
     */
    protected $matches;

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
         * Splits lines and removes empty lines
         */
        $return_file = array_filter(
            explode("\n", str_replace("\r", '', $return_file))
        );
        if (empty($return_file)) {
            throw new \InvalidArgumentException('Return File is empty');
        }

        /*
         * Lines length
         */
        $lengths = array_map('strlen', $return_file);

        /*
         * Defines CNAB by longest line
         */
        $cnab = max($lengths);
        if (!array_key_exists($cnab, $matcher)) {
            throw new \InvalidArgumentException(
                'Invalid CNAB: ' . $cnab . ' positions'
            );
        }

        /*
         * Pads shorter lines (maybe are missing ' '. if not, will fail latter)
         */
        $shorter = array_filter($lengths, function ($len) use ($cnab) {
            return $len != $cnab;
        });
        foreach (array_keys($shorter) as $line) {
            $return_file[$line] = str_pad($return_file[$line], $cnab);
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
        $this->registries = [
            'meta' => [],
            'lots' => [],
        ];
    }

    /**
     * Validates the Return File registries
     *
     * @return array[] With validation messages
     * @return null    For a not implemented CNAB
     */
    public function validate()
    {
        $this->matcher_enabled = ['file_header'];
        $matcher_enabled_old = null;
        foreach ($this->return_file as $line => $registry) {
            $matched = false;
            if ($matcher_enabled_old != $this->matcher_enabled) {
                $matcher = Utils\Utils::arrayWhitelist(
                    $this->matcher,
                    $this->matcher_enabled
                );
                $matcher_enabled_old = $this->matcher_enabled;
            }
            foreach ($matcher as $matcher_name => $matcher_data) {
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
                $this->process($line, $matched, $match);
            } else {
                $this->message['error'][] = 'Registry mismatch on line ' . ($line + 1);
            }
        }
        return $this->message;
    }

    /**
     * Specific operations
     */
    protected function process($line, $matched, $match)
    {
        switch ($this->cnab) {
            case 240:
                switch ($matched) {
                    case 'file_header':
                        $meta = Utils\Utils::arrayWhitelist(
                            $match,
                            [
                                'record_date',
                                'record_time',
                                'file_sequence',
                                'assignor_use',
                            ]
                        );

                        $assignor = $this->findAssignor($line, $match);
                        if ($assignor) {
                            $meta['assignor'] = $assignor;
                        }

                        $this->registries['meta'] = $meta;
                        $this->matcher_enabled = ['lot_header', 'file_trailer'];
                        break;

                    case 'lot_header':
                        $lot = [
                            'meta' => Utils\Utils::arrayWhitelist(
                                $match,
                                [
                                    'message1',
                                    'message2',
                                    'shipping_return_number',
                                    'shipping_return_record_date',
                                    'credit_date',
                                ]
                            ),
                            'data' => [],
                            'registries' => 1,
                        ];

                        $assignor = $this->findAssignor($line, $match);
                        if ($assignor) {
                            $lot['meta']['assignor'] = $assignor;
                        }

                        $this->registries['lots'][$match['lot']] = $lot;
                        $this->matcher_enabled = [
                            'title_t',
                            'lot_trailer'
                        ];
                        break;

                    case 'title_t':
                        $data = Utils\Utils::arrayWhitelist(
                            $match,
                            [
                                'movement',
                                'doc_number',
                                'our_number',
                                'due',
                                'value',
                                'receiver_bank',
                                'receiver_agency',
                                'receiver_agency_cd',
                                'assignor_use',
                                'specie',
                                'contract',
                                'tax',
                                'occurrence',
                            ]
                        );

                        $title = $this->findTitle($line, $match);
                        if ($title) {
                            $data['title'] = $title;
                        }

                        $this->registries['lots'][$match['lot']]['data'][$match['lot_registry']] = $data;
                        $this->registries['lots'][$match['lot']]['registries']++;
                        $this->matcher_enabled = [
                            'title_u',
                            'lot_trailer'
                        ];
                        break;

                    case 'title_u':
                        $data = Utils\Utils::arrayWhitelist(
                            $match,
                            [
                                'value_paid',
                                'value_net',
                                'expenses',
                                'credits',
                                'occurrence_date',
                                'credit_date',
                                'payer_occurrence_code',
                                'payer_occurrence_date',
                                'payer_occurrence_value',
                                'payer_occurrence_detail',
                                'corresponding_bank',
                                'corresponding_bank_our_number',
                            ]
                        );

                        $this->registries['lots'][$match['lot']]['data'][$match['lot_registry']] = array_merge(
                            $this->registries['lots'][$match['lot']]['data'][$match['lot_registry']] ?? [],
                            $data
                        );
                        $this->registries['lots'][$match['lot']]['registries']++;
                        $this->matcher_enabled = [
                            'title_t',
                            'lot_trailer'
                        ];
                        break;

                    case 'lot_trailer':
                        $data = Utils\Utils::arrayWhitelist(
                            $match,
                            [
                                'title_cs_count',
                                'title_cs_total',
                                'title_cv_count',
                                'title_cv_total',
                                'title_cc_count',
                                'title_cc_total',
                                'title_cd_count',
                                'title_cd_total',
                                'warning',
                            ]
                        );

                        if (++$this->registries['lots'][$match['lot']]['registries'] != $match['lot_registry_count']) {
                            $this->message['error'][] = "Lot {$match['lot']} has different ammount of registries from it's Trailer";
                        }

                        $this->registries['lots'][$match['lot']]['meta'] = array_merge(
                            $this->registries['lots'][$match['lot']]['meta'] ?? [],
                            $data
                        );
                        $this->matcher_enabled = [
                            'lot_header',
                            'file_trailer'
                        ];
                        break;

                    case 'file_trailer':
                        if (count($this->registries['lots']) != $match['lot_count']) {
                            $this->message['error'][] = "Lot count differ";
                        }

                        $registry_count = array_sum(
                            array_column(
                                $this->registries['lots'],
                                'registries'
                            )
                        );
                        if ($registry_count + 2 != $match['registry_count']) {
                            $this->message['error'][] = "Registry count differ";
                        }

                        $this->matcher_enabled = [];
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
        $this->matches[] = $match;
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

    /*
     * Helper
     * =========================================================================
     */

    protected function findAssignor($line, $match)
    {
        $assignor = new BankI\Models\Assignor;
        $database = $assignor->getDatabase();

        // Find based on document and covenant
        $result = $database->select(
            'assignors',
            [
                '[><]people' => ['person' => 'id'],
            ],
            ['assignors.id'],
            [
                'people.document[~]' => (int) $match['assignor_document'],
                'assignors.covenant[~]' => (int) $match['assignor_covenant'],
            ]
        );

        if (empty($result)) {
            $this->message['warning'][] = 'Assignor in line ' . ($line + 1) . ' not found in the Database';
        } else {
            $id = $result[0]['id'];
            if (count($result) > 1) {
                $this->message['warning'][] = 'Multiple Assignors found in line ' . ($line + 1) . '. Using id $id';
            }
            $assignor->load($id);
            return $assignor;
        }
    }

    protected function findTitle($line, $match)
    {
        $title = new BankI\Models\Title;

        $loaded = $title->load(
            ['our_number[~]' => (int) $match['our_number']]
        );

        if (!$loaded) {
            $this->message['warning'][] = 'Our number in line ' . ($line + 1) . ' not found in the Database';
        } else {
            return $title;
        }
    }
}
