<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
	|                                                                             |
	| This program is free software; you can redistribute it and/or               |
	| modify it under the terms of the GNU General Public License                 |
	| as published by the Free Software Foundation; either version 2              |
	| of the License, or (at your option) any later version.                      |
	|                                                                             |
	| This program is distributed in the hope that it will be useful,             |
	| but WITHOUT ANY WARRANTY; without even the implied warranty of              |
	| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
	| GNU General Public License for more details.                                |
	|                                                                             |
	| You should have received a copy of the GNU General Public License           |
	| along with this program; if not, write to the Free Software                 |
	| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
	+-----------------------------------------------------------------------------+
*/

/**
* Class ilFulltextMetaDataSearch
*
* class for searching meta 
*
* @author Stefan Meyer <smeyer@databay.de>
* @version $Id
* 
* @package ilias-search
*
*/
include_once 'Services/Search/classes/class.ilMetaDataSearch.php';

class ilFulltextMetaDataSearch extends ilMetaDAtaSearch
{

	/**
	* Constructor
	* @access public
	*/
	function ilFulltextMetaDataSearch(&$qp_obj)
	{
		parent::ilMetaDataSearch($qp_obj);
	}

	// Private
	function __searchKeywordContribute()
	{
		// Todo: add contribute

		$query = "SELECT obj_id,rbac_id,obj_type FROM il_meta_keyword as kw";

		// IN BOOLEAN MODE
		if($this->db->isMysql4_0OrHigher())
		{
			$query .= " WHERE MATCH(keyword) AGAINST('";
			$prefix = $this->query_parser->getCombination() == 'and' ? '+' : '';
			foreach($this->query_parser->getWords() as $word)
			{
				$query .= $prefix;
				$query .= $word;
				$query .= '* ';
			}
			$query .= "' IN BOOLEAN MODE) ";
		}
		else
		{
			// Mysql 3.23
			$query .= " WHERE ";
			$counter = 0;
			foreach($this->query_parser->getWords() as $word)
			{
				if($counter++)
				{
					$query .= strtoupper($this->query_parser->getCombination());
				}
				$query .= " MATCH (keyword) AGAINST('";
				$query .= $word;
				$query .= "') ";
			}
		}			
			
		$res = $this->db->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$this->search_result->addEntry($row->obj_id,$row->obj_type,$row->rbac_id);
		}

		return $this->search_result;
	}		
}
?>
