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

$GLOBALS['TL_DCA']['tl_module']['palettes']['search_news_categories'] .= ";{template_legend},news_template,customTpl;{category_legend},ignore_first_category_layer";
// print_r($GLOBALS['TL_DCA']['tl_module']['palettes']['search_news_categories']);


$GLOBALS['TL_DCA']['tl_module']['fields']['ignore_first_category_layer'] = array(
    'label' => &$GLOBALS['TL_LANG']['tl_module']['ignore_first_category_layer'],
    'exclude' => true,
    'inputType' => 'checkbox',
    'eval' => array(
        'mandatory' => false,
    ),
    'sql' => "char(1) NOT NULL default ''",
);

