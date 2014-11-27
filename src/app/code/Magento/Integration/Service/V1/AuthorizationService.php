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

namespace Magento\Integration\Service\V1;

use Magento\Authorization\Model\Resource\Role\CollectionFactory as RoleCollectionFactory;
use Magento\Authorization\Model\Resource\Rules\CollectionFactory as RulesCollectionFactory;
use Magento\Authorization\Model\Role;
use Magento\Authorization\Model\RoleFactory;
use Magento\Authorization\Model\RulesFactory;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\Acl;
use Magento\Framework\Acl\Builder as AclBuilder;
use Magento\Framework\Acl\RootResource as RootAclResource;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Logger;

/**
 * Service for integration permissions management.
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AuthorizationService implements AuthorizationServiceInterface
{
    /**
     * @var AclBuilder
     */
    protected $_aclBuilder;

    /**
     * @var RoleFactory
     */
    protected $_roleFactory;

    /**
     * @var RoleCollectionFactory
     */
    protected $_roleCollectionFactory;

    /**
     * @var RulesFactory
     */
    protected $_rulesFactory;

    /**
     * @var RulesCollectionFactory
     */
    protected $_rulesCollectionFactory;

    /**
     * @var Logger
     */
    protected $_logger;

    /**
     * @var RootAclResource
     */
    protected $_rootAclResource;

    /**
     * Initialize dependencies.
     *
     * @param AclBuilder $aclBuilder
     * @param RoleFactory $roleFactory
     * @param RoleCollectionFactory $roleCollectionFactory
     * @param RulesFactory $rulesFactory
     * @param RulesCollectionFactory $rulesCollectionFactory
     * @param Logger $logger
     * @param RootAclResource $rootAclResource
     */
    public function __construct(
        AclBuilder $aclBuilder,
        RoleFactory $roleFactory,
        RoleCollectionFactory $roleCollectionFactory,
        RulesFactory $rulesFactory,
        RulesCollectionFactory $rulesCollectionFactory,
        Logger $logger,
        RootAclResource $rootAclResource
    ) {
        $this->_aclBuilder = $aclBuilder;
        $this->_roleFactory = $roleFactory;
        $this->_rulesFactory = $rulesFactory;
        $this->_rulesCollectionFactory = $rulesCollectionFactory;
        $this->_roleCollectionFactory = $roleCollectionFactory;
        $this->_logger = $logger;
        $this->_rootAclResource = $rootAclResource;
    }

    /**
     * {@inheritdoc}
     */
    public function grantPermissions($integrationId, $resources)
    {
        try {
            $role = $this->_getUserRole($integrationId);
            if (!$role) {
                $role = $this->_createRole($integrationId);
            }
            $this->_associateResourcesWithRole($role, $resources);
        } catch (\Exception $e) {
            $this->_logger->logException($e);
            throw new LocalizedException('Error happened while granting permissions. Check exception log for details.');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function grantAllPermissions($integrationId)
    {
        $this->grantPermissions($integrationId, array($this->_rootAclResource->getId()));
    }

    /**
     * {@inheritdoc}
     */
    public function removePermissions($integrationId)
    {
        try {
            $this->_deleteRole($integrationId);
        } catch (\Exception $e) {
            $this->_logger->logException($e);
            throw new LocalizedException(
                'Error happened while deleting role and permissions. Check exception log for details.'
            );
        }
    }

    /**
     * Create new ACL role.
     *
     * @param int $integrationId
     * @return \Magento\Authorization\Model\Role
     */
    protected function _createRole($integrationId)
    {
        $roleName = UserContextInterface::USER_TYPE_INTEGRATION . $integrationId;
        $role = $this->_roleFactory->create();
        $role->setRoleName($roleName)
            ->setUserType(UserContextInterface::USER_TYPE_INTEGRATION)
            ->setUserId($integrationId)
            ->setRoleType(\Magento\Authorization\Model\Acl\Role\User::ROLE_TYPE)
            ->setParentId(0)
            ->save();
        return $role;
    }

    /**
     * Remove integration role. This deletes the cascading permissions
     *
     * @param int $integrationId
     * @return \Magento\Authorization\Model\Role
     */
    protected function _deleteRole($integrationId)
    {
        $roleName = UserContextInterface::USER_TYPE_INTEGRATION . $integrationId;
        $role = $this->_roleFactory->create()->load($roleName, 'role_name');
        return $role->delete();
    }

    /**
     * Identify authorization role associated with provided integration.
     *
     * @param int $integrationId
     * @return \Magento\Authorization\Model\Role|false Return false in case when no role associated with user was found.
     */
    protected function _getUserRole($integrationId)
    {
        $roleCollection = $this->_roleCollectionFactory->create();
        /** @var Role $role */
        $role = $roleCollection
            ->setUserFilter($integrationId, UserContextInterface::USER_TYPE_INTEGRATION)
            ->getFirstItem();
        return $role->getId() ? $role : false;
    }

    /**
     * Associate resources with the specified role. All resources previously assigned to the role will be unassigned.
     *
     * @param \Magento\Authorization\Model\Role $role
     * @param string[] $resources
     * @return void
     * @throws \LogicException
     */
    protected function _associateResourcesWithRole($role, $resources)
    {
        /** @var \Magento\Authorization\Model\Rules $rules */
        $rules = $this->_rulesFactory->create();
        $rules->setRoleId($role->getId())->setResources($resources)->saveRel();
    }
}
