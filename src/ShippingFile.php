<?php
/**
 * This Software is part of aryelgois\cnab240 and is provided "as is".
 *
 * @see LICENSE
 */

namespace aryelgois\cnab240;

use aryelgois\utils;
use aryelgois\objects;

/**
 * Generates Shipping Files to be sent to banks
 *
 * @author Aryel Mota Góis
 * @license MIT
 * @link https://www.github.com/aryelgois/cnab240
 * @version 0.3
 */
class ShippingFile extends namespace\Cnab240File
{
    /**
     * Total registries in the file
     *
     * integer
     */
    protected $registries = 0;
    
    /**
     * Current Lot
     *
     * @var integer
     */
    protected $lot = 0;
    
    /**
     * Contais lot's data
     *
     * array[]
     */
    protected $lots;
    
    /**
     * Creates a new Shipping File object
     *
     * @param Bank     $bank     Contains Bank's information
     * @param Assignor $assignor Contains Assignor's information
     * @param integer  $file_id  Sequential file number, max 6 digits
     */
    public function __construct(
        namespace\objects\Bank $bank,
        namespace\objects\Assignor $assignor,
        $file_id
    ) {
        $this->bank = $bank;
        $this->assignor = $assignor;
        $this->file_id = $file_id;
        
        $this->open();
    }
    
    /**
     * Adds a File Header
     */
    protected function open()
    {
        $this->registerFileHeader();
        $this->registries++;
        $this->addLot();
    }
    
    /**
     * Starts a new Lot
     *
     * @throws new OverflowException If the file has too many Lots
     */
    public function addLot()
    {
        if ($this->lot >= 9998) {
            throw new \OverflowException('The File got too many Lots');
        } elseif ($this->lot > 0) {
            $this->closeLot();
        }
        $this->lots[++$this->lot] = [
            'registries' => 1, // already counting this LotHeader
            'titles' => 0,
            'total' => 0.0,
            'closed' => false
        ];
        $this->registerLotHeader();
        $this->registries++;
    }
    
    /**
     * Adds a new Title entry
     *
     * @param integer $movement ...
     * @param Title   $title What the entry is about
     *
     * @return boolean For success or failure
     *
     * @throws OverflowException If there are too many lot registries
     */
    public function addEntry($movement, namespace\objects\Title $title)
    {
        // Check if the file or the current log is closed
        if ($this->closed || $this->lots[$this->lot]['closed']) {
            return false;
        }
        // Check if the Lot is full
        $count = $this->lots[$this->lot]['registries'];
        if ($count > 999998) {
            throw new \OverflowException('The Lot got too many registries');
        } elseif ($count == 999998) {
            $this->addLot();
        }
        
        $this->registerLotDetail($movement, $title);
        
        $this->lots[$this->lot]['registries']++;
        $this->lots[$this->lot]['titles']++;
        $this->lots[$this->lot]['total'] += $title->value;
        $this->registries++;
        
        return true;
    }
    
    /**
     * Adds a Lot Trailer if the current lot is open
     */
    protected function closeLot()
    {
        if (!$this->closed || !$this->lots[$this->lot]['closed']) {
            $this->lots[$this->lot]['registries']++;
            $this->registerLotTrailer();
            $this->registries++;
            $this->lots[$this->lot]['closed'] = true;
        }
    }
    
    /**
     * Adds a File Trailer
     */
    protected function close()
    {
        if (!$this->closed) {
            $this->closeLot();
            $this->lot = 9999;
            $this->registries++;
            $this->registerFileTrailer();
            $this->closed = true;
        }
    }
    
    /**
     * Outputs the contents in a multiline string
     *
     * NOTES:
     * - Closes the current Lot and the File
     *
     * @return string A long, long string. Each line with 240 bytes.
     */
    final public function output()
    {
        $this->close();
        return implode("\n", $this->file);
    }
    
    
    /*
     * Formatting
     * =========================================================================
     */
    
    
    /**
     * [desc]
     *
     * @return string
     */
    protected function assignorAgencyAccount()
    {
        $a = $this->assignor;
        $result = self::padNumber($a->agency['number'], 5) . $a->agency['cd']
                . self::padNumber($a->account['number'], 12) . $a->account['cd'];
        $result .= self::assignorAgencyAccountCheck($result);
        return $result;
    }
    
    /**
     * [desc]
     *
     * @param string $agency_account ..
     *
     * @return string
     */
    protected static function assignorAgencyAccountCheck($agency_account)
    {
        $cd = utils\Validation::mod10($agency_account);
        
        return $cd;
    }
    
    
    /*
     * Internals
     * =========================================================================
     */
    
    
    /**
     * Adds a File Header
     */
    protected function registerFileHeader()
    {
        $this->file[] = self::fieldControl(0)
                      . str_repeat(' ', 9)
                      . $this->formatDocument($this->assignor)
                      . self::padNumber($this->assignor->covenant, 20)
                      . $this->assignorAgencyAccount()
                      . self::padAlfa($this->assignor->name, 30)
                      . self::padAlfa($this->bank->name, 30)
                      . str_repeat(' ', 10)
                      . '1' . date('dmYHis') . self::padNumber($this->file_id, 6) . self::VERSION_FILE_LAYOUT . '00000'
                      . str_repeat(' ', 20)
                      . str_repeat(' ', 20)
                      . str_repeat(' ', 29);
    }
    
    /**
     * Adds a Lot Header
     */
    protected function registerLotHeader()
    {
        $this->file[] = self::fieldControl(1)
                      . 'R' . '01' . '  ' . self::VERSION_LOT_LAYOUT
                      . ' '
                      . $this->formatDocument($this->assignor, 15)
                      . self::padNumber($this->assignor->covenant, 20)
                      . $this->assignorAgencyAccount()
                      . self::padAlfa($this->assignor->name, 30)
                      . str_repeat(' ', 40) // message 1
                      . str_repeat(' ', 40) // message 2
                      . '00000000'          // number shipping/return
                      . '00000000'          // recording date
                      . '00000000'          // credit date
                      . str_repeat(' ', 33);
    }
    
    /**
     * Adds a Lot Detail
     *
     * @param integer $movement ...
     * @param Title   $title    ...
     */
    protected function registerLotDetail($movement, namespace\objects\Title $title)
    {
        $control = self::fieldControl(3);
        $service = [
            self::padNumber($this->lots[$this->lot]['registries'], 5),
            null, // changed later
            ' ',
            self::padNumber($movement, 2)
        ];
        $payer = $title->payer;
        
        $service[1] = 'P';
        $this->file[] = $control
                      . implode('', $service)
                      . $this->assignorAgencyAccount()
                      
                      . self::padNumber($title->onum, 20)
                      . $title->wallet
                      . '1'                     // Title's Registration
                      . $title->doc_type
                      . '2'                     // Emission identifier
                      . '2'                     // Distribuition identifier
                      . self::padNumber($title->id, 15)
                      . date('dmY', strtotime($title->due))
                      . self::padNumber(number_format($title->value, 2, '', ''), 15)
                      . '00000'                 // Collection agency
                      . ' '                     // Collection agency Check Digit
                      . self::padNumber($title->kind, 2)
                      . 'A'                     // Identifies title acceptance by payer
                      . date('dmY', strtotime($title->stamp))
                      
                      . $title->fine['type']
                      . ($title->fine['date'] != '' ? date('dmY', strtotime($title->fine['date'])) : '00000000')
                      . self::padNumber(number_format($title->fine['value'], 2, '', ''), 15)
                      
                      . $title->discount['type']
                      . ($title->discount['date'] != '' ? date('dmY', strtotime($title->discount['date'])) : '00000000')
                      . self::padNumber(number_format($title->discount['value'], 2, '', ''), 15)
                      
                      . self::padNumber(number_format($title->iof, 2, '', ''), 15)
                      . self::padNumber(number_format($title->rebate, 2, '', ''), 15)
                      . self::padAlfa($title->description, 25)
                      . '3'                     // Protest code
                      . '00'                    // Protest deadline
                      . '1'                     // low/return code
                      . '000'                   // low/return deadline
                      . self::padNumber($title->specie, 2)
                      . '0000000000'            // Contract number
                      . '1';                    // Free use: it's defining partial payment isn't allowed
        
        $service[1] = 'Q';
        $this->file[] = $control
                      . implode('', $service)
                      . $payer->cnab240_string
                      
                      . (($title->guarantor === null)
                          ? str_repeat('0', 16) . str_repeat(' ', 40)
                          : self::formatDocument($title->guarantor, 15) . self::padAlfa($title->guarantor->name, 40))
                      
                      . '000'                   // Corresponding bank
                      . '00000000000000000000'  // "Our number" at corresponding bank
                      . '        ';
    }
    
    /**
     * Adds a Lot Trailer
     */
    protected function registerLotTrailer()
    {
        $this->file[] = self::fieldControl(5)
                      . '         '
                      . self::padNumber($this->lots[$this->lot]['registries'], 6)
                      . self::padNumber($this->lots[$this->lot]['titles'], 6)
                      . self::padNumber(number_format($this->lots[$this->lot]['total'], 2, '', ''), 17)
                      . '000000' . '00000000000000000'
                      . '000000' . '00000000000000000'
                      . '000000' . '00000000000000000'
                      . '        '
                      . str_repeat(' ', 117);
    }
    
    /**
     * Adds a File Trailer
     */
    protected function registerFileTrailer()
    {
        $this->file[] = self::fieldControl(9)
                      . '         '
                      . self::padNumber(count($this->lots), 6)
                      . self::padNumber($this->registries, 6)
                      . '000000'
                      . str_repeat(' ', 205);
    }
}
