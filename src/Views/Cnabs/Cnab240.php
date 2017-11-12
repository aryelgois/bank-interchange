<?php
/**
 * This Software is part of aryelgois\BankInterchange and is provided "as is".
 *
 * @see LICENSE
 */

namespace aryelgois\BankInterchange\Views\Cnabs;

use aryelgois\Utils;
use aryelgois\BankInterchange as BankI;

/**
 * Generates Shipping Files to be sent to banks
 *
 * @author Aryel Mota Góis
 * @license MIT
 * @link https://www.github.com/aryelgois/bank-interchange
 */
class Cnab240 extends BankI\Views\Cnab
{
    /**
     * FEBRABAN's version of file layout
     *
     * @const string
     */
    const VERSION_FILE_LAYOUT = '101';

    /**
     * FEBRABAN's version of lot layout
     *
     * @const string
     */
    const VERSION_LOT_LAYOUT = '060';

    /**
     * Current Lot
     *
     * @var integer
     */
    protected $lot = 0;

    /**
     * Contais lot's data
     *
     * @var array[]
     */
    protected $lots;

    /**
     * Adds a File Header and a Lot
     */
    protected function open()
    {
        parent::open();
        $this->addLot();
    }

    /**
     * Starts a new Lot
     *
     * @throws \OverflowException If the file has too many Lots
     */
    public function addLot()
    {
        if ($this->lot >= 9998) {
            throw new \OverflowException('The File got too many Lots');
        } elseif ($this->lot > 0) {
            $this->closeLot();
        }
        $this->lots[++$this->lot] = [
            'registries' => 1, // already counting the following LotHeader
            'lines' => 1, // already counting the following LotHeader
            'titles' => 0,
            'total' => 0.0,
            'closed' => false
        ];
        $this->addLotHeader();
        $this->registry_count++;
    }

    /**
     * Adds a new Title entry
     *
     * @param integer $movement ...
     * @param Title   $title    What the entry is about
     *
     * @return boolean For success or failure
     *
     * @throws \OverflowException If there are too many lot registries
     */
    public function addEntry($movement, BankI\Objects\Title $title)
    {
        // Check if the file or the current log is closed
        if ($this->closed || $this->lots[$this->lot]['closed']) {
            return false;
        }
        // Check if the Lot is full
        /**
         * @todo change those big numbers to be 999999 - the amount of lines
         *       to be added
         */
        $count = $this->lots[$this->lot]['registries'];
        if ($count > 999998) {
            throw new \OverflowException('The Lot got too many registries');
        } elseif ($count == 999998) {
            $this->addLot();
        }

        $this->addLotDetail($movement, $title);

        $this->lots[$this->lot]['registries']++;
        $this->lots[$this->lot]['lines'] += 2;
        $this->lots[$this->lot]['titles']++;
        $this->lots[$this->lot]['total'] += $title->value;
        $this->registry_count += 2; // the amount of segments (type 3)

        return true;
    }

    /**
     * Adds a Lot Trailer if the current lot is open
     */
    protected function closeLot()
    {
        if (!$this->lots[$this->lot]['closed']) {
            $this->lots[$this->lot]['registries']++;
            $this->lots[$this->lot]['lines']++;
            $this->addLotTrailer();
            $this->registry_count++;
            $this->lots[$this->lot]['closed'] = true;
        }
    }

    /**
     * Adds a File Trailer
     */
    protected function close()
    {
        $this->closeLot();
        $this->lot = 9999;
        $this->registry_count++;
        $this->addFileTrailer();
    }

    /*
     * Internals
     * =========================================================================
     */

    protected function addTitle(BankI\Models\Title $title)
    {

    }

    /**
     * Adds a File Header
     */
    protected function addFileHeader()
    {
        $assignor = $this->shipping_file->getForeign('assignor');
        $assignor_person = $assignor->getForeign('person');
        $bank = $assignor->getForeign('bank');

        $rg = self::fieldControl(0)
            . str_repeat(' ', 9)
            . BankI\Utils::padNumber($assignor_person->get('document'), 14)
            . BankI\Utils::padNumber($assignor->get('covenant'), 20)
            . $this->formatAgencyAccount()
            . BankI\Utils::padAlfa($assignor_person->get('name'), 30)
            . BankI\Utils::padAlfa($bank->get('name'), 30)
            . str_repeat(' ', 10)
            . '1' . date('dmYHis') . BankI\Utils::padNumber($this->shipping_file->get('id'), 6) . self::VERSION_FILE_LAYOUT . '00000'
            . str_repeat(' ', 20)
            . str_repeat(' ', 20)
            . str_repeat(' ', 29);

        $this->file .= $rg . static::LINE_END;
    }

    /**
     * Adds a Lot Header
     */
    protected function addLotHeader()
    {
        $assignor = $this->shipping_file->getForeign('assignor');
        $assignor_person = $assignor->getForeign('person');
        $bank = $assignor->getForeign('bank');

        $rg = self::fieldControl(1)
            . 'R' . '01' . '  ' . self::VERSION_LOT_LAYOUT
            . ' '
            . BankI\Utils::padNumber($assignor_person->get('document'), 15)
            . BankI\Utils::padNumber($assignor->get('covenant'), 20)
            . $this->formatAgencyAccount()
            . BankI\Utils::padAlfa($assignor_person->get('name'), 30)
            . str_repeat(' ', 40) // message 1
            . str_repeat(' ', 40) // message 2
            . '00000000'          // number shipping/return
            . '00000000'          // recording date
            . '00000000'          // credit date
            . str_repeat(' ', 33);

        $this->file .= $rg . static::LINE_END;
    }

    /**
     * Adds a Lot Detail
     *
     * @param integer $movement ...
     * @param Title   $title    Holds data about the title and the related payer
     */
    protected function addLotDetail($movement, BankI\Objects\Title $title)
    {
        $control = self::fieldControl(3);
        $service = [
            BankI\Utils::padNumber($this->lots[$this->lot]['registries'], 5),
            null, // changed later
            ' ',
            BankI\Utils::padNumber($movement, 2)
        ];
        $payer = $title->payer;

        $service[1] = 'P';
        $rg = $control
            . implode('', $service)
            . $this->formatAgencyAccount()

            . BankI\Utils::padNumber($title->onum, 20)
            . $title->wallet['febraban']
            . '1'                     // Title's Registration
            . $title->doc_type
            . '2'                     // Emission identifier
            . '2'                     // Distribuition identifier
            . BankI\Utils::padNumber($title->id, 15)
            . date('dmY', strtotime($title->due))
            . BankI\Utils::padNumber(number_format($title->value, 2, '', ''), 15)
            . '00000'                 // Collection agency
            . ' '                     // Collection agency Check Digit
            . BankI\Utils::padNumber($title->kind, 2)
            . 'A'                     // Identifies title acceptance by payer
            . date('dmY', strtotime($title->stamp))

            . $title->fine['type']
            . ($title->fine['date'] != '' ? date('dmY', strtotime($title->fine['date'])) : '00000000')
            . BankI\Utils::padNumber(number_format($title->fine['value'], 2, '', ''), 15)

            . $title->discount['type']
            . ($title->discount['date'] != '' ? date('dmY', strtotime($title->discount['date'])) : '00000000')
            . BankI\Utils::padNumber(number_format($title->discount['value'], 2, '', ''), 15)

            . BankI\Utils::padNumber(number_format($title->iof, 2, '', ''), 15)
            . BankI\Utils::padNumber(number_format($title->rebate, 2, '', ''), 15)
            . BankI\Utils::padAlfa($title->description, 25)
            . '3'                     // Protest code
            . '00'                    // Protest deadline
            . '1'                     // low/return code
            . '000'                   // low/return deadline
            . BankI\Utils::padNumber($title->specie['cnab240'], 2)
            . '0000000000'            // Contract number
            . '1';                    // Free use: it's defining partial payment isn't allowed

        $this->file .= $rg . static::LINE_END;

        $service[1] = 'Q';
        $rg = $control
            . implode('', $service)
            . $payer->toCnab240()

            . (($title->guarantor === null)
              ? str_repeat('0', 16) . str_repeat(' ', 40)
              : BankI\Utils::formatDocument($title->guarantor, 15) . BankI\Utils::padAlfa($title->guarantor->name, 40))

            . '000'                   // Corresponding bank
            . '00000000000000000000'  // "Our number" at corresponding bank
            . '        ';

        $this->file .= $rg . static::LINE_END;
    }

    /**
     * Adds a Lot Trailer
     */
    protected function addLotTrailer()
    {
        $rg = self::fieldControl(5)
            . '         '
            . BankI\Utils::padNumber($this->lots[$this->lot]['lines'], 6)
            . BankI\Utils::padNumber($this->lots[$this->lot]['titles'], 6)
            . BankI\Utils::padNumber(number_format($this->lots[$this->lot]['total'], 2, '', ''), 17)
            . '000000' . '00000000000000000'
            . '000000' . '00000000000000000'
            . '000000' . '00000000000000000'
            . '        '
            . str_repeat(' ', 117);

        $this->file .= $rg . static::LINE_END;
    }

    /**
     * Adds a File Trailer
     */
    protected function addFileTrailer()
    {
        $rg = self::fieldControl(9)
            . '         '
            . BankI\Utils::padNumber(count($this->lots), 6)
            . BankI\Utils::padNumber($this->registry_count, 6)
            . '000000'
            . str_repeat(' ', 205);

        $this->file .= $rg . static::LINE_END;
    }

    /*
     * Helper
     * =========================================================================
     */

    /**
     * Formats Control field
     *
     * @param integer $type Code adopted by FEBRABAN to identify the registry type
     *
     * @return string
     */
    protected function fieldControl($type)
    {
        return $this->shipping_file->getForeign('assignor')->getForeign('bank')->get('code') . BankI\Utils::padNumber($this->lot, 4) . $type;
    }
}
