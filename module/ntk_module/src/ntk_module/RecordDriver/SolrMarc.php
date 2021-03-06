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
use VuFind\RecordDriver\SolrMarc as SolrMarcBase;

/**
 * Model for MARC records in Solr.
 *
 * @category VuFind2
 * @package  RecordDrivers
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/vufind2:record_drivers Wiki
 */
class SolrMarc extends SolrMarcBase
{
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
        $eiz_info = $this->getMarcRecord()->getFields('530');
        foreach ($eiz_info as $info_eiz) {
                $info_text = $info_eiz->getSubfield('a'); // Marc pole 530a
        }
        return $info_text->getData();
    }

    /**
     * source document
     */
    public function getSourceDoc()
    {
        $sourcedoc = array (
            "title" => isset($this->fields['article_resource_title']) ? $this->fields['article_resource_title'] : null,
            "issn" => isset($this->fields['article_issn']) ? $this->fields['article_issn'] : null,
            "related" => isset($this->fields['article_resource_related']) ? $this->fields['article_resource_related'] : null
        );
        return $sourcedoc;
    }

    /**
     * EOD
     */
    public function isEOD()
    {
        // Get a representative publication date:
        $pubDate = $this->getPublicationDates();
        $pubDate = empty($pubDate) ? '' : $pubDate[0];

        // Get format
        $format=$this->getFormats();

        // Get collection
        // $collection = $this->fields['collection'][0];

        $topyear = date('Y')-75;
        $has_url = $this->getURLs();

        if (($pubDate < $topyear) && ($pubDate > 1500) && ($format[0] == 'Book') && (empty($has_url))) {
            return true;
        }else{
            return false;
        }
    }

    /**
     * Get Collection
     *
     * @return string
     */
    public function getCollection()
    {
        return isset($this->fields['collection'][0]) ?
            $this->fields['collection'][0] : '';
    }

    /**
     * Get the OpenURL parameters to represent this record (useful for the
     * title attribute of a COinS span tag).
     *
     * @param bool $overrideSupportsOpenUrl Flag to override checking
     * supportsOpenUrl() (default is false)
     *
     * @return string OpenURL parameters.
     */
    public function getOpenUrl($overrideSupportsOpenUrl = false)
    {
        // stop here if this record does not support OpenURLs
        if (!$overrideSupportsOpenUrl && !$this->supportsOpenUrl()) {
            return false;
        }

        // Set up parameters based on the format of the record:
        $format = $this->getOpenUrlFormat();
        // NTK - we don't display openurl for articles
        if ($format == 'Article') { return false;}
        $method = "get{$format}OpenUrlParams";
        if (method_exists($this, $method)) {
            $params = $this->$method();
        } else {
            $params = $this->getUnknownFormatOpenUrlParams($format);
        }

        // Assemble the URL:
        return http_build_query($params);
    }

    /**
     * Returns one of three things: a full URL to a thumbnail preview of the record
     * if an image is available in an external system; an array of parameters to
     * send to VuFind's internal cover generator if no fixed URL exists; or false
     * if no thumbnail can be generated.
     *
     * @param string $size Size of thumbnail (small, medium or large -- small is
     * default).
     *
     * @return string|array|bool
     */
    public function getThumbnail($size = 'small')
    {
        if (isset($this->fields['thumbnail']) && $this->fields['thumbnail']) {
            return $this->fields['thumbnail'];
        }
        $arr = [
            'author'     => mb_substr($this->getPrimaryAuthor(), 0, 300, 'utf-8'),
            'callnumber' => $this->getCallNumber(),
            'size'       => $size,
            'title'      => mb_substr($this->getTitle(), 0, 300, 'utf-8')
        ];
        if ($isbn = $this->getCleanISBN()) {
            $arr['isbn'] = $isbn;
        }
        if ($issn = $this->getCleanISSN()) {
            $arr['issn'] = $issn;
        }
        if ($oclc = $this->getCleanOCLCNum()) {
            $arr['oclc'] = $oclc;
        }
        if ($upc = $this->getCleanUPC()) {
            $arr['upc'] = $upc;
        }
        if ($uid = $this->getUniqueID()) {
            $arr['uid'] = $uid;
        }
        // If an ILS driver has injected extra details, check for IDs in there
        // to fill gaps:
        if ($ilsDetails = $this->getExtraDetail('ils_details')) {
            foreach (['isbn', 'issn', 'oclc', 'upc'] as $key) {
                if (!isset($arr[$key]) && isset($ilsDetails[$key])) {
                    $arr[$key] = $ilsDetails[$key];
                }
            }
        }
        return $arr;
    }

    /**
     * Get an array of publication detail lines combining information from
     * getPublicationDates(), getPublishers() and getPlacesOfPublication().
     *
     * @return array
     */
    public function getPublicationDetails()
    {
        $places = $this->getPlacesOfPublication();
        $names = $this->getPublishers();
        $dates = $this->getPublicationDates();
        $i = 0;
        $retval = [];
        while (isset($places[$i]) || isset($names[$i]) || isset($dates[$i])) {
            // Build objects to represent each set of data; these will
            // transform seamlessly into strings in the view layer.
            $retval[] = new \VuFind\RecordDriver\Response\PublicationDetails(
                isset($places[$i]) ? $places[$i] : '',
                isset($names[$i]) ? $names[$i] : '',
                isset($dates[$i]) ? $dates[$i] : ''
            );
            $i++;
        }

        return $retval;
    }

    /**
     * Process marc field 776 for getting other forms of holdings.
     * Examples: on-line, internet,..
     *
     * Return string in brackets.
     * Author: Daniel Mareček, National Library of technology
     */
    public function issnForm()
    {
        $issn_texts=null;
        $issn_form = $this->getMarcRecord()->getFields('776');
        foreach ($issn_form as $row) {
            $subfields = $row->getSubfields();
            if ($subfields) {
                foreach ($subfields as $subfield) {
                    if ($subfield->getCode() == 'x') {
                        $issn_num = $subfield->getData();
                    }else{
                        $issn_texts .= $subfield->getData();
                    }
                }
                $issn_form = preg_match('/\(.*\)/', $issn_texts, $matches);
                if (empty($matches[0])){
                    $issn_form = $row->getSubfield('i');
                    if (!empty($issn_form)){
                        $issn_form = $issn_form->getData();
                    }
                }else{
                    $issn_form = $matches[0];
                }
                $retval[] = Array ( 'text' => $issn_form, 'num' => isset($issn_num)?$issn_num:null);
            }
            $issn_texts=null;
        }
        return isset($retval)?$retval:null;
    }

    /**
     * Get an array of ISSNs from fields 780x:785x (solr:issn_ref).
     *
     * @return array
     */
    public function getRefISSNs()
    {
        $issn_ref = isset($this->fields['issn_ref']) && is_array($this->fields['issn_ref']) ?
        $this->fields['issn_ref'] : [];

        $field780 = $this->getMarcRecord()->getFields('780');
        $field785 = $this->getMarcRecord()->getFields('785');
        $bothfields = array_merge($field780, $field785);

        if (!empty($issn_ref)) {
            for ($i=0; $i<sizeof($bothfields); $i++) {

                foreach ($bothfields as $row) {
                    // hledej v radku issn
                    $sub_x = $row->getSubfield('x');
                    if (!empty($sub_x)){
                        $sub_x = $sub_x->getData();
                        if (!empty($sub_x)){
                            if ($sub_x == (isset($issn_ref[$i])?$issn_ref[$i]:null)){
                                $sub_t = $row->getSubfield('t');
                                if (!empty($sub_t)){
                                    $sub_t = $sub_t->getData();
                                }else{
                                    $sub_t = 'chybí v MARC';
                                }
                                $vysledek[] = Array ( 'nazev' => $sub_t, 'issn' => $issn_ref[$i] );
                            }
                        }
                    }
                }

            }
        }
        return isset($vysledek)?$vysledek:null;
    }

    public function get655a()
    {
        $results = $this->getMarcRecord()->getFields('655');
        $sub_a = array();
        foreach ($results as $result) {
            $sub_a[] = $result->getSubfield('a')->getData();
        }
        return $sub_a;
    }
}
