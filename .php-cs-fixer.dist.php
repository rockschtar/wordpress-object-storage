<?php

declare(strict_types=1);

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__ . '/src')
    ->in(__DIR__ . '/tests')
    ->append([
        __FILE__,
        __DIR__ . '/functions.php',
        __DIR__ . '/object-storage.php',
    ]);

return (new PhpCsFixer\Config())
    ->setRiskyAllowed(true)
    ->setRules([
        '@PER-CS3.0' => true,
        'declare_strict_types' => true,
    ])
    ->setFinder($finder);
