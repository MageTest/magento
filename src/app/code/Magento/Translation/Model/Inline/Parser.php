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
 * This class is responsible for parsing content and applying necessary html element
 * wrapping and client scripts for inline translation.
 */
namespace Magento\Translation\Model\Inline;

class Parser implements \Magento\Framework\Translate\Inline\ParserInterface
{
    /**
     * data-translate html element attribute name
     */
    const DATA_TRANSLATE = 'data-translate';

    /**
     * Response body or JSON content string
     *
     * @var string
     */
    protected $_content;

    /**
     * Current content is JSON or Response body
     *
     * @var bool
     */
    protected $_isJson = false;

    /**
     * Get max translate block in same tag
     *
     * @var int
     */
    protected $_maxTranslateBlocks = 7;

    /**
     * List of global tags
     *
     * @var array
     */
    protected $_allowedTagsGlobal = array('script' => 'String in Javascript', 'title' => 'Page title');

    /**
     * List of simple tags
     *
     * @var array
     */
    protected $_allowedTagsSimple = array(
        'legend' => 'Caption for the fieldset element',
        'label' => 'Label for an input element.',
        'button' => 'Push button',
        'a' => 'Link label',
        'b' => 'Bold text',
        'strong' => 'Strong emphasized text',
        'i' => 'Italic text',
        'em' => 'Emphasized text',
        'u' => 'Underlined text',
        'sup' => 'Superscript text',
        'sub' => 'Subscript text',
        'span' => 'Span element',
        'small' => 'Smaller text',
        'big' => 'Bigger text',
        'address' => 'Contact information',
        'blockquote' => 'Long quotation',
        'q' => 'Short quotation',
        'cite' => 'Citation',
        'caption' => 'Table caption',
        'abbr' => 'Abbreviated phrase',
        'acronym' => 'An acronym',
        'var' => 'Variable part of a text',
        'dfn' => 'Term',
        'strike' => 'Strikethrough text',
        'del' => 'Deleted text',
        'ins' => 'Inserted text',
        'h1' => 'Heading level 1',
        'h2' => 'Heading level 2',
        'h3' => 'Heading level 3',
        'h4' => 'Heading level 4',
        'h5' => 'Heading level 5',
        'h6' => 'Heading level 6',
        'center' => 'Centered text',
        'select' => 'List options',
        'img' => 'Image',
        'input' => 'Form element'
    );

    /**
     * @var \Magento\Translation\Model\Resource\StringFactory
     */
    protected $_resourceFactory;

    /**
     * @var \Magento\Framework\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Zend_Filter_Interface
     */
    protected $_inputFilter;

    /**
     * @var \Magento\Framework\App\State
     */
    protected $_appState;

    /**
     * @var \Magento\Framework\Translate\InlineInterface
     */
    protected $_translateInline;

    /**
     * @var \Magento\Framework\App\Cache\TypeListInterface
     */
    protected $_appCache;

    /**
     * Initialize base inline translation model
     *
     * @param \Magento\Framework\StoreManagerInterface $storeManager
     * @param \Magento\Translation\Model\Resource\StringFactory $resource
     * @param \Zend_Filter_Interface $inputFilter
     * @param \Magento\Framework\App\State $appState
     * @param \Magento\Framework\App\Cache\TypeListInterface $appCache,
     * @param \Magento\Framework\Translate\InlineInterface $translateInline
     */
    public function __construct(
        \Magento\Translation\Model\Resource\StringFactory $resource,
        \Magento\Framework\StoreManagerInterface $storeManager,
        \Zend_Filter_Interface $inputFilter,
        \Magento\Framework\App\State $appState,
        \Magento\Framework\App\Cache\TypeListInterface $appCache,
        \Magento\Framework\Translate\InlineInterface $translateInline
    ) {
        $this->_resourceFactory = $resource;
        $this->_storeManager = $storeManager;
        $this->_inputFilter = $inputFilter;
        $this->_appState = $appState;
        $this->_appCache = $appCache;
        $this->_translateInline = $translateInline;
    }

    /**
     * Parse and save edited translation
     *
     * @param array $translateParams
     * @return $this
     */
    public function processAjaxPost(array $translateParams)
    {
        if (!$this->_translateInline->isAllowed()) {
            return $this;
        }
        $this->_appCache->invalidate(\Magento\Framework\App\Cache\Type\Translate::TYPE_IDENTIFIER);

        $this->_validateTranslationParams($translateParams);
        $this->_filterTranslationParams($translateParams, array('custom'));

        /** @var $validStoreId int */
        $validStoreId = $this->_storeManager->getStore()->getId();

        /** @var $resource \Magento\Translation\Model\Resource\String */
        $resource = $this->_resourceFactory->create();
        foreach ($translateParams as $param) {
            if ($this->_appState->getAreaCode() == \Magento\Backend\App\Area\FrontNameResolver::AREA_CODE) {
                $storeId = 0;
            } else {
                if (empty($param['perstore'])) {
                    $resource->deleteTranslate($param['original'], null, false);
                    $storeId = 0;
                } else {
                    $storeId = $validStoreId;
                }
            }
            $resource->saveTranslate($param['original'], $param['custom'], null, $storeId);
        }
        return $this;
    }

    /**
     * Validate the structure of translation parameters
     *
     * @param array $translateParams
     * @return void
     * @throws \InvalidArgumentException
     */
    protected function _validateTranslationParams(array $translateParams)
    {
        foreach ($translateParams as $param) {
            if (!is_array($param) || !isset($param['original']) || !isset($param['custom'])) {
                throw new \InvalidArgumentException(
                    'Both original and custom phrases are required for inline translation.'
                );
            }
        }
    }

    /**
     * Apply input filter to values of translation parameters
     *
     * @param array &$translateParams
     * @param array $fieldNames Names of fields values of which are to be filtered
     * @return void
     */
    protected function _filterTranslationParams(array &$translateParams, array $fieldNames)
    {
        foreach ($translateParams as &$param) {
            foreach ($fieldNames as $fieldName) {
                $param[$fieldName] = $this->_inputFilter->filter($param[$fieldName]);
            }
        }
    }

    /**
     * Replace html body with translation wrapping.
     *
     * @param string $body
     * @return string
     */
    public function processResponseBodyString($body)
    {
        $this->_content = $body;

        $this->_specialTags();
        $this->_tagAttributes();
        $this->_otherText();

        return $this->_content;
    }

    /**
     * Returns the body content that is being parsed.
     *
     * @return string
     */
    public function getContent()
    {
        return $this->_content;
    }

    /**
     * Sets the body content that is being parsed passed upon the passed in string.
     *
     * @param string $content
     * @return void
     */
    public function setContent($content)
    {
        $this->_content = $content;
    }

    /**
     * Set flag about parsed content is Json
     *
     * @param bool $flag
     * @return $this
     */
    public function setIsJson($flag)
    {
        $this->_isJson = $flag;
        return $this;
    }

    /**
     * Get attribute location.
     *
     * @param array $matches
     * @param array $options
     * @return string
     */
    protected function _getAttributeLocation($matches, $options)
    {
        // return value should not be translated.
        return 'Tag attribute (ALT, TITLE, etc.)';
    }

    /**
     * Get tag location
     *
     * @param array $matches
     * @param array $options
     * @return string
     */
    protected function _getTagLocation($matches, $options)
    {
        $tagName = strtolower($options['tagName']);

        if (isset($options['tagList'][$tagName])) {
            return $options['tagList'][$tagName];
        }

        return ucfirst($tagName) . ' Text';
    }

    /**
     * Format translation for special tags.  Adding translate mode attribute for vde requests.
     *
     * @param string $tagHtml
     * @param string $tagName
     * @param array $trArr
     * @return string
     */
    protected function _applySpecialTagsFormat($tagHtml, $tagName, $trArr)
    {
        $specialTags = $tagHtml . '<span class="translate-inline-' . $tagName . '" ' . $this->_getHtmlAttribute(
            self::DATA_TRANSLATE,
            htmlspecialchars('[' . join(',', $trArr) . ']')
        );
        $additionalAttr = $this->_getAdditionalHtmlAttribute($tagName);
        if ($additionalAttr !== null) {
            $specialTags .= ' ' . $additionalAttr . '>';
        } else {
            $specialTags .= '>' . strtoupper($tagName);
        }
        $specialTags .= '</span>';
        return $specialTags;
    }

    /**
     * Format translation for simple tags.  Added translate mode attribute for vde requests.
     *
     * @param string $tagHtml
     * @param string  $tagName
     * @param array $trArr
     * @return string
     */
    protected function _applySimpleTagsFormat($tagHtml, $tagName, $trArr)
    {
        $simpleTags = substr(
            $tagHtml,
            0,
            strlen($tagName) + 1
        ) . ' ' . $this->_getHtmlAttribute(
            self::DATA_TRANSLATE,
            htmlspecialchars('[' . join(',', $trArr) . ']')
        );
        $additionalAttr = $this->_getAdditionalHtmlAttribute($tagName);
        if ($additionalAttr !== null) {
            $simpleTags .= ' ' . $additionalAttr;
        }
        $simpleTags .= substr($tagHtml, strlen($tagName) + 1);
        return $simpleTags;
    }

    /**
     * Get translate data by regexp
     *
     * @param string $regexp
     * @param string &$text
     * @param string|array $locationCallback
     * @param array $options
     * @return array
     */
    private function _getTranslateData($regexp, &$text, $locationCallback, $options = array())
    {
        $trArr = array();
        $next = 0;
        while (preg_match($regexp, $text, $matches, PREG_OFFSET_CAPTURE, $next)) {
            $trArr[] = json_encode(
                array(
                    'shown' => $matches[1][0],
                    'translated' => $matches[2][0],
                    'original' => $matches[3][0],
                    'location' => call_user_func($locationCallback, $matches, $options)
                )
            );
            $text = substr_replace($text, $matches[1][0], $matches[0][1], strlen($matches[0][0]));
            $next = $matches[0][1];
        }
        return $trArr;
    }

    /**
     * Prepare tags inline translates
     *
     * @return void
     */
    private function _tagAttributes()
    {
        $this->_prepareTagAttributesForContent($this->_content);
    }

    /**
     * Prepare tags inline translates for the content
     *
     * @param string &$content
     * @return void
     */
    private function _prepareTagAttributesForContent(&$content)
    {
        $quoteHtml = $this->_getHtmlQuote();
        $tagMatch = array();
        $nextTag = 0;
        $tagRegExp = '#<([a-z]+)\s*?[^>]+?((' . self::REGEXP_TOKEN . ')[^>]*?)+\\\\?/?>#iS';
        while (preg_match($tagRegExp, $content, $tagMatch, PREG_OFFSET_CAPTURE, $nextTag)) {
            $tagHtml = $tagMatch[0][0];
            $matches = array();
            $attrRegExp = '#' . self::REGEXP_TOKEN . '#S';
            $trArr = $this->_getTranslateData($attrRegExp, $tagHtml, array($this, '_getAttributeLocation'));
            if ($trArr) {
                $transRegExp = '# ' . $this->_getHtmlAttribute(
                    self::DATA_TRANSLATE,
                    '\[([^' . preg_quote($quoteHtml) . ']*)]'
                ) . '#i';
                if (preg_match($transRegExp, $tagHtml, $matches)) {
                    $tagHtml = str_replace($matches[0], '', $tagHtml);
                    //remove tra
                    $trAttr = ' ' . $this->_getHtmlAttribute(
                        self::DATA_TRANSLATE,
                        htmlspecialchars('[' . $matches[1] . ',' . join(',', $trArr) . ']')
                    );
                } else {
                    $trAttr = ' ' . $this->_getHtmlAttribute(
                        self::DATA_TRANSLATE,
                        htmlspecialchars('[' . join(',', $trArr) . ']')
                    );
                }
                $trAttr = $this->_addTranslateAttribute($trAttr);

                $tagHtml = substr_replace($tagHtml, $trAttr, strlen($tagMatch[1][0]) + 1, 1);
                $content = substr_replace($content, $tagHtml, $tagMatch[0][1], strlen($tagMatch[0][0]));
            }
            $nextTag = $tagMatch[0][1] + strlen($tagHtml);
        }
    }

    /**
     * Get html element attribute
     *
     * @param string $name
     * @param string $value
     * @return string
     */
    private function _getHtmlAttribute($name, $value)
    {
        return $name . '=' . $this->_getHtmlQuote() . $value . $this->_getHtmlQuote();
    }

    /**
     * Add data-translate-mode attribute
     *
     * @param string $trAttr
     * @return string
     */
    private function _addTranslateAttribute($trAttr)
    {
        $translateAttr = $trAttr;
        $additionalAttr = $this->_getAdditionalHtmlAttribute();
        if ($additionalAttr !== null) {
            $translateAttr .= ' ' . $additionalAttr . ' ';
        }
        return $translateAttr;
    }

    /**
     * Get html quote symbol
     *
     * @return string
     */
    private function _getHtmlQuote()
    {
        if ($this->_isJson) {
            return '\"';
        } else {
            return '"';
        }
    }

    /**
     * Prepare special tags
     *
     * @return void
     */
    private function _specialTags()
    {
        $this->_translateTags($this->_content, $this->_allowedTagsGlobal, '_applySpecialTagsFormat');
        $this->_translateTags($this->_content, $this->_allowedTagsSimple, '_applySimpleTagsFormat');
    }

    /**
     * Prepare simple tags
     *
     * @param string &$content
     * @param array $tagsList
     * @param string|array $formatCallback
     * @return void
     */
    private function _translateTags(&$content, $tagsList, $formatCallback)
    {
        $nextTag = 0;

        $tags = implode('|', array_keys($tagsList));
        $tagRegExp = '#<(' . $tags . ')(/?>| \s*[^>]*+/?>)#iSU';
        $tagMatch = array();
        while (preg_match($tagRegExp, $content, $tagMatch, PREG_OFFSET_CAPTURE, $nextTag)) {
            $tagName = strtolower($tagMatch[1][0]);
            if (substr($tagMatch[0][0], -2) == '/>') {
                $tagClosurePos = $tagMatch[0][1] + strlen($tagMatch[0][0]);
            } else {
                $tagClosurePos = $this->_findEndOfTag($content, $tagName, $tagMatch[0][1]);
            }

            if ($tagClosurePos === false) {
                $nextTag += strlen($tagMatch[0][0]);
                continue;
            }

            $tagLength = $tagClosurePos - $tagMatch[0][1];

            $tagStartLength = strlen($tagMatch[0][0]);

            $tagHtml = $tagMatch[0][0] . substr(
                $content,
                $tagMatch[0][1] + $tagStartLength,
                $tagLength - $tagStartLength
            );
            $tagClosurePos = $tagMatch[0][1] + strlen($tagHtml);

            $trArr = $this->_getTranslateData(
                '#' . self::REGEXP_TOKEN . '#iS',
                $tagHtml,
                array($this, '_getTagLocation'),
                array('tagName' => $tagName, 'tagList' => $tagsList)
            );

            if (!empty($trArr)) {
                $trArr = array_unique($trArr);
                $tagHtml = call_user_func(array($this, $formatCallback), $tagHtml, $tagName, $trArr);
                $tagClosurePos = $tagMatch[0][1] + strlen($tagHtml);
                $content = substr_replace($content, $tagHtml, $tagMatch[0][1], $tagLength);
            }
            $nextTag = $tagClosurePos;
        }
    }

    /**
     * Find end of tag
     *
     * @param string $body
     * @param string $tagName
     * @param int $from
     * @return bool|int return false if end of tag is not found
     */
    private function _findEndOfTag($body, $tagName, $from)
    {
        $openTag = '<' . $tagName;
        $closeTag = ($this->_isJson ? '<\\/' : '</') . $tagName;
        $tagLength = strlen($tagName);
        $length = $tagLength + 1;
        $end = $from + 1;
        while (substr_count($body, $openTag, $from, $length) !== substr_count($body, $closeTag, $from, $length)) {
            $end = strpos($body, $closeTag, $end + $tagLength + 1);
            if ($end === false) {
                return false;
            }
            $length = $end - $from + $tagLength + 3;
        }
        if (preg_match('#<\\\\?\/' . $tagName . '\s*?>#i', $body, $tagMatch, null, $end)) {
            return $end + strlen($tagMatch[0]);
        } else {
            return false;
        }
    }

    /**
     * Prepare other text inline translates
     *
     * @return void
     */
    private function _otherText()
    {
        $next = 0;
        $matches = array();
        while (preg_match('#' . self::REGEXP_TOKEN . '#', $this->_content, $matches, PREG_OFFSET_CAPTURE, $next)) {
            $translateProperties = json_encode(
                array(
                    'shown' => $matches[1][0],
                    'translated' => $matches[2][0],
                    'original' => $matches[3][0],
                    'location' => 'Text',
                    'scope' => $matches[4][0]
                )
            );

            $spanHtml = $this->_getDataTranslateSpan(
                htmlspecialchars('[' . $translateProperties . ']'),
                $matches[1][0]
            );
            $this->_content = substr_replace($this->_content, $spanHtml, $matches[0][1], strlen($matches[0][0]));
            $next = $matches[0][1] + strlen($spanHtml) - 1;
        }
    }

    /**
     * Returns the html span that contains the data translate attribute including vde specific translate mode attribute
     *
     * @param string $data
     * @param string $text
     * @return string
     */
    protected function _getDataTranslateSpan($data, $text)
    {
        $translateSpan = '<span ' . $this->_getHtmlAttribute(self::DATA_TRANSLATE, $data);
        $additionalAttr = $this->_getAdditionalHtmlAttribute();
        if ($additionalAttr !== null) {
            $translateSpan .= ' ' . $additionalAttr;
        }
        $translateSpan .= '>' . $text . '</span>';
        return $translateSpan;
    }

    /**
     * Add an additional html attribute if needed.
     *
     * @param mixed $tagName
     * @return string
     */
    protected function _getAdditionalHtmlAttribute($tagName = null)
    {
        return $this->_translateInline->getAdditionalHtmlAttribute($tagName);
    }
}
