<?php
// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.plugin.plugin' );

class plgContentNotifyy extends JPlugin {
    public $params;
    
    function plgContentNotifyy( &$subject, $params) {
        $this->params = $params;
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
            $emails = $db->loadObjectList('email');

            if (count($emails)>0) {
                $recipients = array_keys($emails);
                
                $mailer =& JFactory::getMailer();
                $config =& JFactory::getConfig();
                $sender = array( 
                    $config->getValue('config.mailfrom'),
                    $config->getValue('config.fromname').' - Article Watch' );
 
                $mailer->setSender($sender);
                $mailer->addRecipient($recipients);
                
                $body = $this->params->get("message");
                $body .= "<p><b>".$article->title."</b></p>";
                $body .= "<p>You are receiving this message because there is a new article ready to review at ".$config->getValue("config.sitename").". Visit ".JRoute::_(JURI::base().'index.php?option=com_content&view=articles')." to review it. If you would like to be removed from these notifications, please contact your administrator: <a href=\"mailto:".$config->getValue('config.mailfrom')."\">".$config->getValue('config.mailfrom')."</a></p>";                
                
                $mailer->isHTML(true);
                $mailer->Encoding = 'base64';
                $mailer->setBody($body);                
                $mailer->setSubject($this->params->get("subject"));
                
                $send =& $mailer->Send();
                
                if ($send!==true) {
                    die("Could not send mail: " & $send->message);
                }
            }
        }

        return true;
    }	

}
