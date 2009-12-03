<?php

/**
 * Subscriber
 *
 * This class has been auto-generated by the Doctrine ORM Framework
 *
 * @package    ##PACKAGE##
 * @subpackage ##SUBPACKAGE##
 * @author     ##NAME## <##EMAIL##>
 * @version    SVN: $Id: Builder.php 6365 2009-09-15 18:22:38Z jwage $
 */
class Subscriber extends Base_Subscriber {

	/**
	 * Handle tagstr, authorstr, and code changes
	 * @param Doctrine_Event $Event
	 * @return string
	 */
	public function postSave ( $Event ) {
		# Prepare
		$Invoker = $Event->getInvoker();
		$save = false;
		
		# Tags
		$tags = array();
		foreach ( $Invoker->Tags as $Tag ) {
			$tags[] = $Tag->name;
		}
		sort($tags);
		$tagstr = implode($tags, ', ');
		if ( $Invoker->tagstr != $tagstr ) {
			$Invoker->tagstr = $tagstr;
			$save = true;
		}
		
		# Save
		if ( $save ) {
			$Invoker->save();
		}
		
		# Done
		return true;
	}
	
}