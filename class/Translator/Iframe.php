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
    private $pageId;

    /**
     * @var string
     */
    private $language;

    public function __construct($baseUri, $pageId, $language)
    {
        $this->baseUri = $baseUri;
        $this->pageId = $pageId;
        $this->language = $language;
    }

    public function __toString()
    {
        return <<<HTML
<iframe src="{$this->baseUri}" width="1" height="1" frameborder="0" id="translate"
    onload="this.contentWindow.initTranslation('{$this->language}', '{$this->pageId}');"></iframe>
HTML;

    }
}
