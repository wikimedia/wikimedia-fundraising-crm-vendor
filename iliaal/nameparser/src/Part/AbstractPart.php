<?php

namespace Iliaal\NameParser\Part;

abstract class AbstractPart
{
    /**
     * the wrapped value
     */
    protected string $value = '';

    /**
     * memoized camelcase result, keyed by the word it was computed for; parts
     * are effectively immutable after mapping, so this is computed at most once
     * per value and cleared whenever the value changes
     */
    private ?string $camelcaseCache = null;

    private ?string $camelcaseCacheWord = null;

    /**
     * constructor allows passing the value to wrap
     */
    public function __construct(string|AbstractPart $value)
    {
        $this->setValue($value);
    }

    /**
     * set the value to wrap
     * (can take string or part instance)
     */
    public function setValue(string|AbstractPart $value): static
    {
        if ($value instanceof AbstractPart) {
            $value = $value->getValue();
        }

        $this->value = $value;
        $this->camelcaseCache = null;
        $this->camelcaseCacheWord = null;

        return $this;
    }

    /**
     * get the wrapped value
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * get the normalized value
     */
    public function normalize(): string
    {
        return $this->getValue();
    }

    /**
     * helper for camelization of values
     * to be used during normalize
     */
    protected function camelcase(string $word): string
    {
        if ($this->camelcaseCache !== null && $this->camelcaseCacheWord === $word) {
            return $this->camelcaseCache;
        }

        $this->camelcaseCacheWord = $word;

        // the mixed-case pattern backtracks quadratically without the PCRE JIT
        // when a long run fails at every start position (single-case and
        // Title-case shapes alike), so it only runs on tokens short enough for
        // quadratic to be irrelevant — no real name comes close to the bound —
        // and only when both letter cases are present (it cannot match
        // otherwise). Oversized tokens go straight to the title-casing path.
        $caseShape = preg_replace('/\p{M}/u', '', $word) ?? $word;
        $isMixedCase = strlen($caseShape) <= 1024
            && $caseShape !== mb_strtoupper($caseShape, 'UTF-8')
            && $caseShape !== mb_strtolower($caseShape, 'UTF-8');

        if ($isMixedCase && preg_match('/\p{L}(\p{Lu}*\p{Ll}\p{Ll}*\p{Lu}|\p{Ll}*\p{Lu}\p{Lu}*\p{Ll})\p{L}*/u', $caseShape)) {
            return $this->camelcaseCache = $word;
        }

        // hostile long tokens: one title-case pass, no per-run callback
        if (strlen($word) > 256) {
            return $this->camelcaseCache = mb_convert_case($word, MB_CASE_TITLE, 'UTF-8');
        }

        // preg_replace_callback returns null on regex error; fall back to the input.
        return $this->camelcaseCache = preg_replace_callback('/[\p{L}\p{M}0-9]+/ui', $this->camelcaseReplace(...), $word) ?? $word;
    }

    /**
     * camelcasing callback
     *
     * @param  array<int, string>  $matches
     */
    protected function camelcaseReplace(array $matches): string
    {
        return mb_convert_case($matches[0], MB_CASE_TITLE, 'UTF-8');
    }
}
