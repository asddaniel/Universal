<?php  
namespace Dan;
class Structure{
    public array|object|string $definition;
    public function __construct(){
        $data = (file_get_contents("./config-bdd.json"));
        $this->definition = json_decode($data);
    }

}


?>