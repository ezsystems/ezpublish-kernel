<?php
/**
 * File containing the SectionHandler interface
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 *
 */

namespace ezp\persistence\content;

/**
 * @package ezp.persistence.content
 */
class SectionHandler 
{

	/**
	 * @param string name
	 * @param string identifier
	 * @return ezp.persistence.content.values.Section
	 * @ParamType name string
	 * @ParamType identifier string
	 * @ReturnType ezp.persistence.content.values.Section
	 */
	public function create($name, $identifier) {
		// Not yet implemented
	}

	/**
	 * @param string name
	 * @param string identifier
	 * @ParamType name string
	 * @ParamType identifier string
	 */
	public function update($name, $identifier) {
		// Not yet implemented
	}

	/**
	 * @param int id
	 * @ParamType id int
	 */
	public function delete($id) {
		// Not yet implemented
	}

	/**
	 * @param int sectionId
	 * @param int contentId
	 * @ParamType sectionId int
	 * @ParamType contentId int
	 */
	public function assign($sectionId, $contentId) {
		// Not yet implemented
	}
}
?>