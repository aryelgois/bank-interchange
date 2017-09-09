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
 * A basic controller to create the shipping file
 *
 * @author Aryel Mota Góis
 * @license MIT
 * @link https://www.github.com/aryelgois/BankInterchange
 */
class Model
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
     * All titles to be added
     *
     * @var Title[]
     */
    public $titles;
    
    /**
     * Creates a new ShippingFile Model object
     *
     * @param Database $db_address  An interface to `address` database
     * @param Database $db_banki    An interface to `bank_interchange` database
     * @param integer  $assignor_id Assignor's id from database
     * @param integer  $status      Status by which titles will be selected
     */
    public function __construct(
        Utils\Database $db_address,
        Utils\Database $db_banki,
        $assignor_id,
        $status = 0
    ) {
        $this->db_address = $db_address;
        $this->db_banki = $db_banki;
        
        // fetch assignor and bank
        $this->assignor = new BankI\Objects\Assignor($db_banki, $assignor_id);
        $this->bank = new BankI\Objects\Bank($db_banki, $this->assignor->bank);
        
        // fetch titles
        $this->titles = $cache = [];
        $query = "SELECT `id` FROM `titles` WHERE `assignor` = ? AND `status` = ? ORDER BY `stamp`";
        $titles = array_column(Utils\Database::fetch($db_banki->prepare($query, 'ii', [$assignor_id, $status])), 'id');
        foreach ($titles as $id) {
            $this->titles[$id] = new BankI\Objects\Title($db_address, $db_banki, $id, $cache);
        }
    }
    
    /**
     * Locks tables and returns next `shipping_files` index
     *
     * @return integer
     */
    public function getNextId()
    {
        $this->db_banki->query("SET autocommit = 0; LOCK TABLES `shipping_files` WRITE");
        $result = Utils\Database::fetch($this->db_banki->query("SHOW TABLE STATUS LIKE 'shipping_files'"));
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
     * Update `titles` entries to a given status
     *
     * @param integer $status New status for every title entry
     *
     * @return true For success or string[] of errors for failure
     */
    public function updateStatus($status)
    {
        $err = [];
        $query = "UPDATE `titles` SET `status` = ?, `update` = CURRENT_TIMESTAMP WHERE `id` = ?";
        $stmt = $this->db_banki->connect->prepare($query);
        $stmt->bind_param('ii', $status, $id);
        foreach ($this->titles as $row) {
            $id = $row->id;
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
