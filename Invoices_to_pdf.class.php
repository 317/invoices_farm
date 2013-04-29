<?php

/**
 TODO :
 - Rendre dynamique les divers liens OK
 - modifier facture.php pour qu'il fasse appel aux ressources contenues dans le dossier pdf OK
 - faire la boucle de création de factures OK
 - La génération des factures précédentes ne doit s'occuper que des factures pas encore générées (en cas de timeout)
 - appeler la génération de facture lors d'un passage de facture en payé OK

 - EXTRA-MILE : penser un système de téléchargement de packs de factures

**/
include_once(realpath(dirname(__FILE__)) . "/../../../classes/PluginsClassiques.class.php");
class Invoices_to_pdf extends PluginsClassiques
{
	public $pdf_folder;
    public function __construct()
    {
		// echo "ok";
        parent::__construct("invoices_to_pdf");
		
    }
	
	/**
	 * Ici on va créer le dossier des pdf et tous les PDF manquants
	 */
	public function init()
	{
		//Création du dossier s'il n'existe pas
		$this->pdf_folder = realpath(dirname(__FILE__))."/../../generated_pdf";
		if(!is_dir($this->pdf_folder)){
			mkdir($this->pdf_folder);
		}
	}

	
	/**
	 * Creation du pdf lors du paiment de la commande
	 */
	public function statut(Commande $commande, $ancienStatut){
		if($ancienStatut == 1 && $commande->statut == 2){
			// Faire le path
			$date_fact = explode("-", $commande->date);
			$path = realpath(dirname(__FILE__))."/../../generated_pdf/".$date_fact[0]."-".$date_fact[1];
			// Récupérer la référence de la facture
			$ref = $commande->ref;
			// Appeler fonction de génération
			$this->generate_pdf($ref, $ref, $path);
		}	
	}

	
	private function download_pdf($month, $year){
		// include_once(realpath(dirname(__FILE__)) . "/../../../classes/Commande.class.php");
		// echo "ok ok ok";
	}
	
	public function get_commandes_ref($type, $data){
		if($type == "month"){
			if(isset($data["template"])){
				$this->get_all_invoices_in_month($data["month"], $data["year"], $data["template"]);
			}else{
				$this->get_all_invoices_in_month($data["month"], $data["year"]);
			}
			return true;
		}
		
	}
	
	public function get_list_of_months_with_invoices(){
		$query = "SELECT * FROM `commande` WHERE statut != 1 AND statut != 5 AND date <= ALL(SELECT date from commande)";
		$rez = $this->query_liste($query) ;
		
		
		$first_date  = $rez[0]->date;
		$first_date = explode("-",$first_date);
		$year = $first_date[0];
		$month = $first_date[1];
		
		$now_year = date("Y");
		$now_month = date("n");

		$months = array();
		$das_month;
		
		for($year; $year < $now_year; $year++){
			// echo $year."/".$month."<br />";					
			for($month; $month <= 12 ; $month++){
				$das_month = array();
				$das_month["year"] = $year;
				$das_month["month"] = $month;
				$das_month["display_select"] = ($month<9?"0".$month:$month)."/".$year;
				$das_month["value"] = $year."-".($month<9?"0".$month:$month);
				$months[] = $das_month;
			}
			$month = 1;
			
		}
		for($month; $month <= $now_month  ; $month++){
			$das_month = array();
			$das_month["year"] = $year;
			$das_month["month"] = $month;
			$das_month["display_select"] = ($month<9?"0".$month:$month)."/".$year;
			$das_month["value"] = $year."-".($month<9?"0".$month:$month);
			$months[] = $das_month;			
		}
		
		return $months;	
	}
	
	public function get_list_of_templates(){
		$templates = array();
		$dir = realpath(dirname(__FILE__))."/../../pdf/template/";
		if (is_dir($dir)) {
			if ($dh = opendir($dir)) {
				while (($file = readdir($dh)) !== false) {				
					if(substr($file, -5) == ".html"){
						$das_template = array();
						$das_template["display_select"] = substr($file, 0, -5);
						$das_template["value"] = substr($file, 0, -5);
						$templates[] = $das_template;
					}
				}
				closedir($dh);
			}
		}
		// echo $path;
		return $templates;
	}
	
	private function get_all_invoices_in_month($month, $year, $template="facture"){
		//Génération de la date de début (le premier du mois)
		$debut = array();
		$debut["year"] = $year;
		$debut["month"] = $month;
		$debut["day"] = "01";
		
		//Génération de la date de fin (le premier du mois suivant)
		$fin = array();
		$fin["day"] = "01";
		$pathname = realpath(dirname(__FILE__))."/../../generated_pdf/".$year."-".$month;
		if($month == "12"){
			$fin["month"] = "01";
			$year = intval($year)+1;
		}else{	
			$month = intval($month) + 1;
			if($month < 10) $month = "0".$month;
			$fin["month"] =  $month;
		}
		$fin["year"] = $year;
		
		$this->get_all_invoices_per_date($debut, $fin, $pathname, $template);	
	}
	
	private function get_all_invoices_per_date($debut, $fin, $folder_name, $template="facture"){
		if(!is_dir($folder_name)){
			mkdir($folder_name);
			// chmod($folder_name, 0776);
		}

		$query = "SELECT * FROM commande WHERE statut != 1 AND statut != 5 AND date BETWEEN ".
		"'".$debut["year"]."-".$debut["month"]."-".$debut["day"]."'".
		" AND ".
		"'".$fin["year"]."-".$fin["month"]."-".$fin["day"]."';";
		
	
		foreach ($this->query_liste($query) as $result) {
			echo "<br />";
			$this->generate_pdf($result->ref, $result->ref, $folder_name, $template);
			
		}
		
		echo '<script>$(document).ready(function(){$(".message_OK").show();});</script>';
	
	}
	
	public function rechmod(){
		$pathname = realpath(dirname(__FILE__))."/../../generated_pdf/";
		chmod($pathname, 0776);
		$this->rechmod_r($pathname);
	}
	
	private function rechmod_r($path){
		
		if ($dh = opendir($path)) {
			while (($file = readdir($dh)) !== false) {					
				if ($file == '.' || $file == '..') { 
					continue; 
				} 
				if (is_dir($path."/".$file)) {
					chmod($path."/".$file, 0776);
					$this->rechmod_r($path."/".$file);
				}else{
					chmod($path."/".$file, 0664);
				}
			}
			closedir($dh);
		}
	}
	
	private function generate_pdf($ref, $filename, $path, $template="facture"){	
		// set_time_limit (30);
		include_once(realpath(dirname(__FILE__)) . "/../../../classes/Variable.class.php");
		$url = (Variable::lire('urlsite'));	
		if(file_exists($path."/".$filename.".pdf")){
			return true;			
		}
		echo "Facture $filename générée... <br />";
		flush();
		copy(			
			$url."/client/plugins/invoices_to_pdf/facture.php?ref=".$ref."&template=".$template,
			$path."/".$filename.".pdf"
		);
		// chmod($path."/".$filename.".pdf", 0664);
		
		return true;
	}
	
	
	
}
//Créer une pile de fausses commandes
/*
for($i=0; $i<10; $i++){
	if($i<10) $num = "00000".$i;
	else if($i<100) $num = "0000".$i;
	else if($i<1000) $num = "000".$i;
	else if($i<10000) $num = "00".$i;
	else if($i<100000) $num = "0".$i;
	else if($i<1000000) $num = $i;
	
	$commande = new Commande(2);
	$commande->id = null;
	
	$commande->ref = "C1304232054".$num;
	$commande->transaction = $num;
	$commande->facture = "1".$num;
	// $date = time() - rand(1, 69120000);
	// $date = time() - rand(1,     12960000);
	$date = time() - rand(604800,     604899);
	// echo date('Y-m-d', $date) ."\n";
	$commande->datelivraison = date('Y-m-d', $date);
	$commande->datefact = date('Y-m-d', $date);
	$commande->date = date('Y-m-d H:i:s', $date);
	$commande->port = rand(10,500);
	// echo 
	echo $commande->date;
	
	$commande->add();
	// var_dump($commande);
	echo "<br />";
}
*/
?>