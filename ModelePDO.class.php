<?php

require_once 'Mysql_config.class.php';
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ModelePDO
 *
 * @author luangpraseuth
 */
class ModelePDO {

    //Atributs utiles pour la connexion
    protected static $serveur = MysqlConfig::NOM_SERVEUR;
    protected static $base = MysqlConfig::NOM_BASE;
    protected static $utlisateur = MysqlConfig::NOM_UTILISATEUR;
    protected static $passe = MysqlConfig::MOT_DE_PASSE;
    //Atributs utiles pour la manipulation PDO de la BD
    protected static $pdoCnxBase = null;
    protected static $pdoStResults = null;
    protected static $requete = "";
    protected static $resultat = null;

    /**
     * Permet de se connecter à la base de données
     */
    protected static function seConnecter() {

        if (!isset(self::$pdoCnxBase)) { //S'il n'y a pas encore eu de connexion
            try {
                self::$pdoCnxBase = new PDO('mysql:host=' . self::$serveur . ';dbname=' . self::$base, self::$utlisateur, self::$passe);
                self::$pdoCnxBase->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::$pdoCnxBase->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
                self::$pdoCnxBase->query("SET CHARACTER SET utf8");
            } catch (Exception $ex) {
                //l'objet pdoCnxBase a généré automatiquement un objet de type Exception
                echo 'Erreur : ' . $ex->getMessage() . '<br />'; // méthode de la classe Exception
                echo 'Code : ' . $ex->getCode(); // méthode de la classe Exception 
            }
        }
    }

    /**
     * Permet de se déconnecter de la base de données
     */
    public static function seDeconnecter() {
        self::$pdoCnxBase = null;
    }

    public static function getAllFrom($table) {
        self::seConnecter();

        try {
            self::$requete = "SELECT * FROM " . $table;
            self::$pdoStResults = self::$pdoCnxBase->prepare(self::$requete);
            self::$pdoStResults->execute();
            self::$resultat = self::$pdoStResults->fetchAll();

            self::$pdoStResults->closeCursor();
            return self::$resultat;
        } catch (Exception $ex) {
            echo 'Erreur : ' . $ex->getMessage() . '<br />';
            echo 'Code : ' . $ex->getCode();
        }
    }

    /**
     * Supprime une ligne de la table selon l'id
     * @param type $table nom de la table
     * @param type $id  id du tuple
     */
    public static function deleteTupleTableById($table, $id) {
        self::seConnecter();

        try {

            self::$requete = "DELETE FROM " . $table . " where id = " . $id;
            self::$pdoStResults = self::$pdoCnxBase->prepare(self::$requete);
            self::$pdoStResults->execute();
        } catch (Exception $ex) {
            echo 'Erreur : ' . $ex->getMessage() . '<br />';
            echo 'Code : ' . $ex->getCode();
        }
    }

    /**
     * Retourne un tuple correspondant à l'id et à la table en paramètre
     * @param type $table table du tuple
     * @param type $id id du tuple
     * @return type objet
     */
    public static function getleTupleTableById($table, $id) {
        self::seConnecter();
        try {
            self::$requete = "SELECT * FROM " . $table . " where id = " . $id;
            self::$pdoStResults = self::$pdoCnxBase->prepare(self::$requete);
            self::$pdoStResults->execute();
            self::$resultat = self::$pdoStResults->fetch();
            self::$pdoStResults->closeCursor();
            return self::$resultat;
        } catch (Exception $ex) {
            echo 'Erreur : ' . $ex->getMessage() . '<br />';
            echo 'Code : ' . $ex->getCode();
        }
    }

    public static function getNbFrom($table) {
        self::seConnecter();

        try {
            self::$requete = "SELECT count(*) as Nb FROM " . $table;
            self::$pdoStResults = self::$pdoCnxBase->prepare(self::$requete);
            self::$pdoStResults->execute();
            self::$resultat = self::$pdoStResults->fetch();

            self::$pdoStResults->closeCursor();
            return self::$resultat->Nb;
        } catch (Exception $ex) {
            echo 'Erreur : ' . $ex->getMessage() . '<br />';
            echo 'Code : ' . $ex->getCode();
        }
    }

    protected static function getPremierTupleByChamp($table, $nomChamp, $valeurChamp) {
        self::seConnecter();

        self::$requete = "SELECT * FROM" . $table . "WHERE" . $nomChamp . "=:valeurChamp";
        self::$pdoStResults = self::$pdoCnxBase->prepare(self::$requete);
        self::$pdoStResults->bindValue(':valeurChamp', $valeurChamp);
        self::$pdoStResults->execute();
        self::$resultat = self::$pdoStResults->fetch(PDO::FETCH_OBJ);
        self::$pdoStResults->closeCursor();
        return self::$resultat;
    }

    protected static function deleteTupleByChamp($table, $nomChamp, $valeurChamp) {
        self::seConnecter();

        self::$requete = "DELETE * FROM" . $table . "WHERE" . $nomChamp . "=:valeurChamp";
        self::$pdoStResults = self::$pdoCnxBase->prepare(self::$requete);
        self::$pdoStResults->bindValue(':valeurChamp', $valeurChamp);
        self::$pdoStResults->execute();
    }

    protected static function modifTable($type, $objet, $table) {
         self::$requete = sprintf('DESCRIBE %s', $table);
                self::$pdoStResults = self::$pdoCnxBase->prepare(self::$requete);
                self::$pdoStResults->execute();

                $data = array();
                //var_dump(self::$pdoStResults->fetchAll(PDO::FETCH_ASSOC));
                foreach (self::$pdoStResults->fetchAll(PDO::FETCH_ASSOC) as $rows) {
                    $data[] = $rows;
                }
                $valeurs = "";
                
        switch ($type) {
            case "insert":
               
                var_dump($data);
                foreach ($data as $key => $att) {
                    var_dump($key);
                    var_dump($att['Field']);
                    if (strncmp($att['Type'], 'int', 3) == 0) {
                        if ($valeurs != "")
                            $valeurs = $valeurs . "," . $objet->$att['Field'];
                        else
                            $valeurs = $valeurs . $objet->$att['Field'];
                    }
                    else
                    {
                          if ($valeurs != "")
                            $valeurs = $valeurs . "," . "'".$objet->$att['Field']."'";
                        else
                            $valeurs = $valeurs . "'".$objet->$att['Field']."'";  
                    }
                }
                self::$requete = "insert into " . $table . " values(" . $valeurs . ")";
                var_dump(self::$requete);
                self::$pdoStResults = self::$pdoCnxBase->prepare(self::$requete);
                self::$pdoStResults->execute();

                break;
            case "update":
                var_dump($data);
                foreach ($data as $key => $att) {
                    var_dump($key);
                    var_dump($att['Field']);
                    if ($key == 0)
                            $whereValue = $att['Field']. "=" . $objet->$att['Field'];
                    if (strncmp($att['Type'], 'int', 3) == 0) {
                        if ($valeurs != "")
                            $valeurs = $valeurs . "," . $att['Field']. "=" . $objet->$att['Field'];
                        else
                            $valeurs = $valeurs . $att['Field']. "=" . $objet->$att['Field'];
                    }
                    else
                    {
                          if ($valeurs != "")
                            $valeurs = $valeurs . "," . $att['Field']. "=" . "'".$objet->$att['Field']."'";
                        else
                            $valeurs = $valeurs . $att['Field']. "=" . "'".$objet->$att['Field']."'";  
                    }
                }
                self::$requete = "update " . $table . " set " . $valeurs . " where ". $whereValue;
                var_dump(self::$requete);
                self::$pdoStResults = self::$pdoCnxBase->prepare(self::$requete);
                self::$pdoStResults->execute();
                break;
        }
    }

}
