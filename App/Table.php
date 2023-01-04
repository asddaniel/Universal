<?php 
namespace Dan;
use \Exception;

class Table extends Base { // construction d'une table relative à une base de données
	
  

    public function get(int $id){
        $data = $this->all();
        if(count($data)>$id){
            return $data[$id];
        }else{
            return [];
        }
    }
    public function delete($id){
        $records = $this->get_all_records();
        for($i=0; $i<count($records); $i++){
            if($i==$id){
                $db = $this->init_connection();
                $db->exec('DELETE FROM enregistrements WHERE id="'.$records[$i]["id"].'"');
            }
        }
    }
    public function modify(int $id, array $data){
          $donnees = $this->get($id);
          $validate = [];
          if(!empty($data)){
               foreach ($data as $key => $value) {
                if(!array_key_exists($key, $donnees)){
                    // print_r($donnees);
                    throw new Exception("champ de colonne inconnu", 1);    
                }
                if($key!="id"){
                    $donnees[$key] = $value;
                }
                
               }
               $this->commit($donnees);
          }
    }
    private function commit($data){
        $records = $this->get_all_records();
        $id = $records[$data['id']]["id"];
        $db = $this->init_connection();
        $relations = array_map(function($e){
            return remove_numeric_keys($e);
        }, $this->requetteAll('SELECT destination FROM relations WHERE origine="'.$id.'"'));
    //   print_r($relations);
    //   print_r($data);
      $i=0;
      foreach ($data as $key => $value) {
        if($key!="id"){
            $db->exec('UPDATE donnees SET valeur="'.$value.'" WHERE id="'.$relations[$i]['destination'].'"');
            $i++;
        }
           
      }
    //   die;
    }

    public function create(array $data){
        
        $columns = $this->get_array_ordererd_colonne();
        // var_dump($this->get_property());
        //var_dump($columns);
        if(count($data)<count($columns)){
            throw new Exception("Erreur vous devez donnez tous les propriétes", 1);
            
        }else{

            $record_id = $this->register_record()["id"];
         foreach ($data as $key => $value) {
            if(in_array($key, $this->get_property())){
             $id =  $this->register_data($value, $columns[$key]);
             $this->register_relation($record_id, $id["id"]);
            }
         }
        }
    }
    private function register_relation($origine, $destination){
    $bdd = $this->init_connection();
     $bdd->exec('INSERT INTO relations(origine, destination) VALUES("'.$origine.'", "'.$destination.'")');

    }
    protected function get_array_ordererd_colonne(){
        $columns = [];
        foreach ($GLOBALS[$this->get_class_name()]["colonne"] as $key => $value) {

            $columns[$value["nom"]] = $value["id"]; 
        }
        return $columns;
    }
    private function register_data($value, $colonne_id){
     $bdd = $this->init_connection();
    
     $bdd->exec('INSERT INTO donnees(colonne_id, valeur) VALUES("'.$colonne_id.'", "'.$value.'")');
     return $this->requette("SELECT MAX(id) as id FROM donnees");
    }
    private function register_record(){
        $bdd = $this->init_connection();
        $bdd->exec('INSERT INTO enregistrements(id_table) VALUES("'.$this->get_table_id().'")');
        return $this->requette("SELECT MAX(id) as id FROM enregistrements");
    }
    protected function get_class_name(){
        return str_replace("App\\", "", get_class($this));
    }
    private function get_property(){
        return array_keys(get_object_vars($this));
    }
    protected function get_colonne(){
        $colonne = $GLOBALS[$this->get_class_name()]["colonne"]??$this->requetteAll('SELECT * FROM colonnes WHERE id_table=(SELECT id FROM donnees WHERE valeur="'.$this->get_class_name().'" AND colonne_id="-1" )');
        $GLOBALS[$this->get_class_name()]["colonne"] = $colonne;
        $colonne = array_map(function($e){
            return remove_numeric_keys($e)["nom"];
        }, $colonne);
        return $colonne;

    }

    protected function get_colonne_and_id(){
        $colonne = $GLOBALS[$this->get_class_name()]["colonne"]??$this->requetteAll('SELECT * FROM colonnes WHERE id_table=(SELECT id FROM donnees WHERE valeur="'.$table_name.'" AND colonne_id="-1" )');
        $GLOBALS[$this->get_class_name()]["colonne"] = $colonne;
       
        return $colonne;

    }
    private function migrate(){
            $bdd = $this->init_connection();
            $colonne = $this->get_colonne();
            $property = $this->get_property();
            
        
            if(!empty(array_diff($property, $colonne))){
                $column_and_id = $this->get_colonne_and_id();
                foreach ($property as $key => $value) {

                    if($key<=count($colonne)-1){
                       $bdd->exec('UPDATE colonnes SET nom="'.$value.'" WHERE id="'.$column_and_id[$key]["id"].'"');
                       
                    }else{
                        $bdd->exec('INSERT INTO colonnes(id_table, nom) VALUES("'.$this->get_table_id().'", "'.$value.'")');
                    }
                }

                if(count($property)<count($colonne)){
                    $difference = array_diff($colonne, $property);
                    foreach ($column_and_id as $key => $value) {
                        if(in_array($value["nom"], $difference)){
                        $bdd->exec('DELETE FROM colonnes WHERE id="'.$value["id"].'")');
                        }
                    }
                }
              
            

            }

           
           

    }

    protected function get_table_id(){
        $requette =$_GLOBALS[$this->get_class_name()]["id"]??$this->requette('SELECT * FROM donnees WHERE colonne_id="-1" AND valeur ="'.$this->get_class_name().'"');
        $_GLOBALS[$this->get_class_name()]["id"] = $requette["id"];
        return $requette["id"];
    }
    protected function construire_table(){
        $property = array_keys(get_object_vars($this));
       
        $bdd = $this->init_connection();
        $table_name = $this->get_class_name();
      
        $requette = $this->requette('SELECT * FROM donnees WHERE colonne_id="-1" AND valeur ="'.$table_name.'"');
       $data = $requette;
        if(!empty($data)){
             $this->migrate();
        }else{
            $db = $this->init_connection();
            $db->exec('INSERT INTO donnees(colonne_id, valeur) VALUES("-1", "'.$table_name.'")');
            $id = $this->requette("SELECT MAX(id) as id FROM donnees");
           
            $proprietes = $this->get_property();
            foreach ($proprietes as $key => $value) {
                $db->exec('INSERT INTO colonnes(id_table, nom) VALUES("'.$id['id'].'", "'.$value.'")');
            }
            $this->migrate();

        }
    }


}
