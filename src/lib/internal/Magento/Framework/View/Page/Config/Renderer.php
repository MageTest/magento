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

namespace Magento\Framework\View\Page\Config;

use Magento\Framework\View\Page\Config;
use Magento\Framework\View\Asset\GroupedCollection;

/**
 * Page config Renderer model
 */
class Renderer
{
    /**
     * @var \Magento\Framework\View\Page\Config
     */
    protected $pageConfig;

    /**
     * @var \Magento\Framework\View\Asset\MinifyService
     */
    protected $assetMinifyService;

    /**
     * @var \Magento\Framework\View\Asset\MergeService
     */
    protected $assetMergeService;

    /**
     * @var \Magento\Framework\Escaper
     */
    protected $escaper;

    /**
     * @var \Magento\Framework\Stdlib\String
     */
    protected $string;

    /**
     * @var \Magento\Framework\Logger
     */
    protected $logger;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var \Magento\Framework\App\Action\Title
     */
    protected $titles;

    /**
     * @param \Magento\Framework\View\Page\Config $pageConfig
     * @param \Magento\Framework\View\Asset\MinifyService $assetMinifyService
     * @param \Magento\Framework\View\Asset\MergeService $assetMergeService
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \Magento\Framework\Escaper $escaper
     * @param \Magento\Framework\Stdlib\String $string
     * @param \Magento\Framework\Logger $logger
     * @param \Magento\Framework\App\Action\Title $titles
     */
    public function __construct(
        Config $pageConfig,
        \Magento\Framework\View\Asset\MinifyService $assetMinifyService,
        \Magento\Framework\View\Asset\MergeService $assetMergeService,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Framework\Escaper $escaper,
        \Magento\Framework\Stdlib\String $string,
        \Magento\Framework\Logger $logger,
        \Magento\Framework\App\Action\Title $titles
    ) {
        $this->pageConfig = $pageConfig;
        $this->assetMinifyService = $assetMinifyService;
        $this->assetMergeService = $assetMergeService;
        $this->urlBuilder = $urlBuilder;
        $this->escaper = $escaper;
        $this->string = $string;
        $this->logger = $logger;
        $this->titles = $titles;
    }

    /**
     * @param string $elementType
     * @return string
     */
    public function renderElementAttributes($elementType)
    {
        $resultAttributes = [];
        foreach ($this->pageConfig->getElementAttributes($elementType) as $name => $value) {
            $resultAttributes[] = sprintf('%s="%s"', $name, $value);
        }
        return implode(' ', $resultAttributes);
    }

    /**
     * @return string
     */
    public function renderHeadContent()
    {
        $result = '';
        $result .= $this->renderMetadata();
        $result .= $this->renderTitle();
        $this->prepareFavicon();
        $result .= $this->renderAssets();
        $result .= $this->pageConfig->getIncludes();
        return $result;
    }

    /**
     * @return string
     */
    public function renderTitle()
    {
        $title = $this->pageConfig->getTitle();
        $this->titles->add($title, true);
        $this->pageConfig->setTitle(array_reverse($this->titles->get()));
        return '<title>' . $this->pageConfig->getTitle() . '</title>' . "\n";
    }

    /**
     * @return string
     */
    public function renderMetadata()
    {
        $result = '';
        foreach ($this->pageConfig->getMetadata() as $name => $content) {
            $metadataTemplate = $this->getMetadataTemplate($name);
            if (!$metadataTemplate) {
                continue;
            }
            $content = $this->processMetadataContent($name, $content);
            if ($content) {
                $result .= str_replace(['%name', '%content'], [$name, $content], $metadataTemplate);
            }
        }
        return $result;
    }

    /**
     * @param string $name
     * @param string $content
     * @return mixed
     */
    protected function processMetadataContent($name, $content)
    {
        $method = 'get' . $this->string->upperCaseWords($name, '_', '');
        if (method_exists($this->pageConfig, $method)) {
            $content = $this->pageConfig->$method();
        }
        return $content;
    }

    /**
     * @param string $name
     * @return bool|string
     */
    protected function getMetadataTemplate($name)
    {
        switch($name) {
            case 'charset':
                $metadataTemplate = '<meta charset="%content"/>' . "\n";
                break;

            case 'content_type':
                $metadataTemplate = '<meta http-equiv="Content-Type" content="%content"/>' . "\n";
                break;

            case 'x_ua_compatible':
                $metadataTemplate = '<meta http-equiv="X-UA-Compatible" content="%content"/>' . "\n";
                break;

            case 'media_type':
                $metadataTemplate = false;
                break;

            default:
                $metadataTemplate = '<meta name="%name" content="%content"/>' . "\n";
                break;
        }
        return $metadataTemplate;
    }

    /**
     * @return void
     */
    public function prepareFavicon()
    {
        if ($this->pageConfig->getFaviconFile()) {
            $this->pageConfig->addRemotePageAsset(
                $this->pageConfig->getFaviconFile(),
                Generator\Head::VIRTUAL_CONTENT_TYPE_LINK,
                ['attributes' => ['rel' => 'icon', 'type' => 'image/x-icon']],
                'icon'
            );
            $this->pageConfig->addRemotePageAsset(
                $this->pageConfig->getFaviconFile(),
                Generator\Head::VIRTUAL_CONTENT_TYPE_LINK,
                ['attributes' => ['rel' => 'shortcut icon', 'type' => 'image/x-icon']],
                'shortcut-icon'
            );
        } else {
            $this->pageConfig->addPageAsset(
                $this->pageConfig->getDefaultFavicon(),
                ['attributes' => ['rel' => 'icon', 'type' => 'image/x-icon']],
                'icon'
            );
            $this->pageConfig->addPageAsset(
                $this->pageConfig->getDefaultFavicon(),
                ['attributes' => ['rel' => 'shortcut icon', 'type' => 'image/x-icon']],
                'shortcut-icon'
            );
        }
    }

    /**
     * @return string
     */
    public function renderAssets()
    {
        $result = '';
        /** @var $group \Magento\Framework\View\Asset\PropertyGroup */
        foreach ($this->pageConfig->getAssetCollection()->getGroups() as $group) {
            $groupAssets = $this->assetMinifyService->getAssets($group->getAll());
            $groupAssets = $this->processMerge($groupAssets, $group);

            $attributes = $this->getGroupAttributes($group);
            $attributes = $this->addDefaultAttributes(
                $group->getProperty(GroupedCollection::PROPERTY_CONTENT_TYPE),
                $attributes
            );

            $groupTemplate = $this->getAssetTemplate(
                $group->getProperty(GroupedCollection::PROPERTY_CONTENT_TYPE),
                $attributes
            );
            $groupHtml = $this->renderAssetHtml($groupTemplate, $groupAssets);
            $groupHtml = $this->processIeCondition($groupHtml, $group);
            $result .= $groupHtml;
        }
        return $result;
    }

    /**
     * @param array $groupAssets
     * @param \Magento\Framework\View\Asset\PropertyGroup $group
     * @return array
     */
    protected function processMerge($groupAssets, $group)
    {
        if ($group->getProperty(GroupedCollection::PROPERTY_CAN_MERGE) && count($groupAssets) > 1) {
            $groupAssets = $this->assetMergeService->getMergedAssets(
                $groupAssets,
                $group->getProperty(GroupedCollection::PROPERTY_CONTENT_TYPE)
            );
        }
        return $groupAssets;
    }

    /**
     * @param \Magento\Framework\View\Asset\PropertyGroup $group
     * @return string|null
     */
    protected function getGroupAttributes($group)
    {
        $attributes = $group->getProperty('attributes');
        if (!empty($attributes)) {
            if (is_array($attributes)) {
                $attributesString = '';
                foreach ($attributes as $name => $value) {
                    $attributesString .= ' ' . $name . '="' . $this->escaper->escapeHtml($value) . '"';
                }
                $attributes = $attributesString;
            } else {
                $attributes = ' ' . $attributes;
            }
        }
        return $attributes;
    }

    /**
     * @param string $contentType
     * @param string $attributes
     * @return string
     */
    protected function addDefaultAttributes($contentType, $attributes)
    {
        switch ($contentType) {
            case 'js':
                $attributes = ' type="text/javascript" ' . $attributes;
                break;

            case 'css':
                $attributes = ' rel="stylesheet" type="text/css" ' . ($attributes ?: ' media="all"');
                break;
        }
        return $attributes;
    }

    /**
     * @param string $contentType
     * @param string|null $attributes
     * @return string
     */
    protected function getAssetTemplate($contentType, $attributes)
    {
        switch ($contentType) {
            case 'js':
                $groupTemplate = '<script ' . $attributes . ' src="%s"></script>' . "\n";
                break;

            case 'css':
            default:
                $groupTemplate = '<link ' . $attributes . ' href="%s" />' . "\n";
                break;
        }
        return $groupTemplate;
    }

    /**
     * @param string $groupHtml
     * @param \Magento\Framework\View\Asset\PropertyGroup $group
     * @return string
     */
    protected function processIeCondition($groupHtml, $group)
    {
        $ieCondition = $group->getProperty('ie_condition');
        if (!empty($ieCondition)) {
            $groupHtml = '<!--[if ' . $ieCondition . ']>' . "\n" . $groupHtml . '<![endif]-->' . "\n";
        }
        return $groupHtml;
    }

    /**
     * Render HTML tags referencing corresponding URLs
     *
     * @param string $template
     * @param array $assets
     * @return string
     */
    protected function renderAssetHtml($template, $assets)
    {
        $result = '';
        try {
            /** @var $asset \Magento\Framework\View\Asset\AssetInterface */
            foreach ($assets as $asset) {
                $result .= sprintf($template, $asset->getUrl());
            }
        } catch (\Magento\Framework\Exception $e) {
            $this->logger->logException($e);
            $result .= sprintf($template, $this->urlBuilder->getUrl('', ['_direct' => 'core/index/notFound']));
        }
        return $result;
    }
}
