<?php
$finder = PhpCsFixer\Finder::create()
  ->exclude('tests/Fixtures')
  ->exclude('models/Base')
  ->exclude('models/Map')
  ->exclude('tests')
  ->exclude('src')
  ->in(__DIR__)
  ->append([__DIR__.'/php-cs-fixer'])
;
$config = PhpCsFixer\Config::create()
  ->setRiskyAllowed(true)
  ->setRules([
    '@PhpCsFixer' => true,
    '@PhpCsFixer:risky' => true
  ])
  ->setIndent('  ')
  ->setFinder($finder)
;
return $config;
