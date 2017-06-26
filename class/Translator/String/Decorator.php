<?php
namespace Translator\String;

use Translator\MultiString;

class Decorator
{

    public function decorate($keyWithNamespace, $translation)
    {
        $hash = MultiString::create($keyWithNamespace, $translation)->hash();
        return "\xE2\x80\x98$hash\xE2\x80\x99$translation\xE2\x80\x99";
    }

}
