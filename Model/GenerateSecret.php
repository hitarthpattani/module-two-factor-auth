<?php
/**
 * @package     HitarthPattani\TwoFactorAuth
 * @author      Hitarth Pattani <hitarthpattani@gmail.com>
 * @copyright   Copyright © 2022. All rights reserved.
 */
declare(strict_types=1);

namespace HitarthPattani\TwoFactorAuth\Model;

use HitarthPattani\TwoFactorAuth\Model\Helper\ConfigManagerInterface;
use HitarthPattani\TwoFactorAuth\Model\Source\Config\Otptype;

class GenerateSecret
{
    /**
     * @var string
     */
    const RESULT_STR_NUMBER = '0123456789';
    const RESULT_STR_ALPHABETS = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
    const RESULT_STR_ALPHANUMERIC = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';

    /**
     * @var ConfigManagerInterface|null
     */
    private $optConfig;

    /**
     * @param ConfigManagerInterface|null $optConfig
     */
    public function __construct(
        ConfigManagerInterface $optConfig = null
    ) {
        $this->optConfig = $optConfig;
    }

    /**
     * Generate random secret
     *
     * @return string
     */
    public function execute(): string
    {
        $type = $this->optConfig->getType();
        $length = $this->optConfig->getLength();

        if (empty($length)) {
            $length = 6;
        } else {
            $length = (int) $length;
        }

        if ($type == Otptype::OTPTYPE_NUMBER) {
            $code =  substr(str_shuffle(self::RESULT_STR_NUMBER), 0, $length);
        } elseif ($type == Otptype::OTPTYPE_ALPHABETS) {
            $code =  substr(str_shuffle(self::RESULT_STR_ALPHABETS), 0, $length);
        } elseif ($type == Otptype::OTPTYPE_ALPHANUMERIC) {
            $code =  substr(str_shuffle(self::RESULT_STR_ALPHANUMERIC), 0, $length);
        } else {
            $code = random_int(100000, 999999);
        }

        return $code;
    }
}
