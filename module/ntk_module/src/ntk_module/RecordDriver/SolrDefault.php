<?php
/**
 * Default model for Solr records -- used when a more specific model based on
 * the recordtype field cannot be found.
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
use VuFind\RecordDriver\Response\PublicationDetails as Response;
use VuFind\RecordDriver\SolrMarc as SolrMarcBase;

/**
 * Default model for Solr records -- used when a more specific model based on
 * the recordtype field cannot be found.
 *
 * This should be used as the base class for all Solr-based record models.
 *
 * @category VuFind2
 * @package  RecordDrivers
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/vufind2:record_drivers Wiki
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 */
class SolrDefault extends SolrMarcBase
{
    /**
     * Get the OpenURL parameters to represent this record (useful for the
     * title attribute of a COinS span tag).
     *
     * @return string OpenURL parameters.
     */
    public function getOpenURL()
    {
        // Set up parameters based on the format of the record:
        switch ($format = $this->getOpenURLFormat()) {
        case 'Book':
            $params = $this->getBookOpenURLParams();
            break;
        case 'Article':
            $params = $this->getArticleOpenURLParams();
            return false; // don't generate url for articles
            break;
        case 'Journal':
            $params = $this->getJournalOpenURLParams();
            break;
        default:
            $params = $this->getUnknownFormatOpenURLParams($format);
            break;
        }

        // Assemble the URL:
        return http_build_query($params);
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
        $collection = $this->fields['collection'][0];

        $topyear = date('Y')-100;
        $has_url = $this->getURLs();

        if (($pubDate < $topyear) && ($pubDate > 1500) && ($format[0] == 'Book') && ($collection == 'NTK') && (empty($has_url))) {
            return true;
        }else{
            return false;
        }
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
        $retval = array();
        while (isset($places[$i]) || isset($names[$i]) || isset($dates[$i])) {
            // Build objects to represent each set of data; these will
            // transform seamlessly into strings in the view layer.
            $retval[] = new Response(    
                isset($places[$i]) ? $places[$i] : '',
                isset($names[$i]) ? $names[$i] : '',
                isset($dates[$i]) ? $dates[$i] : ''
            );
            $i++;
        }

        return $retval;
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
        $arr = array(
            'author'     => mb_substr($this->getPrimaryAuthor(), 0, 300, 'utf-8'),
            'callnumber' => $this->getCallNumber(),
            'size'       => $size,
            'title'      => mb_substr($this->getTitle(), 0, 300, 'utf-8')
        );
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
            foreach (array('isbn', 'issn', 'oclc', 'upc') as $key) {
                if (!isset($arr[$key]) && isset($ilsDetails[$key])) {
                    $arr[$key] = $ilsDetails[$key];
                }
            }
        }
        return $arr;
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
}
