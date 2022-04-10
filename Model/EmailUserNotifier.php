<?php
/**
 * @package     HitarthPattani\TwoFactorAuth
 * @author      Hitarth Pattani <hitarthpattani@gmail.com>
 * @copyright   Copyright © 2022. All rights reserved.
 */
declare(strict_types=1);

namespace HitarthPattani\TwoFactorAuth\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\User\Api\Data\UserInterface;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\TwoFactorAuth\Model\Exception\NotificationException;
use Psr\Log\LoggerInterface;

class EmailUserNotifier
{
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var TransportBuilder
     */
    private $transportBuilder;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param TransportBuilder $transportBuilder
     * @param StoreManagerInterface $storeManager
     * @param LoggerInterface $logger
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        TransportBuilder $transportBuilder,
        StoreManagerInterface $storeManager,
        LoggerInterface $logger
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->transportBuilder = $transportBuilder;
        $this->storeManager = $storeManager;
        $this->logger = $logger;
    }

    /**
     * Send user secret related message to the admin user.
     *
     * @param UserInterface $user
     * @param string $token
     * @return void
     */
    public function execute(UserInterface $user, string $token): void
    {
        try {
            $transport = $this->transportBuilder
                ->setTemplateIdentifier('email_tfa_admin_user_authenticator_code')
                ->setTemplateOptions(
                    [
                        'area' => 'adminhtml',
                        'store' => 0
                    ]
                )
                ->setTemplateVars(
                    [
                        'username' => $user->getFirstName() . ' ' . $user->getLastName(),
                        'token' => $token,
                        'store_name' => $this->storeManager->getStore()->getFrontendName()
                    ]
                )
                ->setFromByScope(
                    $this->scopeConfig->getValue('twofactorauth/email/authenticator_code_sender_identity')
                )
                ->addTo($user->getEmail(), $user->getFirstName() . ' ' . $user->getLastName())
                ->getTransport();
            $transport->sendMessage();
        } catch (\Throwable $exception) {
            $this->logger->critical($exception);
            throw new NotificationException('Failed to send 2FA E-mail to a user', 0, $exception);
        }
    }
}
