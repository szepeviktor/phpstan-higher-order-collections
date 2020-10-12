<?php

namespace SustainabilIT\PHPStanHOCPlugin\Reflections;

use PHPStan\Reflection\PropertyReflection;
use PHPStan\Reflection\ClassReflection;
use PHPStan\TrinaryLogic;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
use PHPStan\Type\NeverType;

use SustainabilIT\PHPStanHOCPlugin\Support\ConfigInterface;

class HigherOrderCollectionPropertyReflection implements PropertyReflection
{
    use AggregatesReflections;

    /**
     * @var \PHPStan\Reflection\ClassReflection
     */
    private $classReflection;

    /**
     * @var \SustainabilIT\PHPStanHOCPlugin\Support\ConfigInterface
     */
    private $config;

    /**
     * @var PropertyReflection[]
     */
    private $reflections;

    /**
     * @param PropertyReflection[] $reflections
     */
    public function __construct(
        ClassReflection $classReflection,
        ConfigInterface $config,
        array $reflections
    ) {
        $this->classReflection = $classReflection;
        $this->config = $config;
        $this->reflections = $reflections;
    }

    public function getReadableType(): Type
    {
        $types = $this->getPropertyTypes();

        return $this->classReflection
                    ->withTypes([ count($types) > 1 ? new UnionType($types) : $types[0] ])
                    ->getActiveTemplateTypeMap()
                    ->getType($this->config->proxyTemplate()) ?? new NeverType;
    }

    /**
     * @return Type[]
     */
    private function getPropertyTypes(): array
    {
        return $this->mapReflections(function (PropertyReflection $property) : Type {
            return $property->getReadableType();
        });
    }

    public function getWritableType(): Type
    {
        return new NeverType(true);
    }

    public function getDeclaringClass(): ClassReflection
    {
        return $this->classReflection;
    }

    public function isStatic(): bool
    {
        return $this->checkAnyReflectionsPass(function (PropertyReflection $property) : bool {
            return $property->isStatic();
        });
    }

    public function isPrivate(): bool
    {
        return $this->checkAnyReflectionsPass(function (PropertyReflection $property) : bool {
            return $property->isPrivate();
        });
    }

    public function isPublic(): bool
    {
        return $this->checkAllReflectionsPass(function (PropertyReflection $property) : bool {
            return $property->isPublic();
        });
    }

    public function canChangeTypeAfterAssignment(): bool
    {
        return false;
    }

    public function isReadable(): bool
    {
        return $this->checkAllReflectionsPass(function (PropertyReflection $property) : bool {
            return $property->isReadable();
        });
    }

    public function isWritable(): bool
    {
        return false;
    }

    public function isDeprecated(): TrinaryLogic
    {
        return $this->trinaryForReflection(function (PropertyReflection $property) : bool {
            return $property->isDeprecated()->no();
        });
    }

    public function getdocComment(): ?string
    {
        $messages = $this->mapReflections(function (PropertyReflection $property) : ? string {
            return $property->getDocComment();
        });
        
        return implode("\n", array_filter($messages)) ?: null;
    }

    public function getDeprecatedDescription(): ?string
    {
        $messages = $this->mapReflections(function (PropertyReflection $property) : ? string {
            return $property->getDeprecatedDescription();
        });
        
        return implode("\n", array_filter($messages)) ?: null;
    }

    public function isInternal(): TrinaryLogic
    {
        return $this->trinaryForReflection(function (PropertyReflection $property) : bool {
            return $property->isInternal()->no();
        });
    }
}
