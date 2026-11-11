<?php

namespace Phrity\Util\Interpolator;

use Phrity\Util\Transformer\TransformerInterface;
use Stringable;

class Interpolator
{
    use InterpolatorTrait {
        interpolate as protected traitInterpolate;
    }

    /**
     * @param non-empty-string $separator
     * @param TransformerInterface|null $transformer
     */
    public function __construct(
        private string $separator = '.',
        private TransformerInterface|null $transformer = null,
    ) {
    }

    /**
     * @param string|Stringable $source
     * @param mixed $replacers
     */
    public function interpolate(
        string|Stringable $source,
        mixed $replacers = [],
    ): string {
        return $this->traitInterpolate($source, $replacers, $this->separator, $this->transformer);
    }
}
