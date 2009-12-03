<?php

/**
 * User
 *
 * This class has been auto-generated by the Doctrine ORM Framework
 *
 * @package    ##PACKAGE##
 * @subpackage ##SUBPACKAGE##
 * @author     ##NAME## <##EMAIL##>
 * @version    SVN: $Id: Builder.php 6508 2009-10-14 06:28:49Z jwage $
 */
class User extends Base_User {

	/**
	 * Set the Role(s) for a User (clear others)
	 * @param mixed $role
	 */
	public function setRole ( $role ) {
		$this->unlink('Roles');
		$this->link('Roles', $role);
		return true;
	}

	/**
	 * Add a Role(s) to the User
	 * @param mixed $role
	 */
	public function addRole ( $role ) {
		$this->link('Roles', $role);
		return true;
	}

	/**
	 * Does user have Role?
	 * @param mixed $permission
	 */
	public function hasRole ( $role ) {
		// Prepare
		if ( is_object($role) ) {
			$role = $role->code;
		} elseif ( is_array($role) ) {
			$role = $role['code'];
		}
		// Search
		$List = $this->Roles;
		foreach ( $List as $Role ) {
			if ( $role === $Role->code ) {
				$result = true;
				break;
			}
		}
		// Done
		return $result;
	}
	
	/**
	 * Does user have Permission?
	 * @param mixed $permission
	 */
	public function hasPermission ( $permission ) {
		// Prepare
		if ( is_object($permission) ) {
			$permission = $permission->code;
		} elseif ( is_array($permission) ) {
			$permission = $permission['code'];
		}
		// Search
		$List = $this->Permissions;
		foreach ( $List as $Permission ) {
			if ( $permission === $Permission->code ) {
				$result = true;
				break;
			}
		}
		// Done
		return $result;
	}
	
}