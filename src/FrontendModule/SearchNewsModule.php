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
use Contao\FilesModel;
use Contao\Date;
use Contao\News;
use Contao\FrontendTemplate;
use Contao\ContentModel;
use Contao\ModuleRegistration;
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

        $keywordsArray = explode(' ', $strKeywords);


        $selectedTopics = Input::get('article_topics');
        $selectedCategories = Input::get('article_categories');
        $extended = Input::get('extended');

        $isAllTopics = Input::get('is_all_topics');
        $isAllCategories = Input::get('is_all_categories');

        $timeSpan = Input::get('time_span');


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


        $newsCategoriesDb = $this->Database->prepare("SELECT * FROM tl_news_categories")->execute();
        $newsCategories = $newsCategoriesDb->fetchAllAssoc();


        $searchIndexDb = $this->Database->prepare("SELECT * FROM tl_search_index ORDER BY word")->execute();
        $searchIndex = $searchIndexDb->fetchAllAssoc();

        $autocompleteArray = '[';
        $autocompleteCount = 0;
        $keywords = array();


        foreach ($searchIndex as $index) {
            if ($index['word'] && is_string($index['word']) && !is_numeric($index['word'])) {
                $wordExist = false;
                foreach ($keywords as $word) {
                    if ($word === $index['word']) {
                        $wordExist = true;
                    }
                }
                if (!$wordExist) {
                    $keywords[] = $index['word'];
                    $word = str_replace("'", " ", $index['word']);
                    $autocompleteArray .= "'" . $word . "',";
                    $autocompleteCount++;
                }
            }
        }
        if ($autocompleteCount) {
            $autocompleteArray = substr($autocompleteArray, 0, -1);
        }
        $autocompleteArray .= ']';

        // print_r($autocompleteArray);

        $this->Template->autocomplete = $autocompleteArray;
        $allTopics = array();
        if ($this->search_topics) {
            $ids = StringUtil::deserialize($this->search_topics);
            if (!$this->search_topic_subcategory) {
                if ($ids && count($ids)) {
                    $topics = NewsCategoryModel::findPublishedByIds($ids);
                    if ($topics && count($topics)) {
                        foreach ($topics as $topic) {
                            $allTopics[] = $topic;
                        }
                    }
                }
            } else {
                if ($ids && count($ids)) {
                    foreach ($ids as $id) {
                        $topics = NewsCategoryModel::findPublishedByPid($id);
                        if ($topics && count($topics)) {
                            foreach ($topics as $topic) {
                                $allTopics[] = $topic;
                            }
                        }
                    }
                }
            }
        }


        $this->Template->topics = $allTopics;

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

        $this->Template->topicsAvailable = false;

        $activeTopics = array();
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
                if ($topic->{'checked'}) {
                    $activeTopics[] = $topic;
                }
            }

            $this->Template->topicsAvailable = true;
        }

        $categories = array();

        if ($this->search_categories) {
            $ids = StringUtil::deserialize($this->search_categories);
            if (!$this->search_category_subcategory) {
                if ($ids && count($ids)) {
                    $categoryResults = NewsCategoryModel::findPublishedByIds($ids);
                    if ($categoryResults && count($categoryResults)) {
                        foreach ($categoryResults as $result) {
                            $categories[] = $result;
                        }
                    }
                }
            } else {
                if ($ids && count($ids)) {
                    foreach ($ids as $id) {
                        $categoryResults = NewsCategoryModel::findPublishedByPid($id);
                        if ($categoryResults && count($categoryResults)) {
                            foreach ($categoryResults as $result) {
                                $categories[] = $result;
                            }
                        }
                    }
                }
            }
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


        $arrResult = null;

        $query_starttime = microtime(true);
        $count = 0;


        if ($this->search_on_start && (count($activeTopics) || count($activeCategories)) || (($strKeywords != '' && $strKeywords != '*'))) {

            $arrResult = NewsSearch::searchFor($strKeywords, $newsCategories, $activeTopics, $activeCategories, $timeSpan);
            $count = count($arrResult);
        }

        $query_endtime = microtime(true);


        $this->Template->count = $count;
        $this->Template->keywords = $strKeywords;

        $this->Template->suggestions = array();

        $suggestions = array();

        if ($strKeywords != '' && $strKeywords != '*') {
            foreach ($keywordsArray as $keyword) {
                $closestWord = $this->findClosestWord($keyword, $searchIndex);
                if ($closestWord !== $keyword) {
                    $suggestions[] = $closestWord;
                    $this->Template->closest = $closestWord;
                }
            }

            // No results
            if ($count < 1) {
                $this->Template->suggestions = $suggestions;
                $this->Template->header = sprintf($GLOBALS['TL_LANG']['MSC']['sEmpty'], $strKeywords);
                $this->Template->duration = substr($query_endtime - $query_starttime, 0, 6) . ' ' . $GLOBALS['TL_LANG']['MSC']['seconds'];
                return;
            }

        }

        if ($count > 0) {
            $newsArticles = $this->parseArticles($arrResult);
            foreach ($newsArticles as $news) {
                $this->Template->categoryResults .= $news;
            }


            $this->Template->header = $count . " Ergebnisse";
            $this->Template->duration = substr($query_endtime - $query_starttime, 0, 6) . ' ' . $GLOBALS['TL_LANG']['MSC']['seconds'];
        }

        return parent::compile();
    }


    /**
     * Return similar words
     *
     * @param string $word
     *
     * @return string
     */
    protected function findClosestWord($input, $searchIndex)
    {
        $shortest = 100;
        $closest = '';
        foreach ($searchIndex as $index) {
            $lev = levenshtein($input, $index['word']);
            if ($lev <= $shortest && $index['word'] !== $input) {
                $closest = $index['word'];
                $shortest = $lev;
            };
        };
        return $closest;
    }

    /**
     * Parse an item and return it as string
     *
     * @param NewsModel $objArticle
     * @param boolean $blnAddArchive
     * @param string $strClass
     * @param integer $intCount
     *
     * @return string
     */
    protected function parseArticle($objArticle, $blnAddArchive = false, $strClass = '', $intCount = 0)
    {
        $objTemplate = new FrontendTemplate($this->news_template ?: 'news_latest');
        $objTemplate->setData($objArticle->row());

        if ($objArticle->cssClass != '') {
            $strClass = ' ' . $objArticle->cssClass . $strClass;
        }

        if ($objArticle->featured) {
            $strClass = ' featured' . $strClass;
        }

        $objTemplate->class = $strClass;
        $objTemplate->newsHeadline = $objArticle->headline;
        $objTemplate->subHeadline = $objArticle->subheadline;
        $objTemplate->hasSubHeadline = $objArticle->subheadline ? true : false;
        $objTemplate->linkHeadline = $this->generateLink($objArticle->headline, $objArticle, $blnAddArchive);
        $objTemplate->more = $this->generateLink($GLOBALS['TL_LANG']['MSC']['more'], $objArticle, $blnAddArchive, true);
        $objTemplate->link = News::generateNewsUrl($objArticle, $blnAddArchive);
        $objTemplate->archive = $objArticle->getRelated('pid');
        $objTemplate->count = $intCount; // see #5708
        $objTemplate->text = '';
        $objTemplate->hasText = false;
        $objTemplate->hasTeaser = false;

        // Clean the RTE output
        if ($objArticle->teaser != '') {
            $objTemplate->hasTeaser = true;
            $objTemplate->teaser = StringUtil::toHtml5($objArticle->teaser);
            $objTemplate->teaser = StringUtil::encodeEmail($objTemplate->teaser);
        }

        // Display the "read more" button for external/article links
        if ($objArticle->source != 'default') {
            $objTemplate->text = true;
            $objTemplate->hasText = true;
        } // Compile the news text
        else {
            $id = $objArticle->id;

            $objTemplate->text = function () use ($id) {
                $strText = '';
                $objElement = ContentModel::findPublishedByPidAndTable($id, 'tl_news');

                if ($objElement !== null) {
                    while ($objElement->next()) {
                        $strText .= $this->getContentElement($objElement->current());
                    }
                }

                return $strText;
            };

            $objTemplate->hasText = static function () use ($objArticle) {
                return ContentModel::countPublishedByPidAndTable($objArticle->id, 'tl_news') > 0;
            };
        }

        $arrMeta = $this->getMetaFields($objArticle);

        // Add the meta information
        $objTemplate->date = $arrMeta['date'];
        $objTemplate->hasMetaFields = !empty($arrMeta);
        $objTemplate->numberOfComments = $arrMeta['ccount'];
        $objTemplate->commentCount = $arrMeta['comments'];
        $objTemplate->timestamp = $objArticle->date;
        $objTemplate->author = $arrMeta['author'];
        $objTemplate->datetime = date('Y-m-d\TH:i:sP', $objArticle->date);

        $objTemplate->addImage = false;

        // Add an image
        if ($objArticle->addImage && $objArticle->singleSRC != '') {
            $objModel = FilesModel::findByUuid($objArticle->singleSRC);

            if ($objModel !== null && is_file(System::getContainer()->getParameter('kernel.project_dir') . '/' . $objModel->path)) {
                // Do not override the field now that we have a model registry (see #6303)
                $arrArticle = $objArticle->row();

                // Override the default image size
                if ($this->imgSize != '') {
                    $size = StringUtil::deserialize($this->imgSize);

                    if ($size[0] > 0 || $size[1] > 0 || is_numeric($size[2]) || ($size[2][0] ?? null) === '_') {
                        $arrArticle['size'] = $this->imgSize;
                    }
                }

                $arrArticle['singleSRC'] = $objModel->path;
                $this->addImageToTemplate($objTemplate, $arrArticle, null, null, $objModel);

                // Link to the news article if no image link has been defined (see #30)
                if (!$objTemplate->fullsize && !$objTemplate->imageUrl) {
                    // Unset the image title attribute
                    $picture = $objTemplate->picture;
                    unset($picture['title']);
                    $objTemplate->picture = $picture;

                    // Link to the news article
                    $objTemplate->href = $objTemplate->link;
                    $objTemplate->linkTitle = StringUtil::specialchars(sprintf($GLOBALS['TL_LANG']['MSC']['readMore'], $objArticle->headline), true);

                    // If the external link is opened in a new window, open the image link in a new window, too (see #210)
                    if ($objTemplate->source == 'external' && $objTemplate->target && strpos($objTemplate->attributes, 'target="_blank"') === false) {
                        $objTemplate->attributes .= ' target="_blank"';
                    }
                }
            }
        }

        $objTemplate->enclosure = array();

        // Add enclosures
        if ($objArticle->addEnclosure) {
            $this->addEnclosuresToTemplate($objTemplate, $objArticle->row());
        }

        // HOOK: add custom logic
        if (isset($GLOBALS['TL_HOOKS']['parseArticles']) && \is_array($GLOBALS['TL_HOOKS']['parseArticles'])) {
            foreach ($GLOBALS['TL_HOOKS']['parseArticles'] as $callback) {
                $this->import($callback[0]);
                $this->{$callback[0]}->{$callback[1]}($objTemplate, $objArticle->row(), $this);
            }
        }

        // Tag the response
        if (System::getContainer()->has('fos_http_cache.http.symfony_response_tagger')) {
            /** @var ResponseTagger $responseTagger */
            $responseTagger = System::getContainer()->get('fos_http_cache.http.symfony_response_tagger');
            $responseTagger->addTags(array('contao.db.tl_news.' . $objArticle->id));
            $responseTagger->addTags(array('contao.db.tl_news_archive.' . $objArticle->pid));
        }

        return $objTemplate->parse();
    }

    /**
     * Parse one or more items and return them as array
     *
     * @param Collection $objArticles
     * @param boolean $blnAddArchive
     *
     * @return array
     */
    protected function parseArticles($objArticles, $blnAddArchive = false)
    {
        $limit = count($objArticles);

        if ($limit < 1) {
            return array();
        }

        $count = 0;
        $arrArticles = array();

        foreach ($objArticles as $objArticle) {
            $arrArticles[] = $this->parseArticle($objArticle, $blnAddArchive, ((++$count == 1) ? ' first' : '') . (($count == $limit) ? ' last' : '') . ((($count % 2) == 0) ? ' odd' : ' even'), $count);
        }

        return $arrArticles;
    }

    /**
     * Return the meta fields of a news article as array
     *
     * @param NewsModel $objArticle
     *
     * @return array
     */
    protected function getMetaFields($objArticle)
    {
        $meta = StringUtil::deserialize($this->news_metaFields);

        if (!\is_array($meta)) {
            return array();
        }

        /** @var PageModel $objPage */
        global $objPage;

        $return = array();

        foreach ($meta as $field) {
            switch ($field) {
                case 'date':
                    $return['date'] = Date::parse($objPage->datimFormat, $objArticle->date);
                    break;

                case 'author':
                    /** @var UserModel $objAuthor */
                    if (($objAuthor = $objArticle->getRelated('author')) instanceof UserModel) {
                        $return['author'] = $GLOBALS['TL_LANG']['MSC']['by'] . ' <span itemprop="author">' . $objAuthor->name . '</span>';
                    }
                    break;

                case 'comments':
                    if ($objArticle->noComments || $objArticle->source != 'default') {
                        break;
                    }

                    $bundles = System::getContainer()->getParameter('kernel.bundles');

                    if (!isset($bundles['ContaoCommentsBundle'])) {
                        break;
                    }

                    $intTotal = CommentsModel::countPublishedBySourceAndParent('tl_news', $objArticle->id);
                    $return['ccount'] = $intTotal;
                    $return['comments'] = sprintf($GLOBALS['TL_LANG']['MSC']['commentCount'], $intTotal);
                    break;
            }
        }

        return $return;
    }

    /**
     * Generate a URL and return it as string
     *
     * @param NewsModel $objItem
     * @param boolean $blnAddArchive
     *
     * @return string
     *
     * @deprecated Deprecated since Contao 4.1, to be removed in Contao 5.
     *             Use News::generateNewsUrl() instead.
     */
    protected function generateNewsUrl($objItem, $blnAddArchive = false)
    {
        @trigger_error('Using ModuleNews::generateNewsUrl() has been deprecated and will no longer work in Contao 5.0. Use News::generateNewsUrl() instead.', E_USER_DEPRECATED);

        return News::generateNewsUrl($objItem, $blnAddArchive);
    }

    /**
     * Generate a link and return it as string
     *
     * @param string $strLink
     * @param NewsModel $objArticle
     * @param boolean $blnAddArchive
     * @param boolean $blnIsReadMore
     *
     * @return string
     */
    protected function generateLink($strLink, $objArticle, $blnAddArchive = false, $blnIsReadMore = false)
    {
        // Internal link
        if ($objArticle->source != 'external') {
            return sprintf(
                '<a href="%s" title="%s" itemprop="url"><span itemprop="headline">%s</span>%s</a>',
                News::generateNewsUrl($objArticle, $blnAddArchive),
                StringUtil::specialchars(sprintf($GLOBALS['TL_LANG']['MSC']['readMore'], $objArticle->headline), true),
                $strLink,
                ($blnIsReadMore ? '<span class="invisible"> ' . $objArticle->headline . '</span>' : '')
            );
        }

        // Encode e-mail addresses
        if (0 === strncmp($objArticle->url, 'mailto:', 7)) {
            $strArticleUrl = StringUtil::encodeEmail($objArticle->url);
        } // Ampersand URIs
        else {
            $strArticleUrl = ampersand($objArticle->url);
        }

        // External link
        return sprintf(
            '<a href="%s" title="%s"%s itemprop="url"><span itemprop="headline">%s</span></a>',
            $strArticleUrl,
            StringUtil::specialchars(sprintf($GLOBALS['TL_LANG']['MSC']['open'], $strArticleUrl)),
            ($objArticle->target ? ' target="_blank" rel="noreferrer noopener"' : ''),
            $strLink
        );
    }
}
