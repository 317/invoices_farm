<?php
session_start();
include_once(realpath(dirname(__FILE__)) . "/Invoices_to_pdf.class.php");
$itp = new Invoices_to_pdf();
if(isset($_POST['select_month']) && isset($_POST['select_template'])){
	// $itp =Invoices_to_pdf::instance();
	$date_fact = explode("-", $_POST['select_month']);
	$year = $date_fact[0];
	$month = $date_fact[1];
	$template = $_POST['select_template'];
	$itp->get_commandes_ref("month", array("month"=>($month), "year"=>($year), "template"=>$template));
}
if(isset($_GET["rechmod"])){
	$itp->rechmod();
}
//Récupérer tout l'historique des commandes
$months = $itp->get_list_of_months_with_invoices();
$templates = $itp->get_list_of_templates();
?>
<link href="styles.css" rel="stylesheet" type="text/css">
<style>
	<?php include("style.css") ?>
</style>
<div class="invoice_to_pdf_container">
		<div class="message_OK" style="display:none">Génération réussie !</div>
	<div class="titre" style="display:none">Génération des Factures</div>
	<form method="post" action="">
		<input type="hidden" name="download_pdf" value="true" />
		<label for="select_month">Choix du mois</label><br />
		<select name="select_month" size=10>
		<?php
			foreach($months as $month){
				echo '<option value="'.$month["value"].'">'.$month["display_select"].'</option>'.PHP_EOL;	
			}
		?>
		</select>
		<br />
		<label for="select_template">Choix du template</label><br />
		<select name="select_template" size=10>
		<?php
			foreach($templates as $template){
				echo '<option value="'.$template["value"].'">'.$template["display_select"].'</option>'.PHP_EOL;	
			}
		?>
		</select>
		<br />
		<input type="submit" value="Générer les PDF" />
	</form>
</div>


