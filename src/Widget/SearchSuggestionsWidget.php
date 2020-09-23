<?php

/*
 * This file is part of [petzka/contao-custom-news].
 *
 * (c) Moritz Petzka
 *
 * @license LGPL-3.0-or-later
 */

namespace Petzka\ContaoNewsSearch\Widget;


use Contao\ContentModel;
use Contao\Widget;
use Contao\Database;
use Contao\NewsModel;
use Contao\StringUtil;

use DOMDocument;
use Contao\Controller;
use Petzka\ContaoNewsSearch\Model\SearchSuggestionModel;

class SearchSuggestionsWidget extends Widget
{

    /**
     * Submit user input
     * @var boolean
     */
    protected $blnSubmitInput = false;

    /**
     * Template
     * @var string
     */
    protected $strTemplate = 'be_widget';


    /**
     * Generate the widget and return it as string
     *
     * @return string
     */
    public function generate()
    {
        $result = '';

        $removeId = null;

        if (isset($_GET['search'])) {
            $search = true;
        }
        if (isset($_GET['remove'])) {
            $result .= $this->removeSuggestion($_GET['remove']);
        }

        $result .= $this->searchOptions();

        if (isset($_GET['clear'])) {
            $this->removeAll();
            $result .= '<h2>Alle Einträge wurden erfolgreich gelöscht</h2>';
        } else {
            $words = array();
            $suggestions = SearchSuggestionModel::findAll(array('order' => 'word'));
            if ($suggestions && count($suggestions)) {
                foreach ($suggestions as $suggestion) {
                    $words[] = $suggestion->word;
                }
            }

            if ($search) {
                $words = $this->findNewWords($words, 8, 16);
                $suggestions = SearchSuggestionModel::findAll(array('order' => 'word'));
            }


            $result .= '<h2>' . count($words) . ' Einträge:</h2>';
            $result .= $this->existingSuggestions($suggestions);

        }


        return $result;
    }

    /**
     * Existing suggestions
     *
     * @return string
     */
    public function existingSuggestions($suggestions)
    {

        $protocol = isset($_SERVER["HTTPS"]) ? 'https' : 'http';
        $pageUrl = $protocol . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        $pageUrl = $this->removeQueryStringParameter($pageUrl, 'search');
        $pageUrl = $this->removeQueryStringParameter($pageUrl, 'clear');
        $pageUrl = $this->removeQueryStringParameter($pageUrl, 'remove');

        $removeUrl = $pageUrl . '&remove=';


        $result = '<div class="tl_listing"><table class="tl_listing"><tbody>';
        if ($suggestions && count($suggestions)) {
            $i = 0;
            foreach ($suggestions as $suggestion) {
                $i++;
                $result .= '<tr class="' . ($i % 2 ? 'odd' : 'even') . '"><td>' . $suggestion->word . '</td>';
                $result .= '<td><a href="' . $removeUrl . $suggestion->id . '" title="" class="delete"><img src="system/themes/flexible/icons/delete.svg" alt="Artikel ID ' . $suggestion->id . ' löschen" width="16" height="16"></a></td>';
                $result .= '</tr>';
            }
        } else {
            $result .= '<li>Keine Wörter vorhanden</li>';
        }
        $result .= '</tbody></table></div>';

        return $result;

    }

    /**
     * searchOptions
     *
     * @return string
     */
    public function searchOptions()
    {
        $result = '';

        $protocol = isset($_SERVER["HTTPS"]) ? 'https' : 'http';
        $pageUrl = $protocol . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        $pageUrl = $this->removeQueryStringParameter($pageUrl, 'search');
        $pageUrl = $this->removeQueryStringParameter($pageUrl, 'clear');
        $pageUrl = $this->removeQueryStringParameter($pageUrl, 'remove');
        $searchUrl = $pageUrl . '&search=1';
        $clearUrl = $pageUrl . '&clear=1';


        $jsResult = '<script>';
        $jsResult .= 'window.addEventListener("load", function() {';
        $jsResult .= '  document.getElementById("searchButton").onclick  = function(e){';
        $jsResult .= '      e.preventDefault();';
        $jsResult .= '      this.style.display = "none";';
        $jsResult .= '      document.getElementById("searchLoader").style.display = "block";';
        $jsResult .= '      window.open("' . $searchUrl . '", "_top");';
        $jsResult .= '  };';
        if (!isset($_GET['clear'])) {
            $jsResult .= '  document.getElementById("clearButton").onclick  = function(e){';
            $jsResult .= '      if(confirm("sollen wirklich alle Einträge gelöscht werden?")){';
            $jsResult .= '          e.preventDefault();';
            $jsResult .= '          this.style.display = "none";';
            $jsResult .= '          document.getElementById("searchLoader").style.display = "block";';
            $jsResult .= '          window.open("' . $clearUrl . '", "_top");';
            $jsResult .= '      }';
            $jsResult .= '  };';
        }
        $jsResult .= '});';
        $jsResult .= '</script>';

        $result .= '<button id="searchButton" class="tl_submit" >Inhalte nach Wörtern durchsuchen</button>';
        if (!isset($_GET['clear'])) {
            $result .= '<button id="clearButton" class="tl_submit" >Alle Einträge löschen</button>';
            $result .= '<div id="clearLoader" style="display: none; padding: 10px;" ><p><i>Alle Einträge werden gelöscht</i></p></div>';
        }
        $result .= '<div id="searchLoader" style="display: none; padding: 10px;" ><p><i>Suche läuft, bitte warten...</i></p></div>';


        $result = $jsResult . $result;
        $result .= '<style>.tl_submit_container{ display: none; }</style><br><br>';
        return $result;

    }


    /**
     * Find words in news
     *
     * @return string
     */
    public function findNewWords($words, $minLength, $maxLength)
    {
        $fields = array('text', 'title', 'html', 'headline');
        $allContents = ContentModel::findAll();


        if ($allContents && count($allContents)) {

            foreach ($allContents as $content) {
                foreach ($fields as $field) {
                    if ($content->{$field}) {
                        $text = $content->{$field};
                        $text = StringUtil::stripInsertTags($text);

                        $text = strip_tags($text);
                        $words = $this->findWordsInText($text, $words, $minLength, $maxLength);
                    }
                }

            }
        }

        sort($words);
        return $words;

    }

    /**
     * Find words in text
     *
     * @return array
     */
    public function findWordsInText($text, $words, $minLength, $maxLength)
    {
        $text = preg_replace('/\s\s+/', ' ', $text);
        $temp = preg_split('/(\s+)/', $text, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);

        $spaces = array();
        $allWords = array_reduce($temp, function (&$result, $item) use (&$spaces) {
            if (strlen(trim($item)) === 0) {
                $spaces[] = strlen($item);
            } else {
                $result[] = $item;
            }
            return $result;
        }, array());

        foreach ($allWords as $newWord) {
            $exist = false;
            foreach ($words as $existingWord) {
                if ($newWord === $existingWord) {
                    $exist = true;
                }
            }
            if (!$exist) {
                $words = $this->addWord($newWord, $words, $minLength, $maxLength);
            }
        }
        return $words;
    }

    /**
     * add words to database
     *
     * @return array
     */
    public function addWord($newWord, $words, $minLength, $maxLength)
    {
        $newWord = str_replace('[nbsp]', '', $newWord);

        $allWords = array_unique(explode(" ", $newWord));
        $newWord = $allWords[0];
        $objDatabase = Database::getInstance();
        $notAllowed = array('/', '"', '(', ')', '.', ',', ':', '„', '“', '[-]', '#', '‚');
        $notAllowedFirstLetter = array('-', '–');

        $allowed = true;
        foreach ($notAllowed as $forbidden) {
            if (strpos($newWord, $forbidden) !== false) {
                $allowed = false;
            }
        }

        $firstLetter = substr($newWord, 0, 1);
        foreach ($notAllowedFirstLetter as $forbidden) {
            if ($firstLetter === $forbidden) {
                $allowed = false;
            }
        }

        if ($allowed) {
            if (strlen($newWord) > $minLength && strlen($newWord) < $maxLength) {
                $objDatabase->prepare("INSERT INTO tl_search_suggestion (word) VALUES (?)")->execute($newWord);
                $words[] = $newWord;
            }
        }


        return $words;
    }

    /**
     * remove all words
     *
     * @return array
     */
    public function removeAll()
    {
        $objDatabase = Database::getInstance();
        $sql = "TRUNCATE TABLE tl_search_suggestion";
        $saveData = $objDatabase->prepare($sql);
        $saveData->execute();
    }

    /**
     * remove one words
     *
     * @return string
     */
    public function removeSuggestion($id)
    {
        $result = '<p>';
        $suggestion = SearchSuggestionModel::findById($id);
        if ($suggestion && $suggestion->id) {
            $objDatabase = Database::getInstance();
            $objDatabase->prepare("DELETE FROM tl_search_suggestion WHERE id=?")->execute($suggestion->id);
            $result .= $suggestion->word . ' wurde erfolgreich gelöscht';
        } else {
            $result .= 'Eintrag mit id: ' . $id . ' konnte nicht gefunden werden';
        }
        $result .= '</p>';
        return $result;

    }

    /**
     * Remove a query string parameter from an URL.
     *
     * @param string $url
     * @param string $varname
     *
     * @return string
     */
    function removeQueryStringParameter($url, $varname)
    {
        $parsedUrl = parse_url($url);
        $query = array();

        if (isset($parsedUrl['query'])) {
            parse_str($parsedUrl['query'], $query);
            unset($query[$varname]);
        }

        $path = isset($parsedUrl['path']) ? $parsedUrl['path'] : '';
        $query = !empty($query) ? '?' . http_build_query($query) : '';

        return $parsedUrl['scheme'] . '://' . $parsedUrl['host'] . $path . $query;
    }


}
