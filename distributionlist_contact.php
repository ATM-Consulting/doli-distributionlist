<?php
/* Copyright (C) 2007-2017 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) ---Put here your own copyright and developer email---
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
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

/**
 *  \file       distributionlist_note.php
 *  \ingroup    distributionlist
 *  \brief      Car with notes on DistributionList
 */

// Load Dolibarr environment
$res = 0;
// Try main.inc.php into web root known defined into CONTEXT_DOCUMENT_ROOT (not always defined)
if (!$res && !empty($_SERVER["CONTEXT_DOCUMENT_ROOT"])) $res = @include $_SERVER["CONTEXT_DOCUMENT_ROOT"]."/main.inc.php";
// Try main.inc.php into web root detected using web root calculated from SCRIPT_FILENAME
$tmp = empty($_SERVER['SCRIPT_FILENAME']) ? '' : $_SERVER['SCRIPT_FILENAME']; $tmp2 = realpath(__FILE__); $i = strlen($tmp) - 1; $j = strlen($tmp2) - 1;
while ($i > 0 && $j > 0 && isset($tmp[$i]) && isset($tmp2[$j]) && $tmp[$i] == $tmp2[$j]) { $i--; $j--; }
if (!$res && $i > 0 && file_exists(substr($tmp, 0, ($i + 1))."/main.inc.php")) $res = @include substr($tmp, 0, ($i + 1))."/main.inc.php";
if (!$res && $i > 0 && file_exists(dirname(substr($tmp, 0, ($i + 1)))."/main.inc.php")) $res = @include dirname(substr($tmp, 0, ($i + 1)))."/main.inc.php";
// Try main.inc.php using relative path
if (!$res && file_exists("../main.inc.php")) $res = @include "../main.inc.php";
if (!$res && file_exists("../../main.inc.php")) $res = @include "../../main.inc.php";
if (!$res && file_exists("../../../main.inc.php")) $res = @include "../../../main.inc.php";
if (!$res) die("Include of main fails");

dol_include_once('/distributionlist/class/distributionlist.class.php');
dol_include_once('/distributionlist/class/distributionlistsocpeople.class.php');
dol_include_once('/distributionlist/lib/distributionlist_distributionlist.lib.php');
require_once DOL_DOCUMENT_ROOT.'/core/class/html.form.class.php';


// Load translation files required by the page
$langs->loadLangs(array("distributionlist@distributionlist", "companies"));

// Get parameters
$id = GETPOST('id', 'int');
$ref        = GETPOST('ref', 'alpha');
$action = GETPOST('action', 'alpha');
$massaction = GETPOST('massaction', 'alpha');
$cancel     = GETPOST('cancel', 'aZ09');
$backtopage = GETPOST('backtopage', 'alpha');
$contacts = GETPOST('toselect');

// Initialize technical objects
$object = new DistributionList($db);
$extrafields = new ExtraFields($db);
$diroutputmassaction = $conf->distributionlist->dir_output.'/temp/massgeneration/'.$user->id;
$hookmanager->initHooks(array('distributionlistnote', 'globalcard')); // Note that conf->hooks_modules contains array
// Fetch optionals attributes and labels
$extrafields->fetch_name_optionals_label($object->table_element);

// Security check - Protection if external user
//if ($user->socid > 0) accessforbidden();
//if ($user->socid > 0) $socid = $user->socid;
//$result = restrictedArea($user, 'distributionlist', $id);

// Load object
include DOL_DOCUMENT_ROOT.'/core/actions_fetchobject.inc.php'; // Must be include, not include_once  // Must be include, not include_once. Include fetch and fetch_thirdparty but not fetch_optionals
if ($id > 0 || !empty($ref)) $upload_dir = $conf->distributionlist->multidir_output[$object->entity]."/".$object->id;

$permissionnote = $user->rights->distributionlist->distributionlist->write; // Used by the include of actions_setnotes.inc.php
$permissiontoadd = $user->rights->distributionlist->distributionlist->write; // Used by the include of actions_addupdatedelete.inc.php



/*
 * Actions
 */

//include DOL_DOCUMENT_ROOT.'/core/actions_changeselectedfields.inc.php';

// Si la liste est clôturée, on renvoie vers l'onlet fiche
if($object->status > 1) header('Location: '.dol_buildpath('/distributionlist/distributionlist_card.php', 1).'?id='.$object->id);

if($massaction === 'add_contacts') {

	if(!empty($contacts)) {
		foreach ($contacts as $id_contact) {
			$o = new DistributionListSocpeople($db);
			$o->fk_socpeople = $id_contact;
			$o->fk_distributionlist = $id;
			$o->create($user);
		}
	}

}

/*
 * View
 */

$form = new Form($db);

$help_url = '';
llxHeader('', $langs->trans('DistributionList'), $help_url);

?>

	<script type="text/javascript" language="javascript">
		$(document).ready(function() {
			$.ajax({
				url:"<?php print dol_buildpath('/contact/list.php', 2).'?origin_page=distributionlist_contact&'.http_build_query($_REQUEST); ?>"
			}).done(function(data) {

				// On remplace les liens de la pagination pour rester sur la liste de diffusion en cas de changement de page
				var contacts_list = $(data).find('div.fiche');
				contacts_list.find('table.table-fiche-title a').each(function() {
					$(this).attr('href', $(this).attr('href').replace("<?php print dol_buildpath('/contact/list.php', 1); ?>", "<?php print dol_buildpath('/distributionlist/distributionlist_contact.php', 1); ?>"));
					$(this).attr('href', $(this).attr('href') + '&id=' + <?php print $id; ?>);
				});

				// On remplace les liens de tri pour rester sur la liste de diffusion en cas de tri sur une colonne
				contacts_list.find('table.liste a').each(function() {
					$(this).attr('href', $(this).attr('href').replace("<?php print dol_buildpath('/contact/list.php', 1); ?>", "<?php print dol_buildpath('/distributionlist/distributionlist_contact.php', 1); ?>"));
					$(this).attr('href', $(this).attr('href') + '&id=' + <?php print $id; ?>);
				});

				// Formulaire
				var form = contacts_list.find('form[name="formfilter"]');
				form.attr('action', contacts_list.find('form[name="formfilter"]').attr('action').replace("<?php print dol_buildpath('/contact/list.php', 1); ?>", "<?php print dol_buildpath('/distributionlist/distributionlist_contact.php', 1); ?>"));
				form.attr('action', form.attr('action') + '?id=' + <?php print $id; ?>);

				// On retire le lien de création de contact (à cet endroit on n'en veut pas)
				form.find(form.find('a[href*="create"]')).parent('li').hide();

				// On affiche la liste des contacts
				$("#inclusion").append(contacts_list);

				// Copie d'un bout de code dans /core/js/lib_foot.js.php car impossible de l'utiliser sinon
				<?php include dol_buildpath('/distributionlist/js/distributionlist.js'); ?>

			});
		});
	</script>

<?php

if ($id > 0 || !empty($ref))
{
	$object->fetch_thirdparty();

	$head = distributionlistPrepareHead($object);

	dol_fiche_head($head, 'contact', $langs->trans("DistributionList"), -1, $object->picto);

	// Object card
	// ------------------------------------------------------------
	$linkback = '<a href="'.dol_buildpath('/distributionlist/distributionlist_list.php', 1).'?restore_lastsearch_values=1'.(!empty($socid) ? '&socid='.$socid : '').'">'.$langs->trans("BackToList").'</a>';

	$morehtmlref = '<div class="refidno">';
	/*
	 // Ref customer
	 $morehtmlref.=$form->editfieldkey("RefCustomer", 'ref_client', $object->ref_client, $object, 0, 'string', '', 0, 1);
	 $morehtmlref.=$form->editfieldval("RefCustomer", 'ref_client', $object->ref_client, $object, 0, 'string', '', null, null, '', 1);
	 // Thirdparty
	 $morehtmlref.='<br>'.$langs->trans('ThirdParty') . ' : ' . (is_object($object->thirdparty) ? $object->thirdparty->getNomUrl(1) : '');
	 // Project
	 if (! empty($conf->projet->enabled))
	 {
	 $langs->load("projects");
	 $morehtmlref.='<br>'.$langs->trans('Project') . ' ';
	 if ($permissiontoadd)
	 {
	 if ($action != 'classify')
	 //$morehtmlref.='<a class="editfielda" href="' . $_SERVER['PHP_SELF'] . '?action=classify&amp;id=' . $object->id . '">' . img_edit($langs->transnoentitiesnoconv('SetProject')) . '</a> : ';
	 $morehtmlref.=' : ';
	 if ($action == 'classify') {
	 //$morehtmlref.=$form->form_project($_SERVER['PHP_SELF'] . '?id=' . $object->id, $object->socid, $object->fk_project, 'projectid', 0, 0, 1, 1);
	 $morehtmlref.='<form method="post" action="'.$_SERVER['PHP_SELF'].'?id='.$object->id.'">';
	 $morehtmlref.='<input type="hidden" name="action" value="classin">';
	 $morehtmlref.='<input type="hidden" name="token" value="'.newToken().'">';
	 $morehtmlref.=$formproject->select_projects($object->socid, $object->fk_project, 'projectid', $maxlength, 0, 1, 0, 1, 0, 0, '', 1);
	 $morehtmlref.='<input type="submit" class="button valignmiddle" value="'.$langs->trans("Modify").'">';
	 $morehtmlref.='</form>';
	 } else {
	 $morehtmlref.=$form->form_project($_SERVER['PHP_SELF'] . '?id=' . $object->id, $object->socid, $object->fk_project, 'none', 0, 0, 0, 1);
	 }
	 } else {
	 if (! empty($object->fk_project)) {
	 $proj = new Project($db);
	 $proj->fetch($object->fk_project);
	 $morehtmlref .= ': '.$proj->getNomUrl();
	 } else {
	 $morehtmlref .= '';
	 }
	 }
	 }*/
	 $morehtmlref .= '</div>';


	dol_banner_tab($object, 'ref', $linkback, 1, 'ref', 'ref', $morehtmlref);


	print '<div class="fichecenter">';
	print '<div class="underbanner clearboth"></div>';


	$cssclass = "titlefield";

	//print '<form name="add_contact" method="post" action="'.$_SERVER['PHP_SELF'].'?id='.$id.'">';
	print '<br /><div id="inclusion"></div>';
	//print '</form>';

	print '</div>';

	dol_fiche_end();
}

// End of page
llxFooter();
$db->close();
