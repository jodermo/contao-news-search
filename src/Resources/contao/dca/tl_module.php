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

$GLOBALS['TL_DCA']['tl_module']['palettes']['search_news_categories'] .= ";{template_legend},news_template,customTpl";
// print_r($GLOBALS['TL_DCA']['tl_module']['palettes']['search_news_categories']);
