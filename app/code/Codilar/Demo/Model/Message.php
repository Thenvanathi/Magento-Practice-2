<?php
namespace Codilar\Demo\Model;

/**
 * Model: Contains the demo's business logic.
 */
class Message
{
    /**
     * Return a message for the storefront.
     *
     * @return string
     */
    public function getMessage(): string
    {
        return 'Hello from the Magento Model!';
    }
}
