<?php

namespace Recurr\Transformer;

class Translator implements TranslatorInterface
{
    protected array $data = [];

    public function __construct(string $locale = 'en', string $fallbackLocale = 'en')
    {
        $this->loadLocale($fallbackLocale);
        if ($locale !== $fallbackLocale) {
            $this->loadLocale($locale);
        }
    }

    public function loadLocale(string $locale, ?string $path = null): void
    {
        if (!$path) {
            $path = __DIR__ . '/../../../translations/' . $locale . '.php';
        }
        if (!file_exists($path)) {
            throw new \InvalidArgumentException('Locale ' . $locale . ' could not be found in ' . $path);
        }

        $this->data = array_merge($this->data, include $path);
    }

    public function trans(string $string, array $params = []): string|array
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
