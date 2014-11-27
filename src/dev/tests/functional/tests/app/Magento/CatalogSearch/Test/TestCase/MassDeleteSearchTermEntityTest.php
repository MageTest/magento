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

namespace Magento\CatalogSearch\Test\TestCase;

use Mtf\TestCase\Injectable;
use Mtf\Fixture\FixtureFactory;
use Magento\CatalogSearch\Test\Fixture\CatalogSearchQuery;
use Magento\CatalogSearch\Test\Page\Adminhtml\CatalogSearchIndex;

/**
 * Test Creation for MassDeleteSearchTermEntity
 *
 * Test Flow:
 *
 * Preconditions:
 * 1. Search terms is created
 *
 * Steps:
 * 1. Go to backend as admin user
 * 2. Navigate to Marketing>SEO & Search>Search
 * 3. Select search terms created in preconditions
 * 4. Select delete from mass-action
 * 5. Submit form
 * 6. Perform all assertions
 *
 * @group Search_Terms_(MX)
 * @ZephyrId MAGETWO-26599
 */
class MassDeleteSearchTermEntityTest extends Injectable
{
    /**
     * Search term page
     *
     * @var CatalogSearchIndex
     */
    protected $indexPage;

    /**
     * Inject page
     *
     * @param CatalogSearchIndex $indexPage
     * @return void
     */
    public function __inject(CatalogSearchIndex $indexPage)
    {
        $this->indexPage = $indexPage;
    }

    /**
     * Run mass delete search term entity test
     *
     * @param string $searchTerms
     * @param FixtureFactory $fixtureFactory
     * @return array
     */
    public function test($searchTerms, FixtureFactory $fixtureFactory)
    {
        // Preconditions
        $result = [];
        $deleteSearchTerms = [];
        $searchTerms = array_map('trim', explode(',', $searchTerms));
        foreach ($searchTerms as $term) {
            list($fixture, $dataSet) = explode('::', $term);
            $term = $fixtureFactory->createByCode($fixture, ['dataSet' => $dataSet]);
            /** @var CatalogSearchQuery $term */
            $term->persist();
            $deleteSearchTerms[] = ['search_query' => $term->getQueryText()];
            $result['searchTerms'][] = $term;
        }

        // Steps
        $this->indexPage->open();
        $this->indexPage->getGrid()->massaction($deleteSearchTerms, 'Delete', true);

        return $result;
    }
}
