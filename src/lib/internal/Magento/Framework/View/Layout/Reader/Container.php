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
namespace Magento\Framework\View\Layout\Reader;

use Magento\Framework\View\Layout;

/**
 * Class Container
 */
class Container implements Layout\ReaderInterface
{
    /**#@+
     * Supported types
     */
    const TYPE_CONTAINER = 'container';
    const TYPE_REFERENCE_CONTAINER = 'referenceContainer';
    /**#@-*/

    /**#@+
     * Names of container options in layout
     */
    const CONTAINER_OPT_HTML_TAG = 'htmlTag';
    const CONTAINER_OPT_HTML_CLASS = 'htmlClass';
    const CONTAINER_OPT_HTML_ID = 'htmlId';
    const CONTAINER_OPT_LABEL = 'label';
    /**#@-*/

    /**
     * @var \Magento\Framework\View\Layout\ScheduledStructure\Helper
     */
    protected $helper;

    /**
     * @var \Magento\Framework\View\Layout\Reader\Pool
     */
    protected $readerPool;

    /**
     * Constructor
     *
     * @param Layout\ScheduledStructure\Helper $helper
     * @param Layout\Reader\Pool $readerPool
     */
    public function __construct(
        Layout\ScheduledStructure\Helper $helper,
        Layout\Reader\Pool $readerPool
    ) {
        $this->helper = $helper;
        $this->readerPool = $readerPool;
    }

    /**
     * @return string[]
     */
    public function getSupportedNodes()
    {
        return [self::TYPE_CONTAINER, self::TYPE_REFERENCE_CONTAINER];
    }

    /**
     * {@inheritdoc}
     *
     * @param Context $readerContext
     * @param Layout\Element $currentElement
     * @param Layout\Element $parentElement
     * @return $this
     */
    public function process(Context $readerContext, Layout\Element $currentElement, Layout\Element $parentElement)
    {
        switch ($currentElement->getName()) {
            case self::TYPE_CONTAINER:
                $this->helper->scheduleStructure(
                    $readerContext->getScheduledStructure(),
                    $currentElement,
                    $parentElement
                );
                $this->mergeContainerAttributes($readerContext->getScheduledStructure(), $currentElement);
                break;

            case self::TYPE_REFERENCE_CONTAINER:
                $this->mergeContainerAttributes($readerContext->getScheduledStructure(), $currentElement);
                break;

            default:
                break;
        }
        return $this->readerPool->readStructure($readerContext, $currentElement);
    }

    /**
     * Merge Container attributes
     *
     * @param \Magento\Framework\View\Layout\ScheduledStructure $scheduledStructure
     * @param \Magento\Framework\View\Layout\Element $currentElement
     * @return void
     */
    protected function mergeContainerAttributes(
        Layout\ScheduledStructure $scheduledStructure,
        Layout\Element $currentElement
    ) {
        $containerName = $currentElement->getAttribute('name');
        $elementData = $scheduledStructure->getStructureElementData($containerName);

        if (isset($elementData['attributes'])) {
            $keys = array_keys($elementData['attributes']);
            foreach ($keys as $key) {
                if (isset($currentElement[$key])) {
                    $elementData['attributes'][$key] = (string)$currentElement[$key];
                }
            }
        } else {
            $elementData['attributes'] = [
                self::CONTAINER_OPT_HTML_TAG   => (string)$currentElement[self::CONTAINER_OPT_HTML_TAG],
                self::CONTAINER_OPT_HTML_ID    => (string)$currentElement[self::CONTAINER_OPT_HTML_ID],
                self::CONTAINER_OPT_HTML_CLASS => (string)$currentElement[self::CONTAINER_OPT_HTML_CLASS],
                self::CONTAINER_OPT_LABEL      => (string)$currentElement[self::CONTAINER_OPT_LABEL]
            ];
        }
        $scheduledStructure->setStructureElementData($containerName, $elementData);
    }
}
