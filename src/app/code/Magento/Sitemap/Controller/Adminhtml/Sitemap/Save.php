<?php
/**
 *
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
namespace Magento\Sitemap\Controller\Adminhtml\Sitemap;

use \Magento\Backend\App\Action;
use Magento\Framework\App\Filesystem\DirectoryList;

class Save extends \Magento\Sitemap\Controller\Adminhtml\Sitemap
{
    /**
     * Save action
     *
     * @return void
     */
    public function execute()
    {
        // check if data sent
        $data = $this->getRequest()->getPost();
        if ($data) {
            // init model and set data
            /** @var \Magento\Sitemap\Model\Sitemap $model */
            $model = $this->_objectManager->create('Magento\Sitemap\Model\Sitemap');

            //validate path to generate
            if (!empty($data['sitemap_filename']) && !empty($data['sitemap_path'])) {
                $data['sitemap_path'] = '/' . ltrim($data['sitemap_path'], '/');
                $path = rtrim($data['sitemap_path'], '\\/') . '/' . $data['sitemap_filename'];
                /** @var $validator \Magento\Core\Model\File\Validator\AvailablePath */
                $validator = $this->_objectManager->create('Magento\Core\Model\File\Validator\AvailablePath');
                /** @var $helper \Magento\Sitemap\Helper\Data */
                $helper = $this->_objectManager->get('Magento\Sitemap\Helper\Data');
                $validator->setPaths($helper->getValidPaths());
                if (!$validator->isValid($path)) {
                    foreach ($validator->getMessages() as $message) {
                        $this->messageManager->addError($message);
                    }
                    // save data in session
                    $this->_objectManager->get('Magento\Backend\Model\Session')->setFormData($data);
                    // redirect to edit form
                    $this->_redirect(
                        'adminhtml/*/edit',
                        array('sitemap_id' => $this->getRequest()->getParam('sitemap_id'))
                    );
                    return;
                }
            }

            /** @var \Magento\Framework\Filesystem\Directory\Write $directory */
            $directory = $this->_objectManager->get(
                'Magento\Framework\Filesystem'
            )->getDirectoryWrite(
                DirectoryList::ROOT
            );

            if ($this->getRequest()->getParam('sitemap_id')) {
                $model->load($this->getRequest()->getParam('sitemap_id'));
                $fileName = $model->getSitemapFilename();

                $path = $model->getSitemapPath() . '/' . $fileName;
                if ($fileName && $directory->isFile($path)) {
                    $directory->delete($path);
                }
            }

            $model->setData($data);

            // try to save it
            try {
                // save the data
                $model->save();
                // display success message
                $this->messageManager->addSuccess(__('The sitemap has been saved.'));
                // clear previously saved data from session
                $this->_objectManager->get('Magento\Backend\Model\Session')->setFormData(false);

                // check if 'Save and Continue'
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('adminhtml/*/edit', array('sitemap_id' => $model->getId()));
                    return;
                }
                // go to grid or forward to generate action
                if ($this->getRequest()->getParam('generate')) {
                    $this->getRequest()->setParam('sitemap_id', $model->getId());
                    $this->_forward('generate');
                    return;
                }
                $this->_redirect('adminhtml/*/');
                return;
            } catch (\Exception $e) {
                // display error message
                $this->messageManager->addError($e->getMessage());
                // save data in session
                $this->_objectManager->get('Magento\Backend\Model\Session')->setFormData($data);
                // redirect to edit form
                $this->_redirect(
                    'adminhtml/*/edit',
                    array('sitemap_id' => $this->getRequest()->getParam('sitemap_id'))
                );
                return;
            }
        }
        $this->_redirect('adminhtml/*/');
    }
}
