<?php
/**
 * @package     HitarthPattani\TwoFactorAuth
 * @author      Hitarth Pattani <hitarthpattani@gmail.com>
 * @copyright   Copyright © 2022. All rights reserved.
 */
declare(strict_types=1);

namespace HitarthPattani\TwoFactorAuth\Block\Provider\Email;

use HitarthPattani\TwoFactorAuth\Model\Helper\ConfigManagerInterface;
use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;

class Auth extends Template
{
    /**
     * @var ConfigManagerInterface|null
     */
    private $emailConfig;

    /**
     * @param Context $context
     * @param array $data
     * @param ConfigManagerInterface|null $emailConfig
     */
    public function __construct(
        Context $context,
        array $data = [],
        ConfigManagerInterface $emailConfig = null
    ) {
        $this->emailConfig = $emailConfig;
        parent::__construct($context, $data);
    }

    /**
     * @return string
     */
    public function getJsLayout()
    {
        $this->jsLayout['components']['tfa-auth']['postUrl'] =
            $this->getUrl('hp_tfa/email/authpost');

        $this->jsLayout['components']['tfa-auth']['resendUrl'] =
            $this->getUrl('hp_tfa/email/resend');

        $this->jsLayout['components']['tfa-auth']['successUrl'] =
            $this->getUrl($this->_urlBuilder->getStartupPageUrl());

        $this->jsLayout['components']['tfa-auth']['timeout'] = (int) $this->emailConfig->getResendCodeIn();

        return parent::getJsLayout();
    }
}
