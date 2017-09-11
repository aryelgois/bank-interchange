<?php
/**
 * This Software is part of aryelgois\BankInterchange and is provided "as is".
 *
 * @see LICENSE
 */

namespace aryelgois\BankInterchange\Objects;

use aryelgois\Utils;

/**
 * A Title represents something a Payer got from an Assignor.
 *
 * It might be one or products/services
 *
 * @author Aryel Mota Góis
 * @license MIT
 * @link https://www.github.com/aryelgois/BankInterchange
 */
class Title
{
    /**
     * Title's id
     *
     * @var integer
     */
    public $id;
    
    /**
     * Title's "our number"
     *
     * @var integer
     */
    public $onum;
    
    /**
     * Title's wallet
     *
     * @var integer
     */
    public $wallet;
    
    /**
     * Title's type of document
     *
     * @var string
     */
    public $doc_type;
    
    /**
     * Title's kind
     *
     * @var integer
     */
    public $kind;
    
    /**
     * Specie used in the Title
     *
     * @var integer
     */
    public $specie;
    
    /**
     * Title's value
     *
     * @var float
     */
    public $value;
    
    /**
     * Title's IOF (Brazilian tax)
     *
     * @var float
     */
    public $iof;
    
    /**
     * Title's rebate
     *
     * @var float
     */
    public $rebate;
    
    /**
     * Title's description
     *
     * @var string
     */
    public $description;
    
    /**
     * Title's due
     *
     * @var string
     */
    public $due;
    
    /**
     * When Title was generated
     *
     * @var string
     */
    public $stamp;
    
    /**
     * Fine details
     *
     * @var mixed[]
     */
    public $fine;
    
    /**
     * Discount details
     *
     * @var mixed[]
     */
    public $discount;
    
    /**
     * Who the Title is destined
     *
     * @var Payer
     */
    public $payer;
    
    /**
     * Someone that would be charged if the Payer could not pay
     *
     * @var Payer
     */
    public $guarantor;
    
    /**
     * Creates a new Title object from data in an array
     *
     * The Database connections are used to load the payer
     *
     * NOTES:
     * - $title keys should be the same as `titles` columns
     * - Due must be between 1997-10-07 and 2025-02-21, inclusives; or should be
     *   empty/with a message
     *
     * @see data/database.sql
     *
     * @param Database $db_address Address Database from aryelgois\databases
     * @param Database $db_banki   Database with an `payers` table
     * @param mixed[   $title      Title's data
     * @param Payer[]  &$cache     For reuse of Payer objects, optional
     */
    public function __construct(
        Utils\Database $db_address,
        Utils\Database $db_banki,
        $title,
        &$cache = null
    ) {
        // is there an easier way?
        $this->id = $title['id'] ?? null; // MAYBE
        $this->onum = $title['onum'];
        $this->wallet = $title['wallet'] ?? 1;
        $this->doc_type = $title['doc_type'];
        $this->kind = $title['kind'];
        $this->specie = $title['specie'];
        $this->value = (float)$title['value'];
        $this->iof = (float)($title['iof'] ?? 0);
        $this->rebate = (float)($title['rebate'] ?? 0);
        $this->description = $title['description'] ?? '';
        $this->due = $title['due'];
        $this->stamp = $title['stamp'] ?? date('Y-m-d H:i:s');
        
        // fine and discount
        $default = ['type' => 3, 'date' => null, 'value' => null];
        if ($title['fine_type'] == 3) {
            $this->fine = $default;
        } else {
            $this->fine = [
                'type' => $title['fine_type'],
                'date' => $title['fine_date'],
                'value' => (float)$title['fine_value']
            ];
        }
        if ($title['discount_type'] == 3) {
            $this->discount = $default;
        } else {
            $this->discount = [
                'type' => $title['discount_type'],
                'date' => $title['discount_date'],
                'value' => (float)$title['discount_value']
            ];
        }
        
        $this->payer = self::newPayer($db_address, $db_banki, $title['payer'], $cache);
        $this->guarantor = (array_key_exists('guarantor', $title) && $title['guarantor'] !== null)
            ? self::newPayer($db_address, $db_banki, $title['guarantor'], $cache)
            : null;
    }
    
    /**
     * Creates a new Title object from data in a Database
     *
     * @see data/database.sql
     *
     * @param Database $db_address Address Database from aryelgois\databases
     * @param Database $db_banki   Database with tables `titles` and `payers`
     * @param integer  $id         Title's id in the table
     * @param Payer[]  &$cache     For reuse of Payer objects, optional
     *
     * @throws RuntimeException If it can not load title from database
     */
    public static function fromDatabase(
        Utils\Database $db_address,
        Utils\Database $db_banki,
        $id,
        &$cache = null
    ) {
        $title = Utils\Database::fetch($db_banki->query("SELECT * FROM `titles` WHERE `id` = " . $id));
        if (empty($title)) {
            throw new \RuntimeException('Could not load title from database');
        }
        
        return new self($db_address, $db_banki, $title[0], $cache);
    }
    
    /**
     * Creates a new Payer object from data in a Database or reuse from cache
     *
     * @see data/database.sql
     *
     * @param Database $db_address Address Database from aryelgois\databases
     * @param Database $db_banki   Database with an `payers` table
     * @param integer  $id         Payer's id in the table
     * @param Payer[]  &$cache     For reuse of Payer objects, optional
     *
     * @return Payer
     */
    protected static function newPayer(
        Utils\Database $db_address,
        Utils\Database $db_banki,
        $id,
        &$cache = null
    ) {
        if (is_array($cache) && array_key_exists($id, $cache)) {
            $payer = $cache[$id];
        } else {
            $payer = new namespace\Payer($db_address, $db_banki, $id);
            if (is_array($cache)) {
                $cache[$id] = $payer;
            }
        }
        return $payer;
    }
}
