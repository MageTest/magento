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

namespace Magento\User\Test\Fixture\AdminUserRole;

use Mtf\Fixture\FixtureFactory;
use Mtf\Fixture\FixtureInterface;
use Mtf\Util\Protocol\CurlTransport;
use Magento\User\Test\Fixture\User;

/**
 * Class InRoleUsers
 *
 * Data keys:
 *  - dataSet
 */
class InRoleUsers implements FixtureInterface
{
    /**
     * Array with data set configuration settings
     *
     * @var array
     */
    protected $params;

    /**
     * Array with Admin Users
     *
     * @var array
     */
    protected $adminUsers;

    /**
     * Array with usernames
     *
     * @var array
     */
    protected $data;

    /**
     * @construct
     * @param FixtureFactory $fixtureFactory
     * @param array $params
     * @param array $data
     */
    public function __construct(FixtureFactory $fixtureFactory, array $params, array $data = [])
    {
        $this->params = $params;
        if (isset($data['dataSet']) && $data['dataSet'] !== '-') {
            $dataSets = explode(',', $data['dataSet']);
            foreach ($dataSets as $dataSet) {
                $adminUser = $fixtureFactory->createByCode('user', ['dataSet' => trim($dataSet)]);
                if (!$adminUser->hasData('user_id')) {
                    $adminUser->persist();
                }
                $this->adminUsers[] = $adminUser;
                $this->data[] = $adminUser->getUsername();
            }
        }
    }

    /**
     * Persist user role
     *
     * @return void
     */
    public function persist()
    {
        //
    }

    /**
     * Return array with usernames
     *
     * @param string $key [optional]
     * @return array|null
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getData($key = null)
    {
        return $this->data;
    }

    /**
     * Return data set configuration settings
     *
     * @return array
     */
    public function getDataConfig()
    {
        return $this->params;
    }

    /**
     * Return array with admin user fixtures
     *
     * @return array
     */
    public function getAdminUsers()
    {
        return $this->adminUsers;
    }
}
