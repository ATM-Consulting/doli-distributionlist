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
dol_include_once('/distributionlist/class/distributionlistsocpeoplefilter.class.php');
dol_include_once('/distributionlist/lib/distributionlist_distributionlist.lib.php');
require_once DOL_DOCUMENT_ROOT.'/core/class/html.form.class.php';

// Load translation files required by the page
$langs->loadLangs(array("distributionlist@distributionlist", "companies", "mails"));

// Get parameters
$id = GETPOST('id', 'int');
$filter_id = GETPOST('filter', 'int');
$ref        = GETPOST('ref', 'alpha');
$action = GETPOST('action', 'alpha');
$massaction = GETPOST('massaction', 'alpha');
$cancel     = GETPOST('cancel', 'aZ09');
$backtopage = GETPOST('backtopage', 'alpha');
$label = GETPOST('label', 'alpha');
$contacts = GETPOST('toselect');
$confirm = GETPOST('confirm', 'alpha');

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

$form = new Form($db);

/*
 * Actions
 */

//include DOL_DOCUMENT_ROOT.'/core/actions_changeselectedfields.inc.php';

// Si la liste est clôturée, on renvoie vers l'onlet fiche
if($object->status > 1 || empty($permissiontoadd)) {
	header('Location: '.dol_buildpath('/distributionlist/distributionlist_card.php', 1).'?id='.$object->id);
	exit;
}

// Suppression de la liste des contacts sélectionnés si existante pour ne pas remplir inutilement l'url lors de l'appel àa la liste standard des contacts (sinon bug)
$TParamURL = $_REQUEST;
unset($TParamURL['toselect']);
$TParamURL_HTTP_build_query = http_build_query($TParamURL);

if($action === 'add_filter') {

	// Create an array for form
	$formquestion = array(
		// 'text' => $langs->trans("ConfirmClone"),
		// array('type' => 'checkbox', 'name' => 'clone_content', 'label' => $langs->trans("CloneMainAttributes"), 'value' => 1),
		// array('type' => 'checkbox', 'name' => 'update_prices', 'label' => $langs->trans("PuttingPricesUpToDate"), 'value' => 1),
		array('type' => 'text', 'name' => 'label', 'label' => $langs->trans('AdvTgtOrCreateNewFilter'), 'value'=>$langs->trans('Filter').'&nbsp;'.date('d/m/Y H:i:s'))
	);
	$formconfirm = $form->formconfirm($_SERVER["PHP_SELF"].'?id='.$object->id.'&'.$TParamURL_HTTP_build_query, $langs->trans('AdvTgtCreateFilter'), '', 'confirm_add_filter', $formquestion, 'yes', 1);

} elseif ($action === 'confirm_add_filter' && $confirm === 'yes') {

	$filter = new DistributionListSocpeopleFilter($db);
	$filter->url_params = $TParamURL_HTTP_build_query;
	$filter->fk_distributionlist = $id;
	$filter->label = $label;
	$filter->create($user);
	// Pour éviter de réenregistrer le filtre en cas de réactualisation de la page
	header('Location: '.$_SERVER['PHP_SELF'].'?id='.$id.'&action=set_filter&filter='.$filter->id);
	exit;

} elseif($action === 'set_filter') {
	$f = new DistributionListSocpeopleFilter($db);
	$f->fetch($filter_id);
	$TParamURL_HTTP_build_query = $f->url_params;
}

// Ajout des contacts à la liste de diffusion
if($massaction === 'distributionlist_add_contacts') {

	if(!empty($contacts) && $object->status < DistributionList::STATUS_CLOSED) {
		$nb_add = 0;
		foreach ($contacts as $id_contact) {
			$o = new DistributionListSocpeople($db);
			$TRes = $o->fetchAll('', '', 0, 0, array('customsql'=>' fk_socpeople = '.$id_contact.' AND fk_distributionlist = '.GETPOST('id', 'int')));

			if(empty($TRes)) { // N'existe pas déjà dans la liste
				$o->fk_socpeople = $id_contact;
				$o->fk_distributionlist = $id;
				if($o->create($user) > 0) $nb_add++;
			}
		}
		if(!empty($nb_add)) {
			setEventMessage($langs->trans('DistributionListNbAddedContacts', $nb_add));
			$object->nb_contacts += $nb_add;
			$object->update($user);
		}
	}

}

/*
 * View
 */

$help_url = '';

llxHeader('', $langs->trans('DistributionList'), $help_url);

?>

	<script type="text/javascript" language="javascript">
		$(document).ready(function() {
			$.ajax({
				url:"<?php print dol_buildpath('/contact/list.php', 2).'?origin_page=distributionlist_contact&'.$TParamURL_HTTP_build_query; ?>"
			}).done(function(data) {

				// On remplace les liens de la pagination pour rester sur la liste de diffusion en cas de changement de page
				var form_contacts = $(data).find('div.fiche form[name="formfilter"]');
				form_contacts.find('table.table-fiche-title a').each(function() {
					$(this).attr('href', $(this).attr('href').replace("<?php print dol_buildpath('/contact/list.php', 1); ?>", "<?php print dol_buildpath('/distributionlist/distributionlist_contact.php', 1); ?>"));
					$(this).attr('href', $(this).attr('href') + '&id=' + <?php print $id; ?> + '&filter=' + <?php if(!empty($filter_id)) print $filter_id; else print 0; ?>);
				});

				// On remplace les liens de tri pour rester sur la liste de diffusion en cas de tri sur une colonne
				form_contacts.find('table.liste tr.liste_titre a').each(function() {
					$(this).attr('href', $(this).attr('href').replace("<?php print dol_buildpath('/contact/list.php', 1); ?>", "<?php print dol_buildpath('/distributionlist/distributionlist_contact.php', 1); ?>"));
					$(this).attr('href', $(this).attr('href') + '&id=' + <?php print $id; ?> + '&filter=' + <?php if(!empty($filter_id)) print $filter_id; else print 0; ?>);
				});

				// Formulaire
				form_contacts.attr('action', form_contacts.attr('action').replace("<?php print dol_buildpath('/contact/list.php', 1); ?>", "<?php print dol_buildpath('/distributionlist/distributionlist_contact.php', 1); ?>"));
				form_contacts.attr('action', form_contacts.attr('action') + '?id=' + <?php print $id; ?> + '&filter=' + <?php if(!empty($filter_id)) print $filter_id; else print 0; ?>);

				// On affiche la liste des contacts
				$("#inclusion").append(form_contacts);

			});
		});
	</script>

<?php

if ($id > 0 || !empty($ref)) {

	// Print form confirm
	print $formconfirm;

	$object->fetch_thirdparty();

	$head = distributionlistPrepareHead($object);

	dol_fiche_head($head, 'contact', $langs->trans("DistributionList"), -1, $object->picto);

	// Object card
	// ------------------------------------------------------------
	$linkback = '<a href="' . dol_buildpath('/distributionlist/distributionlist_list.php', 1) . '?restore_lastsearch_values=1' . (!empty($socid) ? '&socid=' . $socid : '') . '">' . $langs->trans("BackToList") . '</a>';

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
	print '<div class="underbanner clearboth"></div><br />';

	$filter = new DistributionListSocpeopleFilter($db);
	$TFilters = $filter->fetchAll('', '', 0, 0, array(), $filtermode = 'AND');

	print '<div class="tabsAction">';
	print '<form name="set_filter" method="POST" action="'.$_SERVER['PHP_SELF'].'?action=set_filter&id='.$id.'">';

	if(!empty($TFilters)) {

		$TFilterDisplay=array();
		foreach ($TFilters as $obj) $TFilterDisplay[$obj->id] = $obj->label;
		if(!empty(GETPOST('button_removefilter', 'alpha')) || !empty(GETPOST('button_removefilter.x', 'alpha')) || !empty(GETPOST('button_removefilter_x', 'alpha'))) {
			$default_val = '';
		} else $default_val = $filter_id;

		print Form::selectarray('filter', $TFilterDisplay, $default_val, 1);
		print '&emsp;<input class="butAction" type="SUBMIT" value="'.$langs->trans('AdvTgtLoadFilter').'"/>';

	}

	// Le preg_grep('/^search_/', array_keys($_REQUEST)) set à vérifier si le formulaire de recherche a été soumis
	// Si j'utilise un !empty(GETPOST('button_search') c'est pas bon car l'input n'est pas transmis en cas d'appui sur la touche "Entrée"
	if (count(preg_grep('/^search_/', array_keys($_REQUEST))) > 0
		&& empty(GETPOST('button_removefilter', 'alpha'))
		&& empty(GETPOST('button_removefilter.x', 'alpha'))
		&& empty(GETPOST('button_removefilter_x', 'alpha'))
		&& $action !== 'set_filter')	{ // All tests are required to be compatible with all browsers

		print '<a class="butAction" href="'.$_SERVER['PHP_SELF'].'?action=add_filter&'.$TParamURL_HTTP_build_query.'">'.$langs->trans('AdvTgtSaveFilter').'</a>';

	}

	print '</form>';
	print '</div>';

	$cssclass = "titlefield";

	//print '<form name="add_contact" method="post" action="'.$_SERVER['PHP_SELF'].'?id='.$id.'">';
	print '<div id="inclusion"></div>';
	//print '</form>';

	print '</div>';

	dol_fiche_end();
}

// End of page
llxFooter();
$db->close();
