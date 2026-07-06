<?php

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__ . '/src')
    ->in(__DIR__ . '/tests')
    ->append([
        __FILE__,
        __DIR__ . '/functions.php',
        __DIR__ . '/object-storage.php',
    ]);

return (new PhpCsFixer\Config())
    ->setRules([
        '@PER-CS3.0' => true,
    ])
    ->setFinder($finder);
