<?php
/**
 * MyResearch Controller
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
 * @package  Controller
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org   Main Site
 */
namespace ntk_module\Controller;

use VuFind\Controller\MyResearchController as MyResearchControllerBase,
    Zend\Stdlib\Parameters;

/**
 * Controller for the user account area.
 *
 * @category VuFind2
 * @package  Controller
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org   Main Site
 */
class MyResearchController extends MyResearchControllerBase
{
    /**
     * Logout Action
     *
     * @return mixed
     */
    public function logoutAction()
    {
        $config = $this->getConfig();
        if (isset($config->Site->logOutRoute)) {
            $logoutTarget = $this->getServerUrl($config->Site->logOutRoute);
        } else {
            $logoutTarget = $this->getRequest()->getServer()->get('HTTP_REFERER');
            if (empty($logoutTarget)) {
                $logoutTarget = $this->getServerUrl('home');
            }

            // If there is an auth_method parameter in the query, we should strip
            // it out. Otherwise, the user may get stuck in an infinite loop of
            // logging out and getting logged back in when using environment-based
            // authentication methods like Shibboleth.
            $logoutTarget = preg_replace(
                '/([?&])auth_method=[^&]*&?/', '$1', $logoutTarget
            );
            $logoutTarget = rtrim($logoutTarget, '?');
        }

        $this->getAuthManager()->logout($logoutTarget);
        $view = $this->createViewModel();
        return $view;
    }
    /**
     * History of checked out items Action
     *
     * @return mixed
     */
    public function checkedOutHistoryAction()
    {
        // Stop now if the user does not have valid catalog credentials available:
        if (!is_array($patron = $this->catalogLogin())) {
                return $patron;
        }

        $currentLimit = $this->params()->fromQuery('limit');
        if (!isset($currentLimit)) {
            $currentLimit = 20;
        }

        // Connect to the ILS:
        $catalog = $this->getILS();

        // Get history:
        $result = $catalog->getMyHistory($patron, $currentLimit);

        $transactions = array();
        foreach ($result as $current) {
                // Add renewal details if appropriate:
                $current = $this->renewals()->addRenewDetails(
                        $catalog, $current, isset($renewStatus) ? $renewStatus : null
                );
                // Build record driver:
                $transactions[] = $this->getDriverForILSRecord($current);
        }

        return $this->createViewModel(
                array('transactions' => $transactions)
        );
    }
}
