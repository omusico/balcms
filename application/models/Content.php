<?php

/**
 * Content
 *
 * This class has been auto-generated by the Doctrine ORM Framework
 *
 * @package    ##PACKAGE##
 * @subpackage ##SUBPACKAGE##
 * @author     ##NAME## <##EMAIL##>
 * @version    SVN: $Id: Builder.php 6365 2009-09-15 18:22:38Z jwage $
 */
class Content extends BaseContent {
	
	/** Old values */
	protected $_old = array();
	protected $_new = array();
	protected $_View = null;
	
	/**
	 * Backup old values
	 * @param Doctrine_Event $Event
	 */
	public function preSave ( Doctrine_Event $Event ) {
		// Prepare
		$Invoker = $Event->getInvoker();
		
		// Modified
		$this->_old = $Event->getInvoker()->getModified(true);
		$this->_new = $Event->getInvoker()->getModified(false);
		
		// Get View
		$View = $this->getView();
		
		// Render content
		if ( !empty($this->_new['content']) ) {
			$Invoker->content_rendered = $View->content()->render($Invoker->content);
			$Invoker->description_rendered = $View->content()->render($Invoker->description);
		}
		
		// Done
		return true;
	}
	
	/**
	 * Get's the View object
	 */
	public function getView ( ) {
		if ( empty($this->_view) ) {
			$Bootstrap = $GLOBALS['Application']->getBootstrap();
			$Bootstrap->bootstrap('presentation');
			$this->_View = $Bootstrap->getResource('view');
		}
		return $this->_View;
	}
	
	/**
	 * Handle tagstr, authorstr, and code changes
	 * @param Doctrine_Event $Event
	 * @return string
	 */
	public function postSave ( Doctrine_Event $Event ) {
		// Prepare
		$Invoker = $Event->getInvoker();
		$save = false;
		$modified = $Invoker->getLastModified();
		
		// Tags
		$tags = array();
		foreach ( $Invoker['Tags'] as $Tag ) {
			$tags[] = $Tag['name'];
		}
		sort($tags);
		$tagstr = implode($tags, ', ');
		if ( $Invoker->tagstr != $tagstr ) {
			$Invoker->tagstr = $tagstr;
			$save = true;
		}
		
		// Author
		if ( !empty($Invoker->Author) && $Invoker->Author->exists() ) {
			$author = $Invoker->Author->displayname;
			if ( $Invoker->authorstr != $author ) {
				$Invoker->authorstr = $author;
				$save = true;
			}
		}
		
		// Route
		if ( empty($Invoker->Route) || !$Invoker->Route->exists() ) {
			$Route = new Route();
			$path = $Invoker->code;
			$Parent = $Invoker->getNode()->getParent();
			if ( $Parent && $Parent->exists() ) $path = $Parent->Route->path.'/'.$path;
			$Route->path = $path;
			$Route->type = 'content';
			$Route->data = array('id'=>$Invoker->id);
			$Route->save();
			$Invoker->link('Route', $Route->id);
			$save = true;
		}
	
		// Check if code has changed
		if ( !empty($modified['code']) && !empty($this->_old['code']) ) {
			// The code has changed, update route and all children, eee
			$old_code = $this->_old['code'];
			$new_code = $Invoker->code;
			$Route = $this->Route;
			$old_path = $Route->path;
			$new_path = $Route->path = rtrim_value($Route->path, $old_code).$new_code;
			$Route->save();
			// Get children
			$Children = $Invoker->getNode()->getDescendants();
			if ( $Children ) foreach ( $Children as $Child ) {
				$ChildRoute = $Child->Route;
				$ChildRoute->path = $new_path.ltrim_value($ChildRoute->path, $old_path);
				$ChildRoute->save();
			}
			// Done
		}
		
		// Get View
		$View = $this->getView();
	
		// Check if we need to send out to any subscribers
		if ( !empty($tags) ) {
			$SubscriberQuery = Doctrine_Query::create()
				->select('s.email')
				->from('Subscriber s, s.Tags st')
				->where('s.enabled = ?', true)
				->andWhere('NOT EXISTS (SELECT cas.id FROM ContentAndSubscriber cas WHERE cas.subscriber_id = s.id AND cas.content_id = ?)', $Invoker->id)
				->andWhereIn('st.name', $tags)
				->setHydrationMode(Doctrine::HYDRATE_ARRAY);
			$SubscribersArray = $SubscriberQuery->execute();
			if ( !empty($SubscribersArray) ) {
				// Update
				if ( empty($Invoker->send_at) ) {
					$Invoker->send_at = date('Y-m-d H:i:s', time());
				}
				// We would like to send out
				$View = clone $View;
				$View->ContentArray = $Invoker->toArray();
				$View->headTitle()->append($Invoker->title);
				// Mail
				$mail = $GLOBALS['Application']->getOption('mail');
				$mail['subject'] = $Invoker->title;
				$mail['html'] = $View->render('email/subscription.phtml');
				$mail['text'] = strip_tags($mail['html']);
				$Mail = new Zend_Mail();
				$Mail->setFrom($mail['from']['address'], $mail['from']['name']);
				// $Mail->addTo($mail['from']['address'], $mail['from']['name']);
				foreach ( $SubscribersArray as $SubscriberArray ) {
					$Mail->addBcc($SubscriberArray['email']);
					// Save send
					$ContentAndSubscriber = new ContentAndSubscriber();
					$ContentAndSubscriber->content_id = $Invoker->id;
					$ContentAndSubscriber->subscriber_id = $SubscriberArray['id'];
					$ContentAndSubscriber->status = 'delivered';
					$ContentAndSubscriber->save();
				}
				$Mail->setSubject($mail['subject']);
				$Mail->setBodyText($mail['text']);
				$Mail->setBodyHtml($mail['html']);
				$Mail->send();
				// Update
				$Invoker->send_finished_at = date('Y-m-d H:i:s', time());
				$Invoker->send_status = 'completed';
				$Invoker->send_all += count($SubscribersArray);
				$Invoker->send_remaining = 0;
				$save = true;
			}
		}
		
		// Apply
		if ( $save ) {
			$Invoker->save();
		}
		
		// Done
		return true;
	}
	
}