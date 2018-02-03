<?php
/**
 * This Software is part of aryelgois/bank-interchange and is provided "as is".
 *
 * @see LICENSE
 */

namespace aryelgois\BankInterchange\Views\Cnabs;

use aryelgois\Utils;
use aryelgois\BankInterchange as BankI;

/**
 * Generates CNAB400 Shipping Files to be sent to banks
 *
 * @author Aryel Mota Góis
 * @license MIT
 * @link https://www.github.com/aryelgois/bank-interchange
 */
class Cnab400 extends BankI\Views\Cnab
{
    /**
     * Adds a File Header
     */
    protected function addFileHeader()
    {
        $assignor = $this->shipping_file->assignor;
        $assignor_person = $assignor->person;
        $bank = $assignor->bank;

        $format = '%01.1s%01.1s%-7.7s%02.2s%-15.15s%04.4s%02.2s%07.7s%01.1s'
                . '%-6.6s%-30.30s%03.3s%-15.15s%06.6s%03.3s%-291.291s%06.6s';

        $data = [
            '0',
            '1',
            'REMESSA',
            '01',
            'COBRANCA',
            $assignor->agency,
            '',
            $assignor->account,
            $assignor->account_cd,
            '',
            $assignor_person->name,
            $bank->code,
            $bank->name,
            date('dmy'),
            $assignor->edi,
            '',
            $this->registry_count,
        ];

        $this->register($format, $data);
    }

    /**
     * Adds a Transaction
     *
     * @param integer $movement ...
     * @param Title   $title    Holds data about the title and the related payer
     */
    protected function addTitle(BankI\Models\Title $title)
    {
        $assignor = $title->assignor;
        $assignor_person = $assignor->person;
        $bank = $assignor->bank;
        $payer = $title->payer;
        $payer_person = $payer->person;
        $payer_address = $payer->address;

        $format = '%01.1s%-16.16s%04.4s%02.2s%07.7s%01.1s%02.2s%-4.4s%-25.25s'
                . '%07.7s%01.1s%010.10s%06.6s%013.13s%-8.8s%01.1s%02.2s%-10.10s'
                . '%06.6s%013.13s%03.3s%04.4s%-1.1s%02.2s%-1.1s%06.6s%04.4s'
                . '%013.13s%06.6s%013.13s%013.13s%013.13s%02.2s%014.14s%-40.40s'
                . '%-40.40s%-12.12s%08.8s%-15.15s%-2.2s%-40.40s%02.2s%-1.1s%06.6s';

        $data = [
            '1',
            '',
            $assignor->agency,
            '0',
            $assignor->account,
            $assignor->account_cd,
            '0', // fine percent
            '',
            $title->id,
            $title->our_number,
            $title->checkDigitOurNumber(),
            '0', // contract
            '0', // second discount date
            '0', // second discount value
            '',
            $assignor->wallet->febraban,
            $config['service'] ?? '1',
            $title->id,
            date('dmy', strtotime($title->due)),
            number_format($title->value, 2, '', ''), // Currency raw format
            '0', // Charging bank
            '0', // Charging agency
            '',
            $title->kind,
            'A', // accept
            date('dmy', strtotime($title->stamp)),
            $config['instruction_code'] ?? '0',
            '0', // one day fine
            ($title->discount_date != '' ? date('dmy', strtotime($title->discount_date)) : '0'),
            number_format($title->discount_value, 2, '', ''),
            number_format($title->iof, 2, '', ''),
            number_format($title->rebate, 2, '', ''),
            $payer_person->documentValidate()['type'] ?? '',
            $payer_person->document,
            $payer_person->name,
            implode(' ', [$payer_address->place, $payer_address->number, $payer_address->neighborhood]),
            $payer_address->detail,
            $payer_address->zipcode,
            $payer_address->county->name,
            $payer_address->county->state->code,
            '', // message or guarantor name
            '99', // protest deadline
            $title->currency->cnab400 ?? $title->currency->febraban,
            $this->registry_count,
        ];

        $this->register($format, $data);
    }

    /**
     * Adds a Trailer
     */
    protected function addFileTrailer()
    {
        $format = '%01.1s%-393.393s%06.6s';

        $data = [
            '9',
            '',
            $this->registry_count,
        ];

        $this->register($format, $data);
    }
}
