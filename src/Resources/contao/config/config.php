<?php

/*
 * This file is part of [petzka/demo-bundle].
 *
 * (c) Moritz Petzka
 *
 * @license LGPL-3.0-or-later
 */


$GLOBALS['FE_MOD']['application']['search_news_categories'] = 'Petzka\ContaoNewsSearch\FrontendModule\SearchNewsModule';

$GLOBALS['TL_JAVASCRIPT'][] = 'bundles/contaonewssearch/js/contaoNewsSearch.js';
