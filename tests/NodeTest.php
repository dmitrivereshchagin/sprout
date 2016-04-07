<?php

namespace Sprout;

class NodeTest extends \PHPUnit_Framework_TestCase
{
    public function testCanBeConstructedFromName()
    {
        $foo = new Node('foo');
        $this->assertInstanceOf(Node::class, $foo);

        return $foo;
    }

    public function testCanBeConstructedFromNameAndAttributes()
    {
        $bar = new Node('bar', 'baz="qux"');
        $this->assertInstanceOf(Node::class, $bar);

        return $bar;
    }

    /**
     * @depends testCanBeConstructedFromName
     * @depends testCanBeConstructedFromNameAndAttributes
     */
    public function testCanBeCastToString(Node $foo, Node $bar)
    {
        $this->assertEquals('<foo></foo>', (string) $foo);
        $this->assertEquals('<bar baz="qux"></bar>', (string) $bar);
    }

    /**
     * @depends clone testCanBeConstructedFromName
     */
    public function testRootNodeCanBeReached(Node $foo)
    {
        $this->assertSame($foo, $foo->root());
        $this->assertSame($foo, $foo->bar()->root());
    }

    /**
     * @depends clone testCanBeConstructedFromName
     */
    public function testParentNodeCanBeReached(Node $foo)
    {
        $this->assertSame($foo, $foo->bar()->up());
    }

    /**
     * @depends testCanBeConstructedFromName
     * @expectedException \Sprout\Exception\NodeNotFoundException
     */
    public function testParentOfRootNodeCannotBeReached(Node $foo)
    {
        $foo->up();
    }

    /**
     * @depends clone testCanBeConstructedFromName
     */
    public function testMarkedNodeCanBeReached(Node $foo)
    {
        $this->assertSame($foo, $foo->mark('label')->to('label'));
        $this->assertSame($foo, $foo->mark('label')->bar()->to('label'));
    }

    /**
     * @depends testCanBeConstructedFromName
     * @expectedException \Sprout\Exception\NodeNotFoundException
     */
    public function testNonexistentMarkCannotBeReached(Node $foo)
    {
        $foo->to('nonexistent');
    }

    /**
     * @depends clone testCanBeConstructedFromName
     */
    public function testCanBeFilledWithText(Node $foo)
    {
        $this->assertEquals('<foo>bar</foo>', (string) $foo->text('bar'));
    }

    /**
     * @depends clone testCanBeConstructedFromName
     */
    public function testCanBeTurnedIntoEmptyNode(Node $foo)
    {
        $this->assertEquals('<foo>', (string) $foo->merge());
    }

    /**
     * @depends clone testCanBeConstructedFromName
     * @depends testCanBeConstructedFromNameAndAttributes
     */
    public function testNewNodesCanBeInserted(Node $foo, Node $bar)
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
     * @depends clone testCanBeConstructedFromName
     */
    public function testNewNodeCanBeAdded(Node $foo)
    {
        $this->assertEquals(
            '<foo><bar><baz zim="qux"></baz></bar></foo>',
            (string) $foo->bar()->baz('zim="qux"')->root()
        );
    }

    /**
     * @depends clone testCanBeConstructedFromName
     */
    public function testMultipleNodesCanBeAdded(Node $foo)
    {
        $this->assertEquals(
            '<foo><bar></bar><bar></bar><bar></bar></foo>',
            (string) $foo->bar()->times(3)
        );
    }

    /**
     * @depends clone testCanBeConstructedFromName
     * @expectedException \Sprout\Exception\NodeNotFoundException
     */
    public function testMultipleNodesCanBeAdded2(Node $foo)
    {
        $foo->times(3);
    }

    /**
     * @depends clone testCanBeConstructedFromName
     * @expectedException \Sprout\Exception\InvalidArgumentException
     */
    public function testMultipleNodesCannotBeAddedZeroTimes(Node $foo)
    {
        $foo->baz()->times(0);
    }

    /**
     * @depends clone testCanBeConstructedFromName
     */
    public function testMultipleSubtreesCanBeAdded(Node $foo)
    {
        $this->assertEquals(
            '<foo><bar><baz></baz></bar><bar><baz></baz></bar></foo>',
            (string) $foo->bar()->mark('label')->baz()->times(2, 'label')
        );
    }
}
