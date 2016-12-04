<?php

declare(strict_types=1);

namespace Sprout;

use PHPUnit\Framework\TestCase;

class NodeTest extends TestCase
{
    public function testCanBeCreatedFromName(): Node
    {
        $foo = Node::create('foo');
        $this->assertAttributeEquals('foo', 'name', $foo);

        return $foo;
    }

    public function testCanBeCreatedFromNameAndAttributes(): Node
    {
        $bar = Node::create('bar', 'baz="qux"');
        $this->assertAttributeEquals('bar', 'name', $bar);
        $this->assertAttributeEquals('baz="qux"', 'attributes', $bar);

        return $bar;
    }

    /**
     * @depends testCanBeCreatedFromName
     * @depends testCanBeCreatedFromNameAndAttributes
     */
    public function testCanBeCastToString(Node $foo, Node $bar): void
    {
        $this->assertEquals('<foo></foo>', (string) $foo);
        $this->assertEquals('<bar baz="qux"></bar>', (string) $bar);
    }

    /**
     * @depends clone testCanBeCreatedFromName
     */
    public function testRootNodeCanBeReached(Node $foo): void
    {
        $this->assertSame($foo, $foo->root());
        $this->assertSame($foo, $foo->bar()->root());
    }

    /**
     * @depends clone testCanBeCreatedFromName
     */
    public function testParentNodeCanBeReached(Node $foo): void
    {
        $this->assertSame($foo, $foo->bar()->up());
    }

    /**
     * @depends testCanBeCreatedFromName
     * @expectedException \Sprout\Exception\NodeNotFoundException
     */
    public function testParentOfRootNodeCannotBeReached(Node $foo): void
    {
        $foo->up();
    }

    /**
     * @depends clone testCanBeCreatedFromName
     */
    public function testMarkedNodeCanBeReached(Node $foo): void
    {
        $this->assertSame($foo, $foo->mark('label')->to('label'));
        $this->assertSame($foo, $foo->mark('label')->bar()->to('label'));
    }

    /**
     * @depends testCanBeCreatedFromName
     * @expectedException \Sprout\Exception\NodeNotFoundException
     */
    public function testNonexistentMarkCannotBeReached(Node $foo): void
    {
        $foo->to('nonexistent');
    }

    /**
     * @depends clone testCanBeCreatedFromName
     */
    public function testCanBeFilledWithText(Node $foo): void
    {
        $this->assertEquals('<foo>bar</foo>', (string) $foo->text('bar'));
    }

    /**
     * @depends clone testCanBeCreatedFromName
     */
    public function testCanBeTurnedIntoEmptyNode(Node $foo): void
    {
        $this->assertEquals('<foo>', (string) $foo->merge());
    }

    /**
     * @depends clone testCanBeCreatedFromName
     * @depends testCanBeCreatedFromNameAndAttributes
     */
    public function testNewNodesCanBeInserted(Node $foo, Node $bar): void
    {
        $this->assertEquals(
            '<foo><bar baz="qux"></bar></foo>',
            (string) $foo->insert($bar)
        );

        $this->assertEquals(
            '<foo><bar baz="qux"></bar></foo>',
            (string) $foo->merge()->insert($bar)
        );
    }

    /**
     * @depends clone testCanBeCreatedFromName
     */
    public function testNewNodeCanBeAdded(Node $foo): void
    {
        $this->assertEquals(
            '<foo><bar><baz zim="qux"></baz></bar></foo>',
            (string) $foo->bar()->baz('zim="qux"')->root()
        );
    }

    /**
     * @depends clone testCanBeCreatedFromName
     */
    public function testChildNodesCanBeRepeated(Node $foo): void
    {
        $this->assertEquals(
            '<foo><bar></bar><bar></bar></foo>',
            (string) $foo->bar()->times(2)
        );
    }

    /**
     * @depends clone testCanBeCreatedFromName
     */
    public function testChildNodesCanBeRepeated2(Node $foo): void
    {
        $this->assertEquals(
            '<foo><bar><baz></baz></bar><bar><baz></baz></bar></foo>',
            (string) $foo->bar()->mark('label')->baz()->times(2, 'label')
        );
    }

    /**
     * @depends clone testCanBeCreatedFromName
     * @expectedException \Sprout\Exception\InvalidArgumentException
     */
    public function testChildNodesCanBeRepeated3(Node $foo): void
    {
        $foo->baz()->times(0);
    }

    /**
     * @depends clone testCanBeCreatedFromName
     * @expectedException \Sprout\Exception\NodeNotFoundException
     */
    public function testRootNodeCannotBeRepeated(Node $foo): void
    {
        $foo->times(2);
    }
}
