<?php

namespace Iliaal\NameParser\Part;

/**
 * A token the parser recognized as belonging to no single person's name: the
 * conjunction in "Andrew and Sally Smith", or the second title in "Mr. Andrew
 * and Mrs Sally Smith". It extends AbstractPart directly rather than any name
 * or salutation type, so no getter exports it and the raw text is still visible
 * in Name::getParts() instead of being dropped.
 *
 * The value is returned verbatim. Title-casing it would be the very defect this
 * type exists to avoid ("and" rendering as the middle name "And").
 */
class Ignored extends AbstractPart {}
