<?php
/**
 * This Software is part of aryelgois\cnab240 and is provided "as is".
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
 * @link https://www.github.com/aryelgois/cnab240
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
     * Creates a new Title object from data in a Database
     *
     * @see data/database.sql
     *
     * @param Database $db_cnab240 Database with an `titles` table
     * @param Database $db_address Address Database from aryelgois\databases
     * @param integer  $id         Title's id in the table
     * @param Payer[]  &$cache     For reuse of Payer objects, optional
     *
     * @throws RuntimeException If it can not load title from database
     */
    public function __construct(
        Utils\Database $db_cnab240,
        Utils\Database $db_address,
        $id,
        &$cache = null
    ) {
        // load from database
        $title = Utils\Database::fetch($db_cnab240->query("SELECT * FROM `titles` WHERE `id` = " . $id));
        if (empty($title)) {
            throw new \RuntimeException('Could not load title from database');
        }
        $title = $title[0];
        
        // is there an easier way?
        $this->id = $title['id'];
        $this->onum = $title['onum'];
        $this->wallet = $title['wallet'];
        $this->doc_type = $title['doc_type'];
        $this->kind = $title['kind'];
        $this->specie = $title['specie'];
        $this->value = (float)$title['value'];
        $this->iof = (float)$title['iof'];
        $this->rebate = (float)$title['rebate'];
        $this->description = $title['description'];
        $this->due = $title['due'];
        $this->stamp = $title['stamp'];
        
        // fine and discount
        $default = ['type' => 3, 'date' => '', 'value' => 0];
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
        
        $this->payer = self::newPayer($db_cnab240, $db_address, $title['payer'], $cache);
        $this->guarantor = ($title['guarantor'] !== null)
            ? self::newPayer($db_cnab240, $db_address, $title['guarantor'], $cache)
            : null;
    }
    
    /**
     * Creates a new Payer object from data in a Database or reuse from cache
     *
     * @see data/database.sql
     *
     * @param Database $db_cnab240 Database with an `payers` table
     * @param Database $db_address Address Database from aryelgois\databases
     * @param integer  $id         Payer's id in the table
     * @param Payer[]  &$cache     For reuse of Payer objects, optional
     *
     * @return Payer
     */
    protected static function newPayer(
        Utils\Database $db_cnab240,
        Utils\Database $db_address,
        $id,
        &$cache = null
    ) {
        if (is_array($cache) && array_key_exists($id, $cache)) {
            $payer = $cache[$id];
        } else {
            $payer = new namespace\Payer($db_cnab240, $db_address, $id);
            if (is_array($cache)) {
                $cache[$id] = $payer;
            }
        }
        return $payer;
    }
}
