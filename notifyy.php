<?php
// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.plugin.plugin' );

class plgContentNotifyy extends JPlugin {
    public $recipients;
    
    function plgContentNotifyy( &$subject, $params) {
        $this->recipients = $params;
        parent::__construct( $subject, $params );
    }
    
    function onContentAfterSave( &$article, $isNew ) {
        global $mainframe;
        
        if ($isNew) {
            $db		=& JFactory::getDBO();
            $groups = implode(',',array_values($this->params->get("recipients")));
            $query  = $db->getQuery(true);
            $query->select('user.email as email');
            $query->from('#__users as user INNER JOIN #__user_usergroup_map as usergroup ON user.id=usergroup.user_id');
            $query->where('usergroup.group_id IN ('.$groups.')');
            $db->setQuery($query);  
            
            if (count($db->loadObjectList('email'))>0) {
                $recipients = array_keys($db->loadObjectList('email'));
    
                JUtility::sendMail("genteel@amsoell.com", "The Genteel Article Watch", implode(',',$recipients), "New article submitted", "There's a new article waiting for review!\n\n".print_r($recipients, true));
            }
        }

        return true;
    }	

}
