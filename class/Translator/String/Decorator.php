<?php
namespace Translator\String;

use Translator\String;

class Decorator
{

    public function decorate($keyWithNamespace, $translation)
    {
        $hash = String::create($keyWithNamespace, $translation)->hash();
        return "\xE2\x80\x98$hash\xE2\x80\x99$translation\xE2\x80\x99";
    }

}
