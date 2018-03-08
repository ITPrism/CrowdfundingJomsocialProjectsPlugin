<?php
/**
 * @package      Crowdfunding
 * @subpackage   Plug-ins
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2017 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

jimport('Prism.init');
jimport('Crowdfunding.init');

if (!class_exists('CFactory')) {
    $path = JPath::clean(JPATH_ROOT . '/components/com_community/libraries/core.php');
    if (!is_file($path)) {
        return;
    }

    require_once $path;
}

/**
 * Community - Crowdfunding Projects Plugin
 *
 * @package        ITPrism
 * @subpackage     Plugins
 */
class plgCommunityCrowdfundingProjects extends CApplications
{
    /**
     * Affects constructor behavior. If true, language files will be loaded automatically.
     *
     * @var    boolean
     * @since  3.1
     */
    protected $autoloadLanguage = true;

    public $name = 'Crowdfunding Projects';
    public $_name = 'community_crowdfundingprojects';

    public function onProfileDisplay()
    {
        if (!JComponentHelper::isInstalled('com_crowdfunding')) {
            return '';
        }

        $caching = JFactory::getApplication()->get('caching');

        $cache = JFactory::getCache('plgCommunityCrowdfundingProjects');
        $cache->setCaching($caching);

        $callback = array($this, 'getCampaignsHtml');
        $content  = $cache->call($callback);

        return $content;
    }

    public function getCampaignsHtml()
    {
        $componentParams = JComponentHelper::getParams('com_crowdfunding');
        $mediaFolder     = $componentParams->get('images_directory', 'images/crowdfunding');

        $userId  = JFactory::getApplication()->input->get('userid');
        $options = array(
            'users_ids' => array($userId),
            'published' => Prism\Constants::PUBLISHED,
            'approved'  => Prism\Constants::APPROVED
        );

        $projects = new Crowdfunding\Projects(JFactory::getDbo());
        $projects->load($options);

        $html = array();

        foreach ($projects as $project) {
            $link = CrowdfundingHelperRoute::getDetailsRoute($project['slug'], $project['catslug']);

            $html[] = '<div class="row" style="margin-bottom: 5px;">';

            if ($this->params->get('image_size', 'square') === 'square') {
                $image  = ($project['image_square'] !== '') ? $mediaFolder . '/' . $project['image_square'] : 'media/com_crowdfunding/images/no_image_50x50.png';
                $html[] = '<div class="col-md-3 center-block"><a href="' . $link . '"><img src="' . $image . '" width="' . $componentParams->get('image_square_width', 50) . '" height="' . $componentParams->get('image_square_height', 50) . '"></a></div>';
            } else {
                $image  = ($project['image_small'] !== '') ? $mediaFolder . '/' . $project['image_small'] : 'media/com_crowdfunding/images/no_image_100x100.png';
                $html[] = '<div class="col-md-3 center-block"><a href="' . $link . '"><img src="' . $image . '" width="' . $componentParams->get('image_small_height', 100) . '" height="' . $componentParams->get('image_small_height', 100) . '"></a></div>';
            }

            $html[] = '<div class="col-md-9 center-block"><a href="' . $link . '">' . htmlentities($project['title'], ENT_QUOTES, 'UTF-8') . '</a></div>';
            $html[] = '</div>';
        }

        return implode("\n", $html);
    }
}
