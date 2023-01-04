<?php 
$bdd->exec("CREATE TABLE IF NOT EXISTS `donnees` (
    `id` int NOT NULL AUTO_INCREMENT,
    `colonne_id` VARCHAR(255) NOT NULL,
    `valeur` TEXT,
    PRIMARY KEY (`id`)
  );");
  
  $bdd->exec("CREATE TABLE IF NOT EXISTS `enregistrements` (
    `id` int NOT NULL AUTO_INCREMENT,
    `id_table` int NOT NULL,
    PRIMARY KEY (`id`)
  );");
  
  $bdd->exec("CREATE  TABLE IF NOT EXISTS `colonnes` (
    `id` int NOT NULL AUTO_INCREMENT,
    `id_table` int NOT NULL,
    `nom` VARCHAR(255) NOT NULL,
    PRIMARY KEY (`id`)
  );");

$bdd->exec("CREATE TABLE IF NOT EXISTS `relations` (
    `id` int NOT NULL AUTO_INCREMENT,
    `origine` int NOT NULL,
    `destination` int NOT NULL,
    PRIMARY KEY (`id`)
  );");
  
  
  


?>