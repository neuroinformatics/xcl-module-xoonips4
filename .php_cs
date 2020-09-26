<?php

$finder = PhpCsFixer\Finder::create()
    ->exclude('vendor')
    ->in(__DIR__)
;

return PhpCsFixer\Config::create()
    ->setRules([
        '@Symfony' => true,
        '@DoctrineAnnotation' => true,
        'array_indentation' => true,
        'no_superfluous_phpdoc_tags' => false,
    ])
    ->setFinder($finder)
;
