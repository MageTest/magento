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
namespace Magento\Core\Model;

use Magento\Framework\App\DesignInterface;
use Magento\Framework\Model\Resource\AbstractResource;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Object\IdentityInterface;

/**
 * Design settings change model
 *
 * @method int getStoreId()
 * @method \Magento\Core\Model\Design setStoreId(int $value)
 * @method string getDesign()
 * @method \Magento\Core\Model\Design setDesign(string $value)
 * @method string getDateFrom()
 * @method \Magento\Core\Model\Design setDateFrom(string $value)
 * @method string getDateTo()
 * @method \Magento\Core\Model\Design setDateTo(string $value)
 */
class Design extends AbstractModel implements IdentityInterface, DesignInterface
{
    /**
     * Cache tag
     */
    const CACHE_TAG = 'CORE_DESIGN';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'core_design';

    /**
     * Model cache tag for clear cache in after save and after delete
     *
     * When you use true - all cache will be clean
     *
     * @var string|bool
     */
    protected $_cacheTag = self::CACHE_TAG;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $_localeDate;

    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    protected $_dateTime;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\Db $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\Db $resourceCollection = null,
        array $data = array()
    ) {
        $this->_localeDate = $localeDate;
        $this->_dateTime = $dateTime;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\Core\Model\Resource\Design');
    }

    /**
     * Load custom design settings for specified store and date
     *
     * @param string $storeId
     * @param string|null $date
     * @return $this
     */
    public function loadChange($storeId, $date = null)
    {
        if (is_null($date)) {
            $date = $this->_dateTime->formatDate($this->_localeDate->scopeTimeStamp($storeId), false);
        }

        $changeCacheId = 'design_change_' . md5($storeId . $date);
        $result = $this->_cacheManager->load($changeCacheId);
        if ($result === false) {
            $result = $this->getResource()->loadChange($storeId, $date);
            if (!$result) {
                $result = array();
            }
            $this->_cacheManager->save(serialize($result), $changeCacheId, array(self::CACHE_TAG), 86400);
        } else {
            $result = unserialize($result);
        }

        if ($result) {
            $this->setData($result);
        }

        return $this;
    }

    /**
     * Apply design change from self data into specified design package instance
     *
     * @param \Magento\Framework\View\DesignInterface $packageInto
     * @return $this
     */
    public function changeDesign(\Magento\Framework\View\DesignInterface $packageInto)
    {
        $design = $this->getDesign();
        if ($design) {
            $packageInto->setDesignTheme($design);
        }
        return $this;
    }

    /**
     * Get identities
     *
     * @return array
     */
    public function getIdentities()
    {
        return array(self::CACHE_TAG . '_' . $this->getId());
    }
}
