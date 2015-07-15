# VeMessDetector

This is an additional set of rules for the PHP Mess Detector.

## Installation

This package should be installed through composer.

Inside your project ```composer.json``` file add the package to the require-dev list.

```
"require-dev": {
    "phpunit/phpunit": "4.5.*",
    "ve-interactive/php-mess-detector": "*"
},
```

Because the project is currently inside a private repository the repository itself needs to be added to the composer file.

```
"repositories": [
    {
        "type": "vcs",
        "url": "git@github.com:ve-interactive/VeMessDetector.git"
    }
],
```

You will than have to create a new ```phpmd.xml``` file in the root of your project

```
<?xml version="1.0" encoding="UTF-8"?>
<ruleset name="REDACTED" 
		 xmlns="http://pmd.sf.net/ruleset/1.0.0" 
		 xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" 
		 xsi:schemaLocation="http://pmd.sf.net/ruleset/1.0.0 http://pmd.sf.net/ruleset_xml_schema.xsd"
		 xsi:noNamespaceSchemaLocation="http://pmd.sf.net/ruleset_xml_schema.xsd">
	<description>REDACTED</description>
	
	<!-- Ve Standards -->
	<rule ref="vendor/ve-interactive/php-mess-detector/phpmd.xml" />
</ruleset>
```

## Usage

To run the phpmd tests just execute the following command inside your terminal

```
vendor/bin/phpmd srcDirectory/ text phpmd.xml
```

The first parameter is the source directory, the second one is the output format [xml, text, html].


## Rules Details

The following rules are applied by default:

- Cyclomatic Complexity (default values)
- N Path Complexity (default values)
- Excessive Method Length (45 lines)
- Excessive Class Length (300 lines)
- Excessive Parameter List (6 parameters)
- Excessive Class Complexity (default values)
- [Controversial Rules](http://phpmd.org/rules/index.html#controversial-rules)
- Exit Expression
- Eval Expression
- Goto Statement
- Depth of Inheritance (4)
- Coupling Between Objects (10)
- Short Variable (3, with the following exceptions: ```di```, ```id```, ```i```, ```a```, ```b```, ```to```)
- Long Variable (30)
- Short Method Name
- Constructor with Name as Enclosing Class
- Constant Naming Conventions
- Boolean Get Method Name
- Unused Code

All this rules can be looked up on the [official documentation page](http://phpmd.org/rules/index.html).

The following additional rules have been created:

- Undefined Local Variable
- Unreachable Code

## Undefined Local Variable

This rule will search whether or not any local variable in the given scope (method or function) was declared before ever being used.

Some technical limitations do not allow proper checks of closures, therefore everything within them is not being checked.

## Unreachable Code

This rule will check whether or not a return statement is going to systematically stop the execution of the rest of the method.