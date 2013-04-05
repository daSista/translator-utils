<?php
namespace Translator;

class Application
{
    const TRANSLATE_ON = 'on';
    const TRANSLATE_OFF = 'off';

    /**
     * @var string
     */
    private $applicationBaseUri;

    /**
     * @var string TRANSLATE_*
     */
    private $translationMode;

    /**
     * @var \Translator\Adapter\AdapterInterface
     */
    private $translateAdapter;

    /**
     * @param string $applicationBaseUri
     * @param \Translator\Adapter\AdapterInterface $translateAdapter
     * @param string $translationMode
     */
    public function __construct($applicationBaseUri, $translateAdapter, $translationMode = self::TRANSLATE_OFF)
    {
        $this->applicationBaseUri = $applicationBaseUri;
        $this->translateAdapter = $translateAdapter;
        $this->translationMode = $translationMode;
    }

    public function translate($key, $params = array())
    {
        return $this->translateAdapter->translate($key, $params);
    }

    public function authorizeClient()
    {

    }

    public function injectAtClientSide($locale)
    {
        if ($this->translationMode == self::TRANSLATE_ON) {
            return strval(new Iframe($this->applicationBaseUri, $locale));
        }
        return '';
    }

}
