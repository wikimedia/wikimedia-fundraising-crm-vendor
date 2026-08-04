<?php

namespace Iliaal\NameParser;

interface LanguageInterface
{
    /**
     * Array keys are registry lookup keys and must already be in normalized
     * form: lowercase, periods removed, no leading/trailing punctuation
     * (same transform as Text::key). Values are the rendered output form.
     *
     * @return array<int|string, string>
     */
    public function getSuffixes(): array;

    /**
     * Array keys are registry lookup keys and must already be in normalized
     * form: lowercase, periods removed, no leading/trailing punctuation
     * (same transform as Text::key). Values are the rendered output form.
     *
     * @return array<int|string, string>
     */
    public function getLastnamePrefixes(): array;

    /**
     * Array keys are registry lookup keys and must already be in normalized
     * form: lowercase, periods removed, no leading/trailing punctuation
     * (same transform as Text::key). Values are the rendered output form.
     *
     * @return array<int|string, string>
     */
    public function getSalutations(): array;
}
