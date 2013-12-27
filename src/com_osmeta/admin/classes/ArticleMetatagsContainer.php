<?php
/**
 * @category   Joomla Component
 * @package    com_osmeta
 * @author     JoomBoss
 * @copyright  2012, JoomBoss. All rights reserved
 * @copyright  2013 Open Source Training, LLC. All rights reserved
 * @contact    www.ostraining.com, support@ostraining.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @version    1.0.0
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

require_once 'MetatagsContainer.php';

/**
 * Article Metatags Container
 *
 * @since  1.0.0
 */
class ArticleMetatagsContainer extends MetatagsContainer
{
    /**
     * Is front page?
     *
     * @var    bool
     * @since  1.0.0
     */
    private $isFrontpage = false;

    /**
     * Code
     *
     * @var    int
     * @since  1.0.0
     */
    private $code = 1;

    /**
     * Get Meta Tags
     *
     * @param int $lim0   Offset
     * @param int $lim    Limit
     * @param int $filter Filter
     *
     * @access  public
     *
     * @return array
     */
    public function getMetatags($lim0, $lim, $filter = null)
    {
        if (version_compare(JVERSION, "2.5", "ge")) {
            return $this->getMetatags25($lim0, $lim, $filter);
        }

        $db = JFactory::getDBO();

        $sql = "SELECT SQL_CALC_FOUND_ROWS c.id, c.title, c.metakey,
            c.metadesc, m.title as metatitle,
            m.title_tag as title_tag
            FROM
            #__content c
            LEFT JOIN
            #__osmeta_metadata m ON m.item_id=c.id and m.item_type=1 WHERE 1";
        $search = JRequest::getVar("com_content_filter_search", "");
        $section_id = JRequest::getVar("com_content_filter_sectionid", "-1");
        $cat_id = JRequest::getVar("com_content_filter_catid", "0");
        $author_id = JRequest::getVar("com_content_filter_authorid", "0");
        $state = JRequest::getVar("com_content_filter_state", "");
        $com_content_filter_show_empty_keywords = JRequest::getVar("com_content_filter_show_empty_keywords", "-1");
        $com_content_filter_show_empty_descriptions = JRequest::getVar("com_content_filter_show_empty_descriptions", "-1");

        if ($search != "") {
            if (is_numeric($search)) {
                $sql .= " AND c.id=" . $db->quote($search);
            } else {
                $sql .= " AND c.title LIKE " . $db->quote('%' . $search . '%');
            }
        }

        if ($section_id > 0) {
            $sql .= " AND c.sectionid=" . $db->quote($section_id);
        }

        if ($cat_id > 0) {
            $sql .= " AND c.catid=" . $db->quote($cat_id);
        }

        if ($author_id > 0) {
            $sql .= " AND c.created_by=" . $db->quote($author_id);
        }

        switch ($state) {
            case 'P':
                $sql .= " AND c.state=1";
                break;

            case 'U':
                $sql .= " AND c.state=0";
                break;

            case 'A':
                $sql .= " AND c.state=-1";
                break;

            case 'All':
                break;

            default:
                $sql .= " AND c.state=1";
                break;
        }

        if ($com_content_filter_show_empty_keywords != "-1") {
            $sql .= " AND (ISNULL(c.metakey) OR c.metakey='') ";
        }

        if ($com_content_filter_show_empty_descriptions != "-1") {
            $sql .= " AND (ISNULL(c.metadesc) OR c.metadesc='') ";
        }

        // Sorting
        $order = JRequest::getCmd("filter_order", "title");
        $order_dir = JRequest::getCmd("filter_order_Dir", "ASC");

        switch ($order) {
            case "meta_title":
                $sql .= " ORDER BY metatitle ";
                break;
            case "meta_key":
                $sql .= " ORDER BY metakey ";
                break;
            case "meta_desc":
                $sql .= " ORDER BY metadesc ";
                break;

            case "title_tag":
                $sql .= " ORDER BY title_tag ";
                break;

            default:
                $sql .= " ORDER BY title ";
                break;
        }

        if ($order_dir == "asc") {
            $sql .= " ASC";
        } else {
            $sql .= " DESC";
        }

        $db->setQuery($sql, $lim0, $lim);
        $rows = $db->loadObjectList();

        if ($db->getErrorNum()) {
            echo $db->stderr();

            return false;
        }

        for ($i = 0; $i < count($rows); $i++) {
            if (JVERSION == "1.7") {
                $rows[$i]->edit_url = "index.php?option=com_content&task=article.edit&id={$rows[$i]->id}";
            } else {
                $rows[$i]->edit_url = "index.php?option=com_content&task=edit&view=article&cid[]={$rows[$i]->id}";
            }
        }

        return $rows;
    }

    /**
     * Get Meta Tags for Joomla >= 2.5
     *
     * @param int $lim0   Offset
     * @param int $lim    Limit
     * @param int $filter Filter
     *
     * @access  public
     *
     * @return array
     */
    public function getMetatags25($lim0, $lim, $filter = null)
    {
        $db = JFactory::getDBO();

        $sql = "SELECT SQL_CALC_FOUND_ROWS c.id, c.title, c.metakey,
            c.metadesc, m.title as metatitle, m.title_tag as title_tag
            FROM
            #__content c
            LEFT JOIN #__categories cc ON cc.id=c.catid
            LEFT JOIN
            #__osmeta_metadata m ON m.item_id=c.id and m.item_type=1 WHERE 1";

        $search = JRequest::getVar("com_content_filter_search", "");
        $cat_id = JRequest::getVar("com_content_filter_catid", "0");
        $level = JRequest::getVar("com_content_filter_level", "0");
        $author_id = JRequest::getVar("com_content_filter_authorid", "0");
        $state = JRequest::getVar("com_content_filter_state", "");
        $com_content_filter_show_empty_keywords = JRequest::getVar("com_content_filter_show_empty_keywords", "-1");
        $com_content_filter_show_empty_descriptions = JRequest::getVar("com_content_filter_show_empty_descriptions", "-1");

        if ($search != "") {
            if (is_numeric($search)) {
                $sql .= " AND c.id=" . $db->quote($search);
            } else {
                $sql .= " AND c.title LIKE " . $db->quote('%' . $search . '%');
            }
        }

        $baselevel = 1;

        if ($cat_id > 0) {
            $db->setQuery("SELECT * from #__categories where id=" . $db->quote($cat_id));
            $cat_tbl = $db->loadObject();
            $rgt = $cat_tbl->rgt;
            $lft = $cat_tbl->lft;
            $baselevel = (int) $cat_tbl->level;
            $sql .= ' AND cc.lft >= ' . (int) $lft;
            $sql .= ' AND cc.rgt <= ' . (int) $rgt;
        }

        if ($level > 0) {
            $sql .= ' AND cc.level <=' . ((int) $level + (int) $baselevel - 1);
        }

        if ($author_id > 0) {
            $sql .= " AND c.created_by=" . $db->quote($author_id);
        }

        switch ($state) {
            case 'P':
                $sql .= " AND c.state=1";
                break;

            case 'U':
                $sql .= " AND c.state=0";
                break;

            case 'A':
                $sql .= " AND c.state=-1";
                break;

            case 'D':
                $sql .= " AND c.state=-2";
                break;

            case 'All':
                break;

            default:
                $sql .= " AND c.state=1";
                break;
        }

        if ($com_content_filter_show_empty_keywords != "-1") {
            $sql .= " AND (ISNULL(c.metakey) OR c.metakey='') ";
        }

        if ($com_content_filter_show_empty_descriptions != "-1") {
            $sql .= " AND (ISNULL(c.metadesc) OR c.metadesc='') ";
        }

        // Sorting
        $order = JRequest::getCmd("filter_order", "title");
        $order_dir = JRequest::getCmd("filter_order_Dir", "ASC");

        switch ($order) {
            case "meta_title":
                $sql .= " ORDER BY metatitle ";
                break;

            case "meta_key":
                $sql .= " ORDER BY metakey ";
                break;

            case "meta_desc":
                $sql .= " ORDER BY metadesc ";
                break;

            case "title_tag":
                $sql .= " ORDER BY title_tag ";
                break;

            default:
                $sql .= " ORDER BY title ";
            break;

        }

        if ($order_dir == "asc") {
            $sql .= " ASC";
        } else {
            $sql .= " DESC";
        }

        $db->setQuery($sql, $lim0, $lim);
        $rows = $db->loadObjectList();

        if ($db->getErrorNum()) {
            echo $db->stderr();

            return false;
        }

        for ($i = 0; $i < count($rows); $i++) {
            $rows[$i]->edit_url = "index.php?option=com_content&task=article.edit&id={$rows[$i]->id}";
        }

        return $rows;
    }

    /**
     * Get Pages
     *
     * @param int $lim0   Offset
     * @param int $lim    Limit
     * @param int $filter Filter
     *
     * @access  public
     *
     * @return array
     */
    public function getPages($lim0, $lim, $filter = null)
    {
        if (version_compare(JVERSION, "2.5", "ge")) {
            return $this->getPages25($lim0, $lim, $filter);
        }

        $db = JFactory::getDBO();
        $sql = "SELECT SQL_CALC_FOUND_ROWS c.id, c.title, c.metakey, c.state,
        if (c.fulltext != '', c.fulltext, c.introtext) AS content
         FROM
        #__content c WHERE 1
        ";

        $search = JRequest::getVar("com_content_filter_search", "");
        $section_id = JRequest::getVar("com_content_filter_sectionid", "-1");
        $cat_id = JRequest::getVar("com_content_filter_catid", "0");
        $author_id = JRequest::getVar("com_content_filter_authorid", "0");
        $state = JRequest::getVar("com_content_filter_state", "");
        $com_content_filter_show_empty_keywords = JRequest::getVar("com_content_filter_show_empty_keywords", "-1");
        $com_content_filter_show_empty_descriptions = JRequest::getVar("com_content_filter_show_empty_descriptions", "-1");

        if ($search != "") {
            if (is_numeric($search)) {
                $sql .= " AND c.id=" . $db->quote($search);
            } else {
                $sql .= " AND c.title LIKE " . $db->quote('%' . $search . '%');
            }
        }

        if ($section_id > 0) {
            $sql .= " AND c.sectionid=" . $db->quote($section_id);
        }

        if ($cat_id > 0) {
            $sql .= " AND c.catid=" . $db->quote($cat_id);
        }

        if ($author_id > 0) {
            $sql .= " AND c.created_by=" . $db->quote($author_id);
        }

        switch ($state) {
            case 'P':
                $sql .= " AND c.state=1";
                break;

            case 'U':
                $sql .= " AND c.state=0";
                break;

            case 'A':
                $sql .= " AND c.state=-1";
                break;

            case 'All':
                break;

            default:
                $sql .= " AND c.state=1";
                break;
        }

        if ($com_content_filter_show_empty_keywords != "-1") {
            $sql .= " AND (ISNULL(c.metakey) OR c.metakey='') ";
        }

        if ($com_content_filter_show_empty_descriptions != "-1") {
            $sql .= " AND (ISNULL(c.metadesc) OR c.metadesc='') ";
        }

        $db->setQuery($sql, $lim0, $lim);
        $rows = $db->loadObjectList();

        if ($db->getErrorNum()) {
            echo $db->stderr();

            return false;
        }

        // Get outgoing links and keywords density
        for ($i = 0; $i < count($rows); $i++) {
            if ($rows[$i]->metakey) {
                $rows[$i]->metakey = explode(",", $rows[$i]->metakey);
            } else {
                $rows[$i]->metakey = array("");
            }

            if (JVERSION == "1.7") {
                $rows[$i]->edit_url = "index.php?option=com_content&task=article.edit&id={$rows[$i]->id}";
            } else {
                $rows[$i]->edit_url = "index.php?option=com_content&task=edit&view=article&cid[]={$rows[$i]->id}";
            }
        }

        return $rows;
    }

    /**
     * Get Pages for Joomla >= 2.5
     *
     * @param int $lim0   Offset
     * @param int $lim    Limit
     * @param int $filter Filter
     *
     * @access  public
     *
     * @return array
     */
    public function getPages25($lim0, $lim, $filter = null)
    {
        $db = JFactory::getDBO();

        $sql = "SELECT SQL_CALC_FOUND_ROWS c.id, c.title, c.metakey, c.state,
            if (c.fulltext != '', c.fulltext, c.introtext) AS content
             FROM
            #__content c
            LEFT JOIN #__categories cc ON cc.id=c.catid
            WHERE 1
            ";

        $search = JRequest::getVar("com_content_filter_search", "");
        $cat_id = JRequest::getVar("com_content_filter_catid", "0");
        $author_id = JRequest::getVar("com_content_filter_authorid", "0");
        $level = JRequest::getVar("com_content_filter_level", "0");
        $state = JRequest::getVar("com_content_filter_state", "");
        $com_content_filter_show_empty_keywords = JRequest::getVar("com_content_filter_show_empty_keywords", "-1");
        $com_content_filter_show_empty_descriptions = JRequest::getVar("com_content_filter_show_empty_descriptions", "-1");

        if ($search != "") {
            if (is_numeric($search)) {
                $sql .= " AND c.id=" . $db->quote($search);
            } else {
                $sql .= " AND c.title LIKE " . $db->quote('%' . $search . '%');
            }
        }

        $baselevel = 1;

        if ($cat_id > 0) {
            $db->setQuery("SELECT * from #__categories where id=" . $db->quote($cat_id));
            $cat_tbl = $db->loadObject();
            $rgt = $cat_tbl->rgt;
            $lft = $cat_tbl->lft;
            $baselevel = (int) $cat_tbl->level;
            $sql .= ' AND cc.lft >= ' . (int) $lft;
            $sql .= ' AND cc.rgt <= ' . (int) $rgt;
        }

        if ($level > 0) {
            $sql .= ' AND cc.level <=' . ((int) $level + (int) $baselevel - 1);
        }

        if ($author_id > 0) {
            $sql .= " AND c.created_by=" . $db->quote($author_id);
        }

        switch ($state) {
            case 'P':
                $sql .= " AND c.state=1";
                break;

            case 'U':
                $sql .= " AND c.state=0";
                break;

            case 'A':
                $sql .= " AND c.state=-1";
                break;

            case 'D':
                $sql .= " AND c.state=-2";
                break;

            case 'All':
                break;

            default:
                $sql .= " AND c.state=1";
                break;
        }

        if ($com_content_filter_show_empty_keywords != "-1") {
            $sql .= " AND (ISNULL(c.metakey) OR c.metakey='') ";
        }

        if ($com_content_filter_show_empty_descriptions != "-1") {
            $sql .= " AND (ISNULL(c.metadesc) OR c.metadesc='') ";
        }

        $db->setQuery($sql, $lim0, $lim);
        $rows = $db->loadObjectList();

        if ($db->getErrorNum()) {
            echo $db->stderr();

            return false;
        }

        // Get outgoing links and keywords density
        for ($i = 0; $i < count($rows); $i++) {
            if ($rows[$i]->metakey) {
                $rows[$i]->metakey = explode(",", $rows[$i]->metakey);
            } else {
                $rows[$i]->metakey = array("");
            }

            $rows[$i]->edit_url = "index.php?option=com_content&task=article.edit&id={$rows[$i]->id}";
        }

        return $rows;
    }

    /**
     * Save meta tags
     *
     * @param array $ids              IDs
     * @param array $metatitles       Meta titles
     * @param array $metadescriptions Meta Descriptions
     * @param array $metakeys         Meta Keys
     * @param array $titleTags        Title tags
     *
     * @access  public
     *
     * @return void
     */
    public function saveMetatags($ids, $metatitles, $metadescriptions, $metakeys, $titleTags = null)
    {
        $db = JFactory::getDBO();

        for ($i = 0; $i < count($ids); $i++) {
            $sql = "UPDATE #__content SET metakey=" . $db->quote($metakeys[$i]) . " , metadesc="
                . $db->quote($metadescriptions[$i]) . " WHERE id=" . $db->quote($ids[$i]);
            $db->setQuery($sql);
            $db->query();
            $sql = "INSERT INTO #__osmeta_metadata (item_id,
            item_type, title, description, title_tag)
            VALUES (
            " . $db->quote($ids[$i]) . ",
            1,
            " . $db->quote($metatitles[$i]) . ",
            " . $db->quote($metadescriptions[$i]) . ",
            " . $db->quote($titleTags != null ? $titleTags[$i] : '') . "
            ) ON DUPLICATE KEY UPDATE title=" . $db->quote($metatitles[$i]) . " , description="
                . $db->quote($metadescriptions[$i]) .
            ", title_tag=" . $db->quote($titleTags != null ? $titleTags[$i] : '');

            $db->setQuery($sql);
            $db->query();

            parent::saveKeywords($metakeys[$i], $ids[$i], 1);
        }
    }

    /**
     * Method to save the Keywords
     *
     * @param string $keywords   Keywords as CSV
     * @param int    $itemId     Item Id
     * @param string $itemTypeId Item Type Id
     *
     * @access  public
     *
     * @return void
     */
    public function saveKeywords($keywords, $itemId, $itemTypeId = null)
    {
        parent::saveKeywords($keywords, $itemId, $itemTypeId ? $itemTypeId : 1);
    }

    /**
     * Method to copy the keywords to title
     *
     * @param array $ids IDs list
     *
     * @access  public
     *
     * @return void
     */
    public function copyKeywordsToTitle($ids)
    {
        $db = JFactory::getDBO();

        foreach ($ids as $key => $value) {
            if (!is_numeric($value)) {
                unset($ids[$key]);
            }
        }

        $sql = "SELECT id, metakey FROM #__content WHERE id IN (" . implode(",", $ids) . ")";
        $db->setQuery($sql);
        $items = $db->loadObjectList();

        foreach ($items as $item) {
            if ($item->metakey != '') {
                $sql = "INSERT INTO #__osmeta_metadata (item_id,
                    item_type, title, description)
                    VALUES (
                    " . $db->quote($item->id) . ",
                    1,
                    " . $db->quote($item->metakey) . ",
                    ''
                    ) ON DUPLICATE KEY UPDATE title=" . $db->quote($item->metakey);

                $db->setQuery($sql);
                $db->query();
            }
        }
    }

    /**
     * Method to copy the title to keyword
     *
     * @param array $ids IDs list
     *
     * @access  public
     *
     * @return void
     */
    public function copyTitleToKeywords($ids)
    {
        $db = JFactory::getDBO();

        foreach ($ids as $key => $value) {
            if (!is_numeric($value)) {
                unset($ids[$key]);
            }
        }

        $sql = "SELECT item_id, title FROM #__osmeta_metadata WHERE item_id IN ("
            . implode(",", $ids) . ") AND item_type=1";
        $db->setQuery($sql);
        $items = $db->loadObjectList();

        foreach ($items as $item) {
            if ($item->title != '') {
                $sql = "UPDATE #__content SET metakey=" . $db->quote($item->title) . "
                    WHERE id=" . $db->quote($item->item_id);
                $db->setQuery($sql);
                $db->query();
            }
        }
    }

    /**
     * Method to copy the item title to title
     *
     * @param array $ids IDs list
     *
     * @access  public
     *
     * @return void
     */
    public function copyItemTitleToTitle($ids)
    {
        $db = JFactory::getDBO();

        foreach ($ids as $key => $value) {
            if (!is_numeric($value)) {
                unset($ids[$key]);
            }
        }

        $sql = "SELECT id, title FROM  #__content WHERE id IN (" . implode(",", $ids) . ")";
        $db->setQuery($sql);
        $items = $db->loadObjectList();

        foreach ($items as $item) {
            if ($item->title != '') {
                $sql = "INSERT INTO #__osmeta_metadata (item_id,
                    item_type, title, description)
                    VALUES (
                    " . $db->quote($item->id) . ",
                    1,
                    " . $db->quote($item->title) . ",
                    ''
                    ) ON DUPLICATE KEY UPDATE title=" . $db->quote($item->title);

                $db->setQuery($sql);
                $db->query();
            }
        }
    }

    /**
     * Method to copy the item title to keywords
     *
     * @param array $ids IDs list
     *
     * @access  public
     *
     * @return void
     */
    public function copyItemTitleToKeywords($ids)
    {
        $db = JFactory::getDBO();

        foreach ($ids as $key => $value) {
            if (!is_numeric($value)) {
                unset($ids[$key]);
            }
        }

        $sql = "UPDATE #__content SET metakey=title WHERE id IN (" . implode(",", $ids) . ")";
        $db->setQuery($sql);
        $items = $db->query();
    }

    /**
     * Method to generate descriptions
     *
     * @param array $ids IDs list
     *
     * @access  public
     *
     * @return void
     */
    public function generateDescriptions($ids)
    {
        $max_description_length = 500;
        $model = OSModel::getInstance("options", "OSModel");
        $params = $model->getOptions();
        $max_description_length = $params->max_description_length ?
            $params->max_description_length : $max_description_length;

        $db = JFactory::getDBO();

        foreach ($ids as $key => $value) {
            if (!is_numeric($value)) {
                unset($ids[$key]);
            }
        }

        $sql = "SELECT id, introtext FROM  #__content WHERE id IN (" . implode(",", $ids) . ")";
        $db->setQuery($sql);
        $items = $db->loadObjectList();

        foreach ($items as $item) {
            if ($item->introtext != '') {
                $introtext = strip_tags($item->introtext);

                if (strlen($introtext) > $max_description_length) {
                    $introtext = substr($introtext, 0, $max_description_length);
                }

                $sql = "INSERT INTO #__osmeta_metadata (item_id,
                    item_type, title, description)
                    VALUES (
                    " . $db->quote($item->id) . ",
                    1,

                    '',
                    " . $db->quote($introtext) . "
                    ) ON DUPLICATE KEY UPDATE description=" . $db->quote($introtext);

                $db->setQuery($sql);
                $db->query();

                $sql = "UPDATE #__content SET metadesc=" . $db->quote($introtext) . "
                    WHERE id=" . $db->quote($item->id);

                $db->setQuery($sql);
                $db->query();
            }
        }
    }

    /**
     * Method to get Filter
     *
     * @access  public
     *
     * @return string
     */
    public function getFilter()
    {
        $db = JFactory::getDBO();
        $search = JRequest::getVar("com_content_filter_search", "");
        $cat_id = JRequest::getVar("com_content_filter_catid", "0");
        $level = JRequest::getVar("com_content_filter_level", "0");

        // Levels filter.
        $levels = array();
        $levels[]   = JHtml::_('select.option', '1', JText::_('J1'));
        $levels[]   = JHtml::_('select.option', '2', JText::_('J2'));
        $levels[]   = JHtml::_('select.option', '3', JText::_('J3'));
        $levels[]   = JHtml::_('select.option', '4', JText::_('J4'));
        $levels[]   = JHtml::_('select.option', '5', JText::_('J5'));
        $levels[]   = JHtml::_('select.option', '6', JText::_('J6'));
        $levels[]   = JHtml::_('select.option', '7', JText::_('J7'));
        $levels[]   = JHtml::_('select.option', '8', JText::_('J8'));
        $levels[]   = JHtml::_('select.option', '9', JText::_('J9'));
        $levels[]   = JHtml::_('select.option', '10', JText::_('J10'));

        $author_id = JRequest::getVar("com_content_filter_authorid", "0");
        $state = JRequest::getVar("com_content_filter_state", "");
        $com_content_filter_show_empty_keywords = JRequest::getVar("com_content_filter_show_empty_keywords", "-1");
        $com_content_filter_show_empty_descriptions = JRequest::getVar("com_content_filter_show_empty_descriptions", "-1");

        $result = 'Filter:
            <input type="text" name="com_content_filter_search" id="search" value="' . $search . '" class="text_area" onchange="document.adminForm.submit();" title="Filter by Title or enter an Article ID"/>
            <button id="Go" onclick="this.form.submit();">Go</button>
            <button onclick="document.getElementById(\'search\').value=\'\';this.form.getElementById(\'filter_sectionid\').value=\'-1\';this.form.getElementById(\'catid\').value=\'0\';this.form.getElementById(\'filter_authorid\').value=\'0\';this.form.getElementById(\'filter_state\').value=\'\';this.form.submit();">Reset</button>

            &nbsp;&nbsp;&nbsp;';

        $result .= '<select name="com_content_filter_catid" class="inputbox" onchange="submitform();">' .
                        '<option value="">Select category</option>' .
        JHtml::_('select.options', JHtml::_('category.options', 'com_content'), 'value', 'text', $cat_id) .
                    '</select>';

        $result .= '<select name="com_content_filter_level" class="inputbox" onchange="this.form.submit()">' .
                '<option value="">Select max levels</option>' .
                JHtml::_('select.options', $levels, 'value', 'text', $level) .
            '</select>';

        $result .= '

            <select name="com_content_filter_state" id="filter_state" class="inputbox" size="1" onchange="submitform();">
            <option value=""  >- Select State -</option>
            <option value="P" ' . ($state == 'P' ? 'selected="selected"' : '') . '>Published</option>
            <option value="U" ' . ($state == 'U' ? 'selected="selected"' : '') . '>Unpublished</option>
            <option value="A" ' . ($state == 'A' ? 'selected="selected"' : '') . '>Archived</option>
            <option value="D" ' . ($state == 'D' ? 'selected="selected"' : '') . '>Trashed</option>
            <option value="All" ' . ($state == 'All' ? 'selected="selected"' : '') . '>All</option>
            </select>
            <br/>
            <label>Show only Articles with empty keywords</label>
            <input type="checkbox" onchange="document.adminForm.submit();" name="com_content_filter_show_empty_keywords" ' . ($com_content_filter_show_empty_keywords != "-1" ? 'checked="yes" ' : '') . '/>
            <label>Show only Articles with empty descriptions</label>
            <input type="checkbox" onchange="document.adminForm.submit();" name="com_content_filter_show_empty_descriptions" ' . ($com_content_filter_show_empty_descriptions != "-1" ? 'checked="yes" ' : '') . '/>                ';

        return $result;
    }

    /**
     * Method to set Metadata
     *
     * @param int   $id   ID
     * @param array $data Data
     *
     * @access  public
     *
     * @return void
     */
    public function setMetadata($id, $data)
    {
        $db = JFactory::getDBO();
        $sql = "UPDATE #__content SET " .
            (isset($data["title"])&&$data["title"]?
            "`title` = " . $db->quote($data["title"]) . ",":"") . "
            `metakey` = " . $db->quote($data["metakeywords"]) . ",
            `metadesc` = " . $db->quote($data["metadescription"]) . "
            WHERE `id`=" . $db->quote($id);
        $db->setQuery($sql);
        $db->query();

        parent::setMetadata($id, $data);
    }

    /**
     * Method to get the type id
     *
     * @access  public
     *
     * @return int
     */
    public function getTypeId()
    {
        return $this->code;
    }

    /**
     * Method to get Metadata
     *
     * @param string $query Query
     *
     * @access  public
     *
     * @return array
     */
    public function getMetadataByRequest($query)
    {
        $params = array();
        parse_str($query, $params);
        $metadata = null;

        if (isset($params["id"])) {
            $metadata = $this->getMetadata($params["id"]);
        }

        return $metadata;
    }

    /**
     * Method to set Metadata by request
     *
     * @param string $url  URL
     * @param array  $data Data
     *
     * @access  public
     *
     * @return void
     */
    public function setMetadataByRequest($url, $data)
    {
        $params = array();
        parse_str($url, $params);

        if (isset($params["id"]) && $params["id"]) {
            $this->setMetadata($params["id"], $data);
        }
    }

    /**
     * Method to get check if the container is available
     *
     * @access  public
     *
     * @return bool
     */
    public function isAvailable()
    {
        return true;
    }
}
