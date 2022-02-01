<?php
/* Copyright (C) 2017 Laurent Destailleur  <eldy@users.sourceforge.net>
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
 *   	\file       distributionlist_card.php
 *		\ingroup    distributionlist
 *		\brief      Page to create/edit/view distributionlist
 */

//if (! defined('NOREQUIREDB'))              define('NOREQUIREDB','1');					// Do not create database handler $db
//if (! defined('NOREQUIREUSER'))            define('NOREQUIREUSER','1');				// Do not load object $user
//if (! defined('NOREQUIRESOC'))             define('NOREQUIRESOC','1');				// Do not load object $mysoc
//if (! defined('NOREQUIRETRAN'))            define('NOREQUIRETRAN','1');				// Do not load object $langs
//if (! defined('NOSCANGETFORINJECTION'))    define('NOSCANGETFORINJECTION','1');		// Do not check injection attack on GET parameters
//if (! defined('NOSCANPOSTFORINJECTION'))   define('NOSCANPOSTFORINJECTION','1');		// Do not check injection attack on POST parameters
//if (! defined('NOCSRFCHECK'))              define('NOCSRFCHECK','1');					// Do not check CSRF attack (test on referer + on token if option MAIN_SECURITY_CSRF_WITH_TOKEN is on).
//if (! defined('NOTOKENRENEWAL'))           define('NOTOKENRENEWAL','1');				// Do not roll the Anti CSRF token (used if MAIN_SECURITY_CSRF_WITH_TOKEN is on)
//if (! defined('NOSTYLECHECK'))             define('NOSTYLECHECK','1');				// Do not check style html tag into posted data
//if (! defined('NOREQUIREMENU'))            define('NOREQUIREMENU','1');				// If there is no need to load and show top and left menu
//if (! defined('NOREQUIREHTML'))            define('NOREQUIREHTML','1');				// If we don't need to load the html.form.class.php
//if (! defined('NOREQUIREAJAX'))            define('NOREQUIREAJAX','1');       	  	// Do not load ajax.lib.php library
//if (! defined("NOLOGIN"))                  define("NOLOGIN",'1');						// If this page is public (can be called outside logged session). This include the NOIPCHECK too.
//if (! defined('NOIPCHECK'))                define('NOIPCHECK','1');					// Do not check IP defined into conf $dolibarr_main_restrict_ip
//if (! defined("MAIN_LANG_DEFAULT"))        define('MAIN_LANG_DEFAULT','auto');					// Force lang to a particular value
//if (! defined("MAIN_AUTHENTICATION_MODE")) define('MAIN_AUTHENTICATION_MODE','aloginmodule');		// Force authentication handler
//if (! defined("NOREDIRECTBYMAINTOLOGIN"))  define('NOREDIRECTBYMAINTOLOGIN',1);		// The main.inc.php does not make a redirect if not logged, instead show simple error message
//if (! defined("FORCECSP"))                 define('FORCECSP','none');					// Disable all Content Security Policies


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

require_once DOL_DOCUMENT_ROOT.'/core/class/html.formcompany.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formprojet.class.php';
dol_include_once('/distributionlist/class/distributionlist.class.php');
dol_include_once('/distributionlist/class/tools_distributionlist.class.php');
dol_include_once('/distributionlist/class/distributionlistsocpeople.class.php');
dol_include_once('/distributionlist/lib/distributionlist_distributionlist.lib.php');

// Load translation files required by the page
$langs->loadLangs(array("distributionlist@distributionlist", "other", "mails", "products"));

// Get parameters
$id = GETPOST('id', 'int');
$ref        = GETPOST('ref', 'alpha');
$action = GETPOST('action', 'aZ09');
$confirm    = GETPOST('confirm', 'alpha');
$cancel     = GETPOST('cancel', 'aZ09');
$contextpage = GETPOST('contextpage', 'aZ') ?GETPOST('contextpage', 'aZ') : 'distributionlistcard'; // To manage different context of search
$backtopage = GETPOST('backtopage', 'alpha');
$backtopageforcancel = GETPOST('backtopageforcancel', 'alpha');
$massaction = GETPOST('massaction', 'alpha');
$contacts = GETPOST('toselect');
$nb_deleted_contacts = GETPOST('nb_deleted_contacts', 'int');
$label = GETPOST('label','alphanohtml');

// Initialize technical objects
$object = new DistributionList($db);
$extrafields = new ExtraFields($db);
$diroutputmassaction = $conf->distributionlist->dir_output.'/temp/massgeneration/'.$user->id;
$hookmanager->initHooks(array('distributionlistcard', 'globalcard')); // Note that conf->hooks_modules contains array

// Fetch optionals attributes and labels
$extrafields->fetch_name_optionals_label($object->table_element);

$search_array_options = $extrafields->getOptionalsFromPost($object->table_element, '', 'search_');

// Initialize array of search criterias
$search_all = trim(GETPOST("search_all", 'alpha'));
$search = array();
foreach ($object->fields as $key => $val)
{
	if (GETPOST('search_'.$key, 'alpha')) $search[$key] = GETPOST('search_'.$key, 'alpha');
}

if (empty($action) && empty($id) && empty($ref)) $action = 'view';

// Load object
include DOL_DOCUMENT_ROOT.'/core/actions_fetchobject.inc.php'; // Must be include, not include_once.


$permissiontoread = $user->rights->distributionlist->distributionlist->read;
$permissiontoadd = $user->rights->distributionlist->distributionlist->create; // Used by the include of actions_addupdatedelete.inc.php and actions_lineupdown.inc.php
$permissiontodelete = $user->rights->distributionlist->distributionlist->delete || ($permissiontoadd && isset($object->status) && $object->status == $object::STATUS_DRAFT && empty($object->nb_contacts));
$permissionnote = $user->rights->distributionlist->distributionlist->create; // Used by the include of actions_setnotes.inc.php
$permissiondellink = $user->rights->distributionlist->distributionlist->update; // Used by the include of actions_dellink.inc.php
$upload_dir = $conf->distributionlist->multidir_output[isset($object->entity) ? $object->entity : 1];

// Security check - Protection if external user
//if ($user->socid > 0) accessforbidden();
//if ($user->socid > 0) $socid = $user->socid;
//$isdraft = (($object->statut == $object::STATUS_DRAFT) ? 1 : 0);
//$result = restrictedArea($user, 'distributionlist', $object->id, '', '', 'fk_soc', 'rowid', $isdraft);

//if (!$permissiontoread) accessforbidden();


/*
 * Actions
 */

// Retrait des droits network car ici il n'y pas lieu d'effectuer ce type d'action
unset($user->rights->network);

$parameters = array();
$reshook = $hookmanager->executeHooks('doActions', $parameters, $object, $action); // Note that $action and $object may have been modified by some hooks
if ($reshook < 0) setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');

if (empty($reshook))
{
	$error = 0;

	$backurlforlist = dol_buildpath('/distributionlist/distributionlist_list.php', 1);

	if (empty($backtopage) || ($cancel && empty($id))) {
		if (empty($backtopage) || ($cancel && strpos($backtopage, '__ID__'))) {
			if (empty($id) && (($action != 'add' && $action != 'create') || $cancel)) $backtopage = $backurlforlist;
			else $backtopage = dol_buildpath('/distributionlist/distributionlist_card.php', 1).'?id='.($id > 0 ? $id : '__ID__');
		}
	}
	$triggermodname = 'DISTRIBUTIONLIST_DISTRIBUTIONLIST_MODIFY'; // Name of trigger action code to execute when we modify record

	// Actions cancel, add, update, update_extras, confirm_validate, confirm_delete, confirm_deleteline, confirm_clone, confirm_close, confirm_setdraft, confirm_reopen

	if($conf->global->DISTRIBUTIONLISTUNIQUELABEL) {
		if(ToolsDistributionlist::distributionlist_alreadyexist($label) > 0) {
			setEventMessage($langs->trans('DistributionList_Label_AlreadyExist', $label), 'errors');
			$error++;
		}
	}

	include DOL_DOCUMENT_ROOT.'/core/actions_addupdatedelete.inc.php';

	// Actions when linking object each other
	include DOL_DOCUMENT_ROOT.'/core/actions_dellink.inc.php';

	// Actions when printing a doc from card
	include DOL_DOCUMENT_ROOT.'/core/actions_printing.inc.php';

	// Action to move up and down lines of object
	//include DOL_DOCUMENT_ROOT.'/core/actions_lineupdown.inc.php';

	// Action to build doc
	include DOL_DOCUMENT_ROOT.'/core/actions_builddoc.inc.php';

	if ($action == 'set_thirdparty' && $permissiontoadd)
	{
		$object->setValueFrom('fk_soc', GETPOST('fk_soc', 'int'), '', '', 'date', '', $user, 'DISTRIBUTIONLIST_MODIFY');
	}
	if ($action == 'classin' && $permissiontoadd)
	{
		$object->setProject(GETPOST('projectid', 'int'));
	}

	// Actions to send emails
	$triggersendname = 'DISTRIBUTIONLIST_SENTBYMAIL';
	$autocopy = 'MAIN_MAIL_AUTOCOPY_DISTRIBUTIONLIST_TO';
	$trackid = 'distributionlist'.$object->id;
	include DOL_DOCUMENT_ROOT.'/core/actions_sendmails.inc.php';
}


if($massaction === 'distributionlist_delete_contacts' && $permissiontoadd && $object->status < DistributionList::STATUS_CLOSED) {

	if(!empty($contacts) && $object->status < DistributionList::STATUS_CLOSED) {
		$nb_del = 0;

		foreach ($contacts as $id_contact) $nb_del += $object->deleteContact($user, $id_contact, false, false);

		if(!empty($nb_del)) {
			setEventMessage($langs->trans('DistributionListNbDeletedContacts', $nb_del));
			$object->nb_contacts -= $nb_del;
			$object->update($user);
		}
	}

} elseif($action === 'confirm_reset_list' && $confirm === 'yes' && $permissiontoadd && $object->status < DistributionList::STATUS_CLOSED) {

	$nb_del = $object->deleteAllContacts($user);
	setEventMessage('DistributionListNbDeletedContacts', $nb_del);
	header('Location: '.$_SERVER['PHP_SELF'].'?id='.$id.'&nb_deleted_contacts='.$nb_del);
	exit;

} elseif(!empty($nb_deleted_contacts)) {

	setEventMessage($langs->trans('DistributionListNbDeletedContacts', $nb_deleted_contacts));

}

/*
 * View
 *
 * Put here all code to build page
 */

$form = new Form($db);
$formfile = new FormFile($db);
$formproject = new FormProjets($db);

$title = $langs->trans("DistributionList");
$help_url = '';
llxHeader('', $title, $help_url);

$o = new DistributionListSocpeople($db);
$TRes = $o->fetchAll('', '', 0, 0, array('customsql'=>' fk_distributionlist = '.GETPOST('id', 'int')));

// Suppression de la liste des contacts sélectionnés si existante pour ne pas remplir inutilement l'url lors de l'appel à la liste standard des contacts (sinon bug)
$TParamURL = $_REQUEST;
unset($TParamURL['toselect']);

if(!empty($conf->listincsv->enabled)) {

	// Récupération du bouton listincsv
	dol_include_once('/listincsv/lib/listincsv.lib.php');
	$download = getListInCSVDownloadLink();

	// URL spécifique pour listincsv
	// Explication : Sur l'onglet distributionlist_card, on fait un appel ajax à la liste des contacts avec le paramètre origin_page=distributionlist_card et l'id de la liste de diffusion.
	// Tout ça est intercepté par un hook printFieldListWhere de la liste des contacts de manière à n'afficher que les contacts qui font partie de la lite de distribution,
	// or si change le nombre d'éléments affichés via la liste déroulante qui contient les choix 10, 25, 50, 100, 500, etc... le paramètre url limit empêche list in csv de récupérer tous les contacts de la liste de diffusion
	// à cause de la requête sql dont le nombre de résultats est limité
	$TParamURLForListInCSV = $TParamURL;
	unset($TParamURLForListInCSV['limit']);

}

if($action !== 'create' && $action !== 'edit') {

?>

	<script type="text/javascript" language="javascript">
		$(document).ready(function() {
			$.ajax({
				url:"<?php print dol_buildpath('/contact/list.php', 2).'?origin_page=distributionlist_card&'.http_build_query($TParamURL); ?>"
			}).done(function(data) {

				// On remplace les liens de la pagination pour rester sur la liste de diffusion en cas de changement de page
				var form_contacts = $(data).find('div.fiche form[name="formfilter"]');
				form_contacts.find('table.table-fiche-title a').each(function() {
					$(this).attr('href', $(this).attr('href').replace("<?php print dol_buildpath('/contact/list.php', 1); ?>", "<?php print dol_buildpath('/distributionlist/distributionlist_card.php', 1); ?>"));
					$(this).attr('href', $(this).attr('href') + '&id=' + <?php print $id; $hookmanager->executeHooks('addMoreURLParams', $parameters, $object, $action); ?>);
				});

				// On remplace les liens de tri pour rester sur la liste de diffusion en cas de tri sur une colonne
				form_contacts.find('table.liste tr.liste_titre a').each(function() {
					$(this).attr('href', $(this).attr('href').replace("<?php print dol_buildpath('/contact/list.php', 1); ?>", "<?php print dol_buildpath('/distributionlist/distributionlist_card.php', 1); ?>"));
					$(this).attr('href', $(this).attr('href') + '&id=' + <?php print $id; $hookmanager->executeHooks('addMoreURLParams', $parameters, $object, $action); ?>);
				});

				// Formulaire
				form_contacts.attr('action', form_contacts.attr('action').replace("<?php print dol_buildpath('/contact/list.php', 1); ?>", "<?php print dol_buildpath('/distributionlist/distributionlist_card.php', 1); ?>"));
				form_contacts.attr('action', form_contacts.attr('action') + '?id=' + <?php print $id; ?>);

				// Suppression des cases à cocher pour ne plus pouvoir toucher aux contacts présents dans la liste de diffusion si cette dernière est clôturée
				<?php if($object->status > 1 || empty($permissiontoadd)) { ?>
					form_contacts.find('input[name*="toselect"], input[name*="checkallactions"]').remove();
				<?php } ?>

				// On affiche la liste des contacts
				$("#inclusion").append(form_contacts);

				<?php
					if(!empty($conf->listincsv->enabled)) {

						?>
						// Ajout d'une url spécifique au formulaire pour listincsv
						form_contacts.attr('data-listincsv-url', "<?php print dol_buildpath('/contact/list.php', 1).'?&origin_page=distributionlist_card&'.http_build_query($TParamURLForListInCSV); ?>");

						<?php

						print "$('div.fiche div.titre').first().append('".$download."');";
					}
				?>

				<?php

				$reshook = $hookmanager->executeHooks('printMoreAfterAjax', $parameters, $object, $action);

				?>

			});

		});
	</script>

<?php

} else $user->rights->listincsv=array(); // Pour empêcher d'ajouter le boutton listincsv en mode edit et create

// Part to create
if ($action == 'create')
{
	print load_fiche_titre($langs->trans("NewObject", $langs->transnoentitiesnoconv("DistributionList")), '', 'object_'.$object->picto);

	print '<form method="POST" action="'.$_SERVER["PHP_SELF"].'">';
	print '<input type="hidden" name="token" value="'.newToken().'">';
	print '<input type="hidden" name="action" value="add">';
	if ($backtopage) print '<input type="hidden" name="backtopage" value="'.$backtopage.'">';
	if ($backtopageforcancel) print '<input type="hidden" name="backtopageforcancel" value="'.$backtopageforcancel.'">';

	dol_fiche_head(array(), '');

	// Set some default values
	//if (! GETPOSTISSET('fieldname')) $_POST['fieldname'] = 'myvalue';

	print '<table class="border centpercent tableforfieldcreate">'."\n";

	// Common attributes
	include DOL_DOCUMENT_ROOT.'/core/tpl/commonfields_add.tpl.php';

	// Other attributes
	include DOL_DOCUMENT_ROOT.'/core/tpl/extrafields_add.tpl.php';

	print '</table>'."\n";

	dol_fiche_end();

	print '<div class="center">';
	print '<input type="submit" class="button" name="add" value="'.dol_escape_htmltag($langs->trans("Create")).'">';
	print '&nbsp; ';
	print '<input type="'.($backtopage ? "submit" : "button").'" class="button" name="cancel" value="'.dol_escape_htmltag($langs->trans("Cancel")).'"'.($backtopage ? '' : ' onclick="javascript:history.go(-1)"').'>'; // Cancel for create does not post form if we don't know the backtopage
	print '</div>';

	print '</form>';

	//dol_set_focus('input[name="ref"]');
}

// Part to edit record
if (($id || $ref) && $action == 'edit')
{
	print load_fiche_titre($langs->trans("DistributionList"), '', 'object_'.$object->picto);

	print '<form method="POST" action="'.$_SERVER["PHP_SELF"].'">';
	print '<input type="hidden" name="token" value="'.newToken().'">';
	print '<input type="hidden" name="action" value="update">';
	print '<input type="hidden" name="id" value="'.$object->id.'">';
	if ($backtopage) print '<input type="hidden" name="backtopage" value="'.$backtopage.'">';
	if ($backtopageforcancel) print '<input type="hidden" name="backtopageforcancel" value="'.$backtopageforcancel.'">';

	dol_fiche_head();

	print '<table class="border centpercent tableforfieldedit">'."\n";

	// Common attributes
	include DOL_DOCUMENT_ROOT.'/core/tpl/commonfields_edit.tpl.php';

	// Other attributes
	include DOL_DOCUMENT_ROOT.'/core/tpl/extrafields_edit.tpl.php';

	print '</table>';

	dol_fiche_end();

	print '<div class="center"><input type="submit" class="button" name="save" value="'.$langs->trans("Save").'">';
	print ' &nbsp; <input type="submit" class="button" name="cancel" value="'.$langs->trans("Cancel").'">';
	print '</div>';

	print '</form>';
}

// Part to show record
if ($object->id > 0 && (empty($action) || ($action != 'edit' && $action != 'create')))
{
	$res = $object->fetch_optionals();

	$head = distributionlistPrepareHead($object);
	dol_fiche_head($head, 'card', $langs->trans("DistributionList"), -1, $object->picto);

	$formconfirm = '';

	// Confirmation to delete
	if ($action == 'delete')
	{
		$formconfirm = $form->formconfirm($_SERVER["PHP_SELF"].'?id='.$object->id, $langs->trans('DistributionListDelete'), $langs->trans('ConfirmDeleteDistributionList'), 'confirm_delete', '', 0, 1);
	}
	if ($action == 'modif') {
		$text = $langs->trans('ConfirmUnvalidateDistributionList', $object->ref);
		$formquestion = array();
		$formconfirm = $form->formconfirm($_SERVER["PHP_SELF"].'?id='.$object->id, $langs->trans('DistributionListUnvalidate', $object->ref), $text, 'confirm_setdraft', $formquestion, "yes", 1);
	}
	if ($action == 'validate') {
		$error = 0;

		if ($object->nb_contacts > 0){
			// We verify whether the object is provisionally numbering
			$ref = substr($object->ref, 1, 4);
			if ($ref == 'PROV') {
				$numref = $object->getNextNumRef($soc);
				if (empty($numref)) {
					$error++;
					setEventMessages($object->error, $object->errors, 'errors');
				}
			} else {
				$numref = $object->ref;
			}
		} else {
			$error++;
			setEventMessages($langs->transnoentities("NoContactInDistribListError"), null, 'warnings');
		}




		$text = $langs->trans('ConfirmValidateDistributionList', $numref);
		if (!empty($conf->notification->enabled)) {
			require_once DOL_DOCUMENT_ROOT.'/core/class/notify.class.php';
			$notify = new Notify($db);
			$text .= '<br>';
			$text .= $notify->confirmMessage('DISTRIBUTIONLIST_VALIDATE', $object->socid, $object);
		}

		if (!$error)
			$formconfirm = $form->formconfirm($_SERVER["PHP_SELF"].'?id='.$object->id, $langs->trans('DistributionListValidate'), $text, 'confirm_validate', '', 0, 1);
	}
	// Clone action
	if ($action == 'clone') {
		// Create an array for form
		$formquestion = array();
		$formconfirm = $form->formconfirm($_SERVER["PHP_SELF"].'?id='.$object->id, $langs->trans('ToClone'), $langs->trans('ConfirmCloneAsk', $object->ref), 'confirm_clone', $formquestion, 'yes', 1);
	}
	// Close confirmation
	if ($action == 'close') {
		// Create an array for form
		$formquestion = array(
			// 'text' => $langs->trans("ConfirmClone"),
			// array('type' => 'checkbox', 'name' => 'clone_content', 'label' => $langs->trans("CloneMainAttributes"), 'value' => 1),
			// array('type' => 'checkbox', 'name' => 'update_prices', 'label' => $langs->trans("PuttingPricesUpToDate"), 'value' => 1),
			// array('type' => 'date', 'name' => 'date_cloture', 'label' => $langs->trans('DateClosing'), 'value'=>dol_now())
		);
		$formconfirm = $form->formconfirm($_SERVER["PHP_SELF"].'?id='.$object->id, $langs->trans('ToClose'), $langs->trans('ConfirmCloseDistributionList', $object->ref), 'confirm_close', $formquestion, 'yes', 1);
	}

	// Close confirmation
	if ($action == 'confirm_close' && $confirm == 'yes' && $permissiontoadd) {
		$object->date_cloture = dol_now();
		$object->update($user);
	}

	if ($action == 'reopen') {
		// Create an array for form
		$formquestion = array();
		$formconfirm = $form->formconfirm($_SERVER["PHP_SELF"].'?id='.$object->id, $langs->trans('ReOpen'), $langs->trans('ConfirmReopenDistributionList', $object->ref), 'confirm_reopen', '', 0, 1);
	}

	if($action == 'reset_list') {
		// Create an array for form
		$formquestion = array();
		$formconfirm = $form->formconfirm($_SERVER["PHP_SELF"].'?id='.$object->id, $langs->trans('TargetsReset'), $langs->trans('ConfirmDeleteAllContactsDistributionList', $object->ref), 'confirm_reset_list', '', 0, 1);
	}

	// Confirmation of action xxxx
	if ($action == 'xxx')
	{
		$formquestion = array();
		/*
		$forcecombo=0;
		if ($conf->browser->name == 'ie') $forcecombo = 1;	// There is a bug in IE10 that make combo inside popup crazy
		$formquestion = array(
			// 'text' => $langs->trans("ConfirmClone"),
			// array('type' => 'checkbox', 'name' => 'clone_content', 'label' => $langs->trans("CloneMainAttributes"), 'value' => 1),
			// array('type' => 'checkbox', 'name' => 'update_prices', 'label' => $langs->trans("PuttingPricesUpToDate"), 'value' => 1),
			// array('type' => 'other',    'name' => 'idwarehouse',   'label' => $langs->trans("SelectWarehouseForStockDecrease"), 'value' => $formproduct->selectWarehouses(GETPOST('idwarehouse')?GETPOST('idwarehouse'):'ifone', 'idwarehouse', '', 1, 0, 0, '', 0, $forcecombo))
		);
		*/
		$formconfirm = $form->formconfirm($_SERVER["PHP_SELF"].'?id='.$object->id, $langs->trans('XXX'), $text, 'confirm_xxx', $formquestion, 0, 1, 220);
	}

	// Call Hook formConfirm
	$parameters = array('formConfirm' => $formconfirm, 'lineid' => $lineid);
	$reshook = $hookmanager->executeHooks('formConfirm', $parameters, $object, $action); // Note that $action and $object may have been modified by hook
	if (empty($reshook)) $formconfirm .= $hookmanager->resPrint;
	elseif ($reshook > 0) $formconfirm = $hookmanager->resPrint;

	// Print form confirm
	print $formconfirm;


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
	 //if ($action != 'classify') $morehtmlref.='<a class="editfielda" href="' . $_SERVER['PHP_SELF'] . '?action=classify&amp;id=' . $object->id . '">' . img_edit($langs->transnoentitiesnoconv('SetProject')) . '</a> ';
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
	print '<div class="fichehalfleft">';
	print '<div class="underbanner clearboth"></div>';
	print '<table class="border centpercent tableforfield">'."\n";

	// Common attributes
	//$keyforbreak='fieldkeytoswitchonsecondcolumn';	// We change column just before this field
	//unset($object->fields['fk_project']);				// Hide field already shown in banner
	//unset($object->fields['fk_soc']);					// Hide field already shown in banner
	include DOL_DOCUMENT_ROOT.'/core/tpl/commonfields_view.tpl.php';

	// Other attributes. Fields from hook formObjectOptions and Extrafields.
	include DOL_DOCUMENT_ROOT.'/core/tpl/extrafields_view.tpl.php';

	print '</table>';
	print '</div>';
	print '</div>';

	print '<div class="clearboth"></div>';

	dol_fiche_end();


	/*
	 * Lines
	 */

	if (!empty($object->table_element_line))
	{
		// Show object lines
		$result = $object->getLinesArray();

		print '	<form name="addproduct" id="addproduct" action="'.$_SERVER["PHP_SELF"].'?id='.$object->id.(($action != 'editline') ? '#addline' : '#line_'.GETPOST('lineid', 'int')).'" method="POST">
		<input type="hidden" name="token" value="' . newToken().'">
		<input type="hidden" name="action" value="' . (($action != 'editline') ? 'addline' : 'updateline').'">
		<input type="hidden" name="mode" value="">
		<input type="hidden" name="id" value="' . $object->id.'">
		';

		if (!empty($conf->use_javascript_ajax) && $object->status == 0) {
			include DOL_DOCUMENT_ROOT.'/core/tpl/ajaxrow.tpl.php';
		}

		print '<div class="div-table-responsive-no-min">';
		if (!empty($object->lines) || ($object->status == $object::STATUS_DRAFT && $permissiontoadd && $action != 'selectlines' && $action != 'editline'))
		{
			print '<table id="tablelines" class="noborder noshadow" width="100%">';
		}

		if (!empty($object->lines))
		{
			$object->printObjectLines($action, $mysoc, null, GETPOST('lineid', 'int'), 1);
		}

		// Form to add new line
		if ($object->status == 0 && $permissiontoadd && $action != 'selectlines')
		{
			if ($action != 'editline')
			{
				// Add products/services form
				$object->formAddObjectLine(1, $mysoc, $soc);

				$parameters = array();
				$reshook = $hookmanager->executeHooks('formAddObjectLine', $parameters, $object, $action); // Note that $action and $object may have been modified by hook
			}
		}

		if (!empty($object->lines) || ($object->status == $object::STATUS_DRAFT && $permissiontoadd && $action != 'selectlines' && $action != 'editline'))
		{
			print '</table>';
		}
		print '</div>';

		print "</form>\n";
	}


	// Buttons for actions

	if ($action != 'presend' && $action != 'editline') {
		print '<div class="tabsAction">'."\n";
		$parameters = array();
		$reshook = $hookmanager->executeHooks('addMoreActionsButtons', $parameters, $object, $action); // Note that $action and $object may have been modified by hook
		if ($reshook < 0) setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');

		if (empty($reshook))
		{
			// Send
			if (empty($user->socid)) {
				//print '<a class="butAction" href="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'&action=presend&mode=init#formmailbeforetitle">'.$langs->trans('SendMail').'</a>'."\n";
			}

			// Back to draft
			if ($object->status == $object::STATUS_VALIDATED)
			{
				if ($permissiontoadd)
				{
					print '<a class="butAction" href="'.$_SERVER['PHP_SELF'].'?id='.$object->id.'&action=modif">'.$langs->trans("SetToDraft").'</a>';
				}
			}

			// Modify
			if ($permissiontoadd)
			{
				if($object->status < DistributionList::STATUS_CLOSED) {
					print '<a class="butAction" href="' . $_SERVER["PHP_SELF"] . '?id=' . $object->id . '&action=edit">' . $langs->trans("Modify") . '</a>' . "\n";
				}
			}
			else
			{
				print '<a class="butActionRefused classfortooltip" href="#" title="'.dol_escape_htmltag($langs->trans("NotEnoughPermissions")).'">'.$langs->trans('Modify').'</a>'."\n";
			}

			// Validate
			if ($object->status == $object::STATUS_DRAFT)
			{
				if ($permissiontoadd)
				{
					if (empty($object->table_element_line) || (is_array($object->lines) && count($object->lines) > 0))
					{
						print '<a class="butAction" href="'.$_SERVER['PHP_SELF'].'?id='.$object->id.'&action=validate">'.$langs->trans("Validate").'</a>';
					}
					else
					{
						$langs->load("errors");
						print '<a class="butActionRefused" href="" title="'.$langs->trans("ErrorAddAtLeastOneLineFirst").'">'.$langs->trans("Validate").'</a>';
					}
				}
			}

			// Clone
			if ($permissiontoadd)
			{
				print '<a class="butAction" href="'.$_SERVER['PHP_SELF'].'?id='.$object->id.'&socid='.$object->socid.'&action=clone&object=distributionlist">'.$langs->trans("ToClone").'</a>'."\n";
			}

			/*
			if ($permissiontoadd)
			{
				if ($object->status == $object::STATUS_ENABLED)
				{
					print '<a class="butActionDelete" href="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'&action=disable">'.$langs->trans("Disable").'</a>'."\n";
				}
				else
				{
					print '<a class="butAction" href="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'&action=enable">'.$langs->trans("Enable").'</a>'."\n";
				}
			}*/
			if ($permissiontoadd)
			{
				if ($object->status == $object::STATUS_VALIDATED)
				{
					print '<a class="butAction" href="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'&action=close">'.$langs->trans("Close").'</a>'."\n";
				}
				elseif($object->status > 0)
				{
					print '<a class="butAction" href="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'&action=reopen">'.$langs->trans("ReOpen").'</a>'."\n";
				}
			}

			// Supprimer tous les contacts de la liste de diffusion
			if ($object->status < $object::STATUS_CLOSED && $permissiontoadd && $object->nb_contacts > 0)
			{
				print '<a class="butActionDelete" href="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'&amp;action=reset_list">'.$langs->trans('TargetsReset').'</a>'."\n";
			}

			// Delete (need delete permission, or if draft, just need create/modify permission)
			if ($permissiontodelete)
			{
				print '<a class="butActionDelete" href="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'&amp;action=delete">'.$langs->trans('Delete').'</a>'."\n";
			}
			else
			{
				print '<a class="butActionRefused classfortooltip" href="#" title="'.dol_escape_htmltag($langs->trans("NotEnoughPermissions")).'">'.$langs->trans('Delete').'</a>'."\n";
			}
		}
		print '</div>'."\n";
	}


	// Select mail models is same action as presend
	if (GETPOST('modelselected')) {
		$action = 'presend';
	}

	print '<br /><span id="inclusion"></span>';

	/*if ($action != 'presend')
	{
		print '<div class="fichecenter"><div class="fichehalfleft">';
		print '<a name="builddoc"></a>'; // ancre

		$includedocgeneration = 0;

		// Documents
		if ($includedocgeneration) {
			$objref = dol_sanitizeFileName($object->ref);
			$relativepath = $objref . '/' . $objref . '.pdf';
			$filedir = $conf->distributionlist->dir_output.'/'.$object->element.'/'.$objref;
			$urlsource = $_SERVER["PHP_SELF"] . "?id=" . $object->id;
			$genallowed = $user->rights->distributionlist->distributionlist->read;	// If you can read, you can build the PDF to read content
			$delallowed = $user->rights->distributionlist->distributionlist->write;	// If you can create/edit, you can remove a file on card
			print $formfile->showdocuments('distributionlist:DistributionList', $object->element.'/'.$objref, $filedir, $urlsource, $genallowed, $delallowed, $object->model_pdf, 1, 0, 0, 28, 0, '', '', '', $langs->defaultlang);
		}

		// Show links to link elements
		$linktoelem = $form->showLinkToObjectBlock($object, null, array('distributionlist'));
		$somethingshown = $form->showLinkedObjectBlock($object, $linktoelem);


		print '</div><div class="fichehalfright"><div class="ficheaddleft">';

		$MAXEVENT = 10;

		$morehtmlright = '<a href="'.dol_buildpath('/distributionlist/distributionlist_agenda.php', 1).'?id='.$object->id.'">';
		$morehtmlright .= $langs->trans("SeeAll");
		$morehtmlright .= '</a>';

		// List of actions on element
		include_once DOL_DOCUMENT_ROOT.'/core/class/html.formactions.class.php';
		$formactions = new FormActions($db);
		$somethingshown = $formactions->showactions($object, $object->element.'@distributionlist', (is_object($object->thirdparty) ? $object->thirdparty->id : 0), 1, '', $MAXEVENT, '', $morehtmlright);

		print '</div></div></div>';
	}*/

	//Select mail models is same action as presend
	if (GETPOST('modelselected')) $action = 'presend';

	// Presend form
	$modelmail = 'distributionlist';
	$defaulttopic = 'InformationMessage';
	$diroutput = $conf->distributionlist->dir_output;
	$trackid = 'distributionlist'.$object->id;

	include DOL_DOCUMENT_ROOT.'/core/tpl/card_presend.tpl.php';
}

// End of page
llxFooter();
$db->close();
