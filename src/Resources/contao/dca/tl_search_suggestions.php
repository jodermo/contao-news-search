<?php

/*
 * This file is part of [petzka/contao-custom-news].
 *
 * (c) Moritz Petzka
 *
 * @license LGPL-3.0-or-later
 */

use Contao\System;

$GLOBALS['TL_DCA']['tl_search_suggestions'] = array
(
    // Config
    'config' => array
    (
        'dataContainer' => 'File',
        'closed' => true
    ),

    // Buttons callback
    'edit' => [
        'buttons_callback' => [['tl_search_suggestions', 'buttonsCallback']],
    ],

    // Palettes
    'palettes' => array
    (
        'default' => '{suggestion_legend},searchSuggestions'
    ),

    // Fields
    'fields' => array
    (
        'searchSuggestions' => array
        (
            'label' => &$GLOBALS['TL_LANG']['tl_search_suggestions']['searchSuggestions'],
            'inputType' => 'searchSuggestions'
        )
    )
);


/**
 * Class tl_search_suggestions
 * Provide miscellaneous methods that are used by the data configuration array.
 * @package news search
 */
class tl_search_suggestions extends Backend
{


    /**
     * buttons_callback
     * @param $arrButtons
     * @param $dc
     * @return mixed
     */
    public function buttonsCallback($arrButtons, $dc)
    {

        unset($arrButtons);
        $arrButtons = array();



        return $arrButtons;
    }

}