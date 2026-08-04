<?php

namespace Iliaal\NameParser\Part;

/**
 * a surname particle ("van", "de", "del") renders in its lowercase dictionary
 * form supplied at map time, rather than being camelcased like a plain surname
 */
class LastnamePrefix extends Lastname
{
    use PreNormalized;
}
