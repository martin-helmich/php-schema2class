<?php
namespace Helmich\Schema2Class\Generator\Property;

interface PropertyCollectionFilter
{
    function apply(PropertyInterface $property): bool;
}