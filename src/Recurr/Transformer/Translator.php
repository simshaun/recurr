<?php

namespace Recurr\Transformer;

class Translator implements TranslatorInterface
{
    /**
     * @var array<string, string|callable>
     */
    protected array $data = [];

    public function __construct(string $locale = 'en', string $fallbackLocale = 'en')
    {
        $lowercasedLocale = strtolower($locale);
        $lowercasedFallbackLocale = strtolower($fallbackLocale);

        $this->loadLocale($lowercasedFallbackLocale);
        if ($lowercasedLocale !== $lowercasedFallbackLocale) {
            $this->loadLocale($lowercasedLocale);
        }
    }

    public function loadLocale(string $locale, ?string $path = null): void
    {
        if (!$path) {
            $path = __DIR__.'/../../../translations/'.$locale.'.php';
        }
        if (!file_exists($path)) {
            throw new \InvalidArgumentException('Locale '.$locale.' could not be found in '.$path);
        }

        $this->data = array_merge($this->data, include $path);
    }

    public function trans(string $string, array $params = []): string|array
    {
        $res = $this->data[$string];
        if (is_callable($res)) {
            $res = $res($string, $params);
        }

        if (\is_string($res)) {
            foreach ($params as $key => $val) {
                $res = str_replace('%'.$key.'%', (string) $val, $res);
            }
        }

        return $res;
    }
}
