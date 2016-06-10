<?php
/**
 * Created by
 * User: IU Communications
 * Date: 6/9/16
 */
namespace StudentCentralCourseBrowser\Transformers;

/**
 * Base class to hold common functions
 */
class BaseTransformer{


    /***
     * Function looks for http links in the text and converts them into anchor links
     * @param $text
     * Ref: https://css-tricks.com/snippets/php/find-urls-in-text-make-links/
     */
    public function parseTextForHttpLinks($text){
        //1. look for links.
        $reg_exUrl = "/(http|https|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/";

        if(preg_match_all($reg_exUrl, $text, $urls)) {
            collect($urls)->each(function($url)use($reg_exUrl,&$text){
                preg_replace($reg_exUrl, "<a href=".$url[0].">$url[0]</a> ", $text);
            });
        }

        return $text;

    }
}
