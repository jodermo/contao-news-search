<?php

/*
 * This file is part of [petzka/contao-indisign-bundle].
 *
 * (c) Moritz Petzka <info@petzka.com>
 *
 * @license LGPL-3.0-or-later
 */

namespace Petzka\ContaoNewsSearch\Search;

use Petzka\ContaoNewsSearch\Model\ArticleModel;
use Petzka\ContaoNewsSearch\Model\NewsModel;

use Contao\ContentModel;
use Contao\Database\Result;
use Patchwork\Utf8;

class NewsSearch
{

    /**
     * Search the index and return the result object
     *
     * @param string $strKeywords The keyword string
     * @param array $arrPid An optional array of page IDs to limit the result to
     * @param array $arrPid An optional array of page IDs to limit the result to
     *
     * @return Result The database result object
     *
     * @throws \Exception If the cleaned keyword string is empty
     */
    public static function searchFor($strKeywords, $newsCategories, $topics = array(), $categories = array(), $timespan)
    {


        // Clean the keywords
        $strKeywords = \StringUtil::decodeEntities($strKeywords);
        $strKeywords = Utf8::strtolower($strKeywords);

        // Check keyword string
        if (!\strlen($strKeywords)) {
            throw new \Exception('Empty keyword string');
        }
        // Split keywords
        $arrChunks = array();
        preg_match_all('/"[^"]+"|[+-]?[^ ]+\*?/', $strKeywords, $arrChunks);


        $categoryContents = array();
        $searchResult = new SearchResult();

        if (($topics && count($topics)) || ($categories && count($categories))) {
            $categoryNews = NewsModel::findByCategories($newsCategories, $topics, $categories);
        } else {
            $categoryNews = NewsModel::findBy('published', 1);
        }

        $WeekAgo = strtotime("-1 week");
        $MonthAgo = strtotime("-1 month");
        $YearAgo = strtotime("-1 year");

        if ($categoryNews !== null && count($categoryNews)) {
            foreach ($categoryNews as $news) {
                $hasKeywords = $searchResult->contentKeywordFields($news, $arrChunks);
                if ($hasKeywords && count($hasKeywords)) {
                    if ($timespan === 'week' && $news->date >= $WeekAgo
                        || $timespan === 'month' && $news->date >= $MonthAgo
                        || $timespan === 'year' && $news->date >= $YearAgo
                        || $timespan === 'all') {
                        $categoryContents[$news->id] = $news;
                    }

                }
            }
        }

        return $categoryContents;

    }

}
