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
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\TwoFactorAuth\Api\TfaInterface;
use Magento\TwoFactorAuth\Controller\Adminhtml\AbstractAction;

class Resend extends AbstractAction implements HttpGetActionInterface
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
     * @var JsonFactory
     */
    private $jsonFactory;

    /**
     * @var Email
     */
    private $email;

    /**
     * @param Action\Context $context
     * @param Session $session
     * @param JsonFactory $jsonFactory
     * @param Email $email
     * @param TfaInterface $tfa
     */
    public function __construct(
        Action\Context $context,
        Session $session,
        JsonFactory $jsonFactory,
        Email $email,
        TfaInterface $tfa
    ) {
        parent::__construct($context);
        $this->tfa = $tfa;
        $this->session = $session;
        $this->jsonFactory = $jsonFactory;
        $this->email = $email;
    }

    /**
     * @return ResponseInterface|Json|ResultInterface
     * @throws NoSuchEntityException
     */
    public function execute()
    {
        $user = $this->session->getUser();
        $this->session->setAuthSecretEmailSentStatus(false);
        $this->email->sendUserSecretEmail($user);
        return $this->jsonFactory->create()
            ->setData(['success' => true]);
    }

    /**
     * Check if admin has permissions to visit related pages
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        $user = $this->session->getUser();

        return $user
            && $this->tfa->getProviderIsAllowed((int)$user->getId(), Email::CODE)
            && $this->tfa->getProvider(Email::CODE)->isActive((int)$user->getId());
    }
}
