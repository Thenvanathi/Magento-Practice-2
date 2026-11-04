<?php
/**
 * Registers the Codilar_AroundPlugin module with Magento.
 */
use Magento\Framework\Component\ComponentRegistrar;

ComponentRegistrar::register(
    ComponentRegistrar::MODULE,
    'Codilar_AroundPlugin',
    __DIR__
);
