<?php

namespace Recurr\Transformer;

class Translator implements TranslatorInterface
{
    protected $data = array();

    public function __construct($locale = 'en', $fallbackLocale = 'en')
    {
        $lowercasedLocale = strtolower($locale);
        $lowercasedFallbackLocale = strtolower($fallbackLocale);

        $this->loadLocale($lowercasedFallbackLocale);
        if ($lowercasedLocale !== $lowercasedFallbackLocale) {
            $this->loadLocale($lowercasedLocale);
        }
    }

    public function loadLocale($locale, $path = null)
    {
        if (!$path) {
            $path = __DIR__ . '/../../../translations/' . $locale . '.php';
        }
        if (!file_exists($path)) {
            throw new \InvalidArgumentException('Locale '.$locale.' could not be found in '.$path);
        }

        $this->data = array_merge($this->data, include $path);
    }

    public function trans($string, array $params = array())
    {
        $res = $this->data[$string];
        if (is_object($res) && is_callable($res)) {
            $res = $res($string, $params);
        }

        foreach ($params as $key => $val) {
            $res = str_replace('%' . $key . '%', $val, $res);
        }

        return $res;
    }
}
