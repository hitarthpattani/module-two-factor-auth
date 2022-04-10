<?php
/**
 * @package     HitarthPattani\TwoFactorAuth
 * @author      Hitarth Pattani <hitarthpattani@gmail.com>
 * @copyright   Copyright © 2022. All rights reserved.
 */
declare(strict_types=1);

namespace HitarthPattani\TwoFactorAuth\Controller\Adminhtml\Email;

use HitarthPattani\TwoFactorAuth\Model\Provider\Engine\Email;
use Magento\Backend\Model\Auth\Session;
use Magento\Backend\App\Action;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\TwoFactorAuth\Api\TfaInterface;
use Magento\TwoFactorAuth\Controller\Adminhtml\AbstractConfigureAction;
use Magento\User\Model\User;
use Magento\TwoFactorAuth\Model\UserConfig\HtmlAreaTokenVerifier;

class Configure extends AbstractConfigureAction implements HttpGetActionInterface
{
    /**
     * @var TfaInterface
     */
    private $tfa;

    /**
     * @var Session
     */
    private $session;

    /**
     * @param Action\Context $context
     * @param Session $session
     * @param TfaInterface $tfa
     * @param HtmlAreaTokenVerifier $tokenVerifier
     */
    public function __construct(
        Action\Context $context,
        Session $session,
        TfaInterface $tfa,
        HtmlAreaTokenVerifier $tokenVerifier
    ) {
        parent::__construct($context, $tokenVerifier);
        $this->tfa = $tfa;
        $this->session = $session;
    }

    /**
     * Get current user
     *
     * @return User|null
     */
    private function getUser(): ?User
    {
        return $this->session->getUser();
    }

    /**
     * @return ResponseInterface
     * @throws LocalizedException
     */
    public function execute()
    {
        $user = $this->getUser();

        $this->tfa->getProvider(Email::CODE)->activate((int) $user->getId());

        $provider = $this->tfa->getProvider(Email::CODE);
        if ($provider) {
            // Provider found, user will be challenged.
            return $this->_redirect($provider->getAuthAction());
        }

        throw new LocalizedException(__('Internal error accessing 2FA index page'));
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        if (!parent::_isAllowed()) {
            return false;
        }

        $user = $this->getUser();

        return
            $user &&
            $this->tfa->getProviderIsAllowed((int) $user->getId(), Email::CODE) &&
            !$this->tfa->getProvider(Email::CODE)->isActive((int) $user->getId());
    }
}
