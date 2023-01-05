# universal!
![enter image description here](https://img.shields.io/badge/stable-v0.01-success)![universal meta model](https://img.shields.io/badge/asddaniel-universal-blue)
 **Universal**. est un ORM virtuel écrit en PHP, qui vous permet de gérer votre base de donnée sans se préoccuper du SGBD. il fonctionne avec un modèle de base de donnée universel ce qui veut dire qu'au niveau du SGBD votre base de donnée n'aura que 4 table peu importe le nombre des table que vous voulez implémenter dans votre système, **Universal** se transformez vos table virtuelle afin que vous ne puissiez gérer que  les aspects concrète de votre programme. 

## Licence : 
GNU General Public License (GPL) version 2

# Installation

> `composer require asddaniel/universal`

## Demarrer un nouveau programme 

avant de commencer vous devez crée un fichier JSON dans lequel vous devez mettre la configuration de votre base de donnée afin qu'**universal** puisse l'utilisez ce fichier doit s'appeler ***config-bdd.json***
voici un exemple de configuration 

`{ 
    "db_name": "universal",
    "username":"root",
    "password":"",
    "host":"localhost"
}`

## crée un nouveau table 

après avoir importé l'autoloader, tout ce qu'**universal**  a besoin c'est juste d'une classe, chaque propriété non privé de la classe constituera une colonne dans la base de donnée virtuel
la classe que vous devez crée doit heriter de ***Dan\Table***
vous n'avez pas besoin de crée un champ id puisque ***universal*** vous le fournit à la place automatiquement. 

    use Dan\Table;

    class  Article  extends  Table{

            protected  $title;

            protected  $content;

            protected  $created_at;   
            
            }

## enregistrer des données 

vous devez utiliser la méthode **create** après avoir instancié un objet de la classe 

    $article = new Article();
    $art->create(["title"=>"mon super titre", 
			    "content"=>"contenu de mon article",
			    "created_at"=>time()]);






## Lire les données

Universal dispose de deux méthode : une pour lire tous les donnée et l'autre pour récupéré un élément suivant son id 

    $art = new Article();
    $all = $art->all();//recupère tous les articles
    $one = $art->get(5);//recupere l'artcle à la position 5
    echo json_encode($one);     
le resultat est : 

    {"title": "mon super titre",
    "content": "contenu de mon article",
    "created_at": "1672837789",
    "id": 0}



NB: les id ne sont que des position et non des identifiant réel ils sont donc réorganisé après chaque suppression de sorte que les id se suivent toujours 

## modifier une donné

vous utiliser la méthode modify, qui prend comme premier paramètre la position(id), et comme deuxième paramètre.
 une tableau associatif des données. 

    $art = new Article();
    $art->modify(0,["title"=>"mon super titre modifié", 
			    "content"=>"contenu de mis à jour",
			    "created_at"=>time()] 


# Suppresion d'une donnée

Pour supprimer utilisez la méthode delete 

    $art = new Article();
    $art->delete(0);//supprime l'élément de position 0

