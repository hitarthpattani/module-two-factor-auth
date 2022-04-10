<?php
/**
 * @package     HitarthPattani\TwoFactorAuth
 * @author      Hitarth Pattani <hitarthpattani@gmail.com>
 * @copyright   Copyright © 2022. All rights reserved.
 */
declare(strict_types=1);

namespace HitarthPattani\TwoFactorAuth\Model\Provider\Engine;

use HitarthPattani\TwoFactorAuth\Model\EmailUserNotifier;
use HitarthPattani\TwoFactorAuth\Model\GenerateSecret;
use Magento\Backend\Model\Auth\Session;
use Magento\Framework\DataObject;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\TwoFactorAuth\Api\UserConfigManagerInterface;
use Magento\User\Api\Data\UserInterface;
use Magento\TwoFactorAuth\Api\EngineInterface;

class Email implements EngineInterface
{
    /**
     * Engine code
     *
     * Must be the same as defined in di.xml
     */
    public const CODE = 'email';

    /**
     * @var Session
     */
    private $session;

    /**
     * @var UserConfigManagerInterface
     */
    private $configManager;

    /**
     * @var EncryptorInterface
     */
    private $encryptor;

    /**
     * @var EmailUserNotifier
     */
    private $emailUserNotifier;

    /**
     * @var GenerateSecret
     */
    private $generateSecret;

    /**
     * @param Session $session
     * @param UserConfigManagerInterface $configManager
     * @param EncryptorInterface $encryptor
     * @param EmailUserNotifier $emailUserNotifier
     * @param GenerateSecret $generateSecret
     */
    public function __construct(
        Session $session,
        UserConfigManagerInterface $configManager,
        EncryptorInterface $encryptor,
        EmailUserNotifier $emailUserNotifier,
        GenerateSecret $generateSecret
    ) {
        $this->session = $session;
        $this->configManager = $configManager;
        $this->encryptor = $encryptor;
        $this->emailUserNotifier = $emailUserNotifier;
        $this->generateSecret = $generateSecret;
    }

    /**
     * @param UserInterface $user
     * @param DataObject $request
     * @return bool
     * @throws NoSuchEntityException
     */
    public function verify(UserInterface $user, DataObject $request): bool
    {
        $token = $request->getData('tfa_code');

        if (!$token) {
            return false;
        }

        return $token === $this->getSecretCode($user);
    }

    /**
     * Get the secret code used for Google Authentication
     *
     * @param UserInterface $user
     * @param bool $forceGenerate
     * @return string|null
     * @throws NoSuchEntityException
     */
    private function getSecretCode(UserInterface $user, bool $forceGenerate = false): ?string
    {
        $config = $this->configManager->getProviderConfig((int) $user->getId(), static::CODE);

        if (!isset($config['secret']) || $forceGenerate) {
            $config['secret'] = $this->generateSecret->execute();
            $this->setSharedSecret((int)$user->getId(), $config['secret']);
            return $config['secret'];
        }

        return $config['secret'] ? $this->encryptor->decrypt($config['secret']): null;
    }

    /**
     * Set the secret used to generate OTP
     *
     * @param int $userId
     * @param string $secret
     * @return void
     * @throws NoSuchEntityException
     */
    private function setSharedSecret(int $userId, string $secret): void
    {
        $this->configManager->addProviderConfig(
            $userId,
            static::CODE,
            ['secret' => $this->encryptor->encrypt($secret)]
        );
    }

    /**
     * @param UserInterface $user
     * @return void
     * @throws NoSuchEntityException
     */
    public function sendUserSecretEmail(UserInterface $user): void
    {
        $isEmailSent = $this->session->getAuthSecretEmailSentStatus();

        if ($isEmailSent === null) {
            $isEmailSent = false;
        }

        if (!$isEmailSent) {
            $flag = $this->session->getAuthSecretGrenerateStatus();
            if ($flag === null) {
                $flag = false;
            }
            $this->emailUserNotifier->execute($user, $this->getSecretCode($user, !$flag));
            $this->session->setAuthSecretGrenerateStatus(!$flag);
            $this->session->setAuthSecretEmailSentStatus(!$isEmailSent);
        }
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return true;
    }
}
