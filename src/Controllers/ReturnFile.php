<?php
/**
 * This Software is part of aryelgois/bank-interchange and is provided "as is".
 *
 * @see LICENSE
 */

namespace aryelgois\BankInterchange\Controllers;

use aryelgois\BankInterchange as BankI;

/**
 * Controller class for Return Files
 *
 * A ReturnFile is response sent by a Bank to a previous ShippingFile.
 *
 * @author Aryel Mota Góis
 * @license MIT
 * @link https://www.github.com/aryelgois/bank-interchange
 */
abstract class ReturnFile
{
    /**
     * Path to directory with config files
     *
     * @const string
     */
    const CONFIG_PATH = __DIR__ . '/../../config/return_file';

    /**
     * Fully Qualified Model Class name
     *
     * @const string
     */
    const MODEL_CLASS = 'aryelgois\\BankInterchange\\Models\\ReturnFile';

    /**
     * Loads config files and creates a new ReturnFile Model
     *
     * @param string $return_file The Return File to be processed
     * @param string $strategy    How to read the config file:
     *                            - 'combine': mix bank-specific with defaults
     *                            - 'replace': do not use the defaults
     *
     * @return Models\ReturnFile
     *
     * @throws \InvalidArgumentException Multiple cases
     * @throws \RuntimeException         Multiple cases
     * @throws \Exception                On error loading config file
     */
    public static function process($return_file, $strategy = 'combine')
    {
        /*
         * Check strategy
         */
        if (!in_array($strategy, ['combine', 'replace'])) {
            throw new \InvalidArgumentException('Invalid strategy');
        }

        /*
         * Get file header
         */
        $header = substr($return_file, 0, strpos($return_file, "\n"));
        $header = str_replace("\r", '', $header);
        if ($header == '') {
            throw new \InvalidArgumentException('Could not find File Header');
        }

        /*
         * Define bank code
         */
        if (strlen($header) <= 240) {
            $bank_code = substr($header, 0, 3);
        } elseif (strlen($header) == 400) {
            $bank_code = substr($header, 76, 3);
        } else {
            throw new \InvalidArgumentException('Could not define Bank code');
        }

        /*
         * List config files
         */
        $configs = scandir(static::CONFIG_PATH);

        /*
         * Load bank config
         */
        $config = [];
        $bank_config = 'config_' . $bank_code . '.json';
        if (in_array($bank_config, $configs)) {
            $path = static::CONFIG_PATH . '/' . $bank_config;
            $config = json_decode(file_get_contents($path), true);
            if ($config === null) {
                throw new \RuntimeException('Failed to load Bank config');
            }
        } elseif ($strategy == 'replace') {
            throw new \RuntimeException('Could not find Bank config');
        }

        /*
         * Load defaults
         */
        if ($strategy == 'combine') {
            $path = static::CONFIG_PATH . '/config.json';
            $defaults = json_decode(file_get_contents($path), true);
            if ($defaults === null) {
                throw new \RuntimeException('Failed to load defaults');
            }
            $config = array_replace_recursive($defaults, $config);
        }

        /*
         * Check config
         */
        if (empty($config)) {
            throw new \Exception('Error loading config file');
        }

        /*
         * Create Return File Model
         */
        $model_class = static::MODEL_CLASS;
        return (new $model_class($return_file, $config));
    }
}
