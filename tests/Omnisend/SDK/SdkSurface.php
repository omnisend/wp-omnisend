<?php
/**
 * Reflection-based description of the public SDK surface (omnisend/includes/SDK).
 *
 * Used by SdkSurfaceTest to compare the current code against the committed baseline in
 * sdk-surface-baseline.php, and by tests/tools/dump-sdk-surface.php to regenerate it.
 */

namespace Omnisend\Tests\SDK;

use ReflectionClass;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionType;
use ReflectionUnionType;

final class SdkSurface
{
    public const SDK_DIRECTORY = __DIR__ . '/../../../omnisend/includes/SDK';

    /**
     * @return array<string, array<string, mixed>> class name => description of its surface
     */
    public static function collect(): array
    {
        $surface = [];

        foreach (self::sdk_class_names() as $class_name) {
            $reflection = new ReflectionClass($class_name);

            $surface[$class_name] = [
                'kind'      => $reflection->isInterface() ? 'interface' : ($reflection->isAbstract() ? 'abstract class' : 'class'),
                'parent'    => $reflection->getParentClass() === false ? null : $reflection->getParentClass()->getName(),
                'interfaces' => self::sorted(array_values($reflection->getInterfaceNames())),
                'constants' => self::constants($reflection),
                'methods'   => self::methods($reflection),
            ];
        }

        ksort($surface);

        return $surface;
    }

    /**
     * @return string[]
     */
    public static function sdk_class_names(): array
    {
        $names = [];

        foreach (self::sdk_files() as $file) {
            $namespace = self::namespace_of($file);
            $class     = self::class_name_of($file);

            if ($namespace === null || $class === null) {
                continue;
            }

            $names[] = $namespace . '\\' . $class;
        }

        return self::sorted($names);
    }

    /**
     * @return string[]
     */
    private static function sdk_files(): array
    {
        $files    = [];
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(realpath(self::SDK_DIRECTORY), \FilesystemIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $files[] = $file->getPathname();
            }
        }

        return self::sorted($files);
    }

    private static function namespace_of(string $file): ?string
    {
        if (preg_match('/^namespace\s+([^;]+);/m', (string) file_get_contents($file), $matches) !== 1) {
            return null;
        }

        return trim($matches[1]);
    }

    private static function class_name_of(string $file): ?string
    {
        if (preg_match('/^(?:abstract\s+|final\s+)*(?:class|interface)\s+(\w+)/m', (string) file_get_contents($file), $matches) !== 1) {
            return null;
        }

        return $matches[1];
    }

    /**
     * @return array<string, mixed>
     */
    private static function constants(ReflectionClass $reflection): array
    {
        $constants = [];

        foreach ($reflection->getReflectionConstants() as $constant) {
            if (!$constant->isPublic() || $constant->getDeclaringClass()->getName() !== $reflection->getName()) {
                continue;
            }

            $constants[$constant->getName()] = $constant->getValue();
        }

        ksort($constants);

        return $constants;
    }

    /**
     * @return array<string, string> method name => signature
     */
    private static function methods(ReflectionClass $reflection): array
    {
        $methods = [];

        foreach ($reflection->getMethods() as $method) {
            if ($method->isPrivate() || $method->getDeclaringClass()->getName() !== $reflection->getName()) {
                continue;
            }

            $methods[$method->getName()] = self::signature($method);
        }

        ksort($methods);

        return $methods;
    }

    private static function signature(ReflectionMethod $method): string
    {
        $parameters = [];

        foreach ($method->getParameters() as $parameter) {
            $parameters[] = self::parameter($parameter);
        }

        $visibility = $method->isProtected() ? 'protected' : 'public';
        $static     = $method->isStatic() ? ' static' : '';
        $return     = $method->hasReturnType() ? ': ' . self::type($method->getReturnType()) : '';

        return $visibility . $static . ' function ' . $method->getName() . '(' . implode(', ', $parameters) . ')' . $return;
    }

    private static function parameter(ReflectionParameter $parameter): string
    {
        $type = $parameter->hasType() ? self::type($parameter->getType()) . ' ' : '';

        $declaration = $type . ($parameter->isVariadic() ? '...' : '') . '$' . $parameter->getName();

        if (!$parameter->isDefaultValueAvailable()) {
            return $declaration;
        }

        return $declaration . ' = ' . self::default_value($parameter);
    }

    private static function default_value(ReflectionParameter $parameter): string
    {
        if ($parameter->isDefaultValueConstant()) {
            return (string) $parameter->getDefaultValueConstantName();
        }

        $value = $parameter->getDefaultValue();

        if (is_array($value)) {
            return $value === [] ? 'array()' : var_export($value, true);
        }

        return var_export($value, true);
    }

    private static function type(?ReflectionType $type): string
    {
        if ($type instanceof ReflectionNamedType) {
            return ($type->allowsNull() && $type->getName() !== 'mixed' && $type->getName() !== 'null' ? '?' : '') . $type->getName();
        }

        if ($type instanceof ReflectionUnionType) {
            $names = [];
            foreach ($type->getTypes() as $member) {
                $names[] = self::type($member);
            }

            return implode('|', $names);
        }

        return (string) $type;
    }

    /**
     * @param string[] $values
     *
     * @return string[]
     */
    private static function sorted(array $values): array
    {
        sort($values);

        return $values;
    }
}
