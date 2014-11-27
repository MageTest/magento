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
 * Review install
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */

/* @var $installer \Magento\Setup\Module\SetupModule */
$installer = $this;

$installer->startSetup();
/**
 * Create table 'review_entity'
 */
$table = $installer->getConnection()->newTable(
    $installer->getTable('review_entity')
)->addColumn(
    'entity_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true),
    'Review entity id'
)->addColumn(
    'entity_code',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    32,
    array('nullable' => false),
    'Review entity code'
)->setComment(
    'Review entities'
);
$installer->getConnection()->createTable($table);

/**
 * Create table 'review_status'
 */
$table = $installer->getConnection()->newTable(
    $installer->getTable('review_status')
)->addColumn(
    'status_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true),
    'Status id'
)->addColumn(
    'status_code',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    32,
    array('nullable' => false),
    'Status code'
)->setComment(
    'Review statuses'
);
$installer->getConnection()->createTable($table);

/**
 * Create table 'review'
 */
$table = $installer->getConnection()->newTable(
    $installer->getTable('review')
)->addColumn(
    'review_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_BIGINT,
    null,
    array('identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true),
    'Review id'
)->addColumn(
    'created_at',
    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
    null,
    array('nullable' => false),
    'Review create date'
)->addColumn(
    'entity_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => '0'),
    'Entity id'
)->addColumn(
    'entity_pk_value',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => '0'),
    'Product id'
)->addColumn(
    'status_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => '0'),
    'Status code'
)->addIndex(
    $installer->getIdxName('review', array('entity_id')),
    array('entity_id')
)->addIndex(
    $installer->getIdxName('review', array('status_id')),
    array('status_id')
)->addIndex(
    $installer->getIdxName('review', array('entity_pk_value')),
    array('entity_pk_value')
)->addForeignKey(
    $installer->getFkName('review', 'entity_id', 'review_entity', 'entity_id'),
    'entity_id',
    $installer->getTable('review_entity'),
    'entity_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->addForeignKey(
    $installer->getFkName('review', 'status_id', 'review_status', 'status_id'),
    'status_id',
    $installer->getTable('review_status'),
    'status_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_NO_ACTION,
    \Magento\Framework\DB\Ddl\Table::ACTION_NO_ACTION
)->setComment(
    'Review base information'
);
$installer->getConnection()->createTable($table);

/**
 * Create table 'review_detail'
 */
$table = $installer->getConnection()->newTable(
    $installer->getTable('review_detail')
)->addColumn(
    'detail_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_BIGINT,
    null,
    array('identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true),
    'Review detail id'
)->addColumn(
    'review_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_BIGINT,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => '0'),
    'Review id'
)->addColumn(
    'store_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true, 'default' => '0'),
    'Store id'
)->addColumn(
    'title',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    array('nullable' => false),
    'Title'
)->addColumn(
    'detail',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    '64k',
    array('nullable' => false),
    'Detail description'
)->addColumn(
    'nickname',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    128,
    array('nullable' => false),
    'User nickname'
)->addColumn(
    'customer_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('unsigned' => true),
    'Customer Id'
)->addIndex(
    $installer->getIdxName('review_detail', array('review_id')),
    array('review_id')
)->addIndex(
    $installer->getIdxName('review_detail', array('store_id')),
    array('store_id')
)->addIndex(
    $installer->getIdxName('review_detail', array('customer_id')),
    array('customer_id')
)->addForeignKey(
    $installer->getFkName('review_detail', 'customer_id', 'customer_entity', 'entity_id'),
    'customer_id',
    $installer->getTable('customer_entity'),
    'entity_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_SET_NULL,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->addForeignKey(
    $installer->getFkName('review_detail', 'review_id', 'review', 'review_id'),
    'review_id',
    $installer->getTable('review'),
    'review_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->addForeignKey(
    $installer->getFkName('review_detail', 'store_id', 'store', 'store_id'),
    'store_id',
    $installer->getTable('store'),
    'store_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_SET_NULL,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->setComment(
    'Review detail information'
);
$installer->getConnection()->createTable($table);

/**
 * Create table 'review_entity_summary'
 */
$table = $installer->getConnection()->newTable(
    $installer->getTable('review_entity_summary')
)->addColumn(
    'primary_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_BIGINT,
    null,
    array('identity' => true, 'nullable' => false, 'primary' => true),
    'Summary review entity id'
)->addColumn(
    'entity_pk_value',
    \Magento\Framework\DB\Ddl\Table::TYPE_BIGINT,
    null,
    array('nullable' => false, 'default' => '0'),
    'Product id'
)->addColumn(
    'entity_type',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('nullable' => false, 'default' => '0'),
    'Entity type id'
)->addColumn(
    'reviews_count',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('nullable' => false, 'default' => '0'),
    'Qty of reviews'
)->addColumn(
    'rating_summary',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('nullable' => false, 'default' => '0'),
    'Summarized rating'
)->addColumn(
    'store_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => '0'),
    'Store id'
)->addIndex(
    $installer->getIdxName('review_entity_summary', array('store_id')),
    array('store_id')
)->addForeignKey(
    $installer->getFkName('review_entity_summary', 'store_id', 'store', 'store_id'),
    'store_id',
    $installer->getTable('store'),
    'store_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->setComment(
    'Review aggregates'
);
$installer->getConnection()->createTable($table);

/**
 * Create table 'review_store'
 */
$table = $installer->getConnection()->newTable(
    $installer->getTable('review_store')
)->addColumn(
    'review_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_BIGINT,
    null,
    array('unsigned' => true, 'nullable' => false, 'primary' => true),
    'Review Id'
)->addColumn(
    'store_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true, 'nullable' => false, 'primary' => true),
    'Store Id'
)->addIndex(
    $installer->getIdxName('review_store', array('store_id')),
    array('store_id')
)->addForeignKey(
    $installer->getFkName('review_store', 'review_id', 'review', 'review_id'),
    'review_id',
    $installer->getTable('review'),
    'review_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->addForeignKey(
    $installer->getFkName('review_store', 'store_id', 'store', 'store_id'),
    'store_id',
    $installer->getTable('store'),
    'store_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->setComment(
    'Review Store'
);
$installer->getConnection()->createTable($table);

/**
 * Create table 'rating_entity'
 */
$table = $installer->getConnection()->newTable(
    $installer->getTable('rating_entity')
)->addColumn(
    'entity_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true),
    'Entity Id'
)->addColumn(
    'entity_code',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    64,
    array('nullable' => false),
    'Entity Code'
)->addIndex(
    $installer->getIdxName(
        'rating_entity',
        array('entity_code'),
        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
    ),
    array('entity_code'),
    array('type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE)
)->setComment(
    'Rating entities'
);
$installer->getConnection()->createTable($table);

/**
* Create table 'rating'
*/
$table = $installer->getConnection()->newTable(
    $installer->getTable('rating')
)->addColumn(
    'rating_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true),
    'Rating Id'
)->addColumn(
    'entity_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => 0),
    'Entity Id'
)->addColumn(
    'rating_code',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    64,
    array('nullable' => false),
    'Rating Code'
)->addColumn(
    'position',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => 0),
    'Rating Position On Frontend'
)->addColumn(
    'is_active',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('nullable' => false, 'default' => 1),
    'Rating is active.'
)->addIndex(
    $installer->getIdxName(
        'rating',
        array('rating_code'),
        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
    ),
    array('rating_code'),
    array('type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE)
)->addIndex(
    $installer->getIdxName('rating', array('entity_id')),
    array('entity_id')
)->addForeignKey(
    $installer->getFkName('rating', 'entity_id', 'rating_entity', 'entity_id'),
    'entity_id',
    $installer->getTable('rating_entity'),
    'entity_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->setComment(
    'Ratings'
);
$installer->getConnection()->createTable($table);

/**
* Create table 'rating_option'
*/
$table = $installer->getConnection()->newTable(
    $installer->getTable('rating_option')
)->addColumn(
    'option_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true),
    'Rating Option Id'
)->addColumn(
    'rating_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => 0),
    'Rating Id'
)->addColumn(
    'code',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    32,
    array('nullable' => false),
    'Rating Option Code'
)->addColumn(
    'value',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => 0),
    'Rating Option Value'
)->addColumn(
    'position',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => 0),
    'Ration option position on frontend'
)->addIndex(
    $installer->getIdxName('rating_option', array('rating_id')),
    array('rating_id')
)->addForeignKey(
    $installer->getFkName('rating_option', 'rating_id', 'rating', 'rating_id'),
    'rating_id',
    $installer->getTable('rating'),
    'rating_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->setComment(
    'Rating options'
);
$installer->getConnection()->createTable($table);

/**
* Create table 'rating_option_vote'
*/
$table = $installer->getConnection()->newTable(
    $installer->getTable('rating_option_vote')
)->addColumn(
    'vote_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_BIGINT,
    null,
    array('identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true),
    'Vote id'
)->addColumn(
    'option_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => 0),
    'Vote option id'
)->addColumn(
    'remote_ip',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    16,
    array('nullable' => false),
    'Customer IP'
)->addColumn(
    'remote_ip_long',
    \Magento\Framework\DB\Ddl\Table::TYPE_BIGINT,
    null,
    array('nullable' => false, 'default' => 0),
    'Customer IP converted to long integer format'
)->addColumn(
    'customer_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('unsigned' => true, 'default' => 0),
    'Customer Id'
)->addColumn(
    'entity_pk_value',
    \Magento\Framework\DB\Ddl\Table::TYPE_BIGINT,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => 0),
    'Product id'
)->addColumn(
    'rating_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => 0),
    'Rating id'
)->addColumn(
    'review_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_BIGINT,
    null,
    array('unsigned' => true),
    'Review id'
)->addColumn(
    'percent',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('nullable' => false, 'default' => 0),
    'Percent amount'
)->addColumn(
    'value',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('nullable' => false, 'default' => 0),
    'Vote option value'
)->addIndex(
    $installer->getIdxName('rating_option_vote', array('option_id')),
    array('option_id')
)->addForeignKey(
    $installer->getFkName('rating_option_vote', 'option_id', 'rating_option', 'option_id'),
    'option_id',
    $installer->getTable('rating_option'),
    'option_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->setComment(
    'Rating option values'
);
$installer->getConnection()->createTable($table);

/**
* Create table 'rating_option_vote_aggregated'
*/
$table = $installer->getConnection()->newTable(
    $installer->getTable('rating_option_vote_aggregated')
)->addColumn(
    'primary_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('identity' => true, 'nullable' => false, 'primary' => true),
    'Vote aggregation id'
)->addColumn(
    'rating_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => 0),
    'Rating id'
)->addColumn(
    'entity_pk_value',
    \Magento\Framework\DB\Ddl\Table::TYPE_BIGINT,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => 0),
    'Product id'
)->addColumn(
    'vote_count',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => 0),
    'Vote dty'
)->addColumn(
    'vote_value_sum',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => 0),
    'General vote sum'
)->addColumn(
    'percent',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('nullable' => false, 'default' => 0),
    'Vote percent'
)->addColumn(
    'percent_approved',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('default' => '0'),
    'Vote percent approved by admin'
)->addColumn(
    'store_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => 0),
    'Store Id'
)->addIndex(
    $installer->getIdxName('rating_option_vote_aggregated', array('rating_id')),
    array('rating_id')
)->addIndex(
    $installer->getIdxName('rating_option_vote_aggregated', array('store_id')),
    array('store_id')
)->addForeignKey(
    $installer->getFkName('rating_option_vote_aggregated', 'rating_id', 'rating', 'rating_id'),
    'rating_id',
    $installer->getTable('rating'),
    'rating_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->addForeignKey(
    $installer->getFkName('rating_option_vote_aggregated', 'store_id', 'store', 'store_id'),
    'store_id',
    $installer->getTable('store'),
    'store_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->setComment(
    'Rating vote aggregated'
);
$installer->getConnection()->createTable($table);

/**
* Create table 'rating_store'
*/
$table = $installer->getConnection()->newTable(
    $installer->getTable('rating_store')
)->addColumn(
    'rating_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => 0, 'primary' => true),
    'Rating id'
)->addColumn(
    'store_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => 0, 'primary' => true),
    'Store id'
)->addIndex(
    $installer->getIdxName('rating_store', array('store_id')),
    array('store_id')
)->addForeignKey(
    $installer->getFkName('rating_store', 'store_id', 'store', 'store_id'),
    'store_id',
    $installer->getTable('store'),
    'store_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->addForeignKey(
    $installer->getFkName('rating_store', 'rating_id', 'rating', 'rating_id'),
    'rating_id',
    $installer->getTable('rating'),
    'rating_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_NO_ACTION
)->setComment(
    'Rating Store'
);
$installer->getConnection()->createTable($table);

/**
* Create table 'rating_title'
*/
$table = $installer->getConnection()->newTable(
    $installer->getTable('rating_title')
)->addColumn(
    'rating_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => 0, 'primary' => true),
    'Rating Id'
)->addColumn(
    'store_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => 0, 'primary' => true),
    'Store Id'
)->addColumn(
    'value',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    array('nullable' => false),
    'Rating Label'
)->addIndex(
    $installer->getIdxName('rating_title', array('store_id')),
    array('store_id')
)->addForeignKey(
    $installer->getFkName('rating_title', 'rating_id', 'rating', 'rating_id'),
    'rating_id',
    $installer->getTable('rating'),
    'rating_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->addForeignKey(
    $installer->getFkName('rating_title', 'store_id', 'store', 'store_id'),
    'store_id',
    $installer->getTable('store'),
    'store_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->setComment(
    'Rating Title'
);
$installer->getConnection()->createTable($table);

/**
* Review/Rating module upgrade.
* Create FK for 'rating_option_vote'
*/
$table = $installer->getConnection()->addForeignKey(
    $installer->getFkName('rating_option_vote', 'review_id', 'review', 'review_id'),
    $installer->getTable('rating_option_vote'),
    'review_id',
    $installer->getTable('review'),
    'review_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
);

$this->endSetup();
