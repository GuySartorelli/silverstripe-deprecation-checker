<?php

namespace SilverStripe\DeprecationChangelogGenerator\Tests;

use Doctum\Project;
use PHPUnit\Framework\TestCase;
use SilverStripe\DeprecationChangelogGenerator\Parse\ParserFactory;
use SilverStripe\DeprecationChangelogGenerator\Render\Renderer;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Path;

class RendererTest extends TestCase
{
    private static Project $project;

    public static function setUpBeforeClass(): void
    {
        // Note that from-only wouldn't be included in the supported modules list
        // because we only care about modules in the "to" list. See GenerateCommand::findSupportedModules().
        // But we do have some code in some-org/from-only to check that this list is respected.
        $supportedModules = [
            'some-org/module1' => [
                'type' => 'module',
                'packagist' => 'some-org/module1',
            ],
            'some-org/module2' => [
                'type' => 'module',
                'packagist' => 'some-org/module2',
            ],
            'some-org/to-only' => [
                'type' => 'module',
                'packagist' => 'some-org/to-only',
            ],
            'some-org/some-recipe' => [
                'type' => 'recipe',
                'packagist' => 'some-org/some-recipe',
            ],
            'some-org/some-theme' => [
                'type' => 'theme',
                'packagist' => 'some-org/some-theme',
            ],
            // We need to include framework with some stubs for some of the type checks
            // e.g. checking for the configurable trait on DataObject subclasses.
            'silverstripe/framework' => [
                'type' => 'module',
                'packagist' => 'silverstripe/framework',
            ],
        ];
        $factory = new ParserFactory($supportedModules, Path::join(__DIR__, 'fixture-code'));
        RendererTest::$project = $factory->buildProject();
        // The renderer needs the parsed AST so it knows if classes exist in the "to" version or not.
        // This is important for creating API links only for API that actually exists in that version of our code.
        RendererTest::$project->parse();
    }

    public static function tearDownAfterClass(): void
    {
        // Remove any cache from running the test.
        $cacheDir = Path::join(__DIR__, 'fixture-code/cache');
        if (is_dir($cacheDir)) {
            $filesystem = new Filesystem();
            $filesystem->remove($cacheDir);
        }
    }

    public function testRender()
    {
        $fixtureDir = Path::join(__DIR__, 'fixture-code');
        $outputFile = Path::join($fixtureDir, 'render-output/changelog.md');
        $renderer = new Renderer(['branch' => 'old version'], ['branch' => 'new version'], RendererTest::$project);
        $renderer->render(BreakingChangesComparerTest::getExpectedChanges(), $fixtureDir, $outputFile);
        $this->assertStringEqualsFile($outputFile, $this->getExpected());
    }

    private function getExpected(): string
    {
        return <<<'MD'
        ## Full list of removed and changed API (by module, alphabetically) {#api-removed-and-changed}

        This section contains the full list of APIs that have been changed or removed between Silverstripe CMS old version and new version. You most likely don't need to read the entire list. But it can be a useful reference to have open when upgrading a project or module.

        <details>
        <summary>Reveal full list of API changes</summary>

        <!-- markdownlint-disable proper-names enhanced-proper-names -->

        ### `some-org/module1`

        - Removed deprecated class `SomeOrg\Module1\Model\ModelOne` - this deprecation notice has no version
        - Removed deprecated class `SomeOrg\Module1\SomeClassTwo` - 1.2 Version number format is wrong!!
        - Removed deprecated interface `SomeOrg\Module1\SomeInterfaceTwo` - this interface has been deprecated, hurray!
        - Removed deprecated trait `SomeOrg\Module1\SomeTraitTwo`
        - Removed deprecated method `SomeOrg\Module1\Model\ModelTwo::moveMethodToExtensionButNot()`
        - Removed deprecated method `SomeOrg\Module1\SomeTrait::thirdMethod()`
        - Removed deprecated `has_one` relation `FromExtensionHasOne` in [`ExtensionClassTwo`](api:SomeOrg\Module1\Extension\ExtensionClassTwo)
        - Removed deprecated `has_one` relation `FromExtensionHasOne` in [`ExtensionClass`](api:SomeOrg\Module1\Extension\ExtensionClass)
        - Removed deprecated `has_one` relation `FromExtensionHasOne` in [`ModelTwo`](api:SomeOrg\Module1\Model\ModelTwo)
        - Removed deprecated config `SomeOrg\Module1\Extension\ExtensionClass.has_one`
        - Removed deprecated config `SomeOrg\Module1\Extension\ExtensionClassTwo.has_one` - replaced with [`ExtensionClass.has_one`](api:SomeOrg\Module1\Extension\ExtensionClass->has_one)
        - Removed deprecated property `SomeOrg\Module1\SomeInterface->someProperty`
        - Removed deprecated property `SomeOrg\Module1\SomeTrait->someProperty`
        - Removed deprecated parameter `$param2` in [`SomeInterface::anotherMethod()`](api:SomeOrg\Module1\SomeInterface::anotherMethod())
        - Class [`SomeClass`](api:SomeOrg\Module1\SomeClass) is now internal and should not be used
        - Property [`SomeTrait->wasProtected`](api:SomeOrg\Module1\SomeTrait->wasProtected) is now internal and should not be used
        - Changed visibility for method [`SomeTrait::anotherMethod()`](api:SomeOrg\Module1\SomeTrait::anotherMethod()) from `public` to `protected`
        - Changed return type for method [`ModelTwo::moveMethodToExtension()`](api:SomeOrg\Module1\Model\ModelTwo::moveMethodToExtension()) from dynamic to `void`
        - Changed return type for method [`SomeInterface::thirdMethod()`](api:SomeOrg\Module1\SomeInterface::thirdMethod()) from dynamic to `void`
        - Changed return type for method [`SomeTrait::someMethod()`](api:SomeOrg\Module1\SomeTrait::someMethod()) from `array` to `array|null`
        - Changed return type for function [`someGlobalFunctionThree()`](api:someGlobalFunctionThree()) from `bool|null` to `null`
        - Changed type of database field `FromExtensionDB` in [`ExtensionClass`](api:SomeOrg\Module1\Extension\ExtensionClass) from `'Boolean(true)'` to `'Boolean(false)'`
        - Changed type of database field `FromExtensionDB` in [`ModelTwo`](api:SomeOrg\Module1\Model\ModelTwo) from `'Boolean(true)'` to `'Boolean(false)'`
        - Changed type of database field `MoveToExtension` in [`ModelTwo`](api:SomeOrg\Module1\Model\ModelTwo) from `'HTMLText'` to `'Varchar'`
        - Changed type of `has_many` relation `FromExtensionHasMany` in [`ExtensionClassTwo`](api:SomeOrg\Module1\Extension\ExtensionClassTwo) from `SomeOrg\Module1\Model\ModelOne` to [`ModelTwo`](api:SomeOrg\Module1\Model\ModelTwo)
        - Changed type of `has_many` relation `FromExtensionHasMany` in [`ModelTwo`](api:SomeOrg\Module1\Model\ModelTwo) from `SomeOrg\Module1\Model\ModelOne` to [`ModelTwo`](api:SomeOrg\Module1\Model\ModelTwo)
        - Changed type of parameter `$someArg2` in [`someGlobalFunctionTwo()`](api:someGlobalFunctionTwo()) from `bool` to dynamic
        - Changed type of parameter `$string` in [`someGlobalFunctionThree()`](api:someGlobalFunctionThree()) from `string` to `string|null`
        - Renamed parameter `$returnMe` in [`SomeTrait::someMethod()`](api:SomeOrg\Module1\SomeTrait::someMethod()) to `$return`
        - Renamed parameter `$someParam` in [`SomeInterface::anotherMethod()`](api:SomeOrg\Module1\SomeInterface::anotherMethod()) to `$someOtherParam`
        - Renamed parameter `$string` in [`someGlobalFunctionThree()`](api:someGlobalFunctionThree()) to `$stringRenamed`
        - Added new parameter `$anotherParam` in [`SomeInterface::someMethod()`](api:SomeOrg\Module1\SomeInterface::someMethod())
        - Added new parameter `$newArg` in [`someGlobalFunctionThree()`](api:someGlobalFunctionThree())
        - Added new parameter `$param2` in [`SomeTrait::someMethod()`](api:SomeOrg\Module1\SomeTrait::someMethod())
        - Method [`SomeInterface::thirdMethod()`](api:SomeOrg\Module1\SomeInterface::thirdMethod()) is no longer static
        - Method [`SomeTrait::anotherMethod()`](api:SomeOrg\Module1\SomeTrait::anotherMethod()) is now static
        - Method [`SomeTrait::anotherMethod()`](api:SomeOrg\Module1\SomeTrait::anotherMethod()) is now abstract
        - Function [`someGlobalFunctionFive()`](api:someGlobalFunctionFive()) no longer returns its value by reference
        - Function [`someGlobalFunctionSix()`](api:someGlobalFunctionSix()) now returns its value by reference
        - Parameter `$param2` in [`SomeTrait::anotherMethod()`](api:SomeOrg\Module1\SomeTrait::anotherMethod()) is no longer passed by reference
        - Parameter `$string` in [`someGlobalFunctionFour()`](api:someGlobalFunctionFour()) is now passed by reference
        - Parameter `$someArg2` in [`someGlobalFunctionTwo()`](api:someGlobalFunctionTwo()) is now variadic
        - Changed default value for config [`ExtensionClass.array_config`](api:SomeOrg\Module1\Extension\ExtensionClass->array_config) - array values have changed
        - Changed default value for parameter `$returnMe` in [`SomeTrait::someMethod()`](api:SomeOrg\Module1\SomeTrait::someMethod()) from `[]` to `null`
        - Changed default value for parameter `$someArg2` in [`someGlobalFunctionTwo()`](api:someGlobalFunctionTwo()) from `false` to none
        - Changed default value for parameter `$string` in [`someGlobalFunctionThree()`](api:someGlobalFunctionThree()) from `null` to none

        ### `some-org/module2`

        - Removed deprecated method `SomeOrg\Module2\Something\ClassOne::__construct()`
        - Removed deprecated method `SomeOrg\Module2\Something\ClassOne::__destruct()`
        - Removed deprecated config `SomeOrg\Module2\Something\DataObjectOne.config2`
        - Removed deprecated config `SomeOrg\Module2\Something\DataObjectOne.config3` - removed without a replacement
        - Removed deprecated property `SomeOrg\Module2\Something\ClassOne->property3`
        - Removed deprecated constant `SomeOrg\Module2\Something\ClassOne::CONST_ONE`
        - Changed visibility for method [`ClassOne::methodOne()`](api:SomeOrg\Module2\Something\ClassOne::methodOne()) from `public` to `protected`
        - Changed visibility for method [`ClassOne::methodTwo()`](api:SomeOrg\Module2\Something\ClassOne::methodTwo()) from `protected` to `public`
        - Changed visibility for method [`ClassTwo::someMethod()`](api:SomeOrg\Module2\Something\ClassTwo::someMethod()) from `public` to `protected`
        - Changed visibility for property [`ClassOne->property1`](api:SomeOrg\Module2\Something\ClassOne->property1) from `protected` to `public`
        - Changed return type for method [`ClassOne::methodTwo()`](api:SomeOrg\Module2\Something\ClassOne::methodTwo()) from `void` to `null`
        - Changed return type for method [`ClassTwo::someMethod()`](api:SomeOrg\Module2\Something\ClassTwo::someMethod()) from `array` to `array|null`
        - Changed type of property [`ClassOne->property1`](api:SomeOrg\Module2\Something\ClassOne->property1) from dynamic to `string|null`
        - Changed type of property [`ClassOne->property2`](api:SomeOrg\Module2\Something\ClassOne->property2) from `string|`[`Member`](api:SilverStripe\Security\Member) to `int|`[`Member`](api:SilverStripe\Security\Member)`|null`
        - Changed type of parameter `$returnMe` in [`ClassTwo::someMethod()`](api:SomeOrg\Module2\Something\ClassTwo::someMethod()) from `array` to `array|int`
        - Changed type of parameter `$variadicParam` in [`ClassOne::methodTwo()`](api:SomeOrg\Module2\Something\ClassOne::methodTwo()) from dynamic to `array`
        - Method [`ClassOne::methodOne()`](api:SomeOrg\Module2\Something\ClassOne::methodOne()) is now static
        - Method [`ClassTwo::someMethod()`](api:SomeOrg\Module2\Something\ClassTwo::someMethod()) is now static
        - Class [`ClassTwo`](api:SomeOrg\Module2\Something\ClassTwo) is now abstract
        - Method [`ClassTwo::someMethod()`](api:SomeOrg\Module2\Something\ClassTwo::someMethod()) is now abstract
        - Class [`ClassOne`](api:SomeOrg\Module2\Something\ClassOne) is now final and cannot be subclassed
        - Method [`ClassOne::methodFour()`](api:SomeOrg\Module2\Something\ClassOne::methodFour()) now returns its value by reference
        - Method [`ClassOne::methodThree()`](api:SomeOrg\Module2\Something\ClassOne::methodThree()) no longer returns its value by reference
        - Parameter `$variadicParam` in [`ClassOne::methodTwo()`](api:SomeOrg\Module2\Something\ClassOne::methodTwo()) is no longer variadic
        - Changed default value for config [`DataObjectOne.config1`](api:SomeOrg\Module2\Something\DataObjectOne->config1) from `'one'` to `1`
        - Changed default value for config [`DataObjectOne.config4`](api:SomeOrg\Module2\Something\DataObjectOne->config4) from `null` to `4`
        - Changed default value for parameter `$returnMe` in [`ClassTwo::someMethod()`](api:SomeOrg\Module2\Something\ClassTwo::someMethod()) from `[]` to none

        </details>

        MD;
    }
}
