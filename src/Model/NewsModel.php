<?php
/*
 * This file is part of [petzka/demo-bundle].
 *
 * (c) Moritz Petzka
 *
 * @license LGPL-3.0-or-later
 */

namespace Petzka\ContaoNewsSearch\Model;

class NewsModel extends \NewsModel
{
    /**
     * @var string Table name
     */
    protected static $strTable = 'tl_news';

    /**
     * Find all article categories by topics
     */
    public static function findByCategories($newsCategories, $topics, $categories)
    {
        /** @var Database $databaseAdapter */

        $topicResults = array();


        // get news, by topic as new category

        if ($topics && count($topics)) {
            foreach ($topics as $category) {
                foreach ($newsCategories as $newsCategory) {
                    if ($newsCategory['category_id'] === $category->id) {
                        $news = static::findById($newsCategory['news_id']);
                        $topicResults[$news->id] = $news;
                    }
                }
            }
        } else {
            $topicResults = static::findBy('published', 1);
        }


        // filter results, by categories

        if ($categories && count($categories)) {

            $categoryResults = array();
            foreach ($categories as $category) {
                foreach ($newsCategories as $newsCategory) {
                    if ($newsCategory['category_id'] === $category->id) {
                        foreach ($topicResults as $news) {
                            if ($news->id === $newsCategory['news_id']) {
                                $categoryResults[] = $news;
                            }
                        }
                    }
                }
            }
            return $categoryResults;
        } else {
            return $topicResults;
        }
    }

}
