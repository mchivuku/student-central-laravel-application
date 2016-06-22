<?php
/**
 * Created by
 * User: IU Communications
 * Date: 5/27/16
 */

namespace StudentCentralApp\Utils;


class ArrayHelpers{

    public static function  array_extract_filter($array, $extract_str,$filterfunc){

        return array_filter(array_map(function($item)use($extract_str){
            return $item[$extract_str];
        },$array),function($item)use($filterfunc){
            return $filterfunc($item);
        });

    }
}