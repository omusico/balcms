<?php

/**
 * Media
 *
 * This class has been auto-generated by the Doctrine ORM Framework
 *
 * @package    ##PACKAGE##
 * @subpackage ##SUBPACKAGE##
 * @author     ##NAME## <##EMAIL##>
 * @version    SVN: $Id: Builder.php 6365 2009-09-15 18:22:38Z jwage $
 */
class Media extends BaseMedia {

	/**
	 * Apply modifiers
	 * @return
	 */
	public function setUp ( ) {
		$this->hasMutator('file', 'setFile');
		$this->hasMutator('code', 'setCode');
		parent::setUp();
	}

	/**
	 * Sets the code field
	 * @param int $code
	 * @return bool
	 */
	public function setCode ( $code ) {
		$code = strtolower($code);
		$code = preg_replace('/[\s_]/', '-', $code);
		$code = preg_replace('/[^-a-z0-9]/', '', $code);
		$code = preg_replace('/--+/', '-', $code);
		$this->_set('code', $code);
		return true;
	}
	
	/**
	 * Set the physical file using a $_FILE
	 * @return
	 */
	public function setFile ( $file ) {
		# Configuration
		$applicationConfig = Zend_Registry::get('applicationConfig');
		
		# Check the file
		if ( !empty($file['error']) ) {
			$error = $file['error'];
			switch ( $file['error'] ) {
				case UPLOAD_ERR_INI_SIZE :
					$error = 'ini_size';
					break;
				case UPLOAD_ERR_FORM_SIZE :
					$error = 'form_size';
					break;
				case UPLOAD_ERR_PARTIAL :
					$error = 'partial';
					break;
				case UPLOAD_ERR_NO_FILE :
					$error = 'no_file';
					break;
				case UPLOAD_ERR_NO_TMP_DIR :
					$error = 'no_tmp_dir';
					break;
				case UPLOAD_ERR_CANT_WRITE :
					$error = 'cant_write';
					break;
				default :
					$error = 'unknown';
					break;
			}
			throw new Doctrine_Exception('error-application-file-' . $error);
			return false;
		}
		if ( empty($file['tmp_name']) || !is_uploaded_file($file['tmp_name']) ) {
			throw new Doctrine_Exception('error-application-file-invalid');
			return false;
		}
		
		# Prepare file
		$file_title = $file_name = $file['name'];
		$file_old_path = $file['tmp_name'];
		$file_new_path = UPLOADS_PATH . DIRECTORY_SEPARATOR . $file_name;
		$exist_attempt = 0;
		$extension = get_extension($file_name); if ( !$extension ) $extension = 'file';
		while ( file_exists($file_new_path) ) {
			// File already exists
			// Pump exist attempts
			++$exist_attempt;
			// Add the attempt to the end of the file
			$file_name = get_filename($file_title, false) . $exist_attempt . '.' . $extension;
			$file_new_path = UPLOADS_PATH . DIRECTORY_SEPARATOR . $file_name;
		}
		
		# Move file
		$success = move_uploaded_file($file_old_path, $file_new_path);
		if ( !$success ) {
			throw new Doctrine_Exception('Unable to upload the file.');
			return false;
		}
		
		# Prepare
		$file_path = realpath($file_new_path);
		$file_type = get_filetype($file_path);
		$file_size = filesize($file_path);
		
		# Image
		if ( $file_type === 'image' ) {
			# Dimensions
			$image_dimensions = image_dimensions($file_path);
			if ( !empty($image_dimensions) ) {
				// It is not a image we can modify
				$this->width = 0;
				$this->height = 0;
			} else {
				$this->width = $image_dimensions['width'];
				$this->height = $image_dimensions['height'];
			}
			# Compress
			$image_info = image_read($file_path, true);
			if ( $image_info ) {
				$image_info['image_type'] = IMAGETYPE_JPEG;
				$image_info = image_write($image_info, true);
				if ( $image_info ) {
					$image_info = image_compress($image_info, true);
					if ( $image_info ) {
						$file_title = get_filename($file_title,false) . '.jpg';
						$file_name = get_filename($file_name,false) . '.jpg';
						$file_path = UPLOADS_PATH . DIRECTORY_SEPARATOR . $file_name;
						$image_contents = $image_info['image'];
						file_put_contents($file_path, $image_contents, LOCK_EX);
						$file_path = realpath($file_path);
						$file_size = strlen($image_contents);
					} else {
						// Is not an image we can compress
						//echo '!compress';
					}
				} else {
					// Is not an image we can write to
					//echo '!write';
				}
			} else {
				// Is not an image we can read from
				//echo '!read';
			}
		}
		
		# Delete Previous
		if ( $this->path !== $file_path && file_exists($this->path) ) {
			unlink($this->path);
		}
		
		# Secure
		$file_mimetype = trim_mime_type(get_mime_type($file_path));
		$file_humantype = filetype_human($file_path);
		$file_extension = get_extension($file_path);
		
		# Apply
		$this->code = $file_name;
		$this->name = $file_name;
		$this->title = $file_title;
		$this->path = $file_path;
		$this->size = $file_size;
		$this->mimetype = $file_mimetype;
		$this->humantype = $file_humantype;
		$this->extension = $file_extension;
		$this->type = $file_type;
		
		# Done
		return true;
	}

	/**
	 * Download the physical file
	 * @return
	 */
	public function download ( ) {
		global $Application;
		
		// Get path
		$file_path = $this->path;
		
		// Output result and download
		become_file_download($file_path, null, null);
		die();
	}

	/**
	 * Delete the physical file
	 * @return
	 */
	public function postDelete ( $Event ) {
		global $Application;
		// Prepare
		$Invoker = $Event->getInvoker();
		
		// Get Path
		$file_path = $Invoker->path;
		
		// Delete the file
		unlink($file_path);
		
		// Done
		return true;
	}
}
