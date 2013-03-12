<?php
namespace Translator;

class Iframe
{

    private $hostname;


    private $pageId;

    private $language;

    public function __construct($hostname, $pageId, $language)
    {
        $this->hostname = $hostname;
        $this->pageId = $pageId;
        $this->language = $language;
    }

    public function __toString()
    {
        return <<<HTML
<script type="text/javascript">document.domain = document.location.hostname</script>
<iframe src="//{$this->hostname}" width="1" height="1" frameborder="0" id="translate"
    onload="this.contentWindow.initTranslation('{$this->language}', '{$this->pageId}');"></iframe>
HTML;

    }
}
