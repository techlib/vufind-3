<?php
/**
 * Model for MARC records in Solr.
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
 * @package  RecordDrivers
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/vufind2:record_drivers Wiki
 */
namespace ntk_module\RecordDriver;


/**
 * Model for MARC records in Solr.
 *
 * @category VuFind2
 * @package  RecordDrivers
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/vufind2:record_drivers Wiki
 */
class SolrMarc extends SolrDefault
{
    /**
     * Return an array of associative URL arrays with one or more of the following
     * keys:
     *
     * <li>
     *   <ul>desc: URL description text to display (optional)</ul>
     *   <ul>url: fully-formed URL (required if 'route' is absent)</ul>
     *   <ul>route: VuFind route to build URL with (required if 'url' is absent)</ul>
     *   <ul>routeParams: Parameters for route (optional)</ul>
     *   <ul>queryString: Query params to append after building route (optional)</ul>
     * </li>
     *
     * @return array
     */
    public function getURLs()
    {
        $retVal = array();

        // Which fields/subfields should we check for URLs?
        $fieldsToCheck = array(
            '856' => array('y', 'z', '3'),   // Standard URL
            '555' => array('a')         // Cumulative index/finding aids
        );

        foreach ($fieldsToCheck as $field => $subfields) {
            $urls = $this->marcRecord->getFields($field);
            if ($urls) {
                foreach ($urls as $url) {
                    // Is there an address in the current field?
                    $address = $url->getSubfield('u');
                    if ($address) {
                        $address = $address->getData();

                        // Is there a description?  If not, just use the URL itself.
                        foreach ($subfields as $current) {
                            $desc = $url->getSubfield($current);
                            if ($desc) {
                                break;
                            }
                        }
                        if ($desc) {
                            $desc = $desc->getData();
                        } else {
                            $desc = $address;
                        }

                        $retVal[] = array('url' => $address, 'desc' => $desc);
                    }
                }
            }
        }

        return $retVal;
    }

    /**
     * Support functions for holding filters
     * Daniel Marecek, NTK
     */

    public function getHoldingFilters()
    {
	    return $this->ils->getDriver()->getHoldingFilters($this->getUniqueID());
    }

    public function getAvailableHoldingFilters()
    {
        return array(
            'year' => array('type' => 'select', 'keep' => array('hide_loans')),
            'volume' => array('type' => 'select', 'keep' => array('hide_loans')),
            'hide_loans' => array('type' => 'checkbox', 'keep' => array('year', 'volume')),
        );
    }

    /**
     * Get an array of information about record holdings, obtained in real-time
     * from the ILS.
     *
     * @return array
     */
    public function getRealTimeHoldings($filters = array())
    {   // add format as a parametr because of holding's sorting
        return $this->hasILS()
            ? $this->holdLogic->getHoldings($this->getUniqueID(), $filters, $this->fields['format'])
            : array();
    }

    // Informace o prejiti tistene formy casopisu do elektronicke podoby.
    public function infoText()
    {
        $eiz_info = $this->marcRecord->getFields('530');
        foreach ($eiz_info as $info_eiz) {
                $info_text = $info_eiz->getSubfield('a'); // Marc pole 530a
        }
        return $info_text->getData();
    }
}
