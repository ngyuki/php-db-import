<?php
$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__)
    ->path('bin/')
    ->path('src/')
    ->path('tests/')
;

return PhpCsFixer\Config::create()
    ->setRules(['@PSR2' => true])
    ->setFinder($finder)
;
