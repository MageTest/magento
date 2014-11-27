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
namespace Magento\Indexer\Model;

interface IndexerInterface
{
    /**
     * Return indexer ID
     *
     * @return string
     */
    public function getId();

    /**
     * Return indexer's view ID
     *
     * @return string
     */
    public function getViewId();

    /**
     * Return indexer action class
     *
     * @return string
     */
    public function getActionClass();

    /**
     * Return indexer title
     *
     * @return string
     */
    public function getTitle();

    /**
     * Return indexer description
     *
     * @return string
     */
    public function getDescription();

    /**
     * Fill indexer data from config
     *
     * @param string $indexerId
     * @return IndexerInterface
     * @throws \InvalidArgumentException
     */
    public function load($indexerId);

    /**
     * Return related view object
     *
     * @return \Magento\Framework\Mview\ViewInterface
     */
    public function getView();

    /**
     * Return related state object
     *
     * @return Indexer\State
     */
    public function getState();

    /**
     * Set indexer state object
     *
     * @param Indexer\State $state
     * @return IndexerInterface
     */
    public function setState(Indexer\State $state);

    /**
     * Check whether indexer is run by schedule
     *
     * @return bool
     */
    public function isScheduled();

    /**
     * Turn scheduled mode on/off
     *
     * @param bool $scheduled
     * @return void
     */
    public function setScheduled($scheduled);

    /**
     * Check whether indexer is valid
     *
     * @return bool
     */
    public function isValid();

    /**
     * Check whether indexer is invalid
     *
     * @return bool
     */
    public function isInvalid();

    /**
     * Check whether indexer is working
     *
     * @return bool
     */
    public function isWorking();

    /**
     * Set indexer invalid
     *
     * @return void
     */
    public function invalidate();

    /**
     * Return indexer status
     *
     * @return string
     */
    public function getStatus();

    /**
     * Return indexer or mview latest updated time
     *
     * @return string
     */
    public function getLatestUpdated();

    /**
     * Regenerate full index
     *
     * @return void
     * @throws \Exception
     */
    public function reindexAll();

    /**
     * Regenerate one row in index by ID
     *
     * @param int $id
     * @return void
     */
    public function reindexRow($id);

    /**
     * Regenerate rows in index by ID list
     *
     * @param int[] $ids
     * @return void
     */
    public function reindexList($ids);
}
