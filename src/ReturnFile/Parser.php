<?php
/**
 * This Software is part of aryelgois/bank-interchange and is provided "as is".
 *
 * @see LICENSE
 */

namespace aryelgois\BankInterchange\ReturnFile;

use aryelgois\Utils;
use Symfony\Component\Yaml\Yaml;

/**
 * Parses Return Files into an array of Registry instances
 *
 * @author Aryel Mota Góis
 * @license MIT
 * @link https://www.github.com/aryelgois/bank-interchange
 */
class Parser
{
    /**
     * Which CNAB the Return File might be
     *
     * @var int
     */
    protected $cnab;

    /**
     * Loaded parser config
     *
     * @var array[]
     */
    protected $config;

    /**
     * Path to directory with config files
     *
     * @var string
     */
    protected static $config_path;

    /**
     * Contains Return File registries
     *
     * @var string[]
     */
    protected $return_file;

    /**
     * Contains parsed registries
     *
     * @var array[]
     */
    protected $result;

    /**
     * Creates a new return file Parser Object
     *
     * @param string $raw Return File to be parsed
     *
     * @throws \BadMethodCallException       If called before setConfigPath()
     * @throws \InvalidArgumentException     If Return File is empty
     * @throws \RuntimeException             If config file does not exist
     * @throws Yaml\Exception\ParseException If could not load config file
     */
    public function __construct(string $raw)
    {
        if (self::$config_path === null) {
            throw new \BadMethodCallException('Config path is null');
        }

        $return_file = explode("\n", rtrim(str_replace("\r", '', $raw), "\n"));
        if (empty($return_file)) {
            throw new \InvalidArgumentException('Return File is empty');
        }

        $length = max(array_map('strlen', $return_file));
        foreach ($return_file as &$line) {
            $line = str_pad($line, $length);
        }
        unset($line);

        $cnab = ($length <= 240 ? 240 : 400);
        $bank_code = substr($return_file[0], ($cnab === 240 ? 0 : 76), 3);

        $config_file = self::$config_path . "/cnab$cnab/$bank_code.yml";
        if (file_exists($config_file)) {
            $config = Yaml::parseFile($config_file);
        } else {
            $message = "Config file for Bank $bank_code in CNAB$cnab not found";
            throw new \RuntimeException($message);
        }

        $this->cnab = $cnab;
        $this->config = $config;
        $this->return_file = $return_file;

        $this->result = self::parse($config['structure'])['registries'];
    }

    /**
     * Outputs parsed registries
     *
     * @return array[]
     */
    public function output()
    {
        return $this->result;
    }

    /**
     * Recursively follows the Return File structure to extract its fields
     *
     * @param array  $structure Structure tree to be used
     * @param int    $offset    Current item in $return_file
     *
     * @return array With keys 'offset' and 'registries'
     *
     * @throws \DomainException For invalid structure amount
     * @throws ParseException   For invalid registry
     */
    protected function parse(array $structure, int $offset = null)
    {
        $result = [];
        $offset = $offset ?? 0;

        foreach ($structure as $registry_group) {
            $type = reset($registry_group);
            $amount = key($registry_group);

            if (!in_array($amount, ['unique', 'multiple'])) {
                $message = "Invalid structure amount '$amount'";
                throw new \DomainException($message);
            }

            if (!is_array($type)) {
                $registries = Utils\Utils::arrayWhitelist(
                    $this->config['registries'],
                    explode(' ', $type)
                );
            }

            do {
                $previous_offset = $offset;
                if (is_array($type)) {
                    try {
                        $rec = self::parse($type, $offset);
                        $result = array_merge($result, [$rec['registries']]);
                        $offset = $rec['offset'];
                    } catch (ParseException $e) {
                        if ($amount !== 'multiple') {
                            throw $e;
                        }
                    }
                } else {
                    $registry = self::pregRegistry(
                        $this->return_file[$offset],
                        $registries
                    );

                    if ($registry !== null) {
                        $result[] = $registry;
                        $offset++;
                    } elseif ($amount === 'unique') {
                        throw ParseException::pregMismatch(
                            $this->cnab,
                            $registries,
                            $offset + 1
                        );
                    }
                }
            } while ($amount === 'multiple' && $offset > $previous_offset);
        }

        return [
            'offset' => $offset,
            'registries' => $result,
        ];
    }

    /**
     * Extracts fields from a Return File registry to fill a Registry instance
     *
     * @param string $registry   Line with fields to be extracted
     * @param array  $registries Sequence of pattern and map
     *
     * @return Registry On success
     * @return null     On failure
     */
    protected function pregRegistry(
        string $registry,
        array $registries
    ) {
        $result = null;
        foreach ($registries as $type => $matcher) {
            if (preg_match($matcher['pattern'], $registry, $matches)) {
                $match = array_combine(
                    $matcher['map'],
                    array_map('trim', array_slice($matches, 1))
                );
                $result = new Registry($this->cnab, $type, $match);
                break;
            }
        }
        return $result;
    }

    /**
     * Sets path to directory with config files
     *
     * @param string $path Path to directory with config files
     */
    public static function setConfigPath(string $path)
    {
        self::$config_path = $path;
    }
}
