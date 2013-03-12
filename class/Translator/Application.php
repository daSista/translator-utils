<?php
namespace Translator;

class Application
{
    const TRANSLATE_ON = 'on';
    const TRANSLATE_OFF = 'off';

    private $hostname;
    private $translationMode;

    /**
     * @var CouchDbStorage
     */
    private $driver;

    public function __construct($hostname, $driver, $translationMode = self::TRANSLATE_OFF)
    {
        $this->hostname = $hostname;
        $this->driver = $driver;
        $this->translationMode = $translationMode;
    }

    public function translateAdapter($pageId)
    {
        return new Adapter\Simple(
            $this->translationMode, $pageId, $this->driver, new String\Decorator()
        );
    }

    public function authorizeClient()
    {

    }

    public function injectAtClientSide($pageId, $language)
    {
        if ($this->translationMode == self::TRANSLATE_ON) {
            return strval(new Iframe($this->hostname, $pageId, $language));
        }
        return '';
    }

}
