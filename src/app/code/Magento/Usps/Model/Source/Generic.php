<?php
/**
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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Usps\Model\Source;

use Magento\Shipping\Model\Carrier\Source\GenericInterface;
use Magento\Usps\Model\Carrier;

/**
 * Generic source
 */
class Generic implements GenericInterface
{
    /**
     * @var \Magento\Usps\Model\Carrier
     */
    protected $shippingUsps;

    /**
     * Carrier code
     *
     * @var string
     */
    protected $code = '';

    /**
     * @param \Magento\Usps\Model\Carrier $shippingUsps
     */
    public function __construct(Carrier $shippingUsps)
    {
        $this->shippingUsps = $shippingUsps;
    }

    /**
     * Returns array to be used in multiselect on back-end
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = array();
        $codes = $this->shippingUsps->getCode($this->code);
        if ($codes) {
            foreach ($codes as $code => $title) {
                $options[] = array('value' => $code, 'label' => __($title));
            }
        }
        return $options;
    }
}
