<?php
/**
 * This Software is part of aryelgois\BankInterchange and is provided "as is".
 *
 * @see LICENSE
 */

namespace aryelgois\BankInterchange\Models;

use aryelgois\Utils;
use aryelgois\BankInterchange as BankI;

/**
 * A basic BankInterchange model
 *
 * @author Aryel Mota Góis
 * @license MIT
 * @link https://www.github.com/aryelgois/BankInterchange
 */
abstract class Model
{
    /**
     * Interfaces a connection to `address` database
     *
     * @var Database
     */
    protected $db_address;
    
    /**
     * Interfaces a connection to `bank_interchange` database
     *
     * @var Database
     */
    protected $db_banki;
    
    /**
     * Assignor's data
     *
     * @var Assignor
     */
    public $assignor;
    
    /**
     * Bank's data
     *
     * @var Bank
     */
    public $bank;
    
    /**
     * Creates a new ShippingFile Model object
     *
     * @param Database $db_address  An interface to `address` database
     * @param Database $db_banki    An interface to `bank_interchange` database
     * @param integer  $assignor_id Assignor's id from database
     */
    public function __construct(
        Utils\Database $db_address,
        Utils\Database $db_banki,
        $assignor_id
    ) {
        $this->db_address = $db_address;
        $this->db_banki = $db_banki;
        
        // fetch assignor and bank
        $this->assignor = new BankI\Objects\Assignor($db_address, $db_banki, $assignor_id);
        $this->bank = new BankI\Objects\Bank($db_banki, $this->assignor->bank);
    }
    
    /**
     * Locks a table and returns next auto increment index
     *
     * @param string $table The table's name to be locked
     *
     * @return integer
     */
    public function getNextId($table)
    {
        $this->db_banki->query("SET autocommit = 0; LOCK TABLES `" . $table . "` WRITE");
        $result = Utils\Database::fetch($this->db_banki->query("SHOW TABLE STATUS LIKE '" . $table . "'"));
        return $result[0]['Auto_increment'];
    }
    
    /**
     * Inserts a record of a previously generated Shipping File
     *
     * @param string $filename Shipping File name
     *
     * @return true For success or string[] of errors for failure
     */
    public function insertFile($filename)
    {
        $query = "INSERT INTO `shipping_files` (`filename`) VALUES (?)";
        $stmt = $this->db_banki->connect->prepare($query);
        $stmt->bind_param('s', $filename);
        $stmt->execute();
        if ($stmt->error !== '') {
            return $stmt->error;
        }
        $this->db_banki->query("UNLOCK TABLES `shipping_files` WRITE");
        return true;
    }
    
    /**
     * Update table's rows to a given status
     *
     * @param string    $table  The table's name to be afected
     * @param integer[] $ids    List of afected rows
     * @param integer   $status New status for all listed rows
     *
     * @return true For success or string[] of errors for failure
     */
    public function updateStatus($table, $ids, $status)
    {
        $err = [];
        $query = "UPDATE `" . $table . "` SET `status` = ?, `update` = CURRENT_TIMESTAMP WHERE `id` = ?";
        $stmt = $this->db_banki->connect->prepare($query);
        $stmt->bind_param('ii', $status, $id);
        foreach ($ids as $id) {
            $stmt->execute();
            if ($stmt->error !== '') {
                $err[] = $stmt->error;
            }
        }
        if (!empty($err)) {
            return $err;
        }
        return true;
    }
}
