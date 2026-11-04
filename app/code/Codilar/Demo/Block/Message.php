<?php
namespace Codilar\Demo\Block;

use Magento\Framework\View\Element\Template;
use Codilar\Demo\Model\Message as MessageModel;

/**
 * Block: Connects the Model's data to the View template.
 */
class Message extends Template
{
    /**
     * Inject the Model through Magento dependency injection.
     *
     * @param Template\Context $context
     * @param MessageModel $messageModel
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        private readonly MessageModel $messageModel,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * Get the message to display in the template.
     *
     * @return string
     */
    public function getMessage(): string
    {
        return $this->messageModel->getMessage();
    }
}
