<?php

namespace Recurr\Transformer;

interface TranslatorInterface
{
    /** @return string|array<string> */
    public function trans(string $string): string|array;
}
