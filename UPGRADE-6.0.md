# Upgrade Guide

### PHP Version Requirement

**Breaking Change**: The minimum PHP version has been raised to **8.4**.


### Custom Translator Implementations

**Breaking Change**: If you have implemented a custom `TranslatorInterface`, the method signature has changed:

**Before:**
```php
interface TranslatorInterface
{
    public function trans($string);
}
```

**After:**
```php
interface TranslatorInterface
{
    public function trans(string $string, array $params = []): string|array;
}
```


### Strict Type Declarations

**Breaking Change**: All methods now have strict type declarations for parameters and return types.

**Impact**: If you were relying on loose type coercion, your code may now throw exceptions.

**Action Required**: Review any code that extends or heavily uses Recurr classes and ensure type compatibility.


### Method Return Types

**Breaking Change**: All public methods now have explicit return type declarations.

**Impact**: If you've extended any Recurr classes and overridden methods, you must add matching return types.


### No Behavioral Changes

**Good News**: While there are breaking changes at the type level, there should be **no breaking changes** to the actual behavior of the library:

- All RRULE parsing and transformation logic works exactly the same
- All public APIs behave identically to the previous version
- Date calculations, recurrence generation, and transformations are unchanged

The upgrade is purely about adding modern PHP type safety and raising the minimum PHP version.
