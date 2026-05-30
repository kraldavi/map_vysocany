<?php

declare(strict_types=1);

$finder = PhpCsFixer\Finder::create()
	->in(__DIR__)
	->exclude(['temp', 'log', 'vendor'])
	->name('*.php')
	->notName('*.generated.php');

return (new PhpCsFixer\Config())
	->setFinder($finder)
	->setIndent("\t")
	->setLineEnding("\n")
	->setRiskyAllowed(true)
	->setRules([
		'@PSR12' => true,
		'array_syntax' => ['syntax' => 'short'],
		'declare_strict_types' => true,
		'fully_qualified_strict_types' => true,
		'global_namespace_import' => [
			'import_classes' => true,
			'import_constants' => false,
			'import_functions' => false,
		],
		'native_function_invocation' => false,
		'no_unused_imports' => true,
		'ordered_imports' => ['sort_algorithm' => 'alpha'],
		'phpdoc_align' => ['align' => 'left'],
		'phpdoc_separation' => true,
		'phpdoc_trim' => true,
		'phpdoc_var_without_name' => true,
		'single_quote' => true,
		'trailing_comma_in_multiline' => ['elements' => ['arrays', 'arguments', 'parameters']],
		'visibility_required' => ['elements' => ['property', 'method', 'const']],
	]);
