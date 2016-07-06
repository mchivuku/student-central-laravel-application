<?php
/**
 * Created by
 * User: IU Communications
 * Date: 6/28/16
 */

namespace StudentCentralApp\Providers;


class HtmlMacros extends \Collective\Html\HtmlBuilder {

    public function __construct( $url,  $view)
    {
        parent::__construct($url, $view);
    }


    /**
     * Parse text for links
     * @param bool $text
     * @return mixed
     */
    public function parseTextForHttpLink($text=true){

        $html= preg_replace('@(https?://([-\w\.]+[-\w])+(:\d+)?(/([\w/_\.#-]*(\?\S+)?[^\.\s])?)?)@',
            '<a href="$1" target="_blank">$1</a>', $text);
        return $html;

    }


    /** Render notes on the page as a ul, li element */
    public function renderNotes($notes){
        $html="<ul>";

        collect($notes)->each(function($note)use(&$html){

            $html.="<li>".$this->parseTextForHttpLink($note)."</li>";

        });

        $html.="</ul>";
        return $html;

    }


}