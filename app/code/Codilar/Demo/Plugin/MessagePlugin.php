<?php
namespace Codilar\Demo\Plugin;

use Codilar\Demo\Model\Message;

class MessagePlugin
{
    public function afterGetMessage(Message $subject, string $result): string
    {
        return $result . ' - Plugin added!';
    }

    public function aroundGetMessage(Message $subject, callable $proceed): string
{
    // 1. Original method munnaadi (BEFORE part)
    $before = 'Before | ';

    // 2. Original method run pannu
    $result = $proceed();

    // 3. Original method pinnaadi (AFTER part)
    return $before . $result . ' | After';
}

}
