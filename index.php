<?php  
require 'init.php';

use Dan\Article;


header("content-type:application/json");

$art = new Article();
 //$art->create(["titre"=>"Messi", "content"=>"Patoti patata", "author"=>"Daniel Assani"]);

 echo json_encode($art->all());


?>
