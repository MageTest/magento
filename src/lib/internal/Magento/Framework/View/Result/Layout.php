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
 * @copyright  Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Framework\View\Result;

use Magento\Framework;
use Magento\Framework\View;
use Magento\Framework\Controller\AbstractResult;
use Magento\Framework\App\ResponseInterface;

/**
 * A generic layout response can be used for rendering any kind of layout
 * So it comprises a response body from the layout elements it has and sets it to the HTTP response
 */
class Layout extends AbstractResult
{
    /**
     * @var \Magento\Framework\View\LayoutFactory
     */
    protected $layoutFactory;

    /**
     * @var \Magento\Framework\View\Layout\BuilderFactory
     */
    protected $layoutBuilderFactory;

    /**
     * @var \Magento\Framework\View\Layout\Reader\Pool
     */
    protected $layoutReaderPool;

    /**
     * @var \Magento\Framework\View\LayoutInterface
     */
    protected $layout;

    /**
     * @var \Magento\Framework\Translate\InlineInterface
     */
    protected $translateInline;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * Constructor
     *
     * @param View\Element\Template\Context $context
     * @param View\LayoutFactory $layoutFactory
     * @param View\Layout\Reader\Pool $layoutReaderPool
     * @param Framework\Translate\InlineInterface $translateInline
     * @param View\Layout\BuilderFactory $layoutBuilderFactory
     * @param bool $isIsolated
     */
    public function __construct(
        View\Element\Template\Context $context,
        View\LayoutFactory $layoutFactory,
        View\Layout\Reader\Pool $layoutReaderPool,
        Framework\Translate\InlineInterface $translateInline,
        View\Layout\BuilderFactory $layoutBuilderFactory,
        $isIsolated = false
    ) {
        $this->layoutFactory = $layoutFactory;
        $this->layoutBuilderFactory = $layoutBuilderFactory;
        $this->layoutReaderPool = $layoutReaderPool;
        $this->eventManager = $context->getEventManager();
        $this->request = $context->getRequest();
        $this->translateInline = $translateInline;
        // TODO Shared layout object will be deleted in MAGETWO-28359
        $this->layout = $isIsolated
            ? $this->layoutFactory->create(['reader' => $this->layoutReaderPool])
            : $context->getLayout();
        $this->initLayoutBuilder();
    }

    /**
     * Create layout builder
     *
     * @return void
     */
    protected function initLayoutBuilder()
    {
        $this->layoutBuilderFactory->create(View\Layout\BuilderFactory::TYPE_LAYOUT, ['layout' => $this->layout]);
    }

    /**
     * Get layout instance for current page
     *
     * @return \Magento\Framework\View\Layout
     */
    public function getLayout()
    {
        return $this->layout;
    }

    /**
     * @return $this
     */
    public function initLayout()
    {
        return $this;
    }

    /**
     * @return $this
     */
    public function addDefaultHandle()
    {
        $this->addHandle($this->getDefaultLayoutHandle());
        return $this;
    }

    /**
     * Retrieve the default layout handle name for the current action
     *
     * @return string
     */
    public function getDefaultLayoutHandle()
    {
        return strtolower($this->request->getFullActionName());
    }

    /**
     * @param string|string[] $handleName
     * @return $this
     */
    public function addHandle($handleName)
    {
        $this->getLayout()->getUpdate()->addHandle($handleName);
        return $this;
    }

    /**
     * Add update to merge object
     *
     * @param string $update
     * @return $this
     */
    public function addUpdate($update)
    {
        $this->getLayout()->getUpdate()->addUpdate($update);
        return $this;
    }

    /**
     * Render current layout
     *
     * @param ResponseInterface $response
     * @return $this
     */
    public function renderResult(ResponseInterface $response)
    {
        \Magento\Framework\Profiler::start('LAYOUT');
        \Magento\Framework\Profiler::start('layout_render');

        $this->applyHttpHeaders($response);
        $this->render($response);

        $this->eventManager->dispatch('controller_action_layout_render_before');
        $this->eventManager->dispatch(
            'controller_action_layout_render_before_' . $this->request->getFullActionName()
        );
        \Magento\Framework\Profiler::stop('layout_render');
        \Magento\Framework\Profiler::stop('LAYOUT');
        return $this;
    }

    /**
     * Render current layout
     *
     * @param ResponseInterface $response
     * @return $this
     */
    protected function render(ResponseInterface $response)
    {
        $response->appendBody($this->layout->getOutput());
        return $this;
    }
}
