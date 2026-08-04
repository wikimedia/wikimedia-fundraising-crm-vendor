<?php

namespace Iliaal\NameParser\Part;

/**
 * a particle in a compound given name ("Maria del Carmen") renders in its
 * lowercase dictionary form, matching how LastnamePrefix normalizes a
 * surname particle, instead of being title-cased like a plain middle name
 */
class MiddlenamePrefix extends Middlename
{
    use PreNormalized;
}
