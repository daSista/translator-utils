<?php

namespace Translator\SourceCode;

class MustacheViewClause
{
    private $firstPart;
    private $paramPart;

    public function __construct($string)
    {
        $parts = preg_split('/^([^ ]+) /', $string, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY) + array('', '');
        $this->firstPart = $parts[0];
        $this->paramPart = $parts[1];
    }

    public function keyWithNamespace()
    {
        return $this->firstPart;
    }

    public function parameters()
    {
        $parts = preg_split(
            '/\\s*([_\.a-zA-Z0-9]+)\\s*=\\s*/',
            $this->paramPart,
            -1,
            PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY
        );

        $nameToValueMap = array();
        while(count($parts)) {
            $name = array_shift($parts);
            $value = array_shift($parts);
            $nameToValueMap[$name] = substr($value, 1, -1);
        }

        return $nameToValueMap;
    }
}