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
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\TwoFactorAuth\Model\AlertInterface;
use Magento\TwoFactorAuth\Api\TfaInterface;
use Magento\TwoFactorAuth\Api\TfaSessionInterface;
use Magento\TwoFactorAuth\Controller\Adminhtml\AbstractAction;

class Authpost extends AbstractAction implements HttpPostActionInterface
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
     * @var TfaSessionInterface
     */
    private $tfaSession;

    /**
     * @var DataObjectFactory
     */
    private $dataObjectFactory;

    /**
     * @var AlertInterface
     */
    private $alert;

    /**
     * @param Action\Context $context
     * @param Session $session
     * @param JsonFactory $jsonFactory
     * @param Email $email
     * @param TfaSessionInterface $tfaSession
     * @param TfaInterface $tfa
     * @param AlertInterface $alert
     * @param DataObjectFactory $dataObjectFactory
     */
    public function __construct(
        Action\Context $context,
        Session $session,
        JsonFactory $jsonFactory,
        Email $email,
        TfaSessionInterface $tfaSession,
        TfaInterface $tfa,
        AlertInterface $alert,
        DataObjectFactory $dataObjectFactory
    ) {
        parent::__construct($context);
        $this->tfa = $tfa;
        $this->session = $session;
        $this->jsonFactory = $jsonFactory;
        $this->email = $email;
        $this->tfaSession = $tfaSession;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->alert = $alert;
    }

    /**
     * @return ResponseInterface|Json|ResultInterface
     * @throws NoSuchEntityException
     */
    public function execute()
    {
        $user = $this->session->getUser();
        $response = $this->jsonFactory->create();
        $request = $this->dataObjectFactory->create(['data' => $this->getRequest()->getParams()]);

        if ($this->email->verify($user, $request)) {
            $this->tfaSession->grantAccess();
            $response->setData(['success' => true]);
        } else {
            $this->alert->event(
                'HitarthPattani_TwoFactorAuth',
                'Email auth invalid token',
                AlertInterface::LEVEL_WARNING,
                $user->getUserName()
            );

            $response->setData(['success' => false, 'message' => 'Invalid code']);
        }

        return $response;
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
