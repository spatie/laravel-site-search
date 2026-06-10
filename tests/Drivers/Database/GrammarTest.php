<?php

use Spatie\SiteSearch\Drivers\Database\Grammar;
use Spatie\SiteSearch\Drivers\Database\MySqlGrammar;
use Spatie\SiteSearch\Drivers\Database\PostgresGrammar;
use Spatie\SiteSearch\Drivers\Database\SqliteGrammar;

it('escapes dangerous characters from search terms', function (Grammar $grammar) {
    expect($grammar->escapeSearchTerm('"hello"'))->toBe('hello');
    expect($grammar->escapeSearchTerm('hello*world'))->toBe('hello world');
    expect($grammar->escapeSearchTerm('test(group)'))->toBe('test group');
    expect($grammar->escapeSearchTerm('field:value'))->toBe('field value');
})->with([
    'sqlite' => fn () => new SqliteGrammar,
    'mysql' => fn () => new MySqlGrammar,
    'postgres' => fn () => new PostgresGrammar,
]);

it('strips mysql boolean mode operators that would break the query', function (Grammar $grammar) {
    expect($grammar->escapeSearchTerm('icu4c@78'))->toBe('icu4c 78');
    expect($grammar->escapeSearchTerm('rank>up'))->toBe('rank up');
    expect($grammar->escapeSearchTerm('rank<down'))->toBe('rank down');
    expect($grammar->escapeSearchTerm('~negate'))->toBe('negate');

    // The real-world path that triggered the crash on freek.dev should no longer
    // contain any boolean mode operator that breaks MySQL's fulltext parser.
    $path = '/System/Volumes/Preboot/Cryptexes/OS/opt/homebrew/opt/icu4c@78/lib/libicuio.78.dylib';
    expect($grammar->escapeSearchTerm($path))
        ->not->toContain('@')
        ->not->toContain('-');
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
