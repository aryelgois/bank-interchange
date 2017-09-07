<?php
/**
 * This Software is part of aryelgois\cnab240 and is provided "as is".
 *
 * @see LICENSE
 */

namespace aryelgois\cnab240\example;

use aryelgois\utils;
use aryelgois\objects;
use aryelgois\cnab240;

/**
 * A basic controller to create the shipping file
 *
 * NOTES:
 * - May be moved to src/
 *
 * @author Aryel Mota Góis
 * @license MIT
 * @link https://www.github.com/aryelgois/cnab240
 * @version 0.1.1
 */
class Controller
{
    protected $database_address;
    protected $database_cnab240;
    protected $assignor;
    protected $bank;
    
    public function __construct($db_path, $assignor)
    {
        $this->database_address = new Database($db_path, 'address');
        $this->database_cnab240 = new Database($db_path, 'cnab240');
        
        $this->assignor = new cnab240\objects\Assignor($this->database_cnab240, $assignor);
        
        $this->bank = new cnab240\objects\Bank($this->database_cnab240, $this->assignor->bank);
    }
    
    public function execute()
    {
        $query = "SELECT `id` FROM `titles` WHERE `assignor` = " . $this->assignor->id . " AND `status` = 0 ORDER BY `stamp`";
        $titles = array_column(Database::fetch($this->database_cnab240->query($query)), 'id');
        if (!empty($titles)) {
            // @todo Get id for new row in databse -> table `shipping_files`
            $file_id = 1;
            $shipping_file = new cnab240\ShippingFile(
                $this->bank,
                $this->assignor,
                $file_id
            );
            
            $cache = [];
            foreach ($titles as $id) {
                $title = new cnab240\objects\Title(
                    $this->database_cnab240,
                    $this->database_address,
                    $id,
                    $cache
                );
                $shipping_file->addEntry(1, $title);
            }
            
            // save file
            $filename = 'COB.240.'
                      . cnab240\Cnab240File::padNumber($this->assignor->edi7, 6) . '.'
                      . date('Ymd') . '.'
                      . cnab240\Cnab240File::padNumber($file_id, 5) . '.'
                      . cnab240\Cnab240File::padNumber($this->assignor->covenant, 5, true) // @todo verify if covenant is actually small and the Headers exagerate the covenant lenght
                      . '.REM';
            
            //$file = fopen($filename, 'w');
            //fwrite($file, $shipping_file->output());
            //fclose($file);
            
            // update rows
            $err = [];
            $stmt = $this->database_cnab240->connect->prepare("UPDATE `titles` SET `status` = 1, `update` = CURRENT_TIMESTAMP WHERE `id` = ?");
            $stmt->bind_param('i', $id);
            foreach ($titles as $id) {
                $stmt->execute();
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
