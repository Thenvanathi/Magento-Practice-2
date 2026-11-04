<?php
/**
 * Registers the Codilar_OrderObserver module with Magento.
 */
use Magento\Framework\Component\ComponentRegistrar;

ComponentRegistrar::register(
    ComponentRegistrar::MODULE,
    'Codilar_OrderObserver',
    __DIR__
);
