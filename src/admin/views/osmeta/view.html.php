<?php
/**
 * @package   OSMeta
 * @contact   www.alledia.com, support@alledia.com
 * @copyright 2013-2014 Alledia.com, All rights reserved
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */

// No direct access
defined('_JEXEC') or die();

jimport('cms.view.legacy');

/**
 * Metatags Manager Default View
 *
 * @since  1.0
 */
class OSMetaViewOSMeta extends JViewLegacy
{
    /**
     * Class constructor method
     *
     * @param mix $config Configuration set
     *
     * @access  public
     * @since   1.0
     */
    public function __construct($config = null)
    {
        parent::__construct($config);

        $this->_addPath('template', $this->_basePath . '/views/osmeta/tmpl');
    }

    /**
     * Method to display the view
     *
     * @param string $tpl Template file
     *
     * @access  public
     *
     * @return void
     */
    public function display($tpl = null)
    {
        JToolBarHelper::title(JText::_('COM_OSMETA_META_TAGS_MANAGER'), 'logo');

        JToolBarHelper::apply("save");

        if (version_compare(JVERSION, '3.0', '>=')) {
            $iconShuffle = 'shuffle';
            $iconEdit = 'pencil-2';
        } else {
            $iconShuffle = 'refresh';
            $iconEdit = 'edit';
        }

        JToolBarHelper::custom(
            'copyItemTitleToSearchEngineTitle',
            $iconShuffle,
            '',
            JText::_('COM_OSMETA_COPY_ITEM_TITLE_TO_TITLE'),
            true
        );

        JToolBarHelper::custom(
            'generateDescriptions',
            $iconEdit,
            '',
            JText::_('COM_OSMETA_GENERATE_DESCRIPTIONS'),
            true
        );

        JToolBarHelper::cancel("cancel");

        $doc = JFactory::getDocument();

        if (version_compare(JVERSION, '3.0', 'ge')) {
            $doc->addStylesheet('../media/com_osmeta/admin/css/main-j3.css');
            // Add the icon font for the logo
            $doc->addStylesheet('../media/com_osmeta/admin/css/alledia.css');
        } else {
            $doc->addStylesheet('../media/com_osmeta/admin/css/main-j2.css');
        }

        parent::display($tpl);
    }
}