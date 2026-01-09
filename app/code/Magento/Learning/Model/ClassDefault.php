<?php
namespace Magento\Learning\Model;

class ClassDefault
{
    public $namespace;

    public function __construct($namespace = 'default')
    {
        $this->namespace = $namespace;
    }
}