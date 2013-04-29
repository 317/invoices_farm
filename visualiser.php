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
    if( ! ((isset($_SESSION['navig']) && $_SESSION['navig']->connecte && $_SESSION['navig']->client->id == $commande->client && $commande->facture != "") || (isset($_SESSION["util"]) && est_autorise("acces_commandes")))) exit;
	
	$date_array = explode("-", $commande->date);
	$ref = $commande->ref;
	$path = realpath(dirname(__FILE__)) . "/../../generated_pdf/".$date_array[0]."-".$date_array[1]."/".$ref.".pdf";
	
	if(!file_exists($path)) {
		header("Location: /client/pdf/facture.php?ref=".$ref);
		exit;
	}
	
	header("Content-type: application/pdf");
	header("Content-disposition: inline; filename=facture.pdf");
	
	@readfile($path);
?>