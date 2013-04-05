<?php

namespace Translator\Adapter;

interface AdapterInterface 
{
    /**
     * @param string $key
     * @param array $params
     * @return string
     */
    public function translate($key, $params = array());
}