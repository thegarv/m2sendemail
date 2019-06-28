<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vendorname\Modulename\Controller\Index;


use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Request\DataPersistorInterface;


class Index extends Action
{
    private $dataPersistor;
    /**
     * @return \Magento\Framework\Controller\Result\Redirect|\Magento\Framework\View\Result\Page
     */

    protected $context;
    private $fileUploaderFactory;
    private $fileSystem;


    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $_transportBuilder;

    /**
     * @var \Magento\Framework\Translate\Inline\StateInterface
     */
    protected $inlineTranslation;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */


    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     * @param \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */

     public function __construct(
        \Magento\Framework\App\Action\Context $context,
        Filesystem $fileSystem,
        \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation, 
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        parent::__construct($context,$transportBuilder,$inlineTranslation, $scopeConfig );
        $this->fileUploaderFactory = $fileUploaderFactory;
        $this->fileSystem          = $fileSystem;
        $this->_transportBuilder = $transportBuilder;
        $this->inlineTranslation = $inlineTranslation;
        $this->scopeConfig = $scopeConfig;
    }

    public function execute()
    {

        $post = $this->getRequest()->getPostValue();
		
        $filesData = $this->getRequest()->getFiles('upload_document');
		print_r($filesData);

        if ($filesData['name']) {
         $uploader = $this->fileUploaderFactory->create(['fileId' => 'upload_document']);
         $uploader->setAllowRenameFiles(true);
         $uploader->setFilesDispersion(true);
         $uploader->setAllowCreateFolders(true);
         $path = $this->fileSystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath('test-doc');
         $result = $uploader->save($path);
         $upload_document = 'test-doc'.$uploader->getUploadedFilename();
         $filePath = $result['path'].$result['file'];
         $fileName = $result['name'];
    } else {
         $upload_document = '';
         $filePath = '';
         $fileName = '';
    }

        $txt='<table>';

        if($post['fname']){         
            $txt.='<tr><td><strong>Client Name</strong>:'.$post['fname'].'</td></tr>';          
        }
        if($post['address']){           
            $txt.='<tr><td><strong>Address</strong>:'.$post['address'].'</td></tr>';            
        }
        if($post['city']){
            $txt.='<tr><td><strong>City</strong>:'.$post['city'].'</td></tr>';
        }
        if($post['state']){
            $txt.='<tr><td><strong>State/Province</strong>:'.$post['state'].'</td></tr>';
        }
        if($post['zipcode']){
            $txt.='<tr><td><strong>Zip Code</strong>:'.$post['zipcode'].'</td></tr>';
        }
        if($post['phone']){
            $txt.='<tr><td><strong>Phone</strong>:'.$post['phone'].'</td></tr>';
        }
        if($post['email']){
            $txt.='<tr><td><strong>Email</strong>:'.$post['email'].'</td></tr>';
        }
        if(!empty($post['project_type'])){      
            $projecttypearray = implode(",",$post['project_type']);     
            $txt.='<tr><td><strong>Project Type</strong>:'.$projecttypearray.'</td></tr>';          
        }

        if($post['comment']){
            $txt.='<tr><td><strong>Comment</strong>:'.$post['comment'].'</td></tr>';
        }
        $txt.='</table>';
        //echo $txt;



        $customerName='Demo Form';
        $message=$txt;

        $userSubject= 'Demo From ';     
        $fromEmail= 'admin@gmail.com.com';
        $fromName = 'Test Demo Form';

         $templateVars = [
                    'store' => 1,
                    'customer_name' => $customerName,
                    'subject' => $userSubject,
                    'message'   => $message
                ];
        $from = ['email' => $fromEmail, 'name' => $fromName];
        $this->inlineTranslation->suspend();


        $to = 'test@gmail.com';     

        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;

         $templateOptions = [
          'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
          'store' => 1
        ];

        $transport = $this->_transportBuilder
		        ->setTemplateIdentifier('1')
                ->setTemplateOptions($templateOptions)
                ->setTemplateVars($templateVars)
                ->setFrom($from)
                ->addTo($to)
                ->addAttachment($filePath, $fileName)               
                ->getTransport();
        $transport->sendMessage();
        $this->inlineTranslation->resume();

        $this->messageManager->addSuccess(__('Form successfully submitted'));

        
    }

}