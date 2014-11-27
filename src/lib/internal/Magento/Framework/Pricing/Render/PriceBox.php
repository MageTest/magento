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

namespace Magento\Framework\Pricing\Render;

use Magento\Framework\Pricing\Amount\AmountInterface;
use Magento\Framework\Pricing\Object\SaleableInterface;
use Magento\Framework\Pricing\Price\PriceInterface;
use Magento\Framework\View\Element\Template;

/**
 * Default price box renderer
 *
 * @method bool hasListClass()
 * @method string getListClass()
 */
class PriceBox extends Template implements PriceBoxRenderInterface
{
    /**
     * @var SaleableInterface
     */
    protected $saleableItem;

    /**
     * @var PriceInterface
     */
    protected $price;

    /**
     * @var RendererPool
     */
    protected $rendererPool;

    /**
     * @param Template\Context  $context
     * @param SaleableInterface $saleableItem
     * @param PriceInterface    $price
     * @param RendererPool      $rendererPool
     * @param array             $data
     */
    public function __construct(
        Template\Context $context,
        SaleableInterface $saleableItem,
        PriceInterface $price,
        RendererPool $rendererPool,
        array $data = []
    ) {
        $this->saleableItem = $saleableItem;
        $this->price = $price;
        $this->rendererPool = $rendererPool;
        parent::__construct($context, $data);
    }

    /**
     * @return string
     */
    protected function _toHtml()
    {
        $cssClasses = $this->hasData('css_classes') ? explode(' ', $this->getData('css_classes')) : [];
        $cssClasses[] = 'price-' . $this->getPrice()->getPriceCode();
        $this->setData('css_classes', implode(' ', $cssClasses));
        return parent::_toHtml();
    }

    /**
     * @return SaleableInterface
     */
    public function getSaleableItem()
    {
        return $this->saleableItem;
    }

    /**
     * @return PriceInterface
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * Get price id
     *
     * @param null|string $defaultPrefix
     * @param null|string $defaultSuffix
     * @return string
     */
    public function getPriceId($defaultPrefix = null, $defaultSuffix = null)
    {
        if ($this->hasData('price_id')) {
            return $this->getData('price_id');
        }
        $priceId = $this->saleableItem->getId();
        $prefix = $this->hasData('price_id_prefix') ? $this->getData('price_id_prefix') : $defaultPrefix;
        $suffix = $this->hasData('price_id_suffix') ? $this->getData('price_id_suffix') : $defaultSuffix;
        $priceId = $prefix . $priceId . $suffix;
        return $priceId;
    }

    /**
     * Retrieve price object of given type and quantity
     *
     * @param string $priceCode
     * @return PriceInterface
     */
    public function getPriceType($priceCode)
    {
        return $this->saleableItem->getPriceInfo()->getPrice($priceCode);
    }

    /**
     * @param AmountInterface $amount
     * @param array $arguments
     * @return string
     */
    public function renderAmount(AmountInterface $amount, array $arguments = [])
    {
        $arguments = array_replace($this->getData(), $arguments);

        //@TODO AmountInterface does not contain toHtml() method
        return $this->getAmountRender($amount, $arguments)->toHtml();
    }

    /**
     * @param AmountInterface $amount
     * @param array $arguments
     * @return AmountRenderInterface
     */
    protected function getAmountRender(AmountInterface $amount, array $arguments = [])
    {
        return $this->rendererPool->createAmountRender(
            $amount,
            $this->getSaleableItem(),
            $this->getPrice(),
            $arguments
        );
    }

    /**
     * @return RendererPool
     */
    public function getRendererPool()
    {
        return $this->rendererPool;
    }
}
