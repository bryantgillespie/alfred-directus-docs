<?php

use Alfred\Workflows\Workflow;

use Algolia\AlgoliaSearch\SearchClient as Algolia;
use Algolia\AlgoliaSearch\Support\UserAgent as AlgoliaUserAgent;

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/functions.php';

$query = $argv[1];

$workflow = new Workflow;
$algolia = Algolia::create('BH4D9OD16A', '84890d566c1f9ad79ca62a1358e05c60');

AlgoliaUserAgent::addCustomUserAgent('Directus Doc Search', '1.0.0');

$results = getResults($algolia, 'directus', $query);

if (empty($results)) {
    $workflow->result()
        ->title('No matches')
        ->icon('google.png')
        ->subtitle("No match found in the docs. Search Google for: \"Directus+{$query}\"")
        ->arg("https://www.google.com/search?q=directus+{$query}")
        ->quicklookurl("https://www.google.com/search?q=directus+{$query}")
        ->valid(true);

    echo $workflow->output();
    exit;
}

foreach ($results as $hit) {
    list($title, $titleLevel) = getTitle($hit);

    if ($title === null) {
        continue;
    }

    $title = html_entity_decode($title);

    $workflow->result()
        ->uid($hit['objectID'])
        ->title($title)
        ->autocomplete($title)
        ->subtitle(html_entity_decode(getSubtitle($hit, $titleLevel)))
        ->arg($hit['url'])
        ->quicklookurl($hit['url'])
        ->valid(true);
}

echo $workflow->output();
