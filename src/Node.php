<?php

namespace Sprout;

use Sprout\Exception\NodeNotFoundException;

class Node
{
    /**
     * @var string
     */
    private $name;
    /**
     * @var string[]
     */
    private $attributes;
    /**
     * @var string
     */
    private $parent;
    /**
     * @var self[]|string|null
     */
    private $content;
    /**
     * @var string
     */
    private $label;

    /**
     * Constructor.
     *
     * @param string $name
     * @param string[] $attributes
     */
    public function __construct(string $name, array $attributes = [])
    {
        $this->name = $name;
        $this->attributes = $attributes;
        $this->content = [];
    }

    public function __call($name, $arguments)
    {
        return $this->add($name, ...$arguments);
    }

    public function __toString()
    {
        return $this->string();
    }

    /**
     * Creates new child of current node.
     *
     * @param string $name
     * @param string[] $attributes
     *
     * @return static
     */
    public function add(string $name, array $attributes = [])
    {
        $node = new static($name, $attributes);
        $this->insert($node);

        return $node;
    }

    /**
     * Inserts new child nodes.
     *
     * @param self $nodes,...
     *
     * @return $this
     */
    public function insert(self ...$nodes)
    {
        if (!is_array($this->content)) {
            $this->content = [];
        }

        foreach ($nodes as $node) {
            $node->parent = $this;
            $this->content[] = $node;
        }

        return $this;
    }

    /**
     * Empties current node.
     *
     * @return $this
     */
    public function join()
    {
        $this->content = null;

        return $this;
    }

    /**
     * Marks current node with label.
     *
     * @param string $label
     *
     * @return $this
     */
    public function mark(string $label)
    {
        $this->label = $label;

        return $this;
    }

    /**
     * Returns root node.
     *
     * @return self
     */
    public function root()
    {
        return $this->rise(function ($node) {
            return !$node->parent;
        });
    }

    /**
     * Returns string representaion of current subtree.
     *
     * @return string
     */
    public function string()
    {
        if (is_string($this->content)) {
            return $this->enclose($this->content);
        }

        if (is_array($this->content)) {
            $content = '';
            foreach ($this->content as $node) {
                $content .= $node->string();
            }

            return $this->enclose($content);
        }

        return $this->empty();
    }

    /**
     * Replaces node content with text.
     *
     * @param string $text
     *
     * @return $this
     */
    public function text(string $text)
    {
        $this->content = $text;

        return $this;
    }

    /**
     * Returns parent node.
     *
     * @return self
     *
     * @throws NodeNotFoundException
     */
    public function up()
    {
        if (!$this->parent) {
            throw new NodeNotFoundException('Parent node does not exist');
        }

        return $this->parent;
    }

    /**
     * Returns parent node marked with label.
     *
     * @param string $label
     *
     * @return self
     *
     * @throws NodeNotFoundException
     */
    public function to(string $label)
    {
        $node = $this->rise(function ($node) use ($label) {
            return $node->label === $label;
        });

        if (!$node) {
            throw new NodeNotFoundException(
                sprintf('Node with label "%s" not found', $label)
            );
        }

        return $node;
    }

    /**
     * Returns start-tag.
     *
     * @return string
     */
    protected function start()
    {
        if (empty($this->attributes)) {
            return "<$this->name>";
        }

        $attributesStrings = [];
        foreach ($this->attributes as $name => $value) {
            $attributesStrings[] = sprintf('%s="%s"', $name, $value);
        }

        return sprintf('<%s %s>', $this->name, implode(' ', $attributesStrings));
    }

    /**
     * Returns end-tag.
     *
     * @return string
     */
    protected function end()
    {
        return "</$this->name>";
    }

    /**
     * Returns empty-element tag.
     *
     * @return string
     */
    protected function empty()
    {
        return $this->start();
    }

    /**
     * Encloses node content with start-tag and end-tag.
     *
     * @param string $content
     *
     * @return string
     */
    protected function enclose(string $content)
    {
        return $this->start().$content.$this->end();
    }

    /**
     * @param callable $predicate
     *
     * @return self|null
     */
    private function rise(callable $predicate)
    {
        $node = $this;

        while ($node && !$predicate($node)) {
            $node = $node->parent;
        }

        return $node;
    }
}
