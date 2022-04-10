<?php
/**
 * @package     HitarthPattani\TwoFactorAuth
 * @author      Hitarth Pattani <hitarthpattani@gmail.com>
 * @copyright   Copyright © 2022. All rights reserved.
 */
declare(strict_types=1);

namespace HitarthPattani\TwoFactorAuth\Model\Source\Config;

use Magento\Framework\Data\OptionSourceInterface;

class Otptype implements OptionSourceInterface
{
    /**
     * @var string
     */
    const OTPTYPE_NUMBER = 'number';
    const OTPTYPE_ALPHABETS = 'alphabets';
    const OTPTYPE_ALPHANUMERIC = 'alphanumeric';

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::OTPTYPE_NUMBER, 'label' => __('Number')],
            ['value' => self::OTPTYPE_ALPHABETS, 'label' => __('Alphabets')],
            ['value' => self::OTPTYPE_ALPHANUMERIC, 'label' => __('Alphanumeric')]
        ];
    }
}
