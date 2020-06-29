<?php
/*
 * This file is part of [petzka/demo-bundle].
 *
 * (c) Moritz Petzka
 *
 * @license LGPL-3.0-or-later
 */

namespace Petzka\ContaoNewsSearch\Model;

class ArticleModel extends \ArticleModel
{
	/**
	 * @var string Table name
	 */
	protected static $strTable = 'tl_article';

	/**
	 * Find all article categories by topics
	 */
	public static function findByCategories($categories)
	{
        $result = array();
        foreach ($categories as $category) {
            $articleDb = static::findAll();
            if ($articleDb !== null && count($articleDb)) {
                foreach ($articleDb as $article) {
                    $articleCategoryIds = \StringUtil::deserialize($article->article_categories, true);
                    if($articleCategoryIds !== null && count($articleCategoryIds)) {
                        foreach ($articleCategoryIds as $id) {
                            if ($category->id === $id) {
                                $result[$article->id] = $article;
                            }
                        }
                    }

                }
            }
        }
        return $result;
	}

    /**
     * Filter given article categories by topics
     */
    public static function filterByCategrory($articles, $categories)
    {
        $result = array();
        if($articles !== null && count($articles))
        {
            foreach($articles as $article)
            {
                $articleCategoryIds = \StringUtil::deserialize($article->article_categories, true);
                if($articleCategoryIds !== null && count($articleCategoryIds))
                {
                    foreach($articleCategoryIds as $id)
                    {
                        foreach($categories as $category)
                        {
                            if($category->id === $id)
                            {
                                $result[$article->id] = $article;
                            }
                        }
                    }

                }

            }
        }
        return $result;
    }
}
