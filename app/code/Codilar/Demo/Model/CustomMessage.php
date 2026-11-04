<?php
namespace Codilar\Demo\Model;

class CustomMessage extends Message
{
    public function getMessage(): string
    {
        return 'Hello from the PREFERENCE class!';
    }
}
