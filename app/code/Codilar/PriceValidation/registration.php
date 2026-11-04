<?php
/**
 * Registers the Codilar_PriceValidation module with Magento.
 */
use Magento\Framework\Component\ComponentRegistrar;

ComponentRegistrar::register(
    ComponentRegistrar::MODULE,
    'Codilar_PriceValidation',
    __DIR__
);
