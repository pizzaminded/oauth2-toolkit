<?php


declare(strict_types=1);

use PhpCsFixer\Runner\Parallel\ParallelConfigFactory;

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__)
    ->exclude('vendor');

return new PhpCsFixer\Config()
    ->setParallelConfig(ParallelConfigFactory::detect())
    ->setRules([
        '@Symfony' => true,
        '@PSR2' => true,
        '@PHP80Migration' => true,
        'array_syntax' => ['syntax' => 'short'],
        'blank_line_after_opening_tag' => true,
        'declare_strict_types' => true,
        'global_namespace_import' => [
            'import_functions' => true,
            'import_constants' => true,
        ],
        'mb_str_functions' => true,
        'no_whitespace_in_blank_line' => true,
        'no_leading_import_slash' => true,
        'no_useless_return' => true,
        'no_unused_imports' => true,
        'ordered_imports' => [
            'imports_order' => [
                'class',
                'function',
                'const'
            ],
            'sort_algorithm' => 'alpha'
        ],
        'php_unit_strict' => true,
        'strict_comparison' => true,
        'strict_param' => true,
        'trailing_comma_in_multiline' => true,
        'lowercase_static_reference' => true,
        'yoda_style' => false,
        'phpdoc_to_comment' => false,
    ])

    ->setRiskyAllowed(true)
    ->setFinder($finder);
