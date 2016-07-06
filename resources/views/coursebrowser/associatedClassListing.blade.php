<?php

/** @var Each class inside associated class list $c */
foreach($class as $c){

// display closed - if closed
    $class_title["clsd"] = $c["class_closed"];

//class number
    $class_title["classNbr"]=$c["class_number"];
// consent - if present
    $class_title["consent"]=$c["consent_type_requirement"];

// Available - seats
    $class_title["seats"]="Seats: &nbsp;".$c["enrollment_capacity"].", ".
            "Avail:&nbsp;".$c["total_available"].", "."Wait:&nbsp;".$c["waitlisted_total_number"];

    echo join("&nbsp;",array_filter($class_title,function($x){
        return isset($x) && $x!="";
    }));
    echo "<br/>";
}

?>


