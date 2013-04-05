<?php
namespace Translator;

class Iframe
{
    /**
     * @var string
     */
    private $baseUri;

    /**
     * @var string
     */
    private $locale;

    public function __construct($baseUri, $locale)
    {
        $this->baseUri = $baseUri;
        $this->locale = $locale;
    }

    public function __toString()
    {
        return <<<HTML
<iframe src="{$this->baseUri}" width="1" height="1" frameborder="0" id="translate"
    onload="this.contentWindow.initTranslation('{$this->locale}');"></iframe>
HTML;

    }
}
