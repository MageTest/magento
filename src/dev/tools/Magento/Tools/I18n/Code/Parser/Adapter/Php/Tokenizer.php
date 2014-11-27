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
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Tools\I18n\Code\Parser\Adapter\Php;

/**
 * Tokenizer
 */
class Tokenizer
{
    /**
     * Tokens
     *
     * @var array
     */
    private $_tokens = array();

    /**
     * Tokens count
     *
     * @var int
     */
    private $_tokensCount;

    /**
     * Open brackets
     *
     * @var int
     */
    private $_openBrackets;

    /**
     * Close brackets
     *
     * @var int
     */
    private $_closeBrackets;

    /**
     * Parse given file
     *
     * @param string $filePath
     * @return void
     */
    public function parse($filePath)
    {
        $this->_tokens = token_get_all(file_get_contents($filePath));
        $this->_tokensCount = count($this->_tokens);
    }

    /**
     * Get arguments tokens of function
     *
     * @return array
     */
    public function getFunctionArgumentsTokens()
    {
        $arguments = array();
        try {
            $this->_openBrackets = 1;
            $this->_closeBrackets = 0;
            $argumentNumber = 0;
            while (true) {
                $token = $this->getNextToken();
                if ($token->isSemicolon()) {
                    break;
                }
                if ($token->isOpenBrace()) {
                    $this->_skipInnerArgumentInvoke();
                    continue;
                }
                if ($token->isCloseBrace()) {
                    $this->_closeBrackets++;
                }
                $arguments[$argumentNumber][] = $token;
                if ($token->isComma() && $this->_isInnerArgumentClosed()) {
                    array_pop($arguments[$argumentNumber]);
                    $argumentNumber++;
                }
                if ($this->_openBrackets == $this->_closeBrackets) {
                    break;
                }
            }
        } catch (\Exception $e) {
            return array();
        }
        return $arguments;
    }

    /**
     * Whenever inner argument closed
     *
     * @return bool
     */
    private function _isInnerArgumentClosed()
    {
        return $this->_openBrackets - 1 == $this->_closeBrackets;
    }

    /**
     * Skip invoke the inner argument of function
     *
     * @return void
     */
    private function _skipInnerArgumentInvoke()
    {
        $this->_openBrackets++;
        while (!$this->getNextToken()->isCloseBrace()) {
            if ($this->getCurrentToken()->isCloseBrace()) {
                $this->_closeBrackets++;
            }
            if ($this->getCurrentToken()->isOpenBrace()) {
                $this->_openBrackets++;
            }
        }
        $this->_closeBrackets++;
    }

    /**
     * Get current token
     *
     * @return \Magento\Tools\I18n\Code\Parser\Adapter\Php\Tokenizer\Token
     */
    public function getCurrentToken()
    {
        return $this->_createToken(current($this->_tokens));
    }

    /**
     * Get next token
     *
     * @return bool|\Magento\Tools\I18n\Code\Parser\Adapter\Php\Tokenizer\Token
     */
    public function getNextToken()
    {
        return ($token = next($this->_tokens)) ? $this->_createToken($token) : false;
    }

    /**
     * Check is it last token
     *
     * @return bool
     */
    public function isLastToken()
    {
        return 0 == $this->_tokensCount || key($this->_tokens) + 1 == $this->_tokensCount;
    }

    /**
     * Create token from array|string
     *
     * @param array|string $tokenData
     * @return \Magento\Tools\I18n\Code\Parser\Adapter\Php\Tokenizer\Token
     */
    private function _createToken($tokenData)
    {
        if (is_array($tokenData)) {
            return new Tokenizer\Token($tokenData[0], $tokenData[1], $tokenData[2]);
        } else {
            return new Tokenizer\Token($tokenData, $tokenData);
        }
    }
}
