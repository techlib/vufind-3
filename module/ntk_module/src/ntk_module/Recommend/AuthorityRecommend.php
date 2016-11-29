<?php
/**
 * AuthorityRecommend Recommendations Module
 *
 * PHP version 5
 *
 * Copyright (C) Villanova University 2012.
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
 * @package  Recommendations
 * @author   Lutz Biedinger (National Library of Ireland)
 * <vufind-tech@lists.sourceforge.net>
 * @author   Ronan McHugh (National Library of Ireland)
 * <vufind-tech@lists.sourceforge.net>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://www.vufind.org  Main Page
 */
namespace ntk_module\Recommend;
use VuFind\Recommend\AuthorityRecommend as AuthorityRecommendBase;
use VuFind\Recommend\RecommendInterface, Zend\StdLib\Parameters;

/**
 * AuthorityRecommend Module
 *
 * This class provides recommendations based on Authority records.
 * i.e. searches for a pseudonym will provide the user with a link
 * to the official name (according to the Authority index)
 *
 * @category VuFind2
 * @package  Recommendations
 * @author   Lutz Biedinger (National Library of Ireland)
 * <vufind-tech@lists.sourceforge.net>
 * @author   Ronan McHugh (National Library of Ireland)
 * <vufind-tech@lists.sourceforge.net>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://www.vufind.org  Main Page
 */
class AuthorityRecommend extends AuthorityRecommendBase implements RecommendInterface
{
    /**
     * process
     *
     * Called after the Search Results object has performed its main search.  This
     * may be used to extract necessary information from the Search Results object
     * or to perform completely unrelated processing.
     *
     * @param \VuFind\Search\Base\Results $results Search results object
     *
     * @return void
     */
    public function process($results)
    {
        $this->results = $results;

        // function will return blank on Advanced Search
        if ($results->getParams()->getSearchType()== 'advanced') {
            return;
        }

        // check result limit before proceeding...
        if ($this->resultLimit > 0
            && $this->resultLimit < $results->getResultTotal()
        ) {
            return;
        }

        // Build an advanced search request that prevents Solr from retrieving
        // records that would already have been retrieved by a search of the biblio
        // core, i.e. it only returns results where $lookfor IS found in in the
        // "Heading" search and IS NOT found in the "MainHeading" search defined
        // in authsearchspecs.yaml.
        // DM - simply request for psh-authority-search
        $request = new Parameters(
            array(
                'join' => 'OR',
                'lookfor0' => array($this->lookfor),
                'lookfor1' => array($this->lookfor),
                'type0' => array('Heading'),
                'type1' => array('EnglishHeading')
            )
        );

        // Initialise and process search (ignore Solr errors -- no reason to fail
        // just because search syntax is not compatible with Authority core):
        try {
            $authResults = $this->resultsManager->get('SolrAuth');
            $authParams = $authResults->getParams();
            $authParams->initFromRequest($request);
            foreach ($this->filters as $filter) {
                $authParams->getOptions()->addHiddenFilter($filter);
            }
            $results = $authResults->getResults();
        } catch (RequestErrorException $e) {
            return;
        }

        // loop through records and assign id and headings to separate arrays defined
        // above
        foreach ($results as $result) {
            // Extract relevant details:
            $recordArray = array(
                'id' => $result->getUniqueID(),
                'heading' => $result->getBreadcrumb(),
                'narrower' => $result->getNarrower(),
                'broader' => $result->getBroader(),
                'see_also' => $result->getSeeAlso()
            );

            // check for duplicates before adding record to recordSet
            if (!$this->inArrayR($recordArray['heading'], $this->recommendations)) {
                array_push($this->recommendations, $recordArray);
            } else {
                continue;
            }
        }
    }
}
