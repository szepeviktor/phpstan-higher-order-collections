<?php

namespace SustainabilIT\PHPStanHOCPlugin\Extensions;

use PHPStan\Analyser\OutOfClassScope;

use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\PropertyReflection;
use PHPStan\Reflection\PropertiesClassReflectionExtension;

use SustainabilIT\PHPStanHOCPlugin\Reflections\HigherOrderCollectionPropertyReflection;

class HigherOrderCollectionPropertyExtension extends BaseHigherOrderCollectionExtension implements PropertiesClassReflectionExtension
{
    public function hasProperty(ClassReflection $class, string $property) : bool
    {
        return $this->isCollectionProxy($class) && $this->getTemplateType($class)->hasProperty($property)->yes();
    }

    public function getProperty(ClassReflection $class, string $property) : PropertyReflection
    {
        return new HigherOrderCollectionPropertyReflection(
            $class,
            $this->config,
            $this->mapClassReflections(
                $class,
                function (ClassReflection $reflection) use ($property) : PropertyReflection {
                    return $reflection->getProperty($property, new OutOfClassScope);
                }
            )
        );
    }
}
