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
    public static function searchFor($strKeywords, $arrPid = array(), $categories = array())
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


        if ($arrPid && count($arrPid)) {
            $allArticles = array();
            foreach ($arrPid as $pid) {
                $pageArticles = ArticleModel::findBy('pid', $pid);
                if ($pageArticles !== null) {
                    foreach ($pageArticles as $article) {
                        $allArticles[] = $article;
                    }
                }
            }
            $categoryArticles = ArticleModel::filterByCategrory($allArticles, $categories);
        } else {
            $categoryArticles = ArticleModel::findByCategories($categories);
        }


        $categoryContents = array();
        $searchResult = new SearchResult();

        if ($categoryArticles !== null && count($categoryArticles)) {
            foreach ($categoryArticles as $article) {
                $articleContents = ContentModel::findPublishedByPidAndTable($article->id, 'tl_article');
                if ($articleContents !== null && count($articleContents)) {
                    foreach ($articleContents as $content) {
                        $hasKeywords = $searchResult->contentKeywordFields($content, $arrChunks);
                        if ($hasKeywords && count($hasKeywords)) {
                            $categoryContents[$content->id] = $content;
                        }
                    }
                }
            }
        }

        $categoryNews = NewsModel::findByCategories($categories);

        if ($categoryNews !== null && count($categoryNews)) {
            foreach ($categoryNews as $news) {
                $newsContents = ContentModel::findPublishedByPidAndTable($news->id, 'tl_news');
                if ($newsContents !== null && count($newsContents)) {
                    foreach ($newsContents as $content) {
                        $hasKeywords = $searchResult->contentKeywordFields($content, $arrChunks);
                        if ($hasKeywords && count($hasKeywords)) {
                            $categoryContents[$content->id] = $content;
                        }
                    }
                }
            }
        }
        return $categoryContents;

    }

}
