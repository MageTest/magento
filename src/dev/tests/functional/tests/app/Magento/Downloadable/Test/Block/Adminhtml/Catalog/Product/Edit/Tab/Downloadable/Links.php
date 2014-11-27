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
namespace Magento\Downloadable\Test\Block\Adminhtml\Catalog\Product\Edit\Tab\Downloadable;

use Mtf\Block\Form;
use Mtf\Client\Element;
use Mtf\Client\Element\Locator;

/**
 * Class Links
 *
 * Link Form of downloadable product
 */
class Links extends Form
{
    /**
     * 'Show Links block' button
     *
     * @var string
     */
    protected $showLinks = '//*[@id="dt-links"]/a';

    /**
     * 'Add New Row for links' button
     *
     * @var string
     */
    protected $addNewLinkRow = '//button[@id="add_link_item"]';

    /**
     * Downloadable link item block
     *
     * @var string
     */
    protected $rowBlock = '//*[@id="link_items_body"]/tr[%d]';

    /**
     * Downloadable link title block
     *
     * @var string
     */
    protected $title = "//*[@id='downloadable_links_title']";

    /**
     * Add new link row button block
     *
     * @var string
     */
    protected $addLinkButtonBlock = '#dd-links .col-actions-add:last-child';

    /**
     * Get Downloadable link item block
     *
     * @param int $index
     * @param Element $element
     * @return LinkRow
     */
    public function getRowBlock($index, Element $element = null)
    {
        $element = $element ? : $this->_rootElement;
        return $this->blockFactory->create(
            'Magento\Downloadable\Test\Block\Adminhtml\Catalog\Product\Edit\Tab\Downloadable\LinkRow',
            ['element' => $element->find(sprintf($this->rowBlock, ++$index), Locator::SELECTOR_XPATH)]
        );
    }

    /**
     * Fill links block
     *
     * @param array $fields
     * @param Element $element
     * @return void
     */
    public function fillLinks(array $fields, Element $element = null)
    {
        $element = $element ? : $this->_rootElement;
        if (!$element->find($this->title, Locator::SELECTOR_XPATH)->isVisible()) {
            $element->find($this->showLinks, Locator::SELECTOR_XPATH)->click();
        }
        $mapping = $this->dataMapping(
            ['title' => $fields['title'], 'links_purchased_separately' => $fields['links_purchased_separately']]
        );
        $this->_fill($mapping);
        foreach ($fields['downloadable']['link'] as $index => $link) {
            $rowBlock = $this->getRowBlock($index, $element);
            if (!$rowBlock->isVisible()) {
                $element->find($this->addLinkButtonBlock)->click();
                $element->find($this->addNewLinkRow, Locator::SELECTOR_XPATH)->click();
            }
            $rowBlock->fillLinkRow($link);
        }
    }

    /**
     * Get data links block
     *
     * @param array|null $fields
     * @param Element|null $element
     * @return array
     */
    public function getDataLinks(array $fields = null, Element $element = null)
    {
        $element = $element ? : $this->_rootElement;
        if (!$element->find($this->title, Locator::SELECTOR_XPATH)->isVisible()) {
            $element->find($this->showLinks, Locator::SELECTOR_XPATH)->click();
        }
        $mapping = $this->dataMapping(
            ['title' => $fields['title'], 'links_purchased_separately' => $fields['links_purchased_separately']]
        );
        $newFields = $this->_getData($mapping);
        foreach ($fields['downloadable']['link'] as $index => $link) {
            $newFields['downloadable']['link'][$index] = $this->getRowBlock($index, $element)
                ->getDataLinkRow($link);
        }
        return $newFields;
    }

    /**
     * Delete all links and clear title.
     *
     * @return void
     */
    public function clearDownloadableData()
    {
        $this->_rootElement->find($this->title, Locator::SELECTOR_XPATH)->setValue('');
        $index = 1;
        while ($this->_rootElement->find(sprintf($this->rowBlock, $index), Locator::SELECTOR_XPATH)->isVisible()) {
            $rowBlock = $this->getRowBlock($index - 1);
            $rowBlock->clickDeleteButton();
            ++$index;
        }
    }
}
