<?php

class ActionsDistributionlist {

	function doActions($parameters, &$object, &$action, $hookmanager) {

		global $user, $langs, $titre;

		$TContext = explode(':', $parameters['context']);
		if(in_array('contactlist', $TContext)) {
			$origin_page = GETPOST('origin_page');
			if($origin_page === 'distributionlist_contact' || $origin_page === 'distributionlist_card') {

				// On personnalise le titre de la liste des contacts dans le contexte d'une liste de diffusion
				if($origin_page === 'distributionlist_card') $titre = $langs->trans('ListOfContactsAddressesDistributionList');

				// On retire la permission de créer dans ce contexte pour enlever le lien "Nouveau contact/adresse"
				unset($user->rights->societe->contact->creer);

			}
		}
	}

	function addMoreMassActions($parameters, &$object, &$action, $hookmanager) {

		global $langs;
		$TContext = explode(':', $parameters['context']);
		if(in_array('contactlist', $TContext)) {

			$origin_page = GETPOST('origin_page');
			if($origin_page === 'distributionlist_contact') {

				$label = $langs->trans('DistributionListAddContacts');
				$hookmanager->resPrint = '<option value="0">-- '.$langs->trans("SelectAction").' --</option>';
				$hookmanager->resPrint.= '<option value="distributionlist_add_contacts" data-html="'.dol_escape_htmltag($label).'">'.$label.'</option>';

				return 1;

			} elseif($origin_page === 'distributionlist_card') {

				$label = $langs->trans('DistributionListDeleteContacts');
				$hookmanager->resPrint = '<option value="0">-- '.$langs->trans("SelectAction").' --</option>';
				$hookmanager->resPrint.= '<option value="distributionlist_delete_contacts" data-html="'.dol_escape_htmltag($label).'">'.$label.'</option>';

				return 1;

			}
		}

	}

	function printFieldListWhere($parameters, &$object, &$action, $hookmanager) {

		global $db;

		dol_include_once('/distributionlist/class/distributionlistsocpeople.class.php');
		$TContext = explode(':', $parameters['context']);
		if(in_array('contactlist', $TContext)) {
			$origin_page = GETPOST('origin_page');
			if($origin_page === 'distributionlist_card') {
				$o = new DistributionListSocpeople($db);

				$TRes = $o->fetchAll('', '', 0, 0, array('customsql'=>' fk_distributionlist = '.GETPOST('id', 'int')));
				if(!empty($TRes)) {
					$TContacts = array();
					foreach ($TRes as $obj) {
						$TContacts[] = $obj->fk_socpeople;
					}
					$this->resprints = ' AND p.rowid IN ('.implode(', ', $TContacts).')';
				} else $this->resprints = ' AND p.rowid = 0 ';
			}
		}

	}

	function printFieldListOption($parameters, &$object, &$action, $hookmanager) {

		global $db;

		$TContext = explode(':', $parameters['context']);
		if(in_array('contactlist', $TContext)) {
			$origin_page = GETPOST('origin_page');
			if($origin_page === 'distributionlist_card') $hookmanager->resPrint = '<td class="liste_titre"></td>';
		}

		return 1;

	}

	function printFieldListTitle($parameters, &$object, &$action, $hookmanager) {

		global $langs;

		$TContext = explode(':', $parameters['context']);
		if(in_array('contactlist', $TContext)) {
			$origin_page = GETPOST('origin_page');
			if ($origin_page === 'distributionlist_card') $hookmanager->resPrint = print_liste_field_titre($langs->trans('DistributionListUserWhoAdd'), $_SERVER["PHP_SELF"], "", "", $param, '', $sortfield, $sortorder, 'center nowrap ');
		}

		return 1;

	}

	function printFieldListValue($parameters, &$object, &$action, $hookmanager) {

		global $db, $TDistributionListLoadedusers;

		if(empty($TDistributionListLoadedusers)) $TDistributionListLoadedusers = array();

		$TContext = explode(':', $parameters['context']);
		if(in_array('contactlist', $TContext)) {
			$origin_page = GETPOST('origin_page');
			if ($origin_page === 'distributionlist_card') {

				require_once DOL_DOCUMENT_ROOT . '/user/class/user.class.php';
				require_once __DIR__ . './../class/distributionlistsocpeople.class.php';

				$contact_id = $parameters['obj']->rowid;
				$o = new DistributionListSocpeople($db);
				$TRes = $o->fetchAll('', '', 0, 0, array('customsql' => ' fk_distributionlist = ' . GETPOST('id', 'int') . ' AND fk_socpeople = ' . $contact_id));
				if (!empty($TRes)) {
					$uid = $TRes[key($TRes)]->fk_user_creat;
					if (!empty($TDistributionListLoadedusers[$uid])) $hookmanager->resPrint = $TDistributionListLoadedusers[$uid];
					else {
						$u = new User($db);
						$u->fetch($uid);
						$TDistributionListLoadedusers[$uid] = '<td class="center">' . $u->getNomUrl(1) . '</td>';
						$hookmanager->resPrint = $TDistributionListLoadedusers[$uid];
					}
				}
			}

		}

		return 1;

	}

	// function printFieldListFooter

}
