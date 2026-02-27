<?php

use Spatie\SiteSearch\Actions\DefaultFindClosestAnchorAction;

beforeEach(function () {
    $this->action = new DefaultFindClosestAnchorAction();
});

it('returns null when no headings with ids exist', function () {
    $html = '<body><p>Some content here</p><p>More content</p></body>';

    $anchor = $this->action->execute($html, 10);

    expect($anchor)->toBeNull();
});

it('finds the closest heading before the text position', function () {
    $html = '
        <body>
            <h2 id="intro">Introduction</h2>
            <p>Some introduction text here</p>
            <h2 id="setup">Setup</h2>
            <p>Setup instructions here</p>
        </body>
    ';

    // Position in "Some introduction text"
    $anchor = $this->action->execute($html, 25);

    expect($anchor)->toBe('intro');
});

it('returns the most recent heading when multiple exist', function () {
    $html = '
        <body>
            <h2 id="intro">Introduction</h2>
            <p>Intro text</p>
            <h2 id="setup">Setup</h2>
            <p>Setup text here</p>
            <h3 id="config">Configuration</h3>
            <p>Config text here</p>
        </body>
    ';

    // Position in "Config text"
    $anchor = $this->action->execute($html, 200);

    expect($anchor)->toBe('config');
});

it('handles all heading levels h1 through h6', function () {
    $html = '
        <body>
            <h1 id="main">Main Title</h1>
            <p>Content</p>
            <h2 id="section1">Section 1</h2>
            <p>Content</p>
            <h3 id="subsection">Subsection</h3>
            <p>Content</p>
            <h4 id="detail">Detail</h4>
            <p>Content</p>
            <h5 id="subdetail">Subdetail</h5>
            <p>Content</p>
            <h6 id="minutiae">Minutiae</h6>
            <p>Content</p>
        </body>
    ';

    expect($this->action->execute($html, 5))->toBe('main');
    expect($this->action->execute($html, 30))->toBe('section1');
    expect($this->action->execute($html, 40))->toBe('subsection');
});

it('ignores headings without id attributes', function () {
    $html = '
        <body>
            <h2>Heading Without ID</h2>
            <p>Some text</p>
            <h2 id="has-id">Heading With ID</h2>
            <p>More text here</p>
        </body>
    ';

    // Position in "More text"
    $anchor = $this->action->execute($html, 100);

    expect($anchor)->toBe('has-id');
});

it('returns null for text before any heading', function () {
    $html = '
        <body>
            <p>Text before headings</p>
            <h2 id="intro">Introduction</h2>
            <p>Intro text</p>
        </body>
    ';

    // Position early in the document
    $anchor = $this->action->execute($html, 10);

    expect($anchor)->toBeNull();
});

it('handles empty html gracefully', function () {
    $html = '';

    $anchor = $this->action->execute($html, 0);

    expect($anchor)->toBeNull();
});

it('finds headings in nested structures', function () {
    $html = '
        <body>
            <div>
                <section>
                    <h2 id="nested">Nested Heading</h2>
                    <p>Content inside nested structure</p>
                </section>
            </div>
        </body>
    ';

    // Position in nested content
    $anchor = $this->action->execute($html, 100);

    expect($anchor)->toBe('nested');
});
