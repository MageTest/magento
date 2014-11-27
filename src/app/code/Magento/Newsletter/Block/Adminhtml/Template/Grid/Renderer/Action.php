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

/**
 * Newsletter templates grid block action item renderer
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Newsletter\Block\Adminhtml\Template\Grid\Renderer;

class Action extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Action
{
    /**
     * Renderer for "Action" column in Newsletter templates grid
     *
     * @param \Magento\Framework\Object $row
     * @return string
     */
    public function render(\Magento\Framework\Object $row)
    {
        if ($row->isValidForSend()) {
            $actions[] = array(
                'url' => $this->getUrl('*/queue/edit', array('template_id' => $row->getId())),
                'caption' => __('Queue Newsletter...')
            );
        }

        $actions[] = array(
            'url' => $this->getUrl('*/*/preview', array('id' => $row->getId())),
            'popup' => true,
            'caption' => __('Preview')
        );

        $this->getColumn()->setActions($actions);

        return parent::render($row);
    }
}
