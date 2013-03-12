<?php
namespace Translator;

class Application
{
    const TRANSLATE_ON = 'on';
    const TRANSLATE_OFF = 'off';

    private $baseUri;
    private $translationMode;

    /**
     * @var CouchDbStorage
     */
    private $storage;

    /**
     * @param string $baseUri
     * @param \Translator\CouchDbStorage $storage
     * @param string $translationMode
     */
    public function __construct($baseUri, $storage, $translationMode = self::TRANSLATE_OFF)
    {
        $this->baseUri = $baseUri;
        $this->storage = $storage;
        $this->translationMode = $translationMode;
    }

    public function translateAdapter($pageId)
    {
        return new Adapter\Simple($pageId, $this->storage, $this->translationMode);
    }

    public function authorizeClient()
    {

    }

    public function injectAtClientSide($pageId, $language)
    {
        if ($this->translationMode == self::TRANSLATE_ON) {
            return strval(new Iframe($this->baseUri, $pageId, $language));
        }
        return '';
    }

}
