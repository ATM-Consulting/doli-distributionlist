<?php
/* Copyright (C) 2018-2018 Andre Schild        <a.schild@aarboard.ch>
 * Copyright (C) 2005-2010 Laurent Destailleur <eldy@users.sourceforge.net>
 * Copyright (C) 2005-2009 Regis Houssin       <regis.houssin@inodbox.com>
 *
 * This file is an example to follow to add your own email selector inside
 * the Dolibarr email tool.
 * Follow instructions given in README file to know what to change to build
 * your own emailing list selector.
 * Code that need to be changed in this file are marked by "CHANGE THIS" tag.
 */

/**
 *	\file       htdocs/core/modules/mailings/distributionlist.modules.php
 *	\ingroup    mailing
 *	\brief      Example file to provide a list of distribution list for mailing module
 */

include_once DOL_DOCUMENT_ROOT.'/core/modules/mailings/modules_mailings.php';
dol_include_once('/distributionlist/class/distributionlist.class.php');



/**
 *	Class to manage a list of distribution list for mailing feature
 */
class mailing_distributionlist extends MailingTargets
{
	public $name = 'DistributionList';
	// This label is used if no translation is found for key XXX neither MailingModuleDescXXX where XXX=name is found
	public $desc = "Distribution List";
	public $require_admin = 0;

	public $require_module = array("distributionlist"); // This module allows to select by categories must be also enabled if category module is not activated

	/**
	 * @var string String with name of icon for myobject. Must be the part after the 'object_' into object_myobject.png
	 */
	public $picto = 'distributionlist@distributionlist';

	/**
	 * @var DoliDB Database handler.
	 */
	public $db;


	/**
	 *	Constructor
	 *
	 *  @param		DoliDB		$db      Database handler
	 */
	public function __construct($db)
	{
		global $conf, $langs;
		$langs->load("companies");

		$this->db = $db;
	}


	// phpcs:disable PEAR.NamingConventions.ValidFunctionName.ScopeNotCamelCaps
	/**
	 *    This is the main function that returns the array of emails
	 *
	 *    @param	int		$mailing_id    	Id of mailing. No need to use it.
	 *    @return   int 					<0 if error, number of emails added if ok
	 */
	public function add_to_target($mailing_id)
	{
		// phpcs:enable
		global $conf, $langs;

		$cibles = array();
		$this->db->begin();
		$addDescription = "";
		$num = 0;

		// Select the third parties from category
		if (!empty($_POST['filter_distributionlist']))
		{
			$sql = "SELECT DISTINCT d.rowid as id, sp.email as email, sp.lastname as name, sp.rowid as fk_contact, sp.firstname as firstname";
			$sql .= " FROM ".MAIN_DB_PREFIX."distributionlist_distributionlistsocpeople as ds";
			$sql .= " JOIN ".MAIN_DB_PREFIX."distributionlist_distributionlist as d ON d.rowid = ds.fk_distributionlist";
			$sql .= " JOIN ".MAIN_DB_PREFIX."socpeople as sp ON ds.fk_socpeople = sp.rowid";
			$sql .= " WHERE sp.email <> ''";
			$sql .= " AND d.entity IN (".getEntity('distributionlist').")";
			$sql .= " AND sp.email NOT IN (SELECT email FROM ".MAIN_DB_PREFIX."mailing_cibles WHERE fk_mailing=".$mailing_id.")";
			$sql .= " AND ds.fk_distributionlist =".$_POST["filter_distributionlist"];
			$sql .= " group by email";
			$res = $this->db->query($sql);
			if ($res) {
				$num = $this->db->num_rows($res);

				$sql = "INSERT INTO llx_mailing_cibles (fk_mailing, fk_contact, lastname, firstname, email, other, source_url, source_id, source_type)";
				$sql .= " SELECT DISTINCT ".$mailing_id.", sp.rowid AS fk_contact, sp.lastname AS lastname, sp.firstname AS firstname, sp.email AS email, '',";
				$sql .= " CONCAT('<a href=\"".DOL_MAIN_URL_ROOT."/contact/card.php?id=',sp.rowid,'\"><span class=\"fas fa-address-book paddingright classfortooltip\" style=\" color: #37a;\"></span>',sp.firstname, ' ', sp.lastname,'</a>') as source_url,";
				$sql .= " d.rowid AS source_id, 'distributionlist' as source_type";
				$sql .= " FROM ".MAIN_DB_PREFIX."distributionlist_distributionlistsocpeople AS ds";
				$sql .= " JOIN ".MAIN_DB_PREFIX."distributionlist_distributionlist AS d ON d.rowid = ds.fk_distributionlist";
				$sql .= " JOIN ".MAIN_DB_PREFIX."socpeople AS sp ON ds.fk_socpeople = sp.rowid";
				$sql .= " WHERE sp.email <> ''";
				$sql .= " AND d.entity IN(".getEntity('distributionlist').")";
				$sql .= " AND sp.email NOT IN(SELECT email FROM ".MAIN_DB_PREFIX."mailing_cibles WHERE fk_mailing=".$mailing_id.")";
				$sql .= " AND ds.fk_distributionlist=".$_POST["filter_distributionlist"];
				$sql .= " group by email";

				$result = $this->db->query($sql);
			}
		}

		// Stock recipients emails into targets table
		if ($result)
		{
			$this->db->commit();
			return $num;
		}
		else
		{
			dol_syslog($this->db->error());
			$this->db->rollback();
			$this->error = $this->db->error();
			return -1;
		}

		//return parent::addTargetsToDatabase($mailing_id, $cibles);
	}


	/**
	 *	On the main mailing area, there is a box with statistics.
	 *	If you want to add a line in this report you must provide an
	 *	array of SQL request that returns two field:
	 *	One called "label", One called "nb".
	 *
	 *	@return		array		Array with SQL requests
	 */
	public function getSqlArrayForStats()
	{
		// CHANGE THIS: Optionnal

		//var $statssql=array();
		//$this->statssql[0]="SELECT field1 as label, count(distinct(email)) as nb FROM mytable WHERE email IS NOT NULL";
		return array();
	}


	/**
	 *	Return here number of distinct emails returned by your selector.
	 *	For example if this selector is used to extract 500 different
	 *	emails from a text file, this function must return 500.
	 *
	 *  @param      string	$sql        Requete sql de comptage
	 *	@return		int					Nb of recipients
	 */
	public function getNbOfRecipients($sql = '')
	{
		return '';
	}

	/**
	 *  This is to add a form filter to provide variant of selector
	 *	If used, the HTML select must be called "filter"
	 *
	 *  @return     string      A html select zone
	 */
	public function formFilter()
	{
		global $conf, $langs;

		$langs->load("DistributionList");

		$distributionlist = new DistributionList($this->db);
		$TDistributionList = $distributionlist->fetchAll();

		$s = '<select name="filter_distributionlist" class="flat">';

		foreach($TDistributionList as $id=>$distributionlist) {
			$s .= '<option value="'.$id.'">'.$distributionlist->label.'</option>';
		}

		$s .= '</select> ';

		return $s;
	}


	/**
	 *  Can include an URL link on each record provided by selector shown on target page.
	 *
	 *  @param	int		$id		ID
	 *  @return string      	Url link
	 */
	public function url($id, $lastname, $firstname)
	{
		return '<a href="'.DOL_MAIN_URL_ROOT.'/contact/card.php?id='.$id.'"><span class="fas fa-address-book paddingright classfortooltip" style=" color: #37a;"></span>'.$firstname.' '.$lastname .'</a>';
	}
}
