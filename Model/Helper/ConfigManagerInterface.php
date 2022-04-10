<?php
/**
 * @package     HitarthPattani\TwoFactorAuth
 * @author      Hitarth Pattani <hitarthpattani@gmail.com>
 * @copyright   Copyright © 2022. All rights reserved.
 */
declare(strict_types=1);

namespace HitarthPattani\TwoFactorAuth\Model\Helper;

use Magento\Store\Model\ScopeInterface;

interface ConfigManagerInterface
{
    /**
     * @param string $path
     * @param string $scope
     * @param int|null $scopeId
     * @return mixed
     */
    public function execute(
        string $path,
        string $scope = ScopeInterface::SCOPE_STORE,
        int $scopeId = null
    );
}
