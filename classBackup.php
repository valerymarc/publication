<?php

/**
* Permet d'effectuer la sauvegarde d'une base de données
*
**/


class BackupMysql
{
    private $db_charset; //encodage de la base UTF8

    private $db_hostname;
    private $db_database;
    private $db_username;
    private $db_password;
    private $db_port;

    private $nFileDuration; //Ancienneté des fichiers à conserver
    private $repertoire_sauvegarde; //Repertoire des sauvegardes
    //private $archive_GZIP;
    private $sqlfichier;

	/**
     *initialisation des variables
     * @param [type] $sDBServer       [description]
     * @param [type] $sDBName         [description]
     * @param [type] $sDBUsername     [description]
     * @param [type] $sDBPassword     [description]
     * @param string $sDBPort         [description]
     * @param Integer $sFileDuration  [description]
     * @param string $sRepSave        [description]
     * @param [type] $sNameZip        [description]
     */
  
	function  __construct($sDBServer, $sDBName, $sDBUsername, $sDBPassword,
                           $sDBCharset = 'utf8', $sRepSave='', $sNameZip = '', $sDBPort = '')
    {
        $this->db_charset = $sDBCharset;
        $this->db_hostname = $sDBServer;
        $this->db_database = $sDBName;
        $this->db_username = $sDBUsername;
        $this->db_password = $sDBPassword;
        $this->db_port = $sDBPort;

        $this->repertoire_sauvegarde = $sRepSave;
        //$this->archive_GZIP = $sNameZip.date('Y-m-d_H-i-s').".gz";
        $this->sqlfichier = $sNameZip.date('Y-m-d_H-i-s').".sql";
		
    }


    /**
     * Suppression des anciennes sauvegardes
     * @param integer $sFileDuration : 3600s = 1h ---> 90 jours = 7776000
     * @return [type]                [description]
     */

    public function deleteOldFile($nDuration=60)
    {
      $this->nFileDuration  = $nDuration;
      echo "<br />Liste des fichiers du répertoire : ".$this->repertoire_sauvegarde;


      //Lister les fichiers presents dans le repertoire
        foreach(glob( $this->repertoire_sauvegarde."*") as $file)
        {
            echo "<br />".$file;
            if( filemtime($file) <= (time() - $this->nFileDuration)){
                unlink($file); //Supprime les vieux fichiers
            }


        }


        echo "<br/><br/> Suppression des anciens fichiers effectuée";
    }

    /**
     * Effectue la sauvegarde de labase de données dans un fichier gzip
     */

    public function setBackupMySQL()
    {
        //Verifier la creation du dossier de sauvegarde
        if( is_dir($this->repertoire_sauvegarde) === FALSE)
        {
            //0700 repertoire non visible par les visiteurs
            if(mkdir($this->repertoire_sauvegarde, 0700) === FALSE ){
                exit("<br/><br/>Impossible de creer le repertoirepour la sauvegarde mysql!!!");
            }
        }

        echo "<br/>Fin de la configurtion mysql";

        /**
         *  EXECUTION DE LA COMMANDE MYSQL DUMP
         */

        $commande =  'mysqldump';
        $commande .= ' --host='.$this->db_hostname;
        $commande .= ' --port='.$this->db_port;
        $commande .= ' --user='.$this->db_username;
        $commande .= ' --password='.$this->db_password;
       /* $commande .= ' --skip-opt';
        $commande .= ' --compress';
        $commande .= ' --addlocks';
        $commande .= ' --create-options';
        $commande .= ' --disable-keys';
        $commande .= ' --quote-names';
        $commande .= ' --quick';
        $commande .= ' --extended-insert';
        $commande .= ' --default-character-set='.$this->db_charset;
        $commande .= ' --compatible=mysql40'; */
        $commande .= ' '.$this->db_database;
        //$commande .= ' | gzip -c > '.$this->repertoire_sauvegarde.$this->archive_GZIP;
        $commande .= ' > '.$this->repertoire_sauvegarde.$this->sqlfichier;

        system($commande);

       //echo "Sauvegarde terminée pour le fichier : ".$this->archive_GZIP;
       echo "Sauvegarde terminée pour le fichier : ".$this->sqlfichier;
    }

}