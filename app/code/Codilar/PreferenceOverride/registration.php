<?php
/**
 * Registers the Codilar_PreferenceOverride module with Magento.
 */
use Magento\Framework\Component\ComponentRegistrar;

ComponentRegistrar::register(
    ComponentRegistrar::MODULE,
    'Codilar_PreferenceOverride',
    __DIR__
);
