<?php
$host='localhost';
$db='bank';
$login='root';
$mdp='mohamed';
try{
    $cnx=new PDO("mysql:host=$host;dbname=$db",$login,$mdp);
}
catch (PDOException $e){
    echo"erreur".$e->getMessage();
}
?>