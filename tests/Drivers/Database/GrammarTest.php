<?php

use Spatie\SiteSearch\Drivers\Database\Grammar;
use Spatie\SiteSearch\Drivers\Database\MySqlGrammar;
use Spatie\SiteSearch\Drivers\Database\PostgresGrammar;
use Spatie\SiteSearch\Drivers\Database\SqliteGrammar;

it('escapes dangerous characters from search terms', function (Grammar $grammar) {
    expect($grammar->escapeSearchTerm('"hello"'))->toBe('hello');
    expect($grammar->escapeSearchTerm('hello*world'))->toBe('helloworld');
    expect($grammar->escapeSearchTerm('test(group)'))->toBe('testgroup');
    expect($grammar->escapeSearchTerm('field:value'))->toBe('fieldvalue');
})->with([
    'sqlite' => fn () => new SqliteGrammar,
    'mysql' => fn () => new MySqlGrammar,
    'postgres' => fn () => new PostgresGrammar,
]);

it('strips boolean operators from search terms', function (Grammar $grammar) {
    expect($grammar->escapeSearchTerm('foo OR bar'))->toBe('foo  bar');
    expect($grammar->escapeSearchTerm('foo AND bar'))->toBe('foo  bar');
    expect($grammar->escapeSearchTerm('NOT something'))->toBe('something');
})->with([
    'sqlite' => fn () => new SqliteGrammar,
    'mysql' => fn () => new MySqlGrammar,
    'postgres' => fn () => new PostgresGrammar,
]);

it('handles empty input gracefully', function (Grammar $grammar) {
    expect($grammar->escapeSearchTerm(''))->toBe('');
    expect($grammar->escapeSearchTerm('   '))->toBe('');
})->with([
    'sqlite' => fn () => new SqliteGrammar,
    'mysql' => fn () => new MySqlGrammar,
    'postgres' => fn () => new PostgresGrammar,
]);
