<?php
/**
 * Registers the Codilar_PrivateOverride module with Magento.
 */
use Magento\Framework\Component\ComponentRegistrar;

ComponentRegistrar::register(
    ComponentRegistrar::MODULE,
    'Codilar_PrivateOverride',
    __DIR__
);
