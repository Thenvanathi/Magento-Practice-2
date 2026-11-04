<?php
/**
 * Registers the Codilar_AfterPlugin module with Magento.
 */
use Magento\Framework\Component\ComponentRegistrar;

ComponentRegistrar::register(
    ComponentRegistrar::MODULE,
    'Codilar_AfterPlugin',
    __DIR__
);
