<?php

namespace SustainabilIT\PHPStanHOCPlugin\Reflections;

use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ClassMemberReflection;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\ParametersAcceptor;
use PHPStan\TrinaryLogic;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;

use SustainabilIT\PHPStanHOCPlugin\Support\ConfigInterface;

class HigherOrderCollectionMethodReflection implements MethodReflection
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
     * @var MethodReflection[]
     */
    private $reflections;

    /**
     * @param MethodReflection[] $reflections
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

    /**
     * @return \PHPStan\Reflection\ParametersAcceptor[]
     */
    public function getVariants(): array
    {
        $decorator = function (ParametersAcceptor $acceptor) : ParametersAcceptor {
            return new HigherOrderCollectionParameterAcceptor(
                $acceptor,
                $this->classReflection,
                $this->config
            );
        };
        
        return array_merge(...$this->mapReflections(
            function (MethodReflection $reflection) use ($decorator) : array {
                return array_map($decorator, $reflection->getVariants());
            }
        ));
    }

    public function getDeclaringClass(): ClassReflection
    {
        return $this->classReflection;
    }

    public function isStatic(): bool
    {
        return $this->checkAnyReflectionsPass(function (MethodReflection $method) : bool {
            return $method->isStatic();
        });
    }

    public function isPrivate(): bool
    {
        return $this->checkAnyReflectionsPass(function (MethodReflection $method) : bool {
            return $method->isPrivate();
        });
    }

    public function isPublic(): bool
    {
        return $this->checkAllReflectionsPass(function (MethodReflection $method) : bool {
            return $method->isPublic();
        });
    }

    public function getName(): string
    {
        return $this->reflections[0]->getName();
    }

    public function getPrototype(): ClassMemberReflection
    {
        return $this;
    }

    public function isDeprecated(): TrinaryLogic
    {
        return $this->trinaryForReflection(function (MethodReflection $method) : bool {
            return $method->isDeprecated()->no();
        });
    }

    public function getDeprecatedDescription(): ?string
    {
        $messages = $this->mapReflections(function (MethodReflection $method) : ? string {
            return $method->getDeprecatedDescription();
        });
        
        return implode("\n", array_filter($messages)) ?: null;
    }

    public function isFinal(): TrinaryLogic
    {
        return $this->trinaryForReflection(function (MethodReflection $method) : bool {
            return $method->isFinal()->no();
        });
    }

    public function isInternal(): TrinaryLogic
    {
        return $this->trinaryForReflection(function (MethodReflection $method) : bool {
            return $method->isInternal()->no();
        });
    }

    public function getThrowType(): ? Type
    {
        $errors = array_filter($this->mapReflections(function (MethodReflection $method) : ? Type {
            return $method->getThrowType();
        }));

        return $errors ? (count($errors) > 1 ? new UnionType($errors) : $errors[0]) : null;
    }

    public function hasSideEffects(): TrinaryLogic
    {
        return $this->trinaryForReflection(function (MethodReflection $method) : bool {
            return $method->hasSideEffects()->no();
        });
    }

    public function getDocComment(): ?string
    {
        $messages = $this->mapReflections(function (MethodReflection $method) : ? string {
            return $method->getDocComment();
        });
        
        return implode("\n", array_filter($messages)) ?: null;
    }
}
