<?php
/**
 * Preview tab
 *
 * PHP version 5
 *
 * Copyright (C) Villanova University 2010.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License version 2,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * @category VuFind2
 * @package  RecordTabs
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/vufind2:record_tabs Wiki
 */
namespace VuFind\RecordTab;

/**
 * Preview tab
 *
 * @category VuFind2
 * @package  RecordTabs
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/vufind2:record_tabs Wiki
 */
class Preview extends AbstractBase
{
    /**
     * Get the on-screen preview for this tab.
     *
     * @return string
     */
    public function getDescription()
    {
        return 'Preview';
    }

    /**
     * Zjisti jestli jsou na Aleph serveru nejake obrazky pro dane ID a dle toho
     * rozhodne, zda se zalozka Nahled zobrazi ci ne
     */
    public function isDisplayed()
    {
        $id = $this->driver->getUniqueID();

        // Links with pictures on this site
        $addr = 'http://aleph.techlib.cz/cgi-bin/obrazek.pl?sn='.$id;
        $links = file_get_contents( $addr );

        // Pattern starts with "http" and ends with ".jpg" or ".JPG"
        $pattern = '/http.{0,100}\.(JPG|jpg)/';
        $count = preg_match_all( $pattern, $links, $url);

        // if the record has no pictures => count = 0
        return $count;
    }

}
