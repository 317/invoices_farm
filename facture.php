<?php
/*************************************************************************************/
/*                                                                                   */
/*      Thelia	                                                            		 */
/*                                                                                   */
/*      Copyright (c) Octolys Development		                                     */
/*		email : thelia@octolys.fr		        	                             	 */
/*      web : http://www.octolys.fr						   							 */
/*                                                                                   */
/*      This program is free software; you can redistribute it and/or modify         */
/*      it under the terms of the GNU General Public License as published by         */
/*      the Free Software Foundation; either version 2 of the License, or            */
/*      (at your option) any later version.                                          */
/*                                                                                   */
/*      This program is distributed in the hope that it will be useful,              */
/*      but WITHOUT ANY WARRANTY; without even the implied warranty of               */
/*      MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the                */
/*      GNU General Public License for more details.                                 */
/*                                                                                   */
/*      You should have received a copy of the GNU General Public License            */
/*      along with this program; if not, write to the Free Software                  */
/*      Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA    */
/*                                                                                   */
/*************************************************************************************/
?>
<?php
	error_reporting(E_ALL ^ E_NOTICE);

	include_once(realpath(dirname(__FILE__)) . "/../../../classes/Navigation.class.php");
	include_once(realpath(dirname(__FILE__)) . "/../../../classes/Administrateur.class.php");

	session_start();

	$commande = new Commande();
	$commande->charger_ref($_GET['ref']);

	// Si un client est identifié mais n'est pas celui qui a commandé ou que la commande n'est pas payée
	// ou qu'un admin identifié n'est pas autorisé
    // if( ! ((isset($_SESSION['navig']) && $_SESSION['navig']->connecte && $_SESSION['navig']->client->id == $commande->client && $commande->facture != "") || (isset($_SESSION["util"]) && est_autorise("acces_commandes")))) exit;

	// Compatibilité 1.4 -> On utilise le modèle PDF si il existe
	if (file_exists(realpath(dirname(__FILE__)).'/modeles/facture.php'))
	{
		include_once(realpath(dirname(__FILE__)) . "/../../classes/Commande.class.php");
		include_once(realpath(dirname(__FILE__)) . "/../../classes/Client.class.php");
		include_once(realpath(dirname(__FILE__)) . "/../../classes/Venteprod.class.php");
		include_once(realpath(dirname(__FILE__)) . "/../../classes/Produit.class.php");
		include_once(realpath(dirname(__FILE__)) . "/../../classes/Adresse.class.php");
		include_once(realpath(dirname(__FILE__)) . "/../../classes/Zone.class.php");
		include_once(realpath(dirname(__FILE__)) . "/../../classes/Pays.class.php");
		include_once(realpath(dirname(__FILE__)) . "/../../fonctions/divers.php");

	    $client = new Client();
	  	$client->charger_id($commande->client);

	  	$pays = new Pays();
	  	$pays->charger($client->pays);

	  	$zone = new Zone();
	  	$zone->charger($pays->zone);

		include_once(realpath(dirname(__FILE__)) . "/modeles/facture.php");

		$facture = new Facture();
		$facture->creer($_GET['ref']);

		exit();
	}

	// Le moteur ne sortira pas le contenu de $res
	$sortie = false;

	// Le fond est le template de facture.
	$reptpl = realpath(dirname(__FILE__)) . "/../../pdf/template/";
	if(!isset($_GET['template'])){
		$fond = "facture.html";
	}else{
		$fond = $_GET['template'].".html";
	}

	$lang = $commande->lang;

	// Compatibilité avec le moteur.
	$_REQUEST['commande'] = $_GET['ref'];

	require_once(realpath(dirname(__FILE__)) . "/../../../fonctions/moteur.php");

	require_once(realpath(dirname(__FILE__)) . "/../../../classes/Pdf.class.php");
	
	if(!isset($_GET['mode']) || $_GET['mode'] == "pdf")
		Pdf::instance()->generer($res, $_GET['ref'] . ".pdf");
	else
		echo $res; exit;
?>