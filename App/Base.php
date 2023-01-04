<?php 
namespace Dan;


use \pdo;
class Base{


	private $user;
	private $db_name;
	private $hote = "localhost";
	private $login = "root";
	private $pass = "";
	private $pdo;

	private $enregistrement;
	private $table;
	private $main_table;
	private $colonne = array();
	private $table_id;



	
	
 public function __construct(){
 
 	
 	$this->construire_base_de_donnees();
  		
 }
 private function init_bdd_config(){
	$structure = new structure();
 	$this->hote = $structure->definition->host;
 	$this->login = $structure->definition->username;
 	$this->pass = $structure->definition->password;
 	$this->db_name = $structure->definition->db_name;
 }
//  private function get_table_id($table_name){
// 	if(isset($_GLOBALS[$table_name])){
// 		return $_GLOBALS[$table_name]["id"];
// 	}
// 	$GLOBALS[$table_name] = ["id"=>$this->requette("SELECT id FROM $table_name  WHERE id_table='-1'")];
// 	return $_GLOBALS[$table_name]["id"]; 
//  }
 private function get_colonne_id(){
	$table_id = $this->get_table_id(str_replace("App\\", "", get_class()));
	return requetteAll("SELECT id FROM colonnes WHERE id_table=$table_id");
 }
 protected function get_all_records(){
	$id = $this->get_table_id();
      $enregistrements = $this->requetteAll('SELECT id FROM enregistrements WHERE id_table="'.$id.'"');
	  return  array_map(function($e){
		return remove_numeric_keys($e);
	}, $enregistrements);
 }
 protected function get_all_relations(){
	return  array_map(function($e){
		return remove_numeric_keys($e);
	}, $this->requetteAll("SELECT * FROM relations"));
 }
 public function get_all_data(){
	return array_filter(array_map(function($e){
          return remove_numeric_keys($e);
	}, $this->requetteAll("SELECT * FROM donnees")), function($data){
		// echo $data["colonne_id"]=!'fhdhdjfhdjfh'?"ok bon": "echecs";
		return (int)$data["colonne_id"]>=0;
	});
 }
 public function all(){
	$records = $this-> get_all_records();
	$data = $this->get_all_data();
	// var_dump($data);
	$relations = $this->get_all_relations();
    $all = $this->match_record($records, $relations);
	$GLOBALS['temp_i'] = 0;
	$final_data =  array_map(function($e){
		$e[count($e)] =["id"=>$GLOBALS['temp_i']];
		$GLOBALS['temp_i']++;
		// print_r($e);
		return array_ordonne($e);
	}, $this->match_data($all, $data));
	$final_ending = [];
	foreach ($final_data as $key => $value) {
		$temp = [];
		foreach ($value as $cle => $valeur) {
			foreach ($valeur as $second_keys => $val) {
				$temp[$second_keys] = $val;
			}
		}
		array_push($final_ending, $temp);
		//  print_r($temp);
		//  echo"ok";
	}
	return $final_ending;
 }
 protected function match_data($relations, $data){
	   $columns = $this->get_array_ordererd_colonne();
      $donnees = [];
	$GLOBALS["temp"] = ["columns"=>$columns, "data"=>$data];
	  foreach ($relations as $key => $value) {
		//   var_dump($data);
		array_push($donnees, array_map(function($e){
			
            return [$this->get_colonne_name_where_id($this->get_value_in_data($GLOBALS["temp"]["data"], $e["destination"])["colonne_id"])=>$this->get_value_in_data($GLOBALS["temp"]["data"], $e["destination"])["valeur"]];
		}, $value));
	  }
  return $donnees;

 }
 private function get_colonne_name_where_id($id){
	//echo array_search($id, $this->get_array_ordererd_colonne());
      return array_search($id, $this->get_array_ordererd_colonne());
 }
 public function get_value_in_data($data, $key){
	$GLOBALS["tempo"] = $key;
	// var_dump($data);
       return array_ordonne(array_filter($data, function($e){
		// echo $e["id"];
             return $e["id"] == $GLOBALS["tempo"];
	   }))[0];
 }
 public function match_record($records, $relations){
	$tous = [];
	foreach ($records as $key => $value) {
		$GLOBALS["temp"] = $value["id"];
		//  echo $value["id"];
		array_push($tous, array_filter($relations, function($e){
			    
				return $e['origine']==$GLOBALS['temp'];
		}));
	}
       return $tous;
 }

 protected function construire_base_de_donnees(){
	$this->init_bdd_config();
 	 
 	try{
$connexion=new PDO("mysql:host=".$this->hote,$this->login,$this->pass, array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'));
	$connexion->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
	$connexion->exec("CREATE DATABASE IF NOT EXISTS ".$this->db_name."");
	$pdo_options[PDO::ATTR_ERRMODE] = PDO::ERRMODE_EXCEPTION;
$bdd = new PDO('mysql:host='.$this->hote.';dbname='.$this->db_name, $this->login, $this->pass,
$pdo_options);
	 // création des  table pour enregistrement comptes utilisateur
include("./migration.php");


 	}catch(Exception $e){
	die('Erreur : '.$e->getMessage());
	
	echo"<h1>echecs de la connexion<h1>";} 

	$this->construire_table();
 }

public  function init_connection(){
	
	if(isset($GLOBALS["pdo"])){
		return $GLOBALS["pdo"];
	}else{

	$dsn = 'mysql:host='.$this->hote.';dbname='.$this->db_name;
	$pdo = new PDO($dsn, $this->login, $this->pass, array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'));
	$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	$this->pdo=$pdo;
	$GLOBALS["pdo"] = $pdo;
	return $pdo;
}
}



private  function start_connection(){
	$dsn = 'mysql:host='.$this->hote.';dbname='.$this->db_name;
	$pdo = new PDO($dsn, $this->user, $this->pass);
	$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	$this->pdo=$pdo;
	return $pdo;
}
public function requette($req){
	$cont=0;
	$connect =$this->init_connection();

	$masque1 = "'";
	$masque2 = '"';
	if(longueur_chaine_filtre($req, $masque1)==longueur_chaine($req)){
		if(longueur_chaine_filtre($req, $masque2)){
			//var_dump($req);
			$reponse=$connect->query($req);
	
	$donnees = $reponse->fetch();
	
}else{
	$chaine = explode('"', $req);
	$requette ='';
	$array = array();
	for($i=0; $i<count($chaine); $i++){
         if(($i+2)%2==0){
           $requette =$requette.''.$chaine[$i];
           if($i!=count($chaine)-1){$requette=$requette.'?';}
         }else{
         	$array[count($array)]=$chaine[$i];
         }

	}
	$reponse = $connect->prepare($requette);
	$reponse->execute($array);
	$donnees = $reponse->fetch();
	}
}else{
	$chaine = explode("'", $req);
	$requette ='';
	$array = array();
	for($i=0; $i<count($chaine); $i++){
         if(($i+2)%2==0){
           $requette =$requette.''.$chaine[$i];
           if($i!=count($chaine)-1){$requette=$requette.'?';}
         }else{
         	$array[count($array)]=$chaine[$i];
         }

	}
	$reponse = $connect->prepare($requette);

	$reponse->execute($array);
	$donnees = $reponse->fetch();
}
	
return $donnees;
}
public function requetteAll($req){
	$cont=0;
	$connect =$this->init_connection();
	$masque1 = "'";
	$masque2 = '"';
	if(longueur_chaine_filtre($req, $masque1)==longueur_chaine($req)){
		if(longueur_chaine_filtre($req, $masque2)){
			$reponse=$connect->query($req);
	
	$donnees = $reponse->fetchAll();
	
}else{
	$chaine = explode('"', $req);
	$requette ='';
	$array = array();
	for($i=0; $i<count($chaine); $i++){
         if(($i+2)%2==0){
           $requette =$requette.''.$chaine[$i];
           if($i!=count($chaine)-1){$requette=$requette.'?';}
         }else{
         	$array[count($array)]=$chaine[$i];
         }

	}
	$reponse = $connect->prepare($requette);
	$reponse->execute($array);
	$donnees = $reponse->fetchAll();
	}
}else{
	$chaine = explode("'", $req);
	$requette ='';
	$array = array();
	for($i=0; $i<count($chaine); $i++){
         if(($i+2)%2==0){
           $requette =$requette.''.$chaine[$i];
           if($i!=count($chaine)-1){$requette=$requette.'?';}
         }else{
         	$array[count($array)]=$chaine[$i];
         }

	}
	$reponse = $connect->prepare($requette);
	$reponse->execute($array);
	$donnees = $reponse->fetchAll();
}
	
return $donnees;
}
public function insert($data, $table, $attributes){	
/** @$table : le nom de la table qui doit recevoir les données  @$data : est un tableau contenant la liste des donnée à inserer dans chaque colonne de la table de donnée suivant l'ordre des colonne
	*/
		$connect = $this->init_connection();
	
	$requet = "INSERT INTO ".$table."(".implode(", ", $attributes).") VALUES(:".implode(", :", $attributes).")";
	$insert = $connect->prepare($requet);

	$array = $this->getArray($attributes, $data); //crée un tableau associatif qui associe chaque attribut au données
	
		$insert->execute($array);
			
    
	
	return true;
}

private function getArray($table1, $table2){
	/** @var : $table1 et $table2 doivent avoir la meme taille
	*/
if(count($table1)==count($table2)){
	for($i=0; $i < count($table1); $i++){
		$nouveau[$table1[$i]]=$table2[$i];

	}
return $nouveau;	
}else{ return false;}
}
public function update($table, $colonne, $data, $reference){
	/*
@$table : contient le nom de la table 
@colonne : contient un tableau censé contenir la liste des éléments à modifier dans la table
@data : contient un tableau contenant toutes les valeurs à modifier de maniere ordonné
@reference : contient la refernces des donnée ex: id=5


	**/
	$connect = $this->init_connection();
	$modification = '';
	$array_fetch = array();
	// var_dump($data);
	for($i=0; $i<count($colonne);$i++){

		if($i==0){$modification = ''.$colonne[$i].'=:'.$colonne[$i];
        $array_fetch[$colonne[$i]] = $data[$i]; }else{
		$modification =$modification.', '.$colonne[$i].'=:'.$colonne[$i];
        $array_fetch[$colonne[$i]] = $data[$i];
 }
	}
	$requette = "UPDATE ".$table." SET ".$modification." WHERE ".$reference;
	
	$base = $connect->prepare($requette);

	$base->execute($array_fetch);
}

public function supprimer($table, $reference){
	/*
	@table : contient le nom de la table 
	@reference : contient le reference ex : id=3

	**/

	$connect = $this->init_connection();
	$modification = '';
	
	$requette = "DELETE FROM ".$table." WHERE ".$reference;
	
	$base = $connect->query($requette);
	//$base->execute($requette);

}



}

?>