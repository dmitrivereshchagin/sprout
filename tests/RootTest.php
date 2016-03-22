<?php

namespace Sprout;

class RootTest extends \PHPUnit_Framework_TestCase
{
    public function testRootNodeCanBeCreated()
    {
        $this->assertInstanceOf(Node::class, root('foo'));
        $this->assertInstanceOf(Node::class, root('foo', 'bar="baz"'));
    }
}
