<?php

namespace Sprout;

if (!function_exists('Sprout\root')) {
    /**
     * @param string $name
     * @param string $attributes
     *
     * @return Node
     */
    function root(string $name, string $attributes = '')
    {
        return new Node($name, $attributes);
    }
}
