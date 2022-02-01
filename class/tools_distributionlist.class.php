<?php

class ToolsDistributionlist
{

	/**
	 * Fonction qui permet de vérifier si une liste de distribution avec ce label existe déjà
	 *
	 * @param string $label
	 * @return int -1 if KO, 0 if not exist, positiv int id if exist
	 */
	static function distributionlist_alreadyexist($label)
	{
		global $db;

		$sql = "SELECT rowid as id FROM " . MAIN_DB_PREFIX . "distributionlist_distributionlist";
		$sql .= " WHERE label = '" . $label . "'";

		$resql = $db->query($sql);

		if($resql){
			if($db->num_rows($resql) > 0){
				$obj = $db->fetch_object($resql);
				return $obj->id;
			} else {
				return 0;
			}

		} else {
			return -1;
		}
	}
}
