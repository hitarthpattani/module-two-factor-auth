<?php
/**
 * @package     HitarthPattani\TwoFactorAuth
 * @author      Hitarth Pattani <hitarthpattani@gmail.com>
 * @copyright   Copyright © 2022. All rights reserved.
 */
declare(strict_types=1);

namespace HitarthPattani\TwoFactorAuth\Model\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class ConfigProvider implements ConfigManagerInterface
{
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var string
     */
    private $fieldset;

    /**
     * @var string
     */
    private $group;

    /**
     * @var array
     */
    private $methods;

    /**
     * Initial dependencies
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param string $fieldset
     * @param string $group
     * @param array $methods
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        string $fieldset = '',
        string $group = '',
        array $methods = []
    ) {
        $this->scopeConfig = $scopeConfig;
        if ($fieldset === '') {
            throw new \InvalidArgumentException((string) __('Invalid Fieldset for Config Provider: %1', $fieldset));
        }
        $this->fieldset = $fieldset;
        if ($group === '') {
            throw new \InvalidArgumentException((string) __('Invalid Group for Config Provider: %1', $group));
        }
        $this->group = $group;
        $this->methods = $methods;
    }

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
    ) {
        return $this->scopeConfig->getValue($path, $scope, $scopeId);
    }

    /**
     * @param string $method
     * @param array $args
     * @return string|null
     */
    public function __call($method, $args)
    {
        if (!in_array($method, array_values($this->methods))) {
            throw new \BadMethodCallException((string) __('Invalid Method for Config Provider'));
        }

        array_unshift($args, sprintf("%s/%s/%s", $this->fieldset, $this->group, array_flip($this->methods)[$method]));

        return $this->execute(...$args);
    }
}
