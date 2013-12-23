<?php
/**
 * @category  Joomla Component
 * @package   osmeta
 * @author    JoomBoss
 * @copyright 2012, JoomBoss. All rights reserved
 * @copyright 2013 Open Source Training, LLC. All rights reserved
 * @contact   www.ostraining.com, support@ostraining.com
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @version   1.0.0
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

abstract class MetatagsContainer{
	public function setMetadata($itemId, $data){
	    $itemTypeId = $this->getTypeId();
		$keywords = $data["metakeywords"];

		$db = JFactory::getDBO();
		//Save metatitles and metadata
		$sql = "INSERT INTO #__osmeta_metadata
		(title,
         title_tag,
		 description,
		 item_id,
		 item_type)
		VALUES (
		  ".$db->quote($data["metatitle"]). " ,
		  ".$db->quote($data["title_tag"]). " ,
		  ".$db->quote($data["metadescription"]). ",
		  ".$db->quote($itemId).",
		  ".$db->quote($itemTypeId).")
		ON DUPLICATE KEY UPDATE title=".$db->quote($data["metatitle"]). ",
		title_tag=".$db->quote($data["title_tag"]).",
        description = ".$db->quote($data["metadescription"]);
		$db->setQuery($sql);
        $db->query();
	    if ($db->getErrorNum()) {
            echo $db->stderr();
            return false;
        }
		//Save keywords
		$this->saveKeywords($keywords, $itemId, $itemTypeId);
	}

	public function saveKeywords($keywords, $itemId, $itemTypeId){
	    $db = JFactory::getDBO();
	    $sql = "DELETE FROM #__osmeta_keywords_items
        WHERE item_id=".$db->quote($itemId)." AND item_type_id=".$db->quote($itemTypeId);
        $db->setQuery($sql);
        $db->query();
    if ($db->getErrorNum()) {
            echo $db->stderr();
            return false;
        }
        $keywords_arr = explode("," , $keywords);
        foreach($keywords_arr as $keyword){
            $keyword = trim($keyword);
            if (!$keyword){
                continue;
            }
            $sql = "SELECT id FROM #__osmeta_keywords WHERE name=".$db->quote($keyword);
            $db->setQuery($sql);
            $id = $db->loadResult();
            if (!$id){
                 $sql = "INSERT INTO #__osmeta_keywords (name) VALUES (".$db->quote($keyword).")";
                 $db->setQuery($sql);
                 $db->query();
                 $id= $db->insertid();
            }

            $sql = "INSERT IGNORE INTO #__osmeta_keywords_items (keyword_id, item_id, item_type_id)
             VALUES (".$db->quote($id).", ".$db->quote($itemId).",".$db->quote($itemTypeId).")";
            $db->setQuery($sql);
            $db->query();
        if ($db->getErrorNum()) {
            echo $db->stderr();
            return false;
        }

        }

        $sql = "DELETE FROM #__osmeta_keywords WHERE NOT EXISTS (SELECT 1 FROM #__osmeta_keywords_items  WHERE keyword_id=#__osmeta_keywords.id)";
        $db->setQuery($sql);
        $db->query();
	}

	public function getMetadata($id){
	  $db = JFactory::getDBO();
	  $sql = "SELECT m.item_id as id,
	  m.title_tag as title_tag,
	  (SELECT GROUP_CONCAT(k.name SEPARATOR ',')
	    FROM #__osmeta_keywords k,
	    #__osmeta_keywords_items ki
	    WHERE ki.item_id=m.item_id and ki.item_type_id=".$db->quote($this->getTypeId())."
	    AND ki.keyword_id=k.id
	 ) AS metakeywords,
	  m.description as metadescription,
	  m.title as metatitle
	  FROM
	  #__osmeta_metadata m
	  WHERE m.item_id=".$db->quote($id)."
	    AND m.item_type=".$db->quote($this->getTypeId());
	  $db->setQuery($sql);
	  return $db->loadAssoc();
	}

	public function clearBrowserTitles($ids){
      foreach($ids as $key=>$value){
	    if (!is_numeric($value)){
	      unset($ids[$key]);
	    }
	  }
	  if (count($ids)>0){
    	  $db = JFactory::getDBO();
    	  $db->setQuery("UPDATE #__osmeta_metadata SET title_tag=''
    	                 WHERE item_id IN ('".implode("','", $ids)."')
    	                AND item_type=".$db->quote($this->getTypeId()));
    	  $db->query();
	  }
	}

	public function mustReplaceTitle(){
	  return true;
	}
	public function mustReplaceMetaTitle(){
	  return true;
	}
	public function mustReplaceMetaKeywords(){
	  return true;
	}
	public function mustReplaceMetaDescription(){
	  return true;
	}

	public abstract function getTypeId();

	public abstract function getMetadataByRequest($query);
	/**
	 * Stores item metadata
	 * @param String $url - query string
	 * @param Array $data
	 * $data should contain followin keys:
	 * - metatitle
	 * - title_tag
	 * - metakeywords
	 * - metadescription
	 */
	public abstract function setMetadataByRequest($url, $data);

	public abstract function isAvailable();

}
