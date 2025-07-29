<?php

/**
 * Author: Dennis Schwab - 2025
 * Licence: GPL2.0 or later
 * Version: 0.1.0
 */

declare(strict_types=1);

namespace DS\fluidHelpers\Utility;

use Exception;
use ReflectionAttribute;
use ReflectionClass;

use __IDE\Pure;

final class AttributeFetcherException extends Exception {}
/**
 * Get all or specific attributes from one or more classes or create instances of the attributes.
 */
final class AttributeFetcher
{
    static private function _getReflectionClassInstance(object|string $class, object|string &$className, array &$attributes): ReflectionClass
    {
        $reflectionClass = new ReflectionClass($class);
        $className = is_object($class) ? array_pop(explode('\\', $reflectionClass->name)) : array_pop(explode('\\', $class));
        if (!isset($attributes[$className])) $attributes[$className] = [];
        return $reflectionClass;
    }

    static private function _getAttributesArray(array &$attributes, array $elements, string $className, string $name, bool $valueOnly, string $type): void
    {
        $looseSearch = NULL;
        if ($name && !str_contains($name, '\\'))
        {
            $looseSearch = $name;
            $name = NULL;
        }
        array_walk($elements, function ($element) use (&$attributes, $className, $name, $looseSearch, $type, $valueOnly) {
            if (sizeof($element->getAttributes($name)) > 0) $attributes[$className][$type][$element->name] = [];
            array_walk($element->getAttributes($name), function ($attribute) use (&$attributes, $className, $type, $element, $looseSearch, $valueOnly) {
                if (!$looseSearch || str_contains($attribute->getName(), $looseSearch))
                {
                    $attributes[$className][$type][$element->name][$attribute->getName()] =
                        $valueOnly ? $attribute->getArguments() : $attribute;
                }
            });
        });
    }

    static private function _returnAttributesByMethod(
        object|string $class, &$attributes, ?string $method = NULL, ?string $name = NULL, ?bool $valueOnly = false
    )
    {
        $className = '';
        $reflectionClass = AttributeFetcher::_getReflectionClassInstance($class, $className, $attributes);
        $attributes[$className]['methodAttributes'] = [];
        $methods = [];
        if ($method !== NULL)
        {
            $method = trim($method, '()');
            if ($reflectionClass->hasMethod($method))
            {
                $methods = [$reflectionClass->getMethod($method)];
            }
            else return;
        }
        else $methods = $reflectionClass->getMethods();
        AttributeFetcher::_getAttributesArray($attributes, $methods, $className, $name, $valueOnly, 'methodAttributes');
    }

    static private function _returnAttributesByConstant(
        object|string $class, array &$attributes, ?string $constant = NULL, ?string $name = NULL, ?bool $valueOnly = false
    )
    {
        $className = '';
        $reflectionClass = AttributeFetcher::_getReflectionClassInstance($class, $className, $attributes);
        $attributes[$className]['constantAttributes'] = [];
        $constants = [];
        if ($constant !== NULL)
        {
            if ($reflectionClass->hasConstant($constant))
            {
                $constants = [$reflectionClass->getReflectionConstant($constant)];
            }
            else return;
        }
        else $constants = $reflectionClass->getReflectionConstants();
        AttributeFetcher::_getAttributesArray($attributes, $constants, $className, $name, $valueOnly, 'constantAttributes');
    }

    static private function _returnAttributesByClass (
        object|string $class, array &$attributes, ?string $name = NULL, ?bool $valueOnly = false
    )
    {
        $className = '';
        $reflectionClass = AttributeFetcher::_getReflectionClassInstance($class, $className, $attributes);
        $attributes[$className]['classAttributes'] = [];
        $looseSearch = NULL;
        if ($name && !str_contains($name, '\\'))
        {
            $looseSearch = $name;
            $name = NULL;
        }
        $tempAttributes = $reflectionClass->getAttributes($name);
        array_walk($tempAttributes, function ($attribute) use (&$attributes, $className, $looseSearch, $valueOnly) {
            if (!$looseSearch || str_contains($attribute->getName(), $looseSearch))
            {
                $attributes[$className]['classAttributes'][$attribute->getName()] = $valueOnly ? $attribute->getArguments() : $attribute;
            }
        });
    }

    static private function _fetchAttributes(array &$attributes, object|string $class, ?bool $valueOnly = false)
    {
        if (is_object($class))
        {
            $class = new ReflectionClass($class);
            $class = $class->name;
        }
        if (is_string($class))
        {
            $class = trim($class);
            $delimited = explode('->', $class);
            $name = NULL;
            switch (sizeof($delimited))
            {
                case 1:
                    if (str_contains($class, '::'))
                    {
                        if ($class[0] === ':' && $class[1] === ':')
                        {
                            $class = trim($class, ':');
                            AttributeFetcher::_returnAttributesByClass($class, $attributes, NULL, $valueOnly);
                        }
                    }
                    else
                    {
                        AttributeFetcher::_returnAttributesByClass($class, $attributes, NULL, $valueOnly);
                        AttributeFetcher::_returnAttributesByMethod($class, $attributes, NULL, NULL, $valueOnly);
                        AttributeFetcher::_returnAttributesByConstant($class, $attributes, NULL, NULL, $valueOnly);
                    }
                    break;
                case 3:
                    $name = array_pop($delimited);
                case 2:
                    $filter = array_pop($delimited);
                    $dummyMatches = [];
                    if ($name) $name = trim($name, "'\"");
                    /** Filtered by a function */
                    if (str_contains($filter, '()'))
                    {
                        if ($filter === '()') AttributeFetcher::_returnAttributesByMethod($delimited[0], $attributes, NULL, $name, $valueOnly);
                        else AttributeFetcher::_returnAttributesByMethod($delimited[0], $attributes, $filter, $name, $valueOnly);
                    }
                    /** Filtered by name */
                    else if (preg_match("/['\"]{1}[\w\\]+['\"]{1}/", $filter, $dummyMatches))
                    {
                        $name = trim($filter, "'\"");
                        AttributeFetcher::_returnAttributesByClass($delimited[0], $attributes, $name, $valueOnly);
                        AttributeFetcher::_returnAttributesByMethod($delimited[0], $attributes, NULL, $name, $valueOnly);
                        AttributeFetcher::_returnAttributesByConstant($delimited[0], $attributes, NULL, $name, $valueOnly);
                    }
                    /** Filtered by constant */
                    else
                    {
                        if (strtolower($filter) === 'const')
                            AttributeFetcher::_returnAttributesByConstant($delimited[0], $attributes, NULL, $name, $valueOnly);
                        else
                            AttributeFetcher::_returnAttributesByConstant($delimited[0], $attributes, $filter, $name, $valueOnly);
                    }
                    break;
                default:
                    throw new AttributeFetcherException("Wrong filter level. Have you exceeded class->type->name ?");
                    break;
            }
        }
    }

    static private function _fetchAllAttributes(
        object|string $_class, array $classes, ?bool $valueOnly = false
    ): array
    {
        $attributes = [];
        $classes = array_merge([$_class], $classes);
        foreach ($classes as $class)
        {
            if ($class) AttributeFetcher::_fetchAttributes($attributes, $class, $valueOnly);
        }
        return $attributes;
    }

    /**
     * Get all or specific attributes from one or more classes.
     * 
     * This function only returns the values of the attributes.
     * 
     * Filter results by extending the filter string with an arrow notation (->).
     * 
     * Valid filters are:
     * 
     * Filter by class:
     * 
     * ::ClassName - Get attributes only associated to the class itself.
     * 
     * ClassName - Get all attributes of this class: The class, the methods and the constants to be clear.
     * 
     * Filter by type:
     * 
     * ClassName->MethodName() - Get all attributes of the given method.
     * 
     * ClassName->() - Get all attributes of all methods of the class
     * 
     * ClassName->ConstantName Get all attributes of the given constant.
     * 
     * ClassName->const - Get all attributes of all constants of the class
     * 
     * Filter by name:
     * 
     * ClassName->'App\Subfolder\ClassNameOfTheAttribute' or ClassName->"App\Subfolder\ClassNameOfTheAttribute" - Get all attributes of that attribute class name (class, methods, constants)
     * 
     * ClassName->TYPE->'App\Subfolder\ClassNameOfTheAttribute' or ClassName->TYPE->"App\Subfolder\ClassNameOfTheAttribute" - Will reduce the example above to the given type (class, methods, constants)
     * E.g.: ClassName->MethodName()->'App\Subfolder\ClassNameOfTheAttribute' will only return attributes associated with the class, the method and the attribute class name.
     * 
     * Furthermore you can do a fuzzy search by obmitting the namespace and just call the name of the class. This can return unwanted results, though.
     * Searching for ClassName->CONSTANT_VAR->TestClass could return App\Subfolder\TestClass, App\Subfolder\TestClass2 and App\TestClass\MyClass.
     * 
     * @param object|string $_class The class to extract the attributes from. Can either be an instance or a full relative path to the class.
     * @param ?bool $looseSearch If true, the class name of the attribute's class can be used. E.g.: ClassName instead of App\Subfolder\ClassName
     * @param object|string ...$classes If more than one class needs to be covered at once
     * @return array Returns an array structured as follows: [ClassName[TypeAttributes[AttributeClassName]]]
     */
    static public function getAttributesValue(object|string $_class, object|string ...$classes): array
    {
        return AttributeFetcher::_fetchAllAttributes($_class, $classes, true);
    }

    /**
     * Get all or specific attributes from one or more classes.
     * 
     * This function returns an array of attributes of the ReflectionAttribute class type.
     * 
     * Filter results by extending the filter string with an arrow notation (->).
     * 
     * Valid filters are:
     * 
     * Filter by class:
     * 
     * ::ClassName - Get attributes only associated to the class itself.
     * 
     * ClassName - Get all attributes of this class: The class, the methods and the constants to be clear.
     * 
     * Filter by type:
     * 
     * ClassName->MethodName() - Get all attributes of the given method.
     * 
     * ClassName->() - Get all attributes of all methods of the class
     * 
     * ClassName->ConstantName Get all attributes of the given constant.
     * 
     * ClassName->const - Get all attributes of all constants of the class
     * 
     * Filter by name:
     * 
     * ClassName->'App\Subfolder\ClassNameOfTheAttribute' or ClassName->"App\Subfolder\ClassNameOfTheAttribute" - Get all attributes of that attribute class name (class, methods, constants)
     * 
     * ClassName->TYPE->'App\Subfolder\ClassNameOfTheAttribute' or ClassName->TYPE->"App\Subfolder\ClassNameOfTheAttribute" - Will reduce the example above to the given type (class, methods, constants)
     * E.g.: ClassName->MethodName()->'App\Subfolder\ClassNameOfTheAttribute' will only return attributes associated with the class, the method and the attribute class name.
     * 
     * Furthermore you can do a fuzzy search by obmitting the namespace and just call the name of the class. This can return unwanted results, though.
     * Searching for ClassName->CONSTANT_VAR->TestClass could return App\Subfolder\TestClass, App\Subfolder\TestClass2 and App\TestClass\MyClass.
     * 
     * @param object|string $_class The class to extract the attributes from. Can either be an instance or a full relative path to the class.
     * @param object|string ...$classes If more than one class needs to be covered at once
     * @return array Returns an array structured as follows: [ClassName[TypeAttributes[AttributeClassName]]]
     */
    static public function getAttributes(object|string $_class, object|string ...$classes): array
    {
        return AttributeFetcher::_fetchAllAttributes($_class, $classes, false);
    }

    /**
     * Creates and returns an instance of the attributes class.
     * 
     * You can forward an direct instance of ReflectionAttribute or a filter string.
     * 
     * Please read the PHPDocs from the methods getAttributes or getAttributesValue for more details about the filtering.
     * 
     * A full filter must be defined, the keywords () and const and the fuzzy search are not valid, as these can return multiple results.
     * 
     * A valid filter could be App\Subfolder\Class->method()->App\Subfolder\AttributeClass
     * 
     * Not allowed are:
     * 
     * App\Subfolder\Class
     * 
     * App\Subfolder\Class->method()
     * 
     * App\Subfolder\Class->()->App\Subfolder\AttributeClass
     * 
     * App\Subfolder\Class->method()->AttributeClass
     * 
     * @param ReflectionAttribute|string $attribute A ReflectionAttribute object or a string filter
     * 
     * @return object The instance of the attribute's class
     */
    public static function createInstance(ReflectionAttribute|string $attribute): object
    {
        if (is_object($attribute) && $attribute instanceof ReflectionAttribute) return $attribute->newInstance();
        else if (!is_string($attribute)) return NULL;
        $delimited = explode('->', $attribute);
        if (sizeof($delimited) != 3)
            throw new AttributeFetcherException(
                "The createInstance method needs an exact attribute filter with two arrow notations. E.g.: ClassName->Type->AttributeClassName"
            );
        else if ($delimited[1] === '()' || $delimited[1] === 'const')
        {
            throw new AttributeFetcherException(
                "The createInstance method needs an exact attribute filter. The generalization tokens '()' and 'const' are not allowed."
            );
        }
        else if (!str_contains($delimited[2], '\\'))
        {
            throw new AttributeFetcherException(
                "The createInstance method needs an exact attribute filter. " .
                "Loose search is not allowed. Specify the full class name. E.g.: App\\Subfolder\\ClassName"
            );
        }
        $attribute = AttributeFetcher::getAttributes($attribute);
        while (is_array($attribute) && $attribute = current($attribute));
        if (is_object($attribute) && $attribute instanceof ReflectionAttribute) return $attribute->newInstance();
        else return NULL;
    }

    private static function _traverseAttributesArray(array &$attributes, array &$_attributes, ?bool $mapped = false): void
    {
        if (is_array($attributes))
        {
            foreach ($attributes as $key => &$attribute)
            {
                if (is_array($attribute)) AttributeFetcher::_traverseAttributesArray($attribute, $_attributes, $mapped);
                else if ($mapped) $attributes[$key] = AttributeFetcher::createInstance($attribute);
                else $_attributes[] = AttributeFetcher::createInstance($attribute);
            }
        }
    }

    /**
     * Creates and returns multiple instances of the given attributes.
     * 
     * The generalizing tokens const and () are allowed, one can use the same filters as for the get methods.
     * 
     * @param array $_attributes An (multidimensional) array of filters or ReflectionAttribute objects
     * @param bool $mapped If false, a simple indexed array will be returned, otherwise it will keep the structure of the original forwarded array
     * 
     * @return array Returns an array - indexed or structured - containing the instances of the attributes classes
     */
    public static function createInstances(array $_attributes, ?bool $mapped = false): array
    {
        $attributes = [];
        foreach ($_attributes as $attribute)
        {
            if (is_array($attribute))
            {
                AttributeFetcher::_traverseAttributesArray($_attributes, $attributes, $mapped);
                return $mapped ? $_attributes : $attributes;
            }
            else if (is_string($attribute) || (is_object($attribute) && !($attribute instanceof ReflectionAttribute)))
            {
                $__attributes = AttributeFetcher::getAttributes($attribute);
                $attributes = array_merge_recursive($attributes, AttributeFetcher::createInstances($__attributes, $mapped));
            }
            else if (is_object($attribute) && $attribute instanceof ReflectionAttribute)
            {
                // Mapping doesn't work here, because of missing meta data
                $attributes[] = $attribute->newInstance();
            }
        }
        return $attributes;
    }
}

?>