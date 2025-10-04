<?php

namespace Recurr\Transformer;

interface TranslatorInterface
{
    /**
     * @param array<string, string|int|float|bool|null> $params
     *
     * @return string|array<array-key, mixed>
     */
    public function trans(string $string, array $params = []): string|array;
}
