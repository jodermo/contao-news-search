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
    public static function findByCategories($categories)
    {
        $result = array();
        foreach ($categories as $category) {
            $newsDb = static::findBy('published', 1);
            if ($newsDb !== null && count($newsDb)) {
                foreach ($newsDb as $news) {
                    $articleCategoryIds = \StringUtil::deserialize($news->article_categories, true);
                    if($articleCategoryIds !== null && count($articleCategoryIds)) {
                        foreach ($articleCategoryIds as $id) {
                            if ($category->id === $id) {
                                $result[$news->id] = $news;
                            }
                        }
                    }

                }
            }
        }
        return $result;
    }

}
