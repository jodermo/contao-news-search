<?php

/*
 * This file is part of [petzka/demo-bundle].
 *
 * (c) Moritz Petzka
 *
 * @license LGPL-3.0-or-later
 */

$GLOBALS['BE_FFL']['searchSuggestions'] = 'Petzka\ContaoNewsSearch\Widget\SearchSuggestionsWidget';


$GLOBALS['TL_MODELS']['tl_search_suggestion'] = 'Petzka\ContaoNewsSearch\Model\SearchSuggestionModel';

$GLOBALS['FE_MOD']['application']['search_news_categories'] = 'Petzka\ContaoNewsSearch\FrontendModule\SearchNewsModule';

$GLOBALS['BE_MOD']['content']['search_suggestions'] = array(
    'tables' => array(
        'tl_search_suggestions',
    )
);

$GLOBALS['TL_JAVASCRIPT'][] = 'bundles/contaonewssearch/js/contaoSearchSuggestions.js';
$GLOBALS['TL_JAVASCRIPT'][] = 'bundles/contaonewssearch/js/contaoNewsSearch.js';

$GLOBALS['TL_JAVASCRIPT'][] = 'bundles/contaonewssearch/js/autocomplete.js';
