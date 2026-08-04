<?php

namespace Iliaal\NameParser\Part;

/**
 * The word joining two titles in a joint salutation ("Mr. and Mrs."). It is a
 * Salutation so it exports with the rest of the honorific, and its own type so
 * Name::isJoint() can report the name as covering two people.
 */
class SalutationConnector extends Salutation {}
