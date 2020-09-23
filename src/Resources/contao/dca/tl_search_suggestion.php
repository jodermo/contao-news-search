<?php

/*
 * This file is part of [petzka/contao-custom-news].
 *
 * (c) Moritz Petzka
 *
 * @license LGPL-3.0-or-later
 */

$GLOBALS['TL_DCA']['tl_search_suggestion'] = [
    // Config
    'config'      => [
        'dataContainer' => 'Table',
        'sql'           => [
            'keys' => [
                'id' => 'primary',
            ],
        ],
    ],
    // Buttons callback
    'edit'        => [
        'buttons_callback' => [['tl_search_suggestion', 'buttonsCallback']],
    ],

    // List
    'list'        => [
        'sorting'           => [
            'fields' => ['word DESC'],
        ],
        'label'             => [
            'fields' => ['word'],
            'format' => '%s',
        ],
        'global_operations' => [],
        'operations'        => [
            'edit'   => [
                'label' => &$GLOBALS['TL_LANG']['MSC']['edit'],
                'href'  => 'act=edit',
                'icon'  => 'edit.gif',
            ],
            'delete' => [
                'label'      => &$GLOBALS['TL_LANG']['MSC']['delete'],
                'href'       => 'act=delete',
                'icon'       => 'delete.gif',
                'attributes' => 'onclick="if (!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\')) return false; Backend.getScrollOffset();"',
            ],
            'show'   => [
                'label' => &$GLOBALS['TL_LANG']['MSC']['show'],
                'href'  => 'act=show',
                'icon'  => 'show.gif',
            ],
        ],
    ],
    // Palettes
    'palettes'    => [
        '__selector__' => [],
        'default'      => '{suggestion_legend},word'
    ],
    'subpalettes' => [],
    // Fields
    'fields'      => [

        'id'                     => [
            'label'  => ['ID'],
            'search' => true,
            'sql'    => "int(10) unsigned NOT NULL auto_increment",
        ],
        'tstamp'                 => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'word'                  => [
            'label'     => &$GLOBALS['TL_LANG']['tl_search_suggestion']['word'],
            'exclude'   => true,
            'search'    => true,
            'inputType' => 'text',
            'eval'      => ['mandatory' => true, 'maxlength' => 255, 'tl_class' => 'clr'],
            'sql'       => "varchar(255) NOT NULL default ''",
        ]
    ],
];

/**
 * Class tl_search_suggestion
 * Provide miscellaneous methods that are used by the data configuration array.
 * @package export_articles
 */
class tl_search_suggestion extends Backend
{

    public function __construct()
    {
        parent::__construct();
    }


    /**
     * buttons_callback
     * @param $arrButtons
     * @param DC_Table $dc
     * @return mixed
     */
    public function buttonsCallback($arrButtons, DC_Table $dc)
    {
        if (\Contao\Input::get('act') == 'edit')
        {
            $save = $arrButtons['save'];
            $saveNclose = $arrButtons['saveNclose'];

            unset($arrButtons);

            // Set correct order
            $arrButtons = [
                'save'        => $save,
                'saveNclose'  => $saveNclose,
            ];
        }

        return $arrButtons;
    }

}
