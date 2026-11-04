<?php
/**
 * Registers the Codilar_Demo module with Magento.
 */
use Magento\Framework\Component\ComponentRegistrar;

// Tell Magento where this module is located.
ComponentRegistrar::register(
    ComponentRegistrar::MODULE,
    'Codilar_Demo',
    __DIR__
);
