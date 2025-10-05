<?php
declare(strict_types=1);

use PhpCsFixer\Runner\Parallel\ParallelConfigFactory;

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__.'/src')
    ->in(__DIR__.'/tests')
    ->in(__DIR__.'/translations')
;

return (new PhpCsFixer\Config())
    ->setParallelConfig(ParallelConfigFactory::detect())
    ->setRules([
        '@Symfony' => true,
        'fully_qualified_strict_types' => ['import_symbols' => true],
        'no_blank_lines_after_class_opening' => true,
        'phpdoc_align' => ['align' => 'left'],
        'phpdoc_summary' => false,
        'phpdoc_var_without_name' => false,
        'single_line_comment_style' => ['comment_types' => ['asterisk']],
        'single_line_empty_body' => true,
        'yoda_style' => false,
    ])
    ->setFinder($finder);
