<?php
declare(strict_types=1);

namespace Laurensmedia\Productdesigner\Controller\Adminhtml\Templates;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\RuntimeException;
use Magento\Framework\Filesystem;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Laurensmedia\Productdesigner\Model\TemplatesFactory;
use Laurensmedia\Productdesigner\Model\ResourceModel\Templatedata\CollectionFactory;

class Save extends Action
{
    /**
     * Authorization level
     */
    const ADMIN_RESOURCE = 'Laurensmedia_Productdesigner::templates_save';

    /**
     * @var StoreManagerInterface
     */
    protected StoreManagerInterface $storeManager;

    /**
     * @var ProductRepositoryInterface
     */
    protected ProductRepositoryInterface $productRepository;

    /**
     * @var TemplatesFactory
     */
    protected TemplatesFactory $templatesFactory;

    /**
     * @var CollectionFactory
     */
    protected CollectionFactory $collectionFactory;

    /**
     * @var Filesystem
     */
    protected Filesystem $filesystem;

    /**
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param ProductRepositoryInterface $productRepository
     * @param TemplatesFactory $templatesFactory
     * @param CollectionFactory $collectionFactory
     * @param Filesystem $filesystem
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        ProductRepositoryInterface $productRepository,
        TemplatesFactory $templatesFactory,
        CollectionFactory $collectionFactory,
        Filesystem $filesystem
    ) {
        $this->storeManager = $storeManager;
        $this->productRepository = $productRepository;
        $this->templatesFactory = $templatesFactory;
        $this->collectionFactory = $collectionFactory;
        $this->filesystem = $filesystem;
        parent::__construct($context);
    }

    /**
     * Save action
     *
     * @return ResultInterface
     */
    public function execute(): ResultInterface
    {
        $data = $this->getRequest()->getPostValue();
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        if ($data) {
            $model = $this->templatesFactory->create();
            $id = $this->getRequest()->getParam('id');

            if ($id) {
                $model->load($id);
                $model->setCreatedAt(date('Y-m-d H:i:s'));
            }

            if (isset($data['is_duplicate']) && $data['is_duplicate'] === 'true') {
                $saveModel = $this->templatesFactory->create();
                $saveData = $model->getData();
                unset($saveData['id'], $saveData['created_at']);
                $saveData['title'] .= ' (copy)';
                $saveModel->addData($saveData);
                $saveModel->save();

                $this->messageManager->addSuccessMessage(__('The Templates has been duplicated.'));
                $this->_objectManager->get('Magento\Backend\Model\Session')->setFormData(false);
                return $resultRedirect->setPath('*/*/edit', ['id' => $saveModel->getId()]);
            } else {
                $permittedChars = '0123456789abcdefghijklmnopqrstuvwxyz';
                $data['password'] = substr(str_shuffle($permittedChars), 0, 10);

                if (isset($_FILES['template_preview']) && $_FILES['template_preview']['tmp_name'] !== '') {
                    $sideId = $this->collectionFactory->create()
                        ->addFieldToFilter('template_id', $id)
                        ->getFirstItem()
                        ->getId();
                    $mediaDirectory = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA);
                    $saveLocation = $mediaDirectory->getAbsolutePath() . 'productdesigner/templates/' . $sideId . '.jpg';
                    copy($_FILES['template_preview']['tmp_name'], $saveLocation);
                }

                $model->addData($data);

                try {
                    $model->save();
                    $this->messageManager->addSuccessMessage(__('The Templates has been saved.'));
                    $this->_objectManager->get('Magento\Backend\Model\Session')->setFormData(false);

                    if ($this->getRequest()->getParam('back')) {
                        return $resultRedirect->setPath('*/*/edit', ['id' => $model->getId(), '_current' => true]);
                    }

                    $product = $this->productRepository->getById($model->getProductId());
                    $url = $product->getProductUrl() . '/?product_id=' . $model->getProductId() . '&edit_template=1&template_id=' . $model->getId() . '&password=' . $data['password'];
                    return $resultRedirect->setUrl($url);
                } catch (LocalizedException | RuntimeException $e) {
                    $this->messageManager->addErrorMessage($e->getMessage());
                } catch (Exception $e) {
                    $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the Templates.'));
                }
            }

            $this->_getSession()->setFormData($data);
            return $resultRedirect->setPath('*/*/edit', ['id' => $this->getRequest()->getParam('id')]);
        }
        return $resultRedirect->setPath('*/*/');
    }
}