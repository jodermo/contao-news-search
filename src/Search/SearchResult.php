<?php

/*
 * This file is part of [petzka/contao-indisign-bundle].
 *
 * (c) Moritz Petzka <info@petzka.com>
 *
 * @license LGPL-3.0-or-later
 */

namespace Petzka\ContaoNewsSearch\Search;

use Contao\FrontendTemplate;
use Contao\ArticleModel;
use Contao\PageModel;
use Contao\ContentModel;

class SearchResult
{
    protected $arrData = array();

    /**
     * check content for keywords
     * @return array
     */
    public function newsKeywordFields($news, array $arrKeywords)
    {
        $this->keywords = $arrKeywords;

        $searchInFields = [
            'caption',
            'teaser',
            'subheadline',
            'description',
            'author',
            'headline',
        ];


        $results = array();


        if ($arrKeywords && count($arrKeywords)) {
            foreach ($arrKeywords as $keyword) {
                if ($keyword[0] && $keyword[0] !== null && $keyword[0] !== '') {
                    $fieldHasKeyword = array();
                    foreach ($searchInFields as $field) {

                        $fieldHasKeyword = $this->fieldHasKeyword($news->{$field}, $keyword, $fieldHasKeyword);

                        if ($news->{$field} && $fieldHasKeyword && count($fieldHasKeyword)) {

                            foreach ($fieldHasKeyword as $keyword) {
                                // highlight words
                                $news->{$field} = preg_replace('/\p{L}*?' . preg_quote($keyword[0]) . '\p{L}*/ui', '<span class="highlight">$0</span>', $news->{$field});
                            }
                            if (!$results[$field]) {
                                $results[$field] = array();
                            }
                            $word = $keyword[0];
                            $results[$field][$word] = $news->{$field};
                        }
                    }
                }
            }
        }

        
        return $results;
    }

    /**
     * check content for keywords
     * @return array
     */
    public function contentKeywordFields($content, array $arrKeywords)
    {
        $this->keywords = $arrKeywords;


        $searchInFields = [
            'headline',
            'text',
            'html',
            'caption'
        ];

        $results = array();
        if ($arrKeywords && count($arrKeywords)) {
            foreach ($arrKeywords as $keyword) {
                if ($keyword[0] && $keyword[0] !== null && $keyword[0] !== '') {
                    $fieldHasKeyword = array();
                    foreach ($searchInFields as $field) {

                        $fieldHasKeyword = $this->fieldHasKeyword($content->{$field}, $keyword, $fieldHasKeyword);

                        if ($content->{$field} && $fieldHasKeyword && count($fieldHasKeyword)) {

                            foreach ($fieldHasKeyword as $keyword) {
                                // highlight words
                                $content->{$field} = preg_replace('/\p{L}*?' . preg_quote($keyword[0]) . '\p{L}*/ui', '<span class="highlight">$0</span>', $content->{$field});
                            }
                            if (!$results[$field]) {
                                $results[$field] = array();
                            }
                            $word = $keyword[0];
                            $results[$field][$word] = $content->{$field};
                        }
                    }
                }
            }
        }
        return $results;
    }

    /**
     * check content field for keyword
     * @return array
     */
    public function fieldHasKeyword($value, $keyword, $hasKeywords)
    {
        $value = strtolower(\StringUtil::specialchars(strip_tags(\StringUtil::stripInsertTags($value))));
        if ($value !== null && $keyword !== null && $keyword[0] !== null && is_string($value) && $value !== '') {
            if (strpos($value, $keyword[0]) !== false) {
                $hasKeywords[] = $keyword;;
            }
        }
        return $hasKeywords;
    }

    /**
     * Parse result data for template
     * @return array
     */
    public function parse(array $arrData)
    {


        $results = '';


        if ($arrData && count($arrData)) {

            foreach ($arrData as $result) {

                $objTemplate = new \FrontendTemplate('search_news_categories');


                $objTemplate->href = '';

                if ($result->pid && $result->pid !== null) {
                    $resultArticle = ArticleModel::findBy('id', $result->pid);
                    if ($resultArticle !== null) {
                        $resultPage = PageModel::findBy('id', $resultArticle->pid);
                        if ($resultPage !== null) {
                            // print_r($resultPage);
                            $objTemplate->href = $resultPage->alias;
                            $objTemplate->link = $resultPage->title;

                        }

                    }

                }


                if ($result->headline && $result->headline !== null) {
                    $title = \StringUtil::deserialize($result->headline);
                    $objTemplate->title = $title['value'];
                }
                if ($result->text && $result->text !== null) {
                    $objTemplate->context = $result->text;
                }


                $results .= $objTemplate->parse();
            }

        }


        return $results;
    }


}
