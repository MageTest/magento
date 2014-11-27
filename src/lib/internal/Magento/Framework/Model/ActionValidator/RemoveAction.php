<?php
/**
 * Action validator, remove action
 *
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Framework\Model\ActionValidator;

use Magento\Framework\Model\AbstractModel;

class RemoveAction
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var array
     */
    protected $protectedModels;

    /**
     * @param \Magento\Framework\Registry $registry
     * @param array $protectedModels
     */
    public function __construct(\Magento\Framework\Registry $registry, array $protectedModels = array())
    {
        $this->registry = $registry;
        $this->protectedModels = $protectedModels;
    }

    /**
     * Safeguard function that checks if item can be removed
     *
     * @param \Magento\Framework\Model\AbstractModel $model
     * @return bool
     */
    public function isAllowed(AbstractModel $model)
    {
        $isAllowed = true;

        if ($this->registry->registry('isSecureArea')) {
            $isAllowed = true;
        } elseif (in_array($this->getBaseClassName($model), $this->protectedModels)) {
            $isAllowed = false;
        }

        return $isAllowed;
    }

    /**
     * Get clean model name without Interceptor and Proxy part and slashes
     * @param object $object
     * @return mixed
     */
    protected function getBaseClassName($object)
    {
        $className = ltrim(get_class($object), "\\");
        $className = str_replace(array('\Interceptor', '\Proxy'), array(''), $className);

        return $className;
    }
}
