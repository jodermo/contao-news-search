<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Petzka\ContaoNewsSearch\FrontendModule;

use Input;

use Petzka\ContaoNewsSearch\Model\ArticleModel;
use Petzka\ContaoNewsSearch\Model\NewsModel;
use Petzka\ContaoNewsSearch\Search\NewsSearch;
use Petzka\ContaoNewsSearch\Search\SearchResult;

use Codefog\NewsCategoriesBundle\Model\NewsCategoryModel;

use Contao\CoreBundle\Exception\PageNotFoundException;
use Contao\Search;
use Contao\File;
use Contao\FrontendTemplate;
use \Config;

use Patchwork\Utf8;
use \StringUtil;
use \Environment;
use \System;


class SearchNewsModule extends \Contao\ModuleSearch
{

    /**
     * @var string
     */
    protected $strTemplate = 'mod_search_news';

    /**
     * Display a wildcard in the back end
     *
     * @return string
     */
    public function generate()
    {


        //	 throw new \Exception(print_r($this->categories));

        return parent::generate();
    }


    /**
     * Generate the module
     */
    protected function compile()
    {

        /** @var \PageModel $objPage */
        global $objPage;

        // Mark the x and y parameter as used (see #4277)
        if (isset($_GET['x'])) {
            Input::get('x');
            Input::get('y');
        }

        // Trigger the search module from a custom form
        if (Input::post('FORM_SUBMIT') == 'tl_search') {
            if (!isset($_GET['keywords'])) {
                $_GET['keywords'] = Input::post('keywords');
            }

            if (!isset($_GET['query_type'])) {
                $_GET['keywords'] = Input::post('query_type');
            }

            if (!isset($_GET['per_page'])) {
                $_GET['per_page'] = Input::post('per_page');
            }

            if (!isset($_GET['time_span'])) {
                $_GET['time_span'] = Input::post('time_span');
            }

            if (!isset($_GET['article_topics'])) {
                $_GET['article_topics'] = Input::post('article_topics');
            }

            if (!isset($_GET['article_categories'])) {
                $_GET['article_categories'] = Input::post('article_categories');
            }

            if (!isset($_GET['extended'])) {
                $_GET['extended'] = Input::post('extended');
            }

            if (!isset($_GET['is_all_topics'])) {
                $_GET['is_all_topics'] = Input::post('is_all_topics');
            }

            if (!isset($_GET['is_all_categories'])) {
                $_GET['is_all_categories'] = Input::post('is_all_categories');
            }

        }

        $blnFuzzy = $this->fuzzy;
        $strQueryType = Input::get('query_type') ?: $this->queryType;

        $strKeywords = trim(Input::get('keywords'));

        $selectedTopics = Input::get('article_topics');
        $selectedCategories = Input::get('article_categories');
        $extended = Input::get('extended');

        $isAllTopics = Input::get('is_all_topics');
        $isAllCategories = Input::get('is_all_categories');

        $timeSpan = Input::get('time_span') || 'all';

        $this->Template->uniqueId = $this->id;
        $this->Template->queryType = $strQueryType;
        $this->Template->timeSpan = $timeSpan;
        $this->Template->keyword = StringUtil::specialchars($strKeywords);
        $this->Template->keywordLabel = $GLOBALS['TL_LANG']['MSC']['keywords'];
        $this->Template->optionsLabel = $GLOBALS['TL_LANG']['MSC']['options'];
        $this->Template->search = StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['searchLabel']);
        $this->Template->action = ampersand(Environment::get('indexFreeRequest'));
        $this->Template->advanced = ($this->searchType == 'advanced');
        $this->Template->extended = $extended;


        $this->Template->topics = NewsCategoryModel::findPublishedByPid('0');



        $this->Template->extensionBoxClass = 'closed';
        if ($extended === '1') {
            $this->Template->extensionBoxClass = 'open';
        }


        $this->Template->allTopicsSelected = true;
        $this->Template->allCategoriesSelected = true;


        $this->Template->allTopicsSelected = false;
        $this->Template->allCategoriesSelected = false;

        if ($selectedTopics && count($selectedTopics)) {
            foreach ($selectedTopics as $selectedTopic) {
                if ($selectedTopic === 'all') {
                    $this->Template->allTopicsSelected = true;
                }
            }
            if ($selectedCategories && count($selectedCategories)) {
                foreach ($selectedCategories as $selectedCategory) {
                    if ($selectedCategory === 'all') {
                        $this->Template->allCategoriesSelected = true;
                    }
                }
            }
        }
        $categories = array();

        foreach ($this->Template->topics as $topic) {
            $topicCategories = NewsCategoryModel::findPublishedByPid($topic->id);
            foreach ($topicCategories as $category) {
                $categories[] = $category;
            }

        }

        $this->Template->topicsAvailable = false;
        if (count($this->Template->topics)) {
            foreach ($this->Template->topics as $topic) {
                $topic->{'checked'} = false;


                if ($selectedTopics && count($selectedTopics)) {
                    foreach ($selectedTopics as $selectedTopic) {
                        if ($selectedTopic === $topic->id) {
                            $topic->{'checked'} = true;
                        }
                    }
                }


                if ($this->Template->allTopicsSelected) {
                    $topic->{'checked'} = true;
                } else if ($isAllTopics) {
                    $topic->{'checked'} = false;
                }


            }
            $this->Template->topicsAvailable = true;
        }
        $this->Template->categories = $categories;
        $this->Template->categoriesVisible = false;
        $activeCategories = array();
        if (count($this->Template->categories)) {
            foreach ($this->Template->categories as $category) {
                $category->{'checked'} = false;
                if ($selectedCategories && count($selectedCategories)) {
                    foreach ($selectedCategories as $selectedCategory) {
                        if ($selectedCategory === $category->id) {
                            $category->{'checked'} = true;
                        }
                    }
                }
                if ($this->Template->allCategoriesSelected) {
                    $category->{'checked'} = true;
                } else if ($isAllCategories) {
                    $category->{'checked'} = false;
                }
                if ($category->checked) {
                    $activeCategories[] = $category;
                }
            }
            $this->Template->categoriesVisible = true;
        }

        // Redirect page
        if ($this->jumpTo && ($objTarget = $this->objModel->getRelated('jumpTo')) instanceof PageModel) {
            /** @var PageModel $objTarget */
            $this->Template->action = $objTarget->getFrontendUrl();
        }

        $this->Template->pagination = '';
        $this->Template->results = '';

        $this->Template->categoryPagination = '';
        $this->Template->categoryResults = '';

        // Execute the search if there are keywords
        if ($strKeywords != '' && $strKeywords != '*') {
            // Search pages

            /** @var PageModel $objPage */
            global $objPage;
            $varRootId = $objPage->rootId;
            $arrPages = $this->Database->getChildRecords($objPage->rootId, 'tl_page');

            // HOOK: add custom logic (see #5223)
            if (isset($GLOBALS['TL_HOOKS']['customizeSearch']) && \is_array($GLOBALS['TL_HOOKS']['customizeSearch'])) {
                foreach ($GLOBALS['TL_HOOKS']['customizeSearch'] as $callback) {
                    $this->import($callback[0]);
                    $this->{$callback[0]}->{$callback[1]}($arrPages, $strKeywords, $strQueryType, $blnFuzzy, $this);
                }
            }

            // Return if there are no pages
            if (empty($arrPages) || !\is_array($arrPages)) {
                return;
            }

            $strCachePath = StringUtil::stripRootDir(System::getContainer()->getParameter('kernel.cache_dir'));

            $arrResult = null;
            $strChecksum = md5($strKeywords . $strQueryType . $varRootId . $blnFuzzy);
            $query_starttime = microtime(true);
            $strCacheFile = $strCachePath . '/contao/search/' . $strChecksum . '.json';


            if (!count($activeCategories)) {
                // old search method ...
                // Load the cached result
                if (file_exists(System::getContainer()->getParameter('kernel.project_dir') . '/' . $strCacheFile)) {
                    $objFile = new File($strCacheFile);

                    if ($objFile->mtime > time() - 1800) {
                        $arrResult = json_decode($objFile->getContent(), true);
                    } else {
                        $objFile->delete();
                    }
                }
                // Cache the result
                if ($arrResult === null) {
                    try {
                        $objSearch = Search::searchFor($strKeywords, ($strQueryType == 'or'), $arrPages, 0, 0, $blnFuzzy);
                        $arrResult = $objSearch->fetchAllAssoc();
                    } catch (\Exception $e) {
                        $this->log('Website search failed: ' . $e->getMessage(), __METHOD__, TL_ERROR);
                        $message = $e->getMessage();

                        $arrResult = array();
                    }

                    File::putContent($strCacheFile, json_encode($arrResult));
                }
            } else {

                $arrResult = NewsSearch::searchFor($strKeywords, $arrPages, $activeCategories);

            }


            $query_endtime = microtime(true);


            $count = \count($arrResult);

            $this->Template->count = $count;
            $this->Template->page = null;
            $this->Template->keywords = $strKeywords;


            // No results
            if ($count < 1) {
                $this->Template->header = sprintf($GLOBALS['TL_LANG']['MSC']['sEmpty'], $strKeywords);
                $this->Template->duration = substr($query_endtime - $query_starttime, 0, 6) . ' ' . $GLOBALS['TL_LANG']['MSC']['seconds'];

                return;
            }

            $from = 1;
            $to = $count;

            // Pagination
            if ($this->perPage > 0) {
                $id = 'page_s' . $this->id;
                $page = Input::get($id) ?? 1;
                $per_page = Input::get('per_page') ?: $this->perPage;

                // Do not index or cache the page if the page number is outside the range
                if ($page < 1 || $page > max(ceil($count / $per_page), 1)) {
                    throw new PageNotFoundException('Page not found: ' . Environment::get('uri'));
                }

                $from = (($page - 1) * $per_page) + 1;
                $to = (($from + $per_page) > $count) ? $count : ($from + $per_page - 1);

                // Pagination menu
                if ($to < $count || $from > 1) {
                    $objPagination = new Pagination($count, $per_page, Config::get('maxPaginationLinks'), $id);
                    $this->Template->pagination = $objPagination->generate("\n  ");
                    $this->Template->categoryPagination = $objPagination->generate("\n  ");

                }

                $this->Template->page = $page;
            }


            // Get the results
            if (!count($activeCategories)) {
                for ($i = ($from - 1); $i < $to && $i < $count; $i++) {
                    $objTemplate = new FrontendTemplate($this->searchTpl);
                    $objTemplate->setData($arrResult[$i]);
                    $objTemplate->href = $arrResult[$i]['url'];
                    $objTemplate->link = $arrResult[$i]['title'];
                    $objTemplate->url = StringUtil::specialchars(urldecode($arrResult[$i]['url']), true, true);
                    $objTemplate->title = StringUtil::specialchars(StringUtil::stripInsertTags($arrResult[$i]['title']));
                    $objTemplate->class = (($i == ($from - 1)) ? 'first ' : '') . (($i == ($to - 1) || $i == ($count - 1)) ? 'last ' : '') . (($i % 2 == 0) ? 'even' : 'odd');
                    $objTemplate->relevance = sprintf($GLOBALS['TL_LANG']['MSC']['relevance'], number_format($arrResult[$i]['relevance'] / $arrResult[0]['relevance'] * 100, 2) . '%');

                    $arrContext = array();
                    $strText = StringUtil::stripInsertTags($arrResult[$i]['text']);
                    $arrMatches = StringUtil::trimsplit(',', $arrResult[$i]['matches']);

                    // Get the context
                    foreach ($arrMatches as $strWord) {
                        $arrChunks = array();
                        preg_match_all('/(^|\b.{0,' . $this->contextLength . '}(?:\PL|\p{Hiragana}|\p{Katakana}|\p{Han}|\p{Myanmar}|\p{Khmer}|\p{Lao}|\p{Thai}|\p{Tibetan}))' . preg_quote($strWord, '/') . '((?:\PL|\p{Hiragana}|\p{Katakana}|\p{Han}|\p{Myanmar}|\p{Khmer}|\p{Lao}|\p{Thai}|\p{Tibetan}).{0,' . $this->contextLength . '}\b|$)/ui', $strText, $arrChunks);

                        foreach ($arrChunks[0] as $strContext) {
                            $arrContext[] = ' ' . $strContext . ' ';
                        }
                    }

                    // Shorten the context and highlight all keywords
                    if (!empty($arrContext)) {
                        $objTemplate->context = trim(StringUtil::substrHtml(implode('…', $arrContext), $this->totalLength));
                        $objTemplate->context = preg_replace('/(?<=^|\PL|\p{Hiragana}|\p{Katakana}|\p{Han}|\p{Myanmar}|\p{Khmer}|\p{Lao}|\p{Thai}|\p{Tibetan})(' . implode('|', array_map('preg_quote', $arrMatches)) . ')(?=\PL|\p{Hiragana}|\p{Katakana}|\p{Han}|\p{Myanmar}|\p{Khmer}|\p{Lao}|\p{Thai}|\p{Tibetan}|$)/ui', '<mark class="highlight">$1</mark>', $objTemplate->context);

                        $objTemplate->hasContext = true;
                    }


                    $this->Template->categoryResults .= $objTemplate->parse();
                    $this->Template->results .= $objTemplate->parse();
                }
            } else {
                $this->Template->categoryResults = SearchResult::parse($arrResult);
                $this->Template->results = SearchResult::parse($arrResult);
            }


            //  throw new \Exception(print_r($this->Template->categoryResults));

            $this->Template->header = vsprintf($GLOBALS['TL_LANG']['MSC']['sResults'], array($from, $to, $count, $strKeywords));
            $this->Template->duration = substr($query_endtime - $query_starttime, 0, 6) . ' ' . $GLOBALS['TL_LANG']['MSC']['seconds'];
        }
        return parent::compile();
    }
}
