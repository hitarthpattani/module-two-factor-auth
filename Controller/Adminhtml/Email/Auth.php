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
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use Magento\TwoFactorAuth\Api\TfaInterface;
use Magento\TwoFactorAuth\Api\UserConfigManagerInterface;
use Magento\TwoFactorAuth\Controller\Adminhtml\AbstractAction;
use Magento\User\Model\User;

class Auth extends AbstractAction implements HttpGetActionInterface
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
     * @var PageFactory
     */
    private $pageFactory;

    /**
     * @var UserConfigManagerInterface
     */
    private $userConfigManager;

    /**
     * @var Email
     */
    private $email;

    /**
     * @param Action\Context $context
     * @param Session $session
     * @param PageFactory $pageFactory
     * @param UserConfigManagerInterface $userConfigManager
     * @param TfaInterface $tfa
     * @param Email $email
     */
    public function __construct(
        Action\Context $context,
        Session $session,
        PageFactory $pageFactory,
        UserConfigManagerInterface $userConfigManager,
        TfaInterface $tfa,
        Email $email
    ) {
        parent::__construct($context);
        $this->tfa = $tfa;
        $this->session = $session;
        $this->pageFactory = $pageFactory;
        $this->userConfigManager = $userConfigManager;
        $this->email = $email;
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
     * @return Page|ResultInterface
     * @throws NoSuchEntityException
     */
    public function execute()
    {
        $this->userConfigManager->setDefaultProvider((int) $this->getUser()->getId(), Email::CODE);
        $this->email->sendUserSecretEmail($this->getUser());
        return $this->pageFactory->create();
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        $user = $this->getUser();

        return
            $user &&
            $this->tfa->getProviderIsAllowed((int) $user->getId(), Email::CODE) &&
            $this->tfa->getProvider(Email::CODE)->isActive((int) $user->getId());
    }
}
