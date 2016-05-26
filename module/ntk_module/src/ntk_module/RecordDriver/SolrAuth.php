<?php
/**
 * Model for Solr authority records.
 *
 * PHP version 5
 *
 * Copyright (C) Villanova University 2011.
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
use VuFind\RecordDriver\SolrAuth as SolrAuthBase;

/**
 * Model for Solr authority records.
 *
 * @category VuFind2
 * @package  RecordDrivers
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/vufind2:record_drivers Wiki
 */
class SolrAuth extends SolrAuthBase
{
    /**
     * Get the narrower references for the record.
     *
     * @return array
     */
    public function getNarrower()
    {
        return isset($this->fields['narrower'])
        && is_array($this->fields['narrower'])
            ? $this->fields['narrower'] : array();
    }

    /**
     * Get the broader references for the record.
     *
     * @return array
     */
    public function getBroader()
    {
        return isset($this->fields['broader'])
        && is_array($this->fields['broader'])
            ? $this->fields['broader'] : array();
    }
}
