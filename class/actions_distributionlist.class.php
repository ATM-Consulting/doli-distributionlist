<?php

class ActionsDistributionlist {

	function addMoreMassActions($parameters, &$object, &$action, $hookmanager) {

		global $langs;
		$TContext = explode(':', $parameters['context']);
		if(in_array('contactlist', $TContext)) {

			$origin_page = GETPOST('origin_page');
			if($origin_page === 'distributionlist_contact') {
				$label = $langs->trans('DistributionListAddContacts');
				$hookmanager->resPrint = '<option value="0">-- '.$langs->trans("SelectAction").' --</option>';
				$hookmanager->resPrint.= '<option value="add_contacts" data-html="'.dol_escape_htmltag($label).'">'.$label.'</option>';
			}
			return 1;
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
					$hookmanager->resPrint = ' AND p.rowid IN ('.implode(', ', $TContacts).')';
				}
			}
		}

	}

}