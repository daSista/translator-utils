<?php
namespace Translator;

class String_Decorator {

    public function decorate($key, $translation) {
        $md5key = md5($key);
        return "\xE2\x80\x98$md5key\xE2\x80\x99$translation\xE2\x80\x99";
    }

}
