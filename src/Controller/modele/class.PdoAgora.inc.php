<?php

/**
 *  AGORA
 * 	©  Logma, 2019
 * @package default
 * @author Mathias FRIES
 * @version    5.245.3
 * @link       http://www.php.net/manual/fr/book.pdo.php
 * 
 * Classe d'accès aux données. 
 * Utilise les services de la classe PDO
 * pour l'application AGORA
 * Les attributs sont tous statiques,
 * $monPdo de type PDO 
 * $monPdoAgora qui contiendra l'unique instance de la classe
 */
class PdoAgora {
    private static $monPdo;
    private static $monPdoAgora = null;

    /**
     * Constructeur privé, crée l'instance de PDO qui sera sollicitée
     * pour toutes les méthodes de la classe
     */

    private function __construct()
    {
        // A) >>>>>>>>>>>>>>> Connexion au serveur et à la base
        try {
            // encodage
            $options = array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\'');
            // Crée une instance (un objet) PDO qui représente une connexion à la base
            PdoAgora::$monPdo = new PDO($_ENV['AGORA_DSN'], $_ENV['AGORA_DB_USER'], $_ENV['AGORA_DB_PWD'], $options);
            // configure l'attribut ATTR_ERRMODE pour définir le mode de rapport d'erreurs
            // PDO::ERRMODE_EXCEPTION: émet une exception
            PdoAgora::$monPdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            // configure l'attribut ATTR_DEFAULT_FETCH_MODE pour définir le mode de récupération par défaut
            // PDO::FETCH_OBJ: retourne un objet anonyme avec les noms de propriétés
            // qui correspondent aux noms des colonnes retournés dans le jeu de résultats
            PdoAgora::$monPdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
        } catch (PDOException $e) { // $e est un objet de la classe PDOException, il expose la description du problème
            die('<section id="main-content"><section class="wrapper"><div class ="erreur">Erreur de connexion à la base de données
            !<p>' . $_ENV['AGORA_DSN'] . $_ENV['AGORA_DB_USER'] . $_ENV['AGORA_DB_PWD'] . $e->getmessage() . '</p></div></section></section>');
        }
    }
    
    /**
     * Destructeur, supprime l'instance de PDO  
     */

    public function _destruct() {
        PdoAgora::$monPdo = null;
    }

    /**
     * Fonction statique qui crée l'unique instance de la classe
     * Appel : $instancePdoAgora = PdoAgora::getPdoAgora();
     * 
     * @return l'unique objet de la classe PdoAgora
     */

    public static function getPdoAgora() {
        if (PdoAgora::$monPdoAgora == null) {
            PdoAgora::$monPdoAgora = new PdoAgora();
        }
        return PdoAgora::$monPdoAgora;
    }

	//==============================================================================
	//
	//	METHODES POUR LA GESTION DES GENRES
	//
	//==============================================================================
	
    /**
     * Retourne tous les genres sous forme d'un tableau d'objets 
     * 
     * @return array le tableau d'objets  (Genre)
     */
    public function getLesGenres(): array {
  		$requete =  'SELECT idGenre as identifiant, libGenre as libelle 
						FROM genre 
						ORDER BY libGenre';
		try	{	 
			$resultat = PdoAgora::$monPdo->query($requete);
			$tbGenres  = $resultat->fetchAll();	
			return $tbGenres;		
		}
		catch (PDOException $e)	{  
			die('<div class = "erreur">Erreur dans la requête !<p>'
				.$e->getmessage().'</p></div>');
		}
    }

	
	/**
	 * Ajoute un nouveau genre avec le libellé donné en paramètre
	 * 
	 * @param string $libGenre : le libelle du genre à ajouter
	 * @return int l'identifiant du genre crée
	 */
    public function ajouterGenre(string $libGenre): int {
        try {
            $requete_prepare = PdoAgora::$monPdo->prepare("INSERT INTO genre "
                    . "(idGenre, libGenre) "
                    . "VALUES (0, :unLibGenre) ");
            $requete_prepare->bindParam(':unLibGenre', $libGenre, PDO::PARAM_STR);
            $requete_prepare->execute();
			// récupérer l'identifiant crée
			return PdoAgora::$monPdo->lastInsertId(); 
        } catch (Exception $e) {
            die('<div class = "erreur">Erreur dans la requête !<p>'
				.$e->getmessage().'</p></div>');
        }
    }
	
	
	 /**
     * Modifie le libellé du genre donné en paramètre
     * 
     * @param int $idGenre : l'identifiant du genre à modifier  
     * @param string $libGenre : le libellé modifié
     */
    public function modifierGenre(int $idGenre, string $libGenre): void {
        try {
            $requete_prepare = PdoAgora::$monPdo->prepare("UPDATE genre "
                    . "SET libGenre = :unLibGenre "
                    . "WHERE genre.idGenre = :txtIdGenre");
            $requete_prepare->bindParam(':txtIdGenre', $idGenre, PDO::PARAM_INT);
            $requete_prepare->bindParam(':unLibGenre', $libGenre, PDO::PARAM_STR);
            $requete_prepare->execute();
        } catch (Exception $e) {
            die('<div class = "erreur">Erreur dans la requête !<p>'
				.$e->getmessage().'</p></div>');
        }
    }
	
	
	/**
     * Supprime le genre donné en paramètre
     * 
     * @param int $idGenre :l'identifiant du genre à supprimer 
     */
    public function supprimerGenre(int $idGenre): void {
       try {
            $requete_prepare = PdoAgora::$monPdo->prepare("DELETE FROM genre "
                    . "WHERE genre.idGenre = :txtIdGenre");
            $requete_prepare->bindParam(':txtIdGenre', $idGenre, PDO::PARAM_INT);
            $requete_prepare->execute();
        } catch (Exception $e) {
            die('<div class = "erreur">Erreur dans la requête !<p>' .$e->getmessage().'</p></div>');
        }
    }

    /**
     * Retourne tous les genres sous forme d'un tableau d'objets AVEC le nom du spécialiste du genre et le nb de jeux par genre
     *
     * @return array le tableau d'objets (Genre)
     */

    public function getLesGenresComplet()
    {
        $requete = 'SELECT G.idGenre as identifiant, G.libGenre as libelle, (SELECT COUNT(refJeu) FROM jeu_video AS J WHERE J.idGenre = G.idGenre) AS nbJeux FROM genre AS G ORDER BY G.libGenre';
        try {
            $resultat = PdoAgora::$monPdo->query($requete);
            $tbGenres = $resultat->fetchAll();
            return $tbGenres;
        } catch (PDOException $e) {
            die('<div class = "erreur">Erreur dans la requête !<p>' . $e->getmessage() . '</p></div>');
        }
    }

	
	//==============================================================================
	//
	//	METHODES POUR LA GESTION DES Plateforme
	//
	//==============================================================================
	
    /**
     * Retourne tous les genres sous forme d'un tableau d'objets 
     * 
     * @return array le tableau d'objets  (Plateforme)
     */
    public function getLesPlateforme(): array {
        $requete =  'SELECT idPlateforme as identifiant, libPlateforme as libelle 
                      FROM plateforme 
                      ORDER BY libPlateforme';
      try	{	 
          $resultat = PdoAgora::$monPdo->query($requete);
          $tbPlateforme  = $resultat->fetchAll();	
          return $tbPlateforme;		
      }
      catch (PDOException $e)	{  
          die('<div class = "erreur">Erreur dans la requête !<p>'
              .$e->getmessage().'</p></div>');
      }
  }

  
  /**
   * Ajoute un nouveau Plateforme avec le libellé donné en paramètre
   * 
   * @param string $libPlateforme : le libelle du Plateforme à ajouter
   * @return int l'identifiant du Plateforme crée
   */
  public function ajouterPlateforme(string $libPlateforme): int {
      try {
          $requete_prepare = PdoAgora::$monPdo->prepare("INSERT INTO plateforme "
                  . "(idPlateforme, libPlateforme) "
                  . "VALUES (0, :unLibPlateforme) ");
          $requete_prepare->bindParam(':unLibPlateforme', $libPlateforme, PDO::PARAM_STR);
          $requete_prepare->execute();
          // récupérer l'identifiant crée
          return PdoAgora::$monPdo->lastInsertId(); 
      } catch (Exception $e) {
          die('<div class = "erreur">Erreur dans la requête !<p>'
              .$e->getmessage().'</p></div>');
      }
  }
  
  
   /**
   * Modifie le libellé du Plateforme donné en paramètre
   * 
   * @param int $idPlateforme : l'identifiant du Plateforme à modifier  
   * @param string $libPlateforme : le libellé modifié
   */
  public function modifierPlateforme(int $idPlateforme, string $libPlateforme): void {
      try {
          $requete_prepare = PdoAgora::$monPdo->prepare("UPDATE plateforme "
                  . "SET libPlateforme = :unLibPlateforme "
                  . "WHERE plateforme.idPlateforme = :unIdPlateforme");
          $requete_prepare->bindParam(':unIdPlateforme', $idPlateforme, PDO::PARAM_INT);
          $requete_prepare->bindParam(':unLibPlateforme', $libPlateforme, PDO::PARAM_STR);
          $requete_prepare->execute();
      } catch (Exception $e) {
          die('<div class = "erreur">Erreur dans la requête !<p>'
              .$e->getmessage().'</p></div>');
      }
  }
  
  
  /**
   * Supprime le Plateforme donné en paramètre
   * 
   * @param int $idPlateforme :l'identifiant du Plateforme à supprimer 
   */
  public function supprimerPlateforme(int $idPlateforme): void {
     try {
          $requete_prepare = PdoAgora::$monPdo->prepare("DELETE FROM plateforme "
                  . "WHERE plateforme.idPlateforme = :unIdPlateforme");
          $requete_prepare->bindParam(':unIdPlateforme', $idPlateforme, PDO::PARAM_INT);
          $requete_prepare->execute();
      } catch (Exception $e) {
          die('<div class = "erreur">Erreur dans la requête !<p>'
              .$e->getmessage().'</p></div>');
      }
  }


  public function getLesPlateformesComplet()
  {
      $requete = 'SELECT P.idPlateforme as identifiant, P.libPlateforme as libelle, (SELECT COUNT(refJeu) FROM jeu_video AS J WHERE J.idPlateforme = P.idPlateforme) AS nbJeux FROM plateforme AS P ORDER BY P.libPlateforme';
      try {
          $resultat = PdoAgora::$monPdo->query($requete);
          $tbPlateformes = $resultat->fetchAll();
          return $tbPlateformes;
      } catch (PDOException $e) {
          die('<div class = "erreur">Erreur dans la requête !<p>' . $e->getmessage() . '</p></div>');
      }
  }
    //==============================================================================
    //
    //  METHODES POUR LA GESTION DES Pegi
    //
    //==============================================================================
    
    /**
     * Retourne tous les genres sous forme d'un tableau d'objets 
     * 
     * @return array le tableau d'objets  (Pegi)
     */
    public function getLesPegi(): array {
        $requete =  'SELECT idPegi as indentifiant, ageLimite as libelle, descPegi as descript 
                      FROM pegi 
                      ORDER BY ageLimite';
      try   {    
          $resultat = PdoAgora::$monPdo->query($requete);
          $tbPegi  = $resultat->fetchAll();   
          return $tbPegi;     
      }
      catch (PDOException $e)   {  
          die('<div class = "erreur">Erreur dans la requête !<p>'
              .$e->getmessage().'</p></div>');
      }
  }

  
  /**
   * Ajoute un nouveau Pegi avec le libellé donné en paramètre
   * 
   * @param int $ageLimite : le libelle du Pegi à ajouter
   * @param string $descPegi : le libelle du Pegi à ajouter
   * @return int $idPegi lidentifiant du Pegi crée
   */
  public function ajouterPegi(int $ageLimite, string $descPegi): int {
      try {
          $requete_prepare = PdoAgora::$monPdo->prepare("INSERT INTO pegi "
                  . "(idPegi, ageLimite, descPegi) "
                  . "VALUES (0, :txtageLimite, :txtdescPegi) ");
          $requete_prepare->bindParam(':txtageLimite', $ageLimite, PDO::PARAM_INT);
          $requete_prepare->bindParam(':txtdescPegi', $descPegi, PDO::PARAM_STR);
          $requete_prepare->execute();
          // récupérer l'identifiant crée
          return PdoAgora::$monPdo->lastInsertId(); 
      } catch (Exception $e) {
          die('<div class = "erreur">Erreur dans la requête !<p>'
              .$e->getmessage().'</p></div>');
      }
  }
  
  
   /**
   * Modifie le libellé du Pegi donné en paramètre
   * 
   * @param int $idPegi : l'identifiant du Pegi à modifier  
   * @param int $ageLimite : le libellé modifié
   */
  public function modifierPegi(int $idPegi, string $ageLimite, string $descPegi): void {
      try {
          $requete_prepare = PdoAgora::$monPdo->prepare("UPDATE pegi "
                  . "SET ageLimite = :txtageLimite, "
                  . "descPegi = :txtdescPegi "
                  . "WHERE pegi.idPegi = :txtidPegi");
          $requete_prepare->bindParam(':txtidPegi', $idPegi, PDO::PARAM_INT);
          $requete_prepare->bindParam(':txtageLimite', $ageLimite, PDO::PARAM_INT);
          $requete_prepare->bindParam(':txtdescPegi', $descPegi, PDO::PARAM_STR);
          $requete_prepare->execute();
      } catch (Exception $e) {
          die('<div class = "erreur">Erreur dans la requête !<p>'
              .$e->getmessage().'</p></div>');
      }
  }
  
  
  /**
   * Supprime le Pegi donné en paramètre
   * 
   * @param int $idPegi :l'identifiant du Pegi à supprimer 
   */
  public function supprimerPegi(int $idPegi): void {
     try {
          $requete_prepare = PdoAgora::$monPdo->prepare("DELETE FROM pegi "
                  . "WHERE pegi.idPegi = :txtidPegi");
          $requete_prepare->bindParam(':txtidPegi', $idPegi, PDO::PARAM_INT);
          $requete_prepare->execute();
      } catch (Exception $e) {
          die('<div class = "erreur">Erreur dans la requête !<p>'
              .$e->getmessage().'</p></div>');
      }
  }

    /**
     * Retourne tous les genres sous forme d'un tableau d'objets AVEC le nom du spécialiste du genre et le nb de jeux par genre
     *
     * @return array le tableau d'objets (Genre)
     */

     public function getLesPegiComplet()
     {
         $requete = 'SELECT G.idPegi as identifiant, G.ageLimite as libelle, G.descPegi as descript, (SELECT COUNT(refJeu) FROM jeu_video AS J WHERE J.idPegi = G.idPegi) AS nbJeux FROM pegi AS G ORDER BY G.ageLimite';
         try {
             $resultat = PdoAgora::$monPdo->query($requete);
             $tbPegi = $resultat->fetchAll();
             return $tbPegi;
         } catch (PDOException $e) {
             die('<div class = "erreur">Erreur dans la requête !<p>' . $e->getmessage() . '</p></div>');
         }
     }

     //==============================================================================
    //
    //  METHODES POUR LA GESTION DES Marques
    //
    //==============================================================================
    
    /**
     * Retourne tous les genres sous forme d'un tableau d'objets 
     * 
     * @return array le tableau d'objets  (Marque)
     */
    public function getLesMarque(): array {
        $requete =  'SELECT idMarque as identifiant, nomMarque as libelle 
                      FROM marque 
                      ORDER BY nomMarque';
      try   {    
          $resultat = PdoAgora::$monPdo->query($requete);
          $tbMarque  = $resultat->fetchAll();   
          return $tbMarque;     
      }
      catch (PDOException $e)   {  
        die('<div class = "erreur">Erreur dans la requête !<p>'
            .$e->getmessage().'</p></div>');
    }
  }

  
  /**
   * Ajoute un nouveau Pegi avec le libellé donné en paramètre
   * 
   * @param string $nomMarque : le libelle du Pegi à ajouter
   * @return int l'identifiant de Marque crée
   */
  public function ajouterMarque(string $nomMarque): int {
      try {
          $requete_prepare = PdoAgora::$monPdo->prepare("INSERT INTO Marque "
                  . "(idMarque, nomMarque) "
                  . "VALUES (0, :unnomMarque) ");
          $requete_prepare->bindParam(':unnomMarque', $nomMarque, PDO::PARAM_STR);
          $requete_prepare->execute();
          // récupérer l'identifiant crée
          return PdoAgora::$monPdo->lastInsertId(); 
      } catch (Exception $e) {
          die('<div class = "erreur">Erreur dans la requête !<p>'
              .$e->getmessage().'</p></div>');
      }
  }
  
  
   /**
   * Modifie le libellé du Marque donné en paramètre
   * 
   * @param int $idMarque : l'identifiant du Marque à modifier  
   * @param string $nomMarque : le libellé modifié
   */
  public function modifierMarque(int $idMarque, string $nomMarque): void {
      try {
          $requete_prepare = PdoAgora::$monPdo->prepare("UPDATE marque "
                  . "SET nomMarque = :unnomMarque "
                  . "WHERE marque.idMarque = :unIdMarque");
          $requete_prepare->bindParam(':unIdMarque', $idMarque, PDO::PARAM_INT);
          $requete_prepare->bindParam(':unnomMarque', $nomMarque, PDO::PARAM_STR);
          $requete_prepare->execute();
      } catch (Exception $e) {
          die('<div class = "erreur">Erreur dans la requête !<p>'
              .$e->getmessage().'</p></div>');
      }
  }
  
  
  /**
   * Supprime le Marque donné en paramètre
   * 
   * @param int $idMarque :l'identifiant du Marque à supprimer 
   */
  public function supprimerMarque($idMarque): void {
     try {
          $requete_prepare = PdoAgora::$monPdo->prepare("DELETE FROM marque "
                  . "WHERE marque.idMarque = :unIdMarque");
          $requete_prepare->bindParam(':unIdMarque', $idMarque, PDO::PARAM_INT);
          $requete_prepare->execute();
      } catch (Exception $e) {
          die('<div class = "erreur">Erreur dans la requête !<p>'
              .$e->getmessage().'</p></div>');
      }
  }

  public function getLesMarquesComplet()
  {
      $requete = 'SELECT M.idMarque as identifiant, M.nomMarque as Marque, (SELECT COUNT(refJeu) FROM jeu_video AS J WHERE J.idGenre = M.idMarque) AS nbJeux FROM marque AS M ORDER BY M.nomMarque';
      try {
          $resultat = PdoAgora::$monPdo->query($requete);
          $tbMarques = $resultat->fetchAll();
          return $tbMarques;
      } catch (PDOException $e) {
          die('<div class = "erreur">Erreur dans la requête !<p>' . $e->getmessage() . '</p></div>');
      }
  }
     //==============================================================================
    //
    //  METHODES POUR LA GESTION DES Jeu
    //
    //==============================================================================
    
    /**
     * Retourne tous les genres sous forme d'un tableau d'objets 
     * 
     * @return array le tableau d'objets  (Marque)
     */
    public function getLesJeu(): array {
        $requete =  'SELECT refJeu, nom, genre.libGenre as `libGenre`, marque.nomMarque as `nomMarque`, idPlateforme, dateParution,prix FROM `jeu_video` JOIN genre ON jeu_video.idGenre JOIN marque ON jeu_video.idMarque GROUP BY jeu_video.refJeu';
      try   {    
          $resultat = PdoAgora::$monPdo->query($requete);
          $tbAgora  = $resultat->fetchAll();   
          return $tbAgora;     
      }
      catch (PDOException $e)   {  
        die('<div class = "erreur">Erreur dans la requête !<p>'
            .$e->getmessage().'</p></div>');
    }
  }

  
  /**
   * Ajoute un nouveau Pegi avec le libellé donné en paramètre
   * 
   * @param string $nomMarque : le libelle du Pegi à ajouter
   * @return int l'identifiant de Marque crée
   */
  public function ajouterJeu(string $refJeu, string $nomJeu, string $idGenre, string $idMarque): int {
      try {
          $requete_prepare = PdoAgora::$monPdo->prepare("INSERT INTO jeu_video "
                  . "(refJeu, nom, idGenre, nomMarque) "
                  . "VALUES (:unrefJeu, :unnomJeu) ");
          $requete_prepare->bindParam(':unrefJeu', $refJeu, PDO::PARAM_STR);
          $requete_prepare->bindParam(':unnomJeu', $nomJeu, PDO::PARAM_STR);
          $requete_prepare->bindParam(':txtIdGenre', $idGenre, PDO::PARAM_STR);
          $requete_prepare->bindParam(':unidMarque', $idMarque, PDO::PARAM_STR);
          $requete_prepare->execute();
          // récupérer l'identifiant crée
          return PdoAgora::$monPdo->lastInsertId(); 
      } catch (Exception $e) {
          die('<div class = "erreur">Erreur dans la requête !<p>'
              .$e->getmessage().'</p></div>');
      }
  }
    public function getgenrederoul(): array {
        $requete =  'SELECT libGenre FROM `genre`';
    try   {    
        $resultat = PdoAgora::$monPdo->query($requete);
        $tbGenrederoule  = $resultat->fetchAll();   
        return $tbGenrederoule;     
    }
    catch (PDOException $e)   {  
        die('<div class = "erreur">Erreur dans la requête !<p>'
            .$e->getmessage().'</p></div>');
    }
    }

    public function getmarquederoul(): array {
        $requete =  'SELECT nomMarque FROM `marque`';
    try   {    
        $resultat = PdoAgora::$monPdo->query($requete);
        $tbMarquederoule  = $resultat->fetchAll();   
        return $tbMarquederoule;     
    }
    catch (PDOException $e)   {  
        die('<div class = "erreur">Erreur dans la requête !<p>'
            .$e->getmessage().'</p></div>');
    }
    }
  
   /**
   * Modifie le libellé du Marque donné en paramètre
   * 
   * @param int $idMarque : l'identifiant du Marque à modifier  
   * @param string $nomMarque : le libellé modifié
   */
  public function modifierJeu(int $refJeu, string $nomJeu): void {
      try {
          $requete_prepare = PdoAgora::$monPdo->prepare("UPDATE marque "
                  . "SET nomMarque = :unnomMarque "
                  . "WHERE marque.idMarque = :unIdMarque");
          $requete_prepare->bindParam(':unrefJeu', $refJeu, PDO::PARAM_INT);
          $requete_prepare->bindParam(':unnomJeu', $nomJeu, PDO::PARAM_STR);
          $requete_prepare->execute();
      } catch (Exception $e) {
          die('<div class = "erreur">Erreur dans la requête !<p>'
              .$e->getmessage().'</p></div>');
      }
  }
  
  
  /**
   * Supprime le Marque donné en paramètre
   * 
   * @param int $idMarque :l'identifiant du Marque à supprimer 
   */
  public function supprimerJeu(int $refJeu): void {
     try {
          $requete_prepare = PdoAgora::$monPdo->prepare("DELETE FROM jeu_video "
                  . "WHERE jeu_video.refJeu = :unrefJeu");
          $requete_prepare->bindParam(':unrefJeu', $refJeu, PDO::PARAM_INT);
          $requete_prepare->execute();
      } catch (Exception $e) {
          die('<div class = "erreur">Erreur dans la requête !<p>'
              .$e->getmessage().'</p></div>');
      }
  }

    /**
     * Retourne tous les genres sous forme d'un tableau d'objets AVEC le nom du spécialiste du genre et le nb de jeux par genre
     *
     * @return array le tableau d'objets (Genre)
     */

  public function getLesJeuxComplet()
    {
        $requete = 'SELECT G.refJeu as identifiant, G.nom as libelle, (SELECT COUNT(refJeu) FROM jeu_video AS J WHERE J.refJeu = G.refJeu) AS nbJeux, libGenre as Genre, libPlateforme as Plateforme, nomMarque as Marque, ageLimite as Pegi FROM jeu_video AS G NATURAL JOIN genre NATURAL JOIN plateforme NATURAL JOIN marque NATURAL JOIN pegi ORDER BY G.nom';
        try {
            $resultat = PdoAgora::$monPdo->query($requete);
            $tbJeux = $resultat->fetchAll();
            return $tbJeux;
        } catch (PDOException $e) {
            die('<div class = "erreur">Erreur dans la requête !<p>' . $e->getmessage() . '</p></div>');
        }
    }

    //==============================================================================
    //
    // METHODES POUR LA GESTION DES MEMBRES
    //
    //==============================================================================
        /**
        * Retourne l'identifiant, le nom et le prénom de l'utilisateur correspondant au compte et mdp
        *
        * @param string $compte le compte de l'utilisateur
        * @param string $mdp le mot de passe de l'utilisateur
        * @return object l'objet ou null si ce membre n'existe pas
        */
        public function getUnMembre(string $loginMembre, string $mdpMembre): ?object {
            try {
                $requete_prepare = PdoAgora::$monPdo->prepare(
                    'SELECT idMembre, prenomMembre, nomMembre, mdpMembre, selMembre
                        FROM membre
                        WHERE loginMembre = :leLoginMembre');
                $requete_prepare->bindValue(':leLoginMembre', $loginMembre, PDO::PARAM_STR);
                $requete_prepare->execute();
    
                if($utilisateur = $requete_prepare->fetch()){
                    $mdpHash = hash('SHA512', $mdpMembre . $utilisateur->selMembre);
                if($mdpHash == $utilisateur->mdpMembre) {
                    return $utilisateur;
                } else {
                    return null;
                    }
                }
            }   
            catch (Exception $e) {
                    die('<div class="erreur">Erreur dans la requête !<p>'
                    .$e->getMessage().'</p></div');
            }
        }   

        /**
     * Retourne l'identifiant et le nom complet de toutes les personnes sous forme d'un tableau d'objets
     *
     * @return array
     */
    public function getLesMembres()
    {
        $requete = 'SELECT idMembre as identifiant, CONCAT(prenomMembre, " ", nomMembre) AS libelle
                    FROM membre
                    ORDER BY nomMembre';
        try {
            $resultat = PdoAgora::$monPdo->query($requete);
            $tbPersonnes = $resultat->fetchAll();
            return $tbPersonnes;
        } catch (PDOException $e) {
            die('<div class = "erreur">Erreur dans la requête !<p>'
                . $e->getmessage() . '</p></div>');
        }
    }
    }
?>

