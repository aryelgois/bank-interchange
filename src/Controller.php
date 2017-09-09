<?php
/**
 * This Software is part of aryelgois\cnab240 and is provided "as is".
 *
 * @see LICENSE
 */

namespace aryelgois\BankInterchange;

use aryelgois\Utils;
use aryelgois\Objects;

/**
 * A basic controller to create the shipping file
 *
 * @author Aryel Mota Góis
 * @license MIT
 * @link https://www.github.com/aryelgois/cnab240
 */
class Controller
{
    protected $database_address;
    protected $database_cnab240;
    protected $assignor;
    protected $bank;
    
    public function __construct($db_path, $assignor)
    {
        $this->database_address = new namespace\example\Database($db_path, 'address');
        $this->database_cnab240 = new namespace\example\Database($db_path, 'cnab240');
        
        $this->assignor = new namespace\Objects\Assignor($this->database_cnab240, $assignor);
        
        $this->bank = new namespace\Objects\Bank($this->database_cnab240, $this->assignor->bank);
    }
    
    public function execute()
    {
        $query = "SELECT `id` FROM `titles` WHERE `assignor` = " . $this->assignor->id . " AND `status` = 0 ORDER BY `stamp`";
        $titles = array_column(Utils\Database::fetch($this->database_cnab240->query($query)), 'id');
        if (!empty($titles)) {
            // @todo Get id for new row in databse -> table `shipping_files`
            $file_id = 1;
            $shipping_file = new namespace\Objects\ShippingFile(
                $this->bank,
                $this->assignor,
                $file_id
            );
            
            $cache = [];
            foreach ($titles as $id) {
                $title = new namespace\Objects\Title(
                    $this->database_cnab240,
                    $this->database_address,
                    $id,
                    $cache
                );
                $shipping_file->addEntry(1, $title);
            }
            
            // save file
            $filename = 'COB.240.'
                      . namespace\Utils::padNumber($this->assignor->edi7, 6) . '.'
                      . date('Ymd') . '.'
                      . namespace\Utils::padNumber($file_id, 5) . '.'
                      . namespace\Utils::padNumber($this->assignor->covenant, 5, true) // @todo verify if covenant is actually small and the Headers exagerate the covenant lenght
                      . '.REM';
            
            //$file = fopen($filename, 'w');
            //fwrite($file, $shipping_file->output());
            //fclose($file);
            
            // update rows
            $err = [];
            $stmt = $this->database_cnab240->connect->prepare("UPDATE `titles` SET `status` = 1, `update` = CURRENT_TIMESTAMP WHERE `id` = ?");
            $stmt->bind_param('i', $id);
            foreach ($titles as $id) {
                //$stmt->execute();
                if ($stmt->error !== '') {
                    $err[] = $stmt->error;
                }
            }
            if (!empty($err)) {
                die(implode("<br />\n", $err));
            }
            
            // DONE
            echo '<h2>' . $filename . "</h2>\n";
            //echo '<pre>', var_dump(explode("\n", $shipping_file->output())), "</pre>\n\n\n";
            echo '<pre>' . $shipping_file->output() . "</pre>\n\n\n";
            //echo '<pre>' . file_get_contents($filename) . "</pre>\n\n\n";
        }
    }
}
