
<?php
//chargement de l' autoloader

require 'vendor/autoload.php';

function match_id($element){

}

use \models\structure;
function route(){
    
    return str_replace(structure::data()['domaine'], "", get_url());
}


?>