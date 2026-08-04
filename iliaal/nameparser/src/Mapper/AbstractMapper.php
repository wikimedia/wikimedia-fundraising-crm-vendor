<?php

namespace Iliaal\NameParser\Mapper;

use Iliaal\NameParser\Part\AbstractPart;
use Iliaal\NameParser\Text;

/**
 * @phpstan-type PartArray array<int, AbstractPart|string>
 */
abstract class AbstractMapper
{
    /**
     * implements the mapping of parts
     *
     * @param  PartArray  $parts
     * @return PartArray
     */
    abstract public function map(array $parts): array;

    /**
     * @param  PartArray  $parts
     * @return PartArray
     */
    protected function normalizeParts(array $parts): array
    {
        return array_is_list($parts) ? $parts : array_values($parts);
    }

    /**
     * checks if there are still unmapped parts left before the given position
     *
     * @param  PartArray  $parts
     */
    protected function hasUnmappedPartsBefore(array $parts, int $index): bool
    {
        foreach ($parts as $k => $part) {
            if ($k === $index) {
                break;
            }

            if (! ($part instanceof AbstractPart)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  class-string  $type
     * @param  PartArray  $parts
     */
    protected function findFirstMapped(string $type, array $parts): int|false
    {
        $total = count($parts);

        for ($i = 0; $i < $total; $i++) {
            if ($parts[$i] instanceof $type) {
                return $i;
            }
        }

        return false;
    }

    /**
     * get the registry lookup key for the given word
     */
    protected function getKey(string $word): string
    {
        return Text::key($word);
    }

    /**
     * true when every unmapped cased token is uppercase and none carries a
     * lowercase letter, i.e. the input casing gives no signal (all-caps registry
     * data). Already-mapped parts are ignored because their normalized values
     * may differ from the original token casing. When $override is non-null it
     * is returned as-is (comma pipeline whole-input signal).
     *
     * @param  PartArray  $parts
     */
    protected function isUniformUpperContext(array $parts, ?bool $override = null): bool
    {
        if ($override !== null) {
            return $override;
        }

        $hasUpper = false;

        foreach ($parts as $part) {
            if ($part instanceof AbstractPart) {
                continue;
            }

            $letters = Text::letters($part);

            if ($letters === '') {
                continue;
            }

            if (mb_strtoupper($letters, 'UTF-8') !== $letters) {
                return false;
            }

            if ($letters !== mb_strtolower($letters, 'UTF-8')) {
                $hasUpper = true;
            }
        }

        return $hasUpper;
    }
}
