<?php
/* Copyright (C) 2017  Laurent Destailleur <eldy@users.sourceforge.net>
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
 * \file        class/distributionlist.class.php
 * \ingroup     distributionlist
 * \brief       This file is a CRUD class file for DistributionList (Create/Read/Update/Delete)
 */

// Put here all includes required by your class file
require_once DOL_DOCUMENT_ROOT.'/core/class/commonobject.class.php';
//require_once DOL_DOCUMENT_ROOT . '/societe/class/societe.class.php';
//require_once DOL_DOCUMENT_ROOT . '/product/class/product.class.php';

/**
 * Class for DistributionList
 */
class DistributionList extends CommonObject
{
	/**
	 * @var string ID to identify managed object.
	 */
	public $element = 'distributionlist';

	/**
	 * @var string Name of table without prefix where object is stored. This is also the key used for extrafields management.
	 */
	public $table_element = 'distributionlist_distributionlist';

	/**
	 * @var int  Does this object support multicompany module ?
	 * 0=No test on entity, 1=Test with field entity, 'field@table'=Test with link by field@table
	 */
	public $ismultientitymanaged = 0;

	/**
	 * @var int  Does object support extrafields ? 0=No, 1=Yes
	 */
	public $isextrafieldmanaged = 1;

	/**
	 * @var string String with name of icon for distributionlist. Must be the part after the 'object_' into object_distributionlist.png
	 */
	public $picto = 'distributionlist@distributionlist';


	const STATUS_DRAFT = 0;
	const STATUS_VALIDATED = 1;
	const STATUS_CLOSED = 2;


	/**
	 *  'type' if the field format ('integer', 'integer:ObjectClass:PathToClass[:AddCreateButtonOrNot[:Filter]]', 'varchar(x)', 'double(24,8)', 'real', 'price', 'text', 'html', 'date', 'datetime', 'timestamp', 'duration', 'mail', 'phone', 'url', 'password')
	 *         Note: Filter can be a string like "(t.ref:like:'SO-%') or (t.date_creation:<:'20160101') or (t.nature:is:NULL)"
	 *  'label' the translation key.
	 *  'enabled' is a condition when the field must be managed (Example: 1 or '$conf->global->MY_SETUP_PARAM)
	 *  'position' is the sort order of field.
	 *  'notnull' is set to 1 if not null in database. Set to -1 if we must set data to null if empty ('' or 0).
	 *  'visible' says if field is visible in list (Examples: 0=Not visible, 1=Visible on list and create/update/view forms, 2=Visible on list only, 3=Visible on create/update/view form only (not list), 4=Visible on list and update/view form only (not create). 5=Visible on list and view only (not create/not update). Using a negative value means field is not shown by default on list but can be selected for viewing)
	 *  'noteditable' says if field is not editable (1 or 0)
	 *  'default' is a default value for creation (can still be overwrote by the Setup of Default Values if field is editable in creation form). Note: If default is set to '(PROV)' and field is 'ref', the default value will be set to '(PROVid)' where id is rowid when a new record is created.
	 *  'index' if we want an index in database.
	 *  'foreignkey'=>'tablename.field' if the field is a foreign key (it is recommanded to name the field fk_...).
	 *  'searchall' is 1 if we want to search in this field when making a search from the quick search button.
	 *  'isameasure' must be set to 1 if you want to have a total on list for this field. Field type must be summable like integer or double(24,8).
	 *  'css' is the CSS style to use on field. For example: 'maxwidth200'
	 *  'help' is a string visible as a tooltip on field
	 *  'showoncombobox' if value of the field must be visible into the label of the combobox that list record
	 *  'disabled' is 1 if we want to have the field locked by a 'disabled' attribute. In most cases, this is never set into the definition of $fields into class, but is set dynamically by some part of code.
	 *  'arraykeyval' to set list of value if type is a list of predefined values. For example: array("0"=>"Draft","1"=>"Active","-1"=>"Cancel")
	 *  'autofocusoncreate' to have field having the focus on a create form. Only 1 field should have this property set to 1.
	 *  'comment' is not used. You can store here any text of your choice. It is not used by application.
	 *
	 *  Note: To have value dynamic, you can set value to 0 in definition and edit the value on the fly into the constructor.
	 */

	// BEGIN MODULEBUILDER PROPERTIES
	/**
	 * @var array  Array with all fields and their property. Do not use it as a static var. It may be modified by constructor.
	 */
	public $fields=array(
		'rowid' => array('type'=>'integer', 'label'=>'TechnicalID', 'enabled'=>'1', 'position'=>1, 'notnull'=>1, 'visible'=>0, 'noteditable'=>'1', 'index'=>1, 'comment'=>"Id"),
		'ref' => array('type'=>'varchar(128)', 'label'=>'Ref', 'enabled'=>'1', 'position'=>10, 'notnull'=>1, 'visible'=>4, 'noteditable'=>'1', 'default'=>'(PROV)', 'index'=>1, 'searchall'=>1, 'showoncombobox'=>'1', 'comment'=>"Reference of object"),
		'entity' =>array('type'=>'integer', 'label'=>'Entity', 'default'=>1, 'enabled'=>1, 'visible'=>-2, 'notnull'=>1, 'position'=>20, 'index'=>1),
		'label' => array('type'=>'varchar(255)', 'label'=>'Label', 'enabled'=>'1', 'position'=>30, 'notnull'=>0, 'visible'=>1, 'searchall'=>1, 'css'=>'minwidth200', 'help'=>"Help text", 'showoncombobox'=>'1',),
		'description' => array('type'=>'text', 'label'=>'Description', 'enabled'=>'1', 'position'=>60, 'notnull'=>0, 'visible'=>3,),
		'nb_contacts' => array('type'=>'integer', 'label'=>'DistributionListNbContactsInList', 'enabled'=>'1', 'position'=>61, 'notnull'=>1, 'visible'=>2, 'default'=>0),
		'date_cloture' => array('type'=>'date', 'label'=>'DateClosing', 'enabled'=>'1', 'position'=>62, 'visible'=>3, 'default'=>0),
		'note_public' => array('type'=>'html', 'label'=>'NotePublic', 'enabled'=>'1', 'position'=>63, 'notnull'=>0, 'visible'=>0,),
		'note_private' => array('type'=>'html', 'label'=>'NotePrivate', 'enabled'=>'1', 'position'=>64, 'notnull'=>0, 'visible'=>0,),
		'date_creation' => array('type'=>'datetime', 'label'=>'DateCreation', 'enabled'=>'1', 'position'=>500, 'notnull'=>1, 'visible'=>-2,),
		'date_valid' => array('type'=>'datetime', 'label'=>'DateCreation', 'enabled'=>'1', 'position'=>500, 'notnull'=>0, 'visible'=>-2,),
		'tms' => array('type'=>'timestamp', 'label'=>'DateModification', 'enabled'=>'1', 'position'=>501, 'notnull'=>1, 'visible'=>-2,),
		'fk_user_creat' => array('type'=>'integer:User:user/class/user.class.php', 'label'=>'UserAuthor', 'enabled'=>'1', 'position'=>510, 'notnull'=>1, 'visible'=>-2, 'foreignkey'=>'user.rowid',),
		'fk_user_modif' => array('type'=>'integer:User:user/class/user.class.php', 'label'=>'UserModif', 'enabled'=>'1', 'position'=>511, 'notnull'=>-1, 'visible'=>-2,),
		'fk_user_valid' => array('type'=>'integer:User:user/class/user.class.php', 'label'=>'UserValid', 'enabled'=>'1', 'position'=>511, 'notnull'=>-1, 'visible'=>-2,),
		'fk_user_cloture' => array('type'=>'integer:User:user/class/user.class.php', 'label'=>'UserCloture', 'enabled'=>'1', 'position'=>511, 'notnull'=>-1, 'visible'=>-2,),
		'import_key' => array('type'=>'varchar(14)', 'label'=>'ImportId', 'enabled'=>'1', 'position'=>1000, 'notnull'=>-1, 'visible'=>-2,),
		'status' => array('type'=>'smallint', 'label'=>'Status', 'enabled'=>'1', 'position'=>1000, 'notnull'=>1, 'visible'=>5, 'index'=>1, 'arrayofkeyval'=>array('0'=>'Brouillon', '1'=>'Valid&eacute;', '9'=>'Closed'),),
	);
	public $rowid;
	public $ref;
	public $label;
	public $description;
	public $note_public;
	public $note_private;
	public $date_creation;
	public $tms;
	public $fk_user_creat;
	public $fk_user_modif;
	public $import_key;
	public $status;
	public $date_cloture;
	// END MODULEBUILDER PROPERTIES


	// If this object has a subtable with lines

	/**
	 * @var int    Name of subtable line
	 */
	//public $table_element_line = 'distributionlist_distributionlistline';

	/**
	 * @var int    Field with ID of parent key if this object has a parent
	 */
	//public $fk_element = 'fk_distributionlist';

	/**
	 * @var int    Name of subtable class that manage subtable lines
	 */
	//public $class_element_line = 'DistributionListline';

	/**
	 * @var array	List of child tables. To test if we can delete object.
	 */
	//protected $childtables = array();

	/**
	 * @var array    List of child tables. To know object to delete on cascade.
	 *               If name matches '@ClassNAme:FilePathClass;ParentFkFieldName' it will
	 *               call method deleteByParentField(parentId, ParentFkFieldName) to fetch and delete child object
	 */
	//protected $childtablesoncascade = array('distributionlist_distributionlistdet');

	/**
	 * @var DistributionListLine[]     Array of subtable lines
	 */
	//public $lines = array();



	/**
	 * Constructor
	 *
	 * @param DoliDb $db Database handler
	 */
	public function __construct(DoliDB $db)
	{
		global $conf, $langs;

		$this->db = $db;

		if (empty($conf->global->MAIN_SHOW_TECHNICAL_ID) && isset($this->fields['rowid'])) $this->fields['rowid']['visible'] = 0;
		if (empty($conf->multicompany->enabled) && isset($this->fields['entity'])) $this->fields['entity']['enabled'] = 0;

		// Example to show how to set values of fields definition dynamically
		/*if ($user->rights->distributionlist->distributionlist->read) {
			$this->fields['myfield']['visible'] = 1;
			$this->fields['myfield']['noteditable'] = 0;
		}*/

		// Unset fields that are disabled
		foreach ($this->fields as $key => $val)
		{
			if (isset($val['enabled']) && empty($val['enabled']))
			{
				unset($this->fields[$key]);
			}
		}

		// Translate some data of arrayofkeyval
		if (is_object($langs))
		{
			foreach ($this->fields as $key => $val)
			{
				if (is_array($val['arrayofkeyval']))
				{
					foreach ($val['arrayofkeyval'] as $key2 => $val2)
					{
						$this->fields[$key]['arrayofkeyval'][$key2] = $langs->trans($val2);
					}
				}
			}
		}
	}

	/**
	 * Create object into database
	 *
	 * @param  User $user      User that creates
	 * @param  bool $notrigger false=launch triggers after, true=disable triggers
	 * @return int             <0 if KO, Id of created object if OK
	 */
	public function create(User $user, $notrigger = false)
	{
		if(empty($this->fk_user_creat)) $this->fk_user_creat = $user->id;
		$this->status = (int)$this->status;
		$this->nb_contacts = (int) $this->nb_contacts;
		return $this->createCommon($user, $notrigger);
	}

	/**
	 * Clone an object into another one
	 *
	 * @param  	User 	$user      	User that creates
	 * @param  	int 	$fromid     Id of object to clone
	 * @return 	mixed 				New object created, <0 if KO
	 */
	public function createFromClone(User $user, $fromid)
	{
		global $langs, $extrafields;
		$error = 0;

		dol_syslog(__METHOD__, LOG_DEBUG);

		$object = new self($this->db);

		$this->db->begin();

		// Load source object
		$result = $object->fetchCommon($fromid);
		if ($result > 0 && !empty($object->table_element_line)) $object->fetchLines();

		// get lines so they will be clone
		//foreach($this->lines as $line)
		//	$line->fetch_optionals();

		// Reset some properties
		unset($object->id);
		unset($object->fk_user_creat);
		unset($object->fk_user_valid);
		unset($object->fk_user_modif);
		unset($object->fk_user_cloture);
		unset($object->import_key);
		unset($object->date_valid);
		unset($object->date_cloture);


		// Clear fields
		$object->ref = empty($this->fields['ref']['default']) ? "copy_of_".$object->ref : $this->fields['ref']['default'];
		$object->label = empty($this->fields['label']['default']) ? $langs->trans("CopyOf")." ".$object->label : $this->fields['label']['default'];
		$object->status = self::STATUS_DRAFT;
		// ...
		// Clear extrafields that are unique
		if (is_array($object->array_options) && count($object->array_options) > 0)
		{
			$extrafields->fetch_name_optionals_label($this->table_element);
			foreach ($object->array_options as $key => $option)
			{
				$shortkey = preg_replace('/options_/', '', $key);
				if (!empty($extrafields->attributes[$this->table_element]['unique'][$shortkey]))
				{
					//var_dump($key); var_dump($clonedObj->array_options[$key]); exit;
					unset($object->array_options[$key]);
				}
			}
		}

		// Create clone
		$object->context['createfromclone'] = 'createfromclone';
		$result = $object->createCommon($user);
		if ($result < 0) {
			$error++;
			$this->error = $object->error;
			$this->errors = $object->errors;
		}

		if (!$error)
		{
			// copy internal contacts
			if ($this->copy_linked_contact($object, 'internal') < 0)
			{
				$error++;
			}
		}

		if (!$error)
		{
			// copy external contacts if same company
			if (property_exists($this, 'socid') && $this->socid == $object->socid)
			{
				if ($this->copy_linked_contact($object, 'external') < 0)
					$error++;
			}
		}

		// Copie des contacts présents dans la liste d'origine
		if(!$error) {

			$o_origin = new DistributionListSocpeople($this->db);

			$TRes = $o_origin->fetchAll('', '', 0, 0, array('customsql'=>' fk_distributionlist = '.$this->id));
			if(!empty($TRes)) {
				$nb_add=0;
				foreach ($TRes as $obj) $nb_add += $object->addContact($user, $obj->fk_socpeople, false, false);
				$object->nb_contacts = $nb_add;
				$object->update($user);
			}
		}

		unset($object->context['createfromclone']);

		// End
		if (!$error) {
			$this->db->commit();
			return $object;
		} else {
			$this->db->rollback();
			return -1;
		}
	}

	/**
	 * Load object in memory from the database
	 *
	 * @param int    $id   Id object
	 * @param string $ref  Ref
	 * @return int         <0 if KO, 0 if not found, >0 if OK
	 */
	public function fetch($id, $ref = null)
	{
		$result = $this->fetchCommon($id, $ref);
		if ($result > 0 && !empty($this->table_element_line)) $this->fetchLines();
		return $result;
	}

	/**
	 * Load object lines in memory from the database
	 *
	 * @return int         <0 if KO, 0 if not found, >0 if OK
	 */
	public function fetchLines()
	{
		$this->lines = array();

		$result = $this->fetchLinesCommon();
		return $result;
	}


	/**
	 * Load list of objects in memory from the database.
	 *
	 * @param  string      $sortorder    Sort Order
	 * @param  string      $sortfield    Sort field
	 * @param  int         $limit        limit
	 * @param  int         $offset       Offset
	 * @param  array       $filter       Filter array. Example array('field'=>'valueforlike', 'customurl'=>...)
	 * @param  string      $filtermode   Filter mode (AND or OR)
	 * @return array|int                 int <0 if KO, array of pages if OK
	 */
	public function fetchAll($sortorder = '', $sortfield = '', $limit = 0, $offset = 0, array $filter = array(), $filtermode = 'AND')
	{
		global $conf;

		dol_syslog(__METHOD__, LOG_DEBUG);

		$records = array();

		$sql = 'SELECT ';
		$sql .= $this->getFieldList();
		$sql .= ' FROM '.MAIN_DB_PREFIX.$this->table_element.' as t';
		if (isset($this->ismultientitymanaged) && $this->ismultientitymanaged == 1) $sql .= ' WHERE t.entity IN ('.getEntity($this->table_element).')';
		else $sql .= ' WHERE 1 = 1';
		// Manage filter
		$sqlwhere = array();
		if (count($filter) > 0) {
			foreach ($filter as $key => $value) {
				if ($key == 't.rowid') {
					$sqlwhere[] = $key.'='.$value;
				}
				elseif (strpos($key, 'date') !== false) {
					$sqlwhere[] = $key.' = \''.$this->db->idate($value).'\'';
				}
				elseif ($key == 'customsql') {
					$sqlwhere[] = $value;
				}
				else {
					$sqlwhere[] = $key.' LIKE \'%'.$this->db->escape($value).'%\'';
				}
			}
		}
		if (count($sqlwhere) > 0) {
			$sql .= ' AND ('.implode(' '.$filtermode.' ', $sqlwhere).')';
		}

		if (!empty($sortfield)) {
			$sql .= $this->db->order($sortfield, $sortorder);
		}
		if (!empty($limit)) {
			$sql .= ' '.$this->db->plimit($limit, $offset);
		}

		$resql = $this->db->query($sql);
		if ($resql) {
			$num = $this->db->num_rows($resql);
			$i = 0;
			while ($i < ($limit ? min($limit, $num) : $num))
			{
				$obj = $this->db->fetch_object($resql);

				$record = new self($this->db);
				$record->setVarsFromFetchObj($obj);

				$records[$record->id] = $record;

				$i++;
			}
			$this->db->free($resql);

			return $records;
		} else {
			$this->errors[] = 'Error '.$this->db->lasterror();
			dol_syslog(__METHOD__.' '.join(',', $this->errors), LOG_ERR);

			return -1;
		}
	}

	/**
	 * Update object into database
	 *
	 * @param  User $user      User that modifies
	 * @param  bool $notrigger false=launch triggers after, true=disable triggers
	 * @return int             <0 if KO, >0 if OK
	 */
	public function update(User $user, $notrigger = false)
	{
		$this->tms = dol_now();
		return $this->updateCommon($user, $notrigger);
	}

	/**
	 * Delete object in database
	 *
	 * @param User $user       User that deletes
	 * @param bool $notrigger  false=launch triggers after, true=disable triggers
	 * @return int             <0 if KO, >0 if OK
	 */
	public function delete(User $user, $notrigger = false)
	{
		$this->deleteAllContacts($user);
		return $this->deleteCommon($user, $notrigger);
	}

	/**
	 * Add contact in distribution list
	 *
	 * @param User $user        User that deletes
	 * @param int $fk_socpeople id of contact to add in distribution list
	 * @param bool $notrigger   false=launch triggers after, true=disable triggers
	 * @param bool $set_new_nb_contacts   false=nb_contacts attribute is not updated in database, true=nb_contacts attribute is updated in database
	 * @return int             <0 if KO, >0 if OK
	 */
	public function addContact(User $user, $fk_socpeople, $notrigger = false, $set_new_nb_contacts=true)
	{
		require_once __DIR__ . '/distributionlistsocpeople.class.php';

		$o = new DistributionListSocpeople($this->db);
		$TRes = $o->fetchAll('', '', 0, 0, array('customsql'=>' fk_socpeople = '.$fk_socpeople.' AND fk_distributionlist = '.$this->id));

		if(empty($TRes)) { // N'existe pas encore dans la liste
			$o->fk_socpeople = $fk_socpeople;
			$o->fk_distributionlist = $this->id;
			$res = $o->create($user);
			if($res > 0) {
				if($set_new_nb_contacts) {
					$this->nb_contacts+=1;
					$this->update($user);
				}
				return 1;
			} else return -1;
		} else return 0; // Existe déjà dans la liste
	}

	/**
	 * Delete contact of distribution list
	 *
	 * @param User $user        User that deletes
	 * @param int $fk_socpeople id of contact to remove from distribution list
	 * @param bool $notrigger   false=launch triggers after, true=disable triggers
	 * @param bool $set_new_nb_contacts   false=nb_contacts attribute is not updated in database, true=nb_contacts attribute is updated in database
	 * @return int             <0 if KO, >0 if OK
	 */
	public function deleteContact(User $user, $fk_socpeople, $notrigger = false, $set_new_nb_contacts=true)
	{

		require_once __DIR__ . '/distributionlistsocpeople.class.php';

		$o = new DistributionListSocpeople($this->db);
		$TRes = $o->fetchAll('', '', 0, 0, array('customsql'=>' fk_socpeople = '.$fk_socpeople.' AND fk_distributionlist = '.$this->id));

		if(!empty($TRes)) {
			$obj = $TRes[key($TRes)];
			$res = $obj->delete($user);
			if($res > 0) {
				if($set_new_nb_contacts) {
					$this->nb_contacts-=1;
					$this->update($user);
				}
				return 1;

			} else return -1;
		}
	}

	/**
	 * Delete object in database
	 *
	 * @param User $user       User that deletes
	 * @param bool $notrigger  false=launch triggers after, true=disable triggers
	 * @return int             <0 if KO, >0 if OK
	 */
	public function deleteAllContacts(User $user, $notrigger = false)
	{
		require_once __DIR__ . '/distributionlistsocpeople.class.php';
		$nb_del = 0;

		$o = new DistributionListSocpeople($this->db);

		$TRes = $o->fetchAll('', '', 0, 0, array('customsql'=>' fk_distributionlist = '.$this->id));
		if(!empty($TRes)) {
			foreach ($TRes as $obj) {
				if($obj->deleteCommon($user, $notrigger) > 0) $nb_del++;
			}
			$this->nb_contacts = 0;
			$this->update($user);
		}
		return $nb_del;
	}

	/**
	 *  Delete a line of object in database
	 *
	 *	@param  User	$user       User that delete
	 *  @param	int		$idline		Id of line to delete
	 *  @param 	bool 	$notrigger  false=launch triggers after, true=disable triggers
	 *  @return int         		>0 if OK, <0 if KO
	 */
	public function deleteLine(User $user, $idline, $notrigger = false)
	{
		if ($this->status < 0)
		{
			$this->error = 'ErrorDeleteLineNotAllowedByObjectStatus';
			return -2;
		}

		return $this->deleteLineCommon($user, $idline, $notrigger);
	}

    public function getAllContactIds() {
        global $user, $conf;
        dol_include_once('/contact/class/contact.class.php');
        $object = new Contact($this->db);
        $extrafields = new ExtraFields($this->db);

// Security check
        $id = GETPOST('id', 'int');
        $contactid = GETPOST('id', 'int');
        $ref = ''; // There is no ref for contacts
        if($user->socid) $socid = $user->socid;
        $result = restrictedArea($user, 'contact', $contactid, '');
        $socialnetworks = getArrayOfSocialNetworks();

        $sall = trim((GETPOST('search_all', 'alphanohtml') != '') ? GETPOST('search_all', 'alphanohtml') : GETPOST('sall', 'alphanohtml'));
        $search_cti = preg_replace('/^0+/', '', preg_replace('/[^0-9]/', '', GETPOST('search_cti', 'alphanohtml'))); // Phone number without any special chars
        $search_phone = GETPOST("search_phone", 'alpha');

        $search_id = trim(GETPOST("search_id", "int"));
        $search_firstlast_only = GETPOST("search_firstlast_only", 'alpha');
        $search_lastname = GETPOST("search_lastname", 'alpha');
        $search_firstname = GETPOST("search_firstname", 'alpha');
        $search_societe = GETPOST("search_societe", 'alpha');
        $search_poste = GETPOST("search_poste", 'alpha');
        $search_phone_perso = GETPOST("search_phone_perso", 'alpha');
        $search_phone_pro = GETPOST("search_phone_pro", 'alpha');
        $search_phone_mobile = GETPOST("search_phone_mobile", 'alpha');
        $search_fax = GETPOST("search_fax", 'alpha');
        $search_email = GETPOST("search_email", 'alpha');
        $search_no_email = GETPOST("search_no_email", 'int');
        if(! empty($conf->socialnetworks->enabled)) {
            foreach($socialnetworks as $key => $value) {
                if($value['active']) {
                    ${"search_".$key} = GETPOST("search_".$key, 'alpha');
                }
            }
        }
        $search_priv = GETPOST("search_priv", 'alpha');
        $search_categ = GETPOST("search_categ", 'int');
        $search_categ_thirdparty = GETPOST("search_categ_thirdparty", 'int');
        $search_categ_supplier = GETPOST("search_categ_supplier", 'int');
        $search_status = GETPOST("search_status", 'int');
        $search_type = GETPOST('search_type', 'alpha');
        $search_zip = GETPOST('search_zip', 'alpha');
        $search_town = GETPOST('search_town', 'alpha');
        $search_import_key = GETPOST("search_import_key", "alpha");
        $search_country = GETPOST("search_country", 'intcomma');
        $search_roles = GETPOST("search_roles", 'array');

        if($search_status == '') $search_status = 1; // always display active customer first

        $optioncss = GETPOST('optioncss', 'alpha');

        $type = GETPOST("type", 'aZ');
        $view = GETPOST("view", 'alpha');

        $limit = GETPOST('limit', 'int') ? GETPOST('limit', 'int') : $conf->liste_limit;
        $sortfield = GETPOST('sortfield', 'alpha');
        $sortorder = GETPOST('sortorder', 'alpha');
        $page = GETPOSTISSET('pageplusone') ? (GETPOST('pageplusone') - 1) : GETPOST("page", 'int');
        $userid = GETPOST('userid', 'int');
        $begin = GETPOST('begin');
        if(! $sortorder) $sortorder = "ASC";
        if(! $sortfield) $sortfield = "p.lastname";
// fetch optionals attributes and labels
        $extrafields->fetch_name_optionals_label($object->table_element);

        $search_array_options = $extrafields->getOptionalsFromPost($object->table_element, '', 'search_');

// List of fields to search into when doing a "search in all"
        $fieldstosearchall = array(
            'p.lastname' => 'Lastname',
            'p.firstname' => 'Firstname',
            'p.email' => 'EMail',
            's.nom' => "ThirdParty",
            'p.phone' => "Phone",
            'p.phone_perso' => "PhonePerso",
            'p.phone_mobile' => "PhoneMobile",
            'p.fax' => "Fax",
            'p.note_public' => "NotePublic",
            'p.note_private' => "NotePrivate",
        );

// Definition of fields for list
        $arrayfields = array(
            'p.rowid' => array('label' => "TechnicalID", 'position' => 1, 'checked' => ($conf->global->MAIN_SHOW_TECHNICAL_ID ? 1 : 0), 'enabled' => ($conf->global->MAIN_SHOW_TECHNICAL_ID ? 1 : 0)),
            'p.lastname' => array('label' => "Lastname", 'position' => 2, 'checked' => 1),
            'p.firstname' => array('label' => "Firstname", 'position' => 3, 'checked' => 1),
            'p.poste' => array('label' => "PostOrFunction", 'position' => 10, 'checked' => 1),
            'p.town' => array('label' => "Town", 'position' => 20, 'checked' => 0),
            'p.zip' => array('label' => "Zip", 'position' => 21, 'checked' => 0),
            'country.code_iso' => array('label' => "Country", 'position' => 22, 'checked' => 0),
            'p.phone' => array('label' => "Phone", 'position' => 30, 'checked' => 1),
            'p.phone_perso' => array('label' => "PhonePerso", 'position' => 31, 'checked' => 0),
            'p.phone_mobile' => array('label' => "PhoneMobile", 'position' => 32, 'checked' => 1),
            'p.fax' => array('label' => "Fax", 'position' => 33, 'checked' => 0),
            'p.email' => array('label' => "EMail", 'position' => 40, 'checked' => 1),
            'p.no_email' => array('label' => "No_Email", 'position' => 41, 'checked' => 0, 'enabled' => (! empty($conf->mailing->enabled))),
            'p.thirdparty' => array('label' => "ThirdParty", 'position' => 50, 'checked' => 1, 'enabled' => empty($conf->global->SOCIETE_DISABLE_CONTACTS)),
            'p.priv' => array('label' => "ContactVisibility", 'checked' => 1, 'position' => 200),
            'p.datec' => array('label' => "DateCreationShort", 'checked' => 0, 'position' => 500),
            'p.tms' => array('label' => "DateModificationShort", 'checked' => 0, 'position' => 500),
            'p.statut' => array('label' => "Status", 'checked' => 1, 'position' => 1000),
            'p.import_key' => array('label' => "ImportId", 'checked' => 0, 'position' => 1100),
        );
        if(! empty($conf->socialnetworks->enabled)) {
            foreach($socialnetworks as $key => $value) {
                if($value['active']) {
                    $arrayfields['p.'.$key] = array(
                        'label' => $value['label'],
                        'checked' => 0,
                        'position' => 300
                    );
                }
            }
        }
// Extra fields
        if(is_array($extrafields->attributes[$object->table_element]['label']) && count($extrafields->attributes[$object->table_element]['label']) > 0) {
            foreach($extrafields->attributes[$object->table_element]['label'] as $key => $val) {
                if(! empty($extrafields->attributes[$object->table_element]['list'][$key])) $arrayfields["ef.".$key] = array(
                    'label' => $extrafields->attributes[$object->table_element]['label'][$key],
                    'checked' => (($extrafields->attributes[$object->table_element]['list'][$key] < 0) ? 0 : 1),
                    'position' => $extrafields->attributes[$object->table_element]['pos'][$key],
                    'enabled' => (abs($extrafields->attributes[$object->table_element]['list'][$key]) != 3 && $extrafields->attributes[$object->table_element]['perms'][$key]),
                    'langfile' => $extrafields->attributes[$object->table_element]['langfile'][$key],
                );
            }
        }
        $object->fields = dol_sort_array($object->fields, 'position');
        $arrayfields = dol_sort_array($arrayfields, 'position');
// Selection of new fields
        include DOL_DOCUMENT_ROOT.'/core/actions_changeselectedfields.inc.php';

        // Did we click on purge search criteria ?
        if(GETPOST('button_removefilter_x', 'alpha') || GETPOST('button_removefilter.x', 'alpha') || GETPOST('button_removefilter', 'alpha'))    // All tests are required to be compatible with all browsers
        {
            $sall = "";
            $search_id = '';
            $search_firstlast_only = "";
            $search_lastname = "";
            $search_firstname = "";
            $search_societe = "";
            $search_town = "";
            $search_zip = "";
            $search_country = "";
            $search_poste = "";
            $search_phone = "";
            $search_phone_perso = "";
            $search_phone_pro = "";
            $search_phone_mobile = "";
            $search_fax = "";
            $search_email = "";
            $search_no_email = -1;
            if(! empty($conf->socialnetworks->enabled)) {
                foreach($socialnetworks as $key => $value) {
                    if($value['active']) {
                        ${"search_".$key} = "";
                    }
                }
            }
            $search_priv = "";
            $search_status = -1;
            $search_categ = '';
            $search_categ_thirdparty = '';
            $search_categ_supplier = '';
            $search_import_key = '';
            $toselect = '';
            $search_array_options = array();
            $search_roles = array();
        }
        if($search_priv < 0) $search_priv = '';

        $sql = "SELECT s.rowid as socid, s.nom as name,";
        $sql .= " p.rowid, p.lastname as lastname, p.statut, p.firstname, p.zip, p.town, p.poste, p.email, p.no_email,";
        $sql .= " p.socialnetworks, p.photo,";
        $sql .= " p.phone as phone_pro, p.phone_mobile, p.phone_perso, p.fax, p.fk_pays, p.priv, p.datec as date_creation, p.tms as date_update,";
        $sql .= " co.label as country, co.code as country_code";
// Add fields from extrafields
        if(! empty($extrafields->attributes[$object->table_element]['label'])) {
            foreach($extrafields->attributes[$object->table_element]['label'] as $key => $val) $sql .= ($extrafields->attributes[$object->table_element]['type'][$key] != 'separate' ? ", ef.".$key.' as options_'.$key : '');
        }
// Add fields from hooks
        $sql .= " FROM ".MAIN_DB_PREFIX."socpeople as p";
        if(is_array($extrafields->attributes[$object->table_element]['label']) && count($extrafields->attributes[$object->table_element]['label'])) $sql .= " LEFT JOIN ".MAIN_DB_PREFIX.$object->table_element."_extrafields as ef on (p.rowid = ef.fk_object)";
        $sql .= " LEFT JOIN ".MAIN_DB_PREFIX."c_country as co ON co.rowid = p.fk_pays";
        $sql .= " LEFT JOIN ".MAIN_DB_PREFIX."societe as s ON s.rowid = p.fk_soc";
        if(! empty($search_categ)) $sql .= ' LEFT JOIN '.MAIN_DB_PREFIX."categorie_contact as cc ON p.rowid = cc.fk_socpeople"; // We need this table joined to the select in order to filter by categ
        if(! empty($search_categ_thirdparty)) $sql .= ' LEFT JOIN '.MAIN_DB_PREFIX."categorie_societe as cs ON s.rowid = cs.fk_soc"; // We need this table joined to the select in order to filter by categ
        if(! empty($search_categ_supplier)) $sql .= ' LEFT JOIN '.MAIN_DB_PREFIX."categorie_fournisseur as cs2 ON s.rowid = cs2.fk_soc"; // We need this table joined to the select in order to filter by categ
        if(! $user->rights->societe->client->voir && ! $socid) $sql .= " LEFT JOIN ".MAIN_DB_PREFIX."societe_commerciaux as sc ON s.rowid = sc.fk_soc";
        $sql .= ' WHERE p.entity IN ('.getEntity('socpeople').')';
        if(! $user->rights->societe->client->voir && ! $socid) //restriction
        {
            $sql .= " AND (sc.fk_user = ".$user->id." OR p.fk_soc IS NULL)";
        }
        if(! empty($userid))    // propre au commercial
        {
            $sql .= " AND p.fk_user_creat=".$this->db->escape($userid);
        }

// Filter to exclude not owned private contacts
        if($search_priv != '0' && $search_priv != '1') {
            $sql .= " AND (p.priv='0' OR (p.priv='1' AND p.fk_user_creat=".$user->id."))";
        }
        else {
            if($search_priv == '0') $sql .= " AND p.priv='0'";
            if($search_priv == '1') $sql .= " AND (p.priv='1' AND p.fk_user_creat=".$user->id.")";
        }

        if($search_categ > 0) $sql .= " AND cc.fk_categorie = ".$this->db->escape($search_categ);
        if($search_categ == -2) $sql .= " AND cc.fk_categorie IS NULL";
        if($search_categ_thirdparty > 0) $sql .= " AND cs.fk_categorie = ".$this->db->escape($search_categ_thirdparty);
        if($search_categ_thirdparty == -2) $sql .= " AND cs.fk_categorie IS NULL";
        if($search_categ_supplier > 0) $sql .= " AND cs2.fk_categorie = ".$this->db->escape($search_categ_supplier);
        if($search_categ_supplier == -2) $sql .= " AND cs2.fk_categorie IS NULL";

        if($sall) $sql .= natural_search(array_keys($fieldstosearchall), $sall);
        if(strlen($search_phone)) $sql .= natural_search(array('p.phone', 'p.phone_perso', 'p.phone_mobile'), $search_phone);
        if(strlen($search_cti)) $sql .= natural_search(array('p.phone', 'p.phone_perso', 'p.phone_mobile'), $search_cti);
        if(strlen($search_firstlast_only)) $sql .= natural_search(array('p.lastname', 'p.firstname'), $search_firstlast_only);

        if($search_id > 0) $sql .= natural_search("p.rowid", $search_id, 1);
        if($search_lastname) $sql .= natural_search('p.lastname', $search_lastname);
        if($search_firstname) $sql .= natural_search('p.firstname', $search_firstname);
        if($search_societe) $sql .= natural_search('s.nom', $search_societe);
        if($search_country) $sql .= " AND p.fk_pays IN (".$search_country.')';
        if(strlen($search_poste)) $sql .= natural_search('p.poste', $search_poste);
        if(strlen($search_phone_perso)) $sql .= natural_search('p.phone_perso', $search_phone_perso);
        if(strlen($search_phone_pro)) $sql .= natural_search('p.phone', $search_phone_pro);
        if(strlen($search_phone_mobile)) $sql .= natural_search('p.phone_mobile', $search_phone_mobile);
        if(strlen($search_fax)) $sql .= natural_search('p.fax', $search_fax);
        if(! empty($conf->socialnetworks->enabled)) {
            foreach($socialnetworks as $key => $value) {
                if($value['active'] && strlen(${"search_".$key})) {
                    $sql .= ' AND p.socialnetworks LIKE \'%"'.$key.'":"'.${"search_".$key}.'%\'';
                }
            }
        }
        if(strlen($search_email)) $sql .= natural_search('p.email', $search_email);
        if(strlen($search_zip)) $sql .= natural_search("p.zip", $search_zip);
        if(strlen($search_town)) $sql .= natural_search("p.town", $search_town);
        if(count($search_roles) > 0) {
            $sql .= " AND p.rowid IN (SELECT sc.fk_socpeople FROM ".MAIN_DB_PREFIX."societe_contacts as sc WHERE sc.fk_c_type_contact IN (".implode(',', $search_roles)."))";
        }

        if($search_no_email != '' && $search_no_email >= 0) $sql .= " AND p.no_email = ".$this->db->escape($search_no_email);
        if($search_status != '' && $search_status >= 0) $sql .= " AND p.statut = ".$this->db->escape($search_status);
        if($search_import_key) $sql .= natural_search("p.import_key", $search_import_key);
        if($type == "o")        // filtre sur type
        {
            $sql .= " AND p.fk_soc IS NULL";
        }
        else if($type == "f")        // filtre sur type
        {
            $sql .= " AND s.fournisseur = 1";
        }
        else if($type == "c")        // filtre sur type
        {
            $sql .= " AND s.client IN (1, 3)";
        }
        else if($type == "p")        // filtre sur type
        {
            $sql .= " AND s.client IN (2, 3)";
        }
        if(! empty($socid)) {
            $sql .= " AND s.rowid = ".$socid;
        }
// Add where from extra fields
        include DOL_DOCUMENT_ROOT.'/core/tpl/extrafields_list_search_sql.tpl.php';

// Add order
        if($view == "recent") {
            $sql .= $this->db->order("p.datec", "DESC");
        }
        else {
            $sql .= $this->db->order($sortfield, $sortorder);
        }
        $TIds = array();
        $resql = $this->db->query($sql);
        if($resql && $this->db->num_rows($resql) > 0) {
            while($obj = $this->db->fetch_object($resql)) {
                $TIds[] = $obj->rowid;
            }
        }
        return $TIds;
    }


	/**
	 *	Validate object
	 *
	 *	@param		User	$user     		User making status change
	 *  @param		int		$notrigger		1=Does not execute triggers, 0= execute triggers
	 *	@return  	int						<=0 if OK, 0=Nothing done, >0 if KO
	 */
	public function validate($user, $notrigger = 0)
	{
		global $conf, $langs;

		require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';

		$error = 0;

		// Protection
		if ($this->status == self::STATUS_VALIDATED)
		{
			dol_syslog(get_class($this)."::validate action abandonned: already validated", LOG_WARNING);
			return 0;
		}

		/*if (! ((empty($conf->global->MAIN_USE_ADVANCED_PERMS) && ! empty($user->rights->distributionlist->distributionlist->write))
		 || (! empty($conf->global->MAIN_USE_ADVANCED_PERMS) && ! empty($user->rights->distributionlist->distributionlist->distributionlist_advance->validate))))
		 {
		 $this->error='NotEnoughPermissions';
		 dol_syslog(get_class($this)."::valid ".$this->error, LOG_ERR);
		 return -1;
		 }*/

		$now = dol_now();

		$this->db->begin();

		// Define new ref
		if (!$error && (preg_match('/^[\(]?PROV/i', $this->ref) || empty($this->ref))) // empty should not happened, but when it occurs, the test save life
		{
			$num = $this->getNextNumRef();
		}
		else
		{
			$num = $this->ref;
		}
		$this->newref = $num;

		if (!empty($num)) {
			// Validate
			$sql = "UPDATE ".MAIN_DB_PREFIX.$this->table_element;
			$sql .= " SET ref = '".$this->db->escape($num)."',";
			$sql .= " status = ".self::STATUS_VALIDATED;
			if (!empty($this->fields['date_valid'])) $sql .= ", date_valid = '".$this->db->idate($now)."'";
			if (!empty($this->fields['fk_user_valid'])) $sql .= ", fk_user_valid = ".$user->id;
			$sql .= " WHERE rowid = ".$this->id;

			dol_syslog(get_class($this)."::validate()", LOG_DEBUG);
			$resql = $this->db->query($sql);
			if (!$resql)
			{
				dol_print_error($this->db);
				$this->error = $this->db->lasterror();
				$error++;
			}

			if (!$error && !$notrigger)
			{
				// Call trigger
				$result = $this->call_trigger('DISTRIBUTIONLIST_VALIDATE', $user);
				if ($result < 0) $error++;
				// End call triggers
			}
		}

		if (!$error)
		{
			$this->oldref = $this->ref;

			// Rename directory if dir was a temporary ref
			if (preg_match('/^[\(]?PROV/i', $this->ref))
			{
				// Now we rename also files into index
				$sql = 'UPDATE '.MAIN_DB_PREFIX."ecm_files set filename = CONCAT('".$this->db->escape($this->newref)."', SUBSTR(filename, ".(strlen($this->ref) + 1).")), filepath = 'distributionlist/".$this->db->escape($this->newref)."'";
				$sql .= " WHERE filename LIKE '".$this->db->escape($this->ref)."%' AND filepath = 'distributionlist/".$this->db->escape($this->ref)."' and entity = ".$conf->entity;
				$resql = $this->db->query($sql);
				if (!$resql) { $error++; $this->error = $this->db->lasterror(); }

				// We rename directory ($this->ref = old ref, $num = new ref) in order not to lose the attachments
				$oldref = dol_sanitizeFileName($this->ref);
				$newref = dol_sanitizeFileName($num);
				$dirsource = $conf->distributionlist->dir_output.'/distributionlist/'.$oldref;
				$dirdest = $conf->distributionlist->dir_output.'/distributionlist/'.$newref;
				if (!$error && file_exists($dirsource))
				{
					dol_syslog(get_class($this)."::validate() rename dir ".$dirsource." into ".$dirdest);

					if (@rename($dirsource, $dirdest))
					{
						dol_syslog("Rename ok");
						// Rename docs starting with $oldref with $newref
						$listoffiles = dol_dir_list($conf->distributionlist->dir_output.'/distributionlist/'.$newref, 'files', 1, '^'.preg_quote($oldref, '/'));
						foreach ($listoffiles as $fileentry)
						{
							$dirsource = $fileentry['name'];
							$dirdest = preg_replace('/^'.preg_quote($oldref, '/').'/', $newref, $dirsource);
							$dirsource = $fileentry['path'].'/'.$dirsource;
							$dirdest = $fileentry['path'].'/'.$dirdest;
							@rename($dirsource, $dirdest);
						}
					}
				}
			}
		}

		// Set new ref and current status
		if (!$error)
		{
			$this->ref = $num;
			$this->status = self::STATUS_VALIDATED;
		}

		if (!$error)
		{
			$this->db->commit();
			return 1;
		}
		else
		{
			$this->db->rollback();
			return -1;
		}
	}


	/**
	 *	Set draft status
	 *
	 *	@param	User	$user			Object user that modify
	 *  @param	int		$notrigger		1=Does not execute triggers, 0=Execute triggers
	 *	@return	int						<0 if KO, >0 if OK
	 */
	public function setDraft($user, $notrigger = 0)
	{
		// Protection
		if ($this->status <= self::STATUS_DRAFT)
		{
			return 0;
		}

		/*if (! ((empty($conf->global->MAIN_USE_ADVANCED_PERMS) && ! empty($user->rights->distributionlist->write))
		 || (! empty($conf->global->MAIN_USE_ADVANCED_PERMS) && ! empty($user->rights->distributionlist->distributionlist_advance->validate))))
		 {
		 $this->error='Permission denied';
		 return -1;
		 }*/

		$this->date_valid = '';
		$this->fk_user_valid = 0;
		$this->update($user);
		return $this->setStatusCommon($user, self::STATUS_DRAFT, $notrigger, 'DISTRIBUTIONLIST_UNVALIDATE');
	}

	/**
	 *	Set cancel status
	 *
	 *	@param	User	$user			Object user that modify
	 *  @param	int		$notrigger		1=Does not execute triggers, 0=Execute triggers
	 *	@return	int						<0 if KO, 0=Nothing done, >0 if OK
	 */
	public function cancel($user, $notrigger = 0)
	{
		// Protection
		if ($this->status != self::STATUS_VALIDATED)
		{
			return 0;
		}

		/*if (! ((empty($conf->global->MAIN_USE_ADVANCED_PERMS) && ! empty($user->rights->distributionlist->write))
		 || (! empty($conf->global->MAIN_USE_ADVANCED_PERMS) && ! empty($user->rights->distributionlist->distributionlist_advance->validate))))
		 {
		 $this->error='Permission denied';
		 return -1;
		 }*/

		if(empty($this->date_cloture)) {
			$this->date_cloture = GETPOST('date_clotureyear', 'int') . '-' . GETPOST('date_cloturemonth', 'int') . '-' . GETPOST('date_clotureday', 'int');
		}
		if(empty($this->fk_user_cloture)) $this->fk_user_cloture = $user->id;
		$this->update($user);
		return $this->setStatusCommon($user, self::STATUS_CLOSED, $notrigger, 'DISTRIBUTIONLIST_CLOSE');
	}

	/**
	 *	Set back to validated status
	 *
	 *	@param	User	$user			Object user that modify
	 *  @param	int		$notrigger		1=Does not execute triggers, 0=Execute triggers
	 *	@return	int						<0 if KO, 0=Nothing done, >0 if OK
	 */
	public function reopen($user, $notrigger = 0)
	{
		// Protection
		if ($this->status != self::STATUS_CLOSED)
		{
			return 0;
		}

		/*if (! ((empty($conf->global->MAIN_USE_ADVANCED_PERMS) && ! empty($user->rights->distributionlist->write))
		 || (! empty($conf->global->MAIN_USE_ADVANCED_PERMS) && ! empty($user->rights->distributionlist->distributionlist_advance->validate))))
		 {
		 $this->error='Permission denied';
		 return -1;
		 }*/

		$this->date_cloture = '';
		$this->fk_user_cloture = 0;
		$this->update($user);
		return $this->setStatusCommon($user, self::STATUS_VALIDATED, $notrigger, 'DISTRIBUTIONLIST_REOPEN');
	}

	/**
	 *  Return a link to the object card (with optionaly the picto)
	 *
	 *  @param  int     $withpicto                  Include picto in link (0=No picto, 1=Include picto into link, 2=Only picto)
	 *  @param  string  $option                     On what the link point to ('nolink', ...)
	 *  @param  int     $notooltip                  1=Disable tooltip
	 *  @param  string  $morecss                    Add more css on link
	 *  @param  int     $save_lastsearch_value      -1=Auto, 0=No save of lastsearch_values when clicking, 1=Save lastsearch_values whenclicking
	 *  @return	string                              String with URL
	 */
	public function getNomUrl($withpicto = 0, $option = '', $notooltip = 0, $morecss = '', $save_lastsearch_value = -1)
	{
		global $conf, $langs, $hookmanager;

		if (!empty($conf->dol_no_mouse_hover)) $notooltip = 1; // Force disable tooltips

		$result = '';

		$label = '<u>'.$langs->trans("DistributionList").'</u>';
		$label .= '<br>';
		$label .= '<b>'.$langs->trans('Ref').':</b> '.$this->ref;
		$label .= '<br>';
		$label .= '<b>'.$langs->trans('Label').':</b> '.$this->label;
		if (isset($this->status)) {
			$label .= '<br><b>'.$langs->trans("Status").":</b> ".$this->getLibStatut(5);
		}
		if($this->status > 1 && !empty($this->date_cloture)) {
			$label .= '<br>';
			$label .= '<b>'.$langs->trans('DateClosing').':</b> '.date('d/m/Y', $this->date_cloture);
		}

		$url = dol_buildpath('/distributionlist/distributionlist_card.php', 1).'?id='.$this->id;

		if ($option != 'nolink')
		{
			// Add param to save lastsearch_values or not
			$add_save_lastsearch_values = ($save_lastsearch_value == 1 ? 1 : 0);
			if ($save_lastsearch_value == -1 && preg_match('/list\.php/', $_SERVER["PHP_SELF"])) $add_save_lastsearch_values = 1;
			if ($add_save_lastsearch_values) $url .= '&save_lastsearch_values=1';
		}

		$linkclose = '';
		if (empty($notooltip))
		{
			if (!empty($conf->global->MAIN_OPTIMIZEFORTEXTBROWSER))
			{
				$label = $langs->trans("ShowDistributionList");
				$linkclose .= ' alt="'.dol_escape_htmltag($label, 1).'"';
			}
			$linkclose .= ' title="'.dol_escape_htmltag($label, 1).'"';
			$linkclose .= ' class="classfortooltip'.($morecss ? ' '.$morecss : '').'"';
		}
		else $linkclose = ($morecss ? ' class="'.$morecss.'"' : '');

		$linkstart = '<a href="'.$url.'"';
		$linkstart .= $linkclose.'>';
		$linkend = '</a>';

		$result .= $linkstart;

		if (empty($this->showphoto_on_popup)) {
			if ($withpicto) $result .= img_object(($notooltip ? '' : $label), ($this->picto ? $this->picto : 'generic'), ($notooltip ? (($withpicto != 2) ? 'class="paddingright"' : '') : 'class="'.(($withpicto != 2) ? 'paddingright ' : '').'classfortooltip"'), 0, 0, $notooltip ? 0 : 1);
		} else {
			if ($withpicto) {
				require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';

				list($class, $module) = explode('@', $this->picto);
				$upload_dir = $conf->$module->multidir_output[$conf->entity]."/$class/".dol_sanitizeFileName($this->ref);
				$filearray = dol_dir_list($upload_dir, "files");
				$filename = $filearray[0]['name'];
				if (!empty($filename)) {
					$pospoint = strpos($filearray[0]['name'], '.');

					$pathtophoto = $class.'/'.$this->ref.'/thumbs/'.substr($filename, 0, $pospoint).'_mini'.substr($filename, $pospoint);
					if (empty($conf->global->{strtoupper($module.'_'.$class).'_FORMATLISTPHOTOSASUSERS'})) {
						$result .= '<div class="floatleft inline-block valignmiddle divphotoref"><div class="photoref"><img class="photo'.$module.'" alt="No photo" border="0" src="'.DOL_URL_ROOT.'/viewimage.php?modulepart='.$module.'&entity='.$conf->entity.'&file='.urlencode($pathtophoto).'"></div></div>';
					}
					else {
						$result .= '<div class="floatleft inline-block valignmiddle divphotoref"><img class="photouserphoto userphoto" alt="No photo" border="0" src="'.DOL_URL_ROOT.'/viewimage.php?modulepart='.$module.'&entity='.$conf->entity.'&file='.urlencode($pathtophoto).'"></div>';
					}

					$result .= '</div>';
				}
				else {
					$result .= img_object(($notooltip ? '' : $label), ($this->picto ? $this->picto : 'generic'), ($notooltip ? (($withpicto != 2) ? 'class="paddingright"' : '') : 'class="'.(($withpicto != 2) ? 'paddingright ' : '').'classfortooltip"'), 0, 0, $notooltip ? 0 : 1);
				}
			}
		}

		if ($withpicto != 2) $result .= $this->ref;

		$result .= $linkend;
		//if ($withpicto != 2) $result.=(($addlabel && $this->label) ? $sep . dol_trunc($this->label, ($addlabel > 1 ? $addlabel : 0)) : '');

		global $action, $hookmanager;
		$hookmanager->initHooks(array('distributionlistdao'));
		$parameters = array('id'=>$this->id, 'getnomurl'=>$result);
		$reshook = $hookmanager->executeHooks('getNomUrl', $parameters, $this, $action); // Note that $action and $object may have been modified by some hooks
		if ($reshook > 0) $result = $hookmanager->resPrint;
		else $result .= $hookmanager->resPrint;

		return $result;
	}

	/**
	 *  Return label of the status
	 *
	 *  @param  int		$mode          0=long label, 1=short label, 2=Picto + short label, 3=Picto, 4=Picto + long label, 5=Short label + Picto, 6=Long label + Picto
	 *  @return	string 			       Label of status
	 */
	public function getLibStatut($mode = 0)
	{
		return $this->LibStatut($this->status, $mode);
	}

	// phpcs:disable PEAR.NamingConventions.ValidFunctionName.ScopeNotCamelCaps
	/**
	 *  Return the status
	 *
	 *  @param	int		$status        Id status
	 *  @param  int		$mode          0=long label, 1=short label, 2=Picto + short label, 3=Picto, 4=Picto + long label, 5=Short label + Picto, 6=Long label + Picto
	 *  @return string 			       Label of status
	 */
	public function LibStatut($status, $mode = 0)
	{
		// phpcs:enable
		if (empty($this->labelStatus) || empty($this->labelStatusShort))
		{
			global $langs;
			//$langs->load("distributionlist@distributionlist");
			$this->labelStatus[self::STATUS_DRAFT] = $langs->trans('Draft');
			$this->labelStatus[self::STATUS_VALIDATED] = $langs->trans('Enabled');
			$this->labelStatus[self::STATUS_CLOSED] = $langs->trans('Closed');
			$this->labelStatusShort[self::STATUS_DRAFT] = $langs->trans('Draft');
			$this->labelStatusShort[self::STATUS_VALIDATED] = $langs->trans('Enabled');
			$this->labelStatusShort[self::STATUS_CLOSED] = $langs->trans('Closed');
		}

		$statusType = 'status'.$status;
		//if ($status == self::STATUS_VALIDATED) $statusType = 'status1';
		if ($status == self::STATUS_CLOSED) $statusType = 'status6';

		return dolGetStatus($this->labelStatus[$status], $this->labelStatusShort[$status], '', $statusType, $mode);
	}

	/**
	 *	Load the info information in the object
	 *
	 *	@param  int		$id       Id of object
	 *	@return	void
	 */
	public function info($id)
	{
		$sql = 'SELECT rowid, date_creation as datec, tms as datem, date_cloture, date_valid as datev,';
		$sql .= ' fk_user_creat, fk_user_valid, fk_user_modif, fk_user_cloture';
		$sql .= ' FROM '.MAIN_DB_PREFIX.$this->table_element.' as t';
		$sql .= ' WHERE t.rowid = '.$id;
		$result = $this->db->query($sql);
		if ($result)
		{
			if ($this->db->num_rows($result))
			{
				$obj = $this->db->fetch_object($result);
				$this->id = $obj->rowid;
				if ($obj->fk_user_creat)
				{
					$cuser = new User($this->db);
					$cuser->fetch($obj->fk_user_creat);
					$this->user_creation = $cuser;
				}

				if ($obj->fk_user_valid)
				{
					$vuser = new User($this->db);
					$vuser->fetch($obj->fk_user_valid);
					$this->user_validation = $vuser;
				}

				if ($obj->fk_user_modif)
				{
					$muser = new User($this->db);
					$muser->fetch($obj->fk_user_modif);
					$this->user_modification = $muser;
				}

				if ($obj->fk_user_cloture)
				{
					$cluser = new User($this->db);
					$cluser->fetch($obj->fk_user_cloture);
					$this->user_cloture = $cluser;
				}

				$this->date_creation     = $this->db->jdate($obj->datec);
				$this->date_modification = $this->db->jdate($obj->datem);
				$this->date_validation   = $this->db->jdate($obj->datev);
				$this->date_cloture   	 = $this->db->jdate($obj->date_cloture);
			}

			$this->db->free($result);
		}
		else
		{
			dol_print_error($this->db);
		}
	}

	/**
	 * Initialise object with example values
	 * Id must be 0 if object instance is a specimen
	 *
	 * @return void
	 */
	public function initAsSpecimen()
	{
		$this->initAsSpecimenCommon();
	}

	/**
	 * 	Create an array of lines
	 *
	 * 	@return array|int		array of lines if OK, <0 if KO
	 */
	public function getLinesArray()
	{
		$this->lines = array();

		$objectline = new DistributionListLine($this->db);
		$result = $objectline->fetchAll('ASC', 'position', 0, 0, array('customsql'=>'fk_distributionlist = '.$this->id));

		if (is_numeric($result))
		{
			$this->error = $this->error;
			$this->errors = $this->errors;
			return $result;
		}
		else
		{
			$this->lines = $result;
			return $this->lines;
		}
	}

	/**
	 *  Returns the reference to the following non used object depending on the active numbering module.
	 *
	 *  @return string      		Object free reference
	 */
	public function getNextNumRef()
	{
		global $langs, $conf;
		$langs->load("distributionlist@distributionlist");

		if (empty($conf->global->DISTRIBUTIONLIST_DISTRIBUTIONLIST_ADDON)) {
			$conf->global->DISTRIBUTIONLIST_DISTRIBUTIONLIST_ADDON = 'mod_distributionlist_standard';
		}

		if (!empty($conf->global->DISTRIBUTIONLIST_DISTRIBUTIONLIST_ADDON))
		{
			$mybool = false;

			$file = $conf->global->DISTRIBUTIONLIST_DISTRIBUTIONLIST_ADDON.".php";
			$classname = $conf->global->DISTRIBUTIONLIST_DISTRIBUTIONLIST_ADDON;

			// Include file with class
			$dirmodels = array_merge(array('/'), (array) $conf->modules_parts['models']);
			foreach ($dirmodels as $reldir)
			{
				$dir = dol_buildpath($reldir."core/modules/distributionlist/");

				// Load file with numbering class (if found)
				$mybool |= @include_once $dir.$file;
			}

			if ($mybool === false)
			{
				dol_print_error('', "Failed to include file ".$file);
				return '';
			}

			if (class_exists($classname)) {
				$obj = new $classname();
				$numref = $obj->getNextValue($this);

				if ($numref != '' && $numref != '-1')
				{
					return $numref;
				}
				else
				{
					$this->error = $obj->error;
					//dol_print_error($this->db,get_class($this)."::getNextNumRef ".$obj->error);
					return "";
				}
			} else {
				print $langs->trans("Error")." ".$langs->trans("ClassNotFound").' '.$classname;
				return "";
			}
		}
		else
		{
			print $langs->trans("ErrorNumberingModuleNotSetup", $this->element);
			return "";
		}
	}

	/**
	 *  Create a document onto disk according to template module.
	 *
	 *  @param	    string		$modele			Force template to use ('' to not force)
	 *  @param		Translate	$outputlangs	objet lang a utiliser pour traduction
	 *  @param      int			$hidedetails    Hide details of lines
	 *  @param      int			$hidedesc       Hide description
	 *  @param      int			$hideref        Hide ref
	 *  @param      null|array  $moreparams     Array to provide more information
	 *  @return     int         				0 if KO, 1 if OK
	 */
	public function generateDocument($modele, $outputlangs, $hidedetails = 0, $hidedesc = 0, $hideref = 0, $moreparams = null)
	{
		global $conf, $langs;

		$result = 0;
		$includedocgeneration = 0;

		$langs->load("distributionlist@distributionlist");

		if (!dol_strlen($modele)) {
			$modele = 'standard_distributionlist';

			if ($this->modelpdf) {
				$modele = $this->modelpdf;
			} elseif (!empty($conf->global->DISTRIBUTIONLIST_ADDON_PDF)) {
				$modele = $conf->global->DISTRIBUTIONLIST_ADDON_PDF;
			}
		}

		$modelpath = "core/modules/distributionlist/doc/";

		if ($includedocgeneration) {
			$result = $this->commonGenerateDocument($modelpath, $modele, $outputlangs, $hidedetails, $hidedesc, $hideref, $moreparams);
		}

		return $result;
	}

	/**
	 * Action executed by scheduler
	 * CAN BE A CRON TASK. In such a case, parameters come from the schedule job setup field 'Parameters'
	 * Use public function doScheduledJob($param1, $param2, ...) to get parameters
	 *
	 * @return	int			0 if OK, <>0 if KO (this function is used also by cron so only 0 is OK)
	 */
	public function doScheduledJob()
	{
		global $conf, $langs;

		//$conf->global->SYSLOG_FILE = 'DOL_DATA_ROOT/dolibarr_mydedicatedlofile.log';

		$error = 0;
		$this->output = '';
		$this->error = '';

		dol_syslog(__METHOD__, LOG_DEBUG);

		$now = dol_now();

		$this->db->begin();

		// ...

		$this->db->commit();

		return $error;
	}
}

/**
 * Class DistributionListLine. You can also remove this and generate a CRUD class for lines objects.
 */
class DistributionListLine
{
	// To complete with content of an object DistributionListLine
	// We should have a field rowid, fk_distributionlist and position

	/**
	 * @var int  Does object support extrafields ? 0=No, 1=Yes
	 */
	public $isextrafieldmanaged = 0;

	/**
	 * Constructor
	 *
	 * @param DoliDb $db Database handler
	 */
	public function __construct(DoliDB $db)
	{
		$this->db = $db;
	}
}
