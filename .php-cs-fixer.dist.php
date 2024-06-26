<?php

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__)
    ->exclude('vendor')
    ->exclude('var')
    ->exclude('config')
    ->exclude('build')
    ->notPath('src/Kernel.php')
    ->notPath('public/index.php')
    ->in(__DIR__)
    ->name('*.php')   
    ->ignoreDotFiles(true);
;

return (new PhpCsFixer\Config())
    ->setRules([
        '@PSR2' => true,
        '@PhpCsFixer' => true,
        '@Symfony' => true,
        '@PHP70Migration:risky' => true,
        '@PHP71Migration:risky' => true,
        '@DoctrineAnnotation' => true,
        '@PhpCsFixer:risky' => true
    ])
    ->setFinder($finder)
;
