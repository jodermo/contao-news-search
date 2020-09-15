<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @license LGPL-3.0+
 */


/**
 * Table tl_module
 */

// Palettes

$GLOBALS['TL_DCA']['tl_module']['palettes']['search_news_categories'] = $GLOBALS['TL_DCA']['tl_module']['palettes']['default'];

$GLOBALS['TL_DCA']['tl_module']['palettes']['search_news_categories'] .= ";{headline_legend},headline;{template_legend},news_template,customTpl;{search_topic_legend},search_topics,search_topic_subcategory;{search_category_legend},search_categories,search_category_subcategory";


$GLOBALS['TL_DCA']['tl_module']['fields']['search_topics'] = array
(
    'label' => &$GLOBALS['TL_LANG']['tl_module']['search_topics'],
    'exclude'                 => true,
    'inputType'               => 'checkbox',
    'foreignKey'              => 'tl_news_category.title',
    'eval'                    => array('multiple'=>true),
    'sql'                     => "blob NULL"
);

$GLOBALS['TL_DCA']['tl_module']['fields']['search_topic_subcategory'] = array
(
    'label' => &$GLOBALS['TL_LANG']['tl_module']['search_topic_subcategory'],
    'exclude'                 => true,
    'filter'                  => true,
    'inputType'               => 'checkbox',
    'sql'                     => "char(1) NOT NULL default ''"
);

$GLOBALS['TL_DCA']['tl_module']['fields']['search_categories'] = array
(
    'label' => &$GLOBALS['TL_LANG']['tl_module']['search_categories'],
    'exclude'                 => true,
    'inputType'               => 'checkbox',
    'foreignKey'              => 'tl_news_category.title',
    'eval'                    => array('multiple'=>true),
    'sql'                     => "blob NULL"
);

$GLOBALS['TL_DCA']['tl_module']['fields']['search_category_subcategory'] = array
(
    'label' => &$GLOBALS['TL_LANG']['tl_module']['search_category_subcategory'],
    'exclude'                 => true,
    'filter'                  => true,
    'inputType'               => 'checkbox',
    'sql'                     => "char(1) NOT NULL default ''"
);
