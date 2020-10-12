<?php

namespace SustainabilIT\PHPStanHOCPlugin\Extensions;

use PHPStan\Type\NeverType;
use PHPStan\Type\Type;

use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\ReflectionProvider;

use SustainabilIT\PHPStanHOCPlugin\Reflections\CollectionPropertyReflection;
use SustainabilIT\PHPStanHOCPlugin\Support\ConfigInterface;

abstract class BaseHigherOrderCollectionExtension
{
    /**
     * @var \SustainabilIT\PHPStanHOCPlugin\Support\ConfigInterface
     */
    protected $config;

    /**
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;

    public function __construct(ConfigInterface $config, ReflectionProvider $reflectionProvider)
    {
        $this->config = $config;
        $this->reflectionProvider = $reflectionProvider;
    }
    
    /**
     * @template S
     * @param callable(ClassReflection): S $cb
     *
     * @return S[]
     */
    protected function mapClassReflections(ClassReflection $reflection, callable $cb) : array
    {
        return array_map(
            function (string $class) use ($cb) {
                return $cb($this->reflectionProvider->getClass($class));
            },
            $this->getTemplateType($reflection)->getReferencedClasses()
        );
    }

    protected function isCollectionProxy(ClassReflection $class) : bool
    {
        $proxy = $this->config->proxyClass();
        
        return $class->getName() === $proxy || $class->isSubclassOf($proxy);
    }
    
    protected function getTemplateType(ClassReflection $class) : Type
    {
        return $class->getActiveTemplateTypeMap()->getType($this->config->typeTemplate()) ?? new NeverType;
    }
}
