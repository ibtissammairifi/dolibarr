<?php
/* Copyright (C) 2001-2007 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2005      Brice Davoleau       <brice.davoleau@gmail.com>
 * Copyright (C) 2005-2012 Regis Houssin        <regis.houssin@capnetworks.com>
 * Copyright (C) 2006-2015 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2007      Patrick Raguin       <patrick.raguin@gmail.com>
 * Copyright (C) 2010      Juanjo Menent        <jmenent@2byte.es>
 * Copyright (C) 2015      Marcos García        <marcosgdf@gmail.com>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 *  \file       htdocs/product/agenda.php
 *  \ingroup    product
 *  \brief      Page of product events
 */

require '../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/product.lib.php';
require_once DOL_DOCUMENT_ROOT.'/product/class/product.class.php';
require_once DOL_DOCUMENT_ROOT.'/societe/class/societe.class.php';
require_once DOL_DOCUMENT_ROOT.'/expedition/class/expedition.class.php';
require_once DOL_DOCUMENT_ROOT.'/commande/class/commande.class.php';

$langs->load("companies");

$id = GETPOST('id', 'int');
$ref = GETPOST('ref', 'alpha');
$action = GETPOST('action','aZ09');


// Security check
$fieldvalue = (! empty($id) ? $id : (! empty($ref) ? $ref : ''));
$fieldtype = (! empty($ref) ? 'ref' : 'rowid');
if ($user->societe_id) $socid=$user->societe_id;
$result=restrictedArea($user,'produit|service',$fieldvalue,'product&product','','',$fieldtype);

$object = new Product($db);
if ($id > 0 || ! empty($ref)) $object->fetch($id, $ref);


$permissionnote=$user->rights->produit->creer;  // Used by the include of actions_setnotes.inc.php

/*
 * Actions
 */



/*
 *  View
 */

$form = new Form($db);

$helpurl='';


llxHeader('', $title, $help_url);

if ($id > 0 || ! empty($ref))
{
    /*
     * Affichage onglets
     */
    if (! empty($conf->notification->enabled)) $langs->load("mails");

    $head = product_prepare_head($object);
    $titre=$langs->trans("CardProduct".$object->type);
    $picto=($object->type==Product::TYPE_SERVICE?'service':'product');

    dol_fiche_head($head, 'mouvement', $titre, -1, $picto);

    $linkback = '<a href="'.DOL_URL_ROOT.'/product/list.php?restore_lastsearch_values=1">'.$langs->trans("BackToList").'</a>';
    $object->next_prev_filter=" fk_product_type = ".$object->type;

    $shownav = 1;
    if ($user->societe_id && ! in_array('product', explode(',',$conf->global->MAIN_MODULES_FOR_EXTERNAL))) $shownav=0;

    dol_banner_tab($object, 'ref', $linkback, $shownav, 'ref');

    $cssclass='titlefield';
    //if ($action == 'editnote_public') $cssclass='titlefieldcreate';
    //if ($action == 'editnote_private') $cssclass='titlefieldcreate';

    //print '<div class="fichecenter">';

    print '<div class="underbanner clearboth"></div>';


    dol_fiche_end();
}



 if ($user->rights->expedition->lire) {


            $sql="SELECT llx_societe.nom, llx_societe.rowid, llx_commande_fournisseur.rowid, llx_commande_fournisseur.date_creation, llx_commande_fournisseurdet.qty, llx_entrepot.ref, llx_commande_fournisseurdet.subprice, llx_societe.name_alias
                FROM (llx_entrepot INNER JOIN llx_commande_fournisseur_dispatch ON llx_entrepot.rowid = llx_commande_fournisseur_dispatch.fk_entrepot) INNER JOIN ((llx_commande_fournisseur INNER JOIN llx_commande_fournisseurdet ON llx_commande_fournisseur.rowid = llx_commande_fournisseurdet.fk_commande) INNER JOIN llx_societe ON llx_commande_fournisseur.fk_soc = llx_societe.rowid) ON llx_commande_fournisseur_dispatch.fk_commandefourndet = llx_commande_fournisseurdet.rowid";
            $sql.=" WHERE llx_commande_fournisseurdet.fk_product=".$id;

                        // Thirdparty

            print_barre_liste($langs->trans("Réceptions"), 0, $_SERVER["PHP_SELF"], '', '', '', '', $num, $num, 'title_accountancy.png');

           // print '<tr>';
            print '<form name="actualiser"  action"' . $_SERVER["PHP_SELF"] .'" method="POST">';
            print '<td class="fieldrequired">' . $langs->trans('Fournisseur') . '</td>';
            if ($socid > 0) {
                print $societe->getNomUrl(1);
                print '<input type="hidden" name="socid1" value="'.$socid.'">';
            } else {
                print '<td>';
                print $form->select_company((empty($socid)?'':$socid), 'socid1', 's.fournisseur = 1', 'SelectThirdParty', 0, 0, null, 0, 'minwidth300');
                print '<input type="submit" class="button" value="actualiser"';
                print '</td>';
                print '<td class="nowrap"></td>';
                    }
                print '</form>';

                

                if (isset($_POST['socid1'])) {
                // Prepare SQL Request
                $sql.=" AND llx_societe.rowid =".$_POST['socid1'];
                }
                

            
        

            print '<div class="div-table-responsive">';
            print '<table class="noborder" width="100%">';
            print '<tr class="liste_titre">';
            print '<td align="center" width="10px">' . $langs->trans("Réception") . '</td>';        
            print '<td align="center" width="10%">' . $langs->trans("N° de Réception") . '</td>';                        
            print '<td align="center" width="10%">' . $langs->trans("Date de Réception") . '</td>';            
            print '<td align="center" width="20%">' . $langs->trans("Nom Fournisseur") . '</td>';            
            print '<td align="centre" width="10%">' . $langs->trans("Qté") . '</td>';
            print '<td align="centre" width="10%">' . $langs->trans("Prix Unitaire") . '</td>';
            print '<td align="centre" >' . $langs->trans("Entrepot") . '</td>';
            print '</tr>';

            $result = $db->query($sql);
            if ($result) {
                $num = $db->num_rows($result); 
                if (! $num)
                    {
                        $db->free($result);
                        $result = $db->query($sql);
                        $num = $db->num_rows($result);
                    }

                    if ($num > 0)
                    {
                        $i = 0;
                        while ($i < $num)
                        {
                $objp = $db->fetch_object($result);

                print '<tr class="oddeven">';

                print '<td>' . $langs->trans("Réception") . '</td>';

                //num expedition
                print '<td align="center">' . $objp->rowid . "</td>";

                // Date expedition
                print '<td align="center">' . dol_print_date($db->jdate($objp->date_creation), "day") . "</td>";

                // nom client
                print '<td align="center">' . $objp->nom ." ".$objp->name_alias. "</td>";
                
                // quantity
                print '<td align="centre">' . $objp->qty. "</td>";

                // P U
                print '<td align="centre">' . number_format($objp->subprice,2). "</td>";

                // entrepot
                print '<td align="centre">' . $objp->ref. "</td>";
                
                print "</tr>\n";
                $i++;
                        }

            print "</table>";
            print '</div>';
            print "<br>";
        }

        if ($user->rights->expedition->lire) {



            $sql = "SELECT llx_expedition.rowid, llx_expedition.date_creation, llx_societe.rowid, llx_societe.nom, llx_expeditiondet.qty, llx_commandedet.price, llx_commandedet.fk_product, llx_expeditiondet.fk_origin_line, llx_entrepot.ref, llx_societe.name_alias
                FROM llx_entrepot INNER JOIN (((llx_expedition INNER JOIN llx_expeditiondet ON llx_expedition.rowid = llx_expeditiondet.fk_expedition) INNER JOIN llx_commandedet ON llx_expeditiondet.fk_origin_line = llx_commandedet.rowid) INNER JOIN llx_societe ON llx_expedition.fk_soc = llx_societe.rowid) ON llx_entrepot.rowid = llx_expeditiondet.fk_entrepot";
            $sql.=" WHERE llx_commandedet.fk_product=".$id;

            // Thirdparty
            print_barre_liste($langs->trans("Livraisons"), 0, $_SERVER["PHP_SELF"], '', '', '', '', $num, $num, 'title_accountancy.png');

           // print '<tr>';
            print '<form name="actualiser"  action"' . $_SERVER["PHP_SELF"] .'" method="POST">';
            print '<td class="fieldrequired">' . $langs->trans('Customer') . '</td>';
            if ($socid > 0) {
                print '<td>';
                print $soc->getNomUrl(1);
                print '<input type="hidden" name="socid" value="' . $soc->id . '">';
                print '</td>';
            } else {
                print '<td>';
                print $form->select_company('', 'socid', '(s.client = 1 OR s.client = 3)', 'SelectThirdParty', 0, 0, null, 0, 'minwidth300');

        //periode **************************************
        
           /* print '&nbsp';
            print '<tr><td class="fieldrequired">' . $langs->trans('Du : ') . '</td><td colspan="2">';
            $du=explode('/', $periode_du);
            print $form->select_date($duv, 'periode_du', '', '', '', "add", 1, 1, 1);
            print '</td>';
        

            print '&nbsp';
            print '<tr><td class="fieldrequired">' . $langs->trans('  Au : ') . '</td><td colspan="2">';
            $au=explode('/', $periode_au);
            print $form->select_date($auv, 'periode_au', '', '', '', "add", 1, 1, 1);
            print '</td>';*/
        
        //periode                  
                print '<input type="submit" class="button" value="actualiser"';
                print '</td>';
                print '<td class="nowrap"></td>';
                    }
                print '</form>';


            if (empty($soc->id))
            if (isset($_POST['socid'])) {
            // Prepare SQL Request
            $sql.=" AND llx_societe.rowid =".$_POST['socid'];
            }

            /*if (empty($du)){
                if (isset($_POST['periode_du'])) {
            // Prepare SQL Request
                $du=explode('/', $periode_du);
                $duv=$du[2].'-'.$du[1].'-'.$du[0];
                $sql.= " AND llx_expedition.date_creation >= ".$_POST['periode_du'];
            }

            }


            if (empty($au)){
                if (isset($_POST['periode_au'])) {
                // Prepare SQL Request
            $au=explode('/', $periode_au);
            $auv=$au[2].'-'.$au[1].'-'.$au[0];
            $sql.= " AND llx_expedition.date_creation <= ".$_POST['periode_du'];;

            }

            }*/

          // print '</tr>' . "\n";

            // thirdparty              

            
            print '<div class="div-table-responsive">';
            print '<tr class="liste_titre_filter">';
            print '<table class="noborder" width="100%">';           
            print '<tr class="liste_titre">';
            print '<td  align="center" width="10px">' . $langs->trans("Livraison") . '</td>';        
            print '<td align="center"  width="10%">' . $langs->trans("N° d'Expédition") . '</td>';                       
            print '<td align="center" width="10%">' . $langs->trans("Date d'Expédition") . '</td>';            
            print '<td align="center" width="20%">' . $langs->trans("Nom Client") . '</td>';          
            print '<td align="centre" width="10%">' . $langs->trans("Qté") . '</td>';
            print '<td align="centre" width="10%">' . $langs->trans("Prix Unitaire") . '</td>';
            print '<td align="centre" >' . $langs->trans("Entrepot") . '</td>';

            $result = $db->query($sql);
            if ($result) {
                $num = $db->num_rows($result); 
                if (! $num)
                    {
                        $db->free($result);
                        $result = $db->query($sql);
                        $num = $db->num_rows($result);
                    }
                if ($num > 0){   
            $i = 0;
            while ($i < $num)
            {
                $objp = $db->fetch_object($result);

                print '<tr class="oddeven">';

                print '<td>' . $langs->trans("Mouvement") . '</td>';

                //num expedition
                print '<td align="center">' . $objp->rowid . "</td>";

                // Date expedition
                print '<td align="center">' . dol_print_date($db->jdate($objp->date_creation), "day") . "</td>";

                // nom client
                print '<td align="center">' . $objp->nom ." ".$objp->name_alias. "</td>";
                
                // quantity
                print '<td align="centre">' . $objp->qty. "</td>";

                // P U
                print '<td align="centre">' . number_format($objp->price,2). "</td>";

                // entrepot
                print '<td align="centre">' . $objp->ref. "</td>";
                
                print "</tr>\n";
                $i++;
            }

            print "</table>";
            print '</div>';
            print "<br>";
        }
        }  
        }
        print '</div>';
        } else {
                dol_print_error($db);
            }
                $db->free($result);
        }

print '</td></tr>';
print '</table></div>';