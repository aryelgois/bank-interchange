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
    protected function registerFileHeader()
    {
        $assignor = $this->shipping_file->getForeign('assignor');
        $assignor_person = $assignor->getForeign('person');
        $bank = $assignor->getForeign('bank');

        $format = '%01.1s%01.1s%-7.7s%02.2s%-15.15s%04.4s%02.2s%07.7s%01.1s'
                . '%-6.6s%-30.30s%03.3s%-15.15s%06.6s%03.3s%-291.291s%06.6s';

        $data = [
            '0',
            '1',
            'REMESSA',
            '01',
            'COBRANCA',
            $assignor->get('agency'),
            '',
            $assignor->get('account'),
            $assignor->get('account_cd'),
            '',
            $assignor_person->get('name'),
            $bank->get('code'),
            $bank->get('name'),
            date('dmy'),
            $assignor->get('edi'),
            '',
            $this->registries,
        ];

        $this->register($format, $data);
    }

    /**
     * Adds a Transaction
     *
     * @param integer $movement ...
     * @param Title   $title    Holds data about the title and the related payer
     */
    protected function registerTransaction(BankI\Models\Title $title)
    {
        $assignor = $title->getForeign('assignor');
        $assignor_person = $assignor->getForeign('person');
        $bank = $assignor->getForeign('bank');
        $payer = $title->getForeign('payer');
        $payer_person = $payer->getForeign('person');
        $payer_address = $payer->getForeign('address');

        $format = '%01.1s%-16.16s%04.4s%02.2s%07.7s%01.1s%02.2s%-4.4s%-25.25s'
                . '%07.7s%01.1s%010.10s%06.6s%013.13s%-8.8s%01.1s%02.2s%-10.10s'
                . '%06.6s%013.13s%03.3s%04.4s%-1.1s%02.2s%-1.1s%06.6s%04.4s'
                . '%013.13s%06.6s%013.13s%013.13s%013.13s%02.2s%014.14s%-40.40s'
                . '%-40.40s%-12.12s%08.8s%-15.15s%-2.2s%-40.40s%02.2s%-1.1s%06.6s';

        $data = [
            '1',
            '',
            $assignor->get('agency'),
            '0',
            $assignor->get('account'),
            $assignor->get('account_cd'),
            '0', // fine percent
            '',
            $title->get('id'),
            $title->get('our_number'),
            BankI\Utils::checkDigitOurNumber($title->get('our_number')),
            '0', // contract
            '0', // second discount date
            '0', // second discount value
            '',
            $assignor->getForeign('wallet')->get('febraban'),
            $config['service'] ?? '1',
            $title->get('id'),
            date('dmy', strtotime($title->get('due'))),
            number_format($title->get('value'), 2, '', ''), // Specie raw format
            '0', // Charging bank
            '0', // Charging agency
            '',
            $title->get('kind'),
            'A', // accept
            date('dmy', strtotime($title->get('stamp'))),
            $config['instruction_code'] ?? '0',
            '0', // one day fine
            ($title->get('discount_date') != '' ? date('dmy', strtotime($title->get('discount_date'))) : '0'),
            number_format($title->get('discount_value'), 2, '', ''),
            number_format($title->get('iof'), 2, '', ''),
            number_format($title->get('rebate'), 2, '', ''),
            $payer_person->documentValidate()['type'] ?? '',
            $payer_person->get('document'),
            $payer_person->get('name'),
            implode(' ', [$payer_address->get('place'), $payer_address->get('number'), $payer_address->get('neighborhood')]),
            $payer_address->get('detail'),
            $payer_address->get('zipcode'),
            $payer_address->getForeign('county')->get('name'),
            $payer_address->getForeign('county')->getForeign('state')->get('code'),
            '', // message or guarantor name
            '99', // protest deadline
            $title->getForeign('specie')->get('febraban'),
            $this->registries,
        ];

        $this->register($format, $data);
    }

    /**
     * Adds a Trailer
     */
    protected function registerFileTrailer()
    {
        $format = '%01.1s%-393.393s%06.6s';

        $data = [
            '9',
            '',
            $this->registries,
        ];

        $this->register($format, $data);
    }
}
