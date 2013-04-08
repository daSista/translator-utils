<?php
namespace Translator\String;

use Translator\String;

class Decorator
{

    public function decorate($keyWithNamespace, $translation)
    {
        $id = String::create($keyWithNamespace, $translation)->id();
        return "\xE2\x80\x98$id\xE2\x80\x99$translation\xE2\x80\x99";
    }

}
