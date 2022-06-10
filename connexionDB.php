<?php

     function connexion(){
        require('config.php');

        try {
            $linkpdo = new PDO("mysql:host=$host;dbname=$db", $user,$pwd);
        }
        catch (Exception $e) {
            die('Erreur : ' . $e->getMessage());
        }

        return $linkpdo;

    }
?>