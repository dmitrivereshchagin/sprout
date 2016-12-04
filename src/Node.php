<?php

declare(strict_types=1);

namespace Sprout;

use Sprout\Exception\InvalidArgumentException;
use Sprout\Exception\NodeNotFoundException;

class Node
{
    /**
     * @var string Node name
     */
    protected $name;
    /**
     * @var string|null Node attributes
     */
    protected $attributes;
    /**
     * @var self[]|string|null Node content
     */
    protected $content;
    /**
     * @var self|null Parent node
     */
    private $parent;
    /**
     * @var string|null Node label
     */
    private $label;

    /**
     * Constructor.
     *
     * @param string      $name
     * @param string|null $attributes
     */
    public function __construct(string $name, string $attributes = null)
    {
        $this->name = $name;
        $this->attributes = $attributes;
        $this->content = [];
    }

    /**
     * Dynamically creates new child of current node.
     *
     * @param string $name
     * @param array  $arguments
     *
     * @return static
     */
    public function __call($name, $arguments): self
    {
        return $this->add($name, ...$arguments);
    }

    /**
     * Returns string representation of current node.
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->string();
    }

    /**
     * Creates new child of current node.
     *
     * @param string      $name
     * @param string|null $attributes
     *
     * @return static
     */
    public function add(string $name, string $attributes = null): self
    {
        $node = new static($name, $attributes);

        $this->insert($node);

        return $node;
    }

    /**
     * Inserts new child nodes.
     *
     * @param self ...$nodes
     *
     * @return $this
     */
    public function insert(self ...$nodes): self
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
     * Marks current node with label.
     *
     * @param string $label
     *
     * @return $this
     */
    public function mark(string $label): self
    {
        $this->label = $label;

        return $this;
    }

    /**
     * Empties current node.
     *
     * @return $this
     */
    public function merge(): self
    {
        $this->content = null;

        return $this;
    }

    /**
     * Returns root node.
     *
     * @return self
     */
    public function root(): self
    {
        return $this->rise(function ($node) {
            return !$node->parent;
        });
    }

    /**
     * Returns string representation of current node.
     *
     * @return string
     */
    public function string(): string
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
    public function text(string $text): self
    {
        $this->content = $text;

        return $this;
    }

    /**
     * Repeats current or marked node given number of times.
     *
     * @param int         $number
     * @param string|null $label
     *
     * @throws InvalidArgumentException if $number less than one
     *
     * @return self
     */
    public function times(int $number, string $label = null): self
    {
        if ($number < 1) {
            throw new InvalidArgumentException(
                'Number of times must be positive'
            );
        }

        $child = ($label === null) ? $this : $this->to($label);
        $parent = $child->up();

        while (--$number) {
            $parent->insert($child);
        }

        return $parent;
    }

    /**
     * Returns parent node.
     *
     * @throws NodeNotFoundException if parent node does not exist
     *
     * @return self
     */
    public function up(): self
    {
        if (!$this->parent) {
            throw new NodeNotFoundException('Parent node does not exist');
        }

        return $this->parent;
    }

    /**
     * Returns current node or first of its parents which is marked with label.
     *
     * @param string $label
     *
     * @throws NodeNotFoundException if node not found
     *
     * @return self
     */
    public function to(string $label): self
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
     * Creates node instance.
     *
     * @param string      $name
     * @param string|null $attributes
     *
     * @return static
     */
    public static function create(string $name, string $attributes = null): self
    {
        return new static($name, $attributes);
    }

    /**
     * Returns start-tag.
     *
     * @return string
     */
    protected function start(): string
    {
        if ($this->attributes !== null) {
            return "<$this->name $this->attributes>";
        }

        return "<$this->name>";
    }

    /**
     * Returns end-tag.
     *
     * @return string
     */
    protected function end(): string
    {
        return "</$this->name>";
    }

    /**
     * Returns empty-element tag.
     *
     * @return string
     */
    protected function empty(): string
    {
        return $this->start();
    }

    /**
     * Returns node content enclosed with start-tag and end-tag.
     *
     * @param string $content
     *
     * @return string
     */
    protected function enclose(string $content): string
    {
        return $this->start().$content.$this->end();
    }

    /**
     * Returns current node or first of its parents for which predicate is true.
     *
     * @param callable $predicate
     *
     * @return self|null
     */
    private function rise(callable $predicate): ?self
    {
        $node = $this;

        while ($node && !$predicate($node)) {
            $node = $node->parent;
        }

        return $node;
    }
}
