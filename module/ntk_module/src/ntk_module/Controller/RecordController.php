<?php
/**
 * Record Controller
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
use VuFind\Controller\RecordController as RecordControllerBase;

/**
 * Record Controller
 *
 * @category VuFind2
 * @package  Controller
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org   Main Site
 */
class RecordController extends RecordControllerBase
{
    /**
     * Action for dealing with holds.
     *
     * @return mixed
     */
    public function holdAction()
    {
        $driver = $this->loadRecord();

        // If we're not supposed to be here, give up now!
        $catalog = $this->getILS();
        $checkHolds = $catalog->checkFunction("Holds", $driver->getUniqueID());
        if (!$checkHolds) {
            return $this->forwardTo('Record', 'Home');
        }

        // Stop now if the user does not have valid catalog credentials available:
        if (!is_array($patron = $this->catalogLogin())) {
            return $patron;
        }

        // check expiration of registration
        $today = strtotime(date("d.m.o"));
        $expire_date = strtotime($patron['expire']);

        if ($today > $expire_date) {
            if ($this->layout()->userLang == 'cs') {
                $renew_link = '<a style="color:#b94a48" target="_blank" href="https://www.techlib.cz/cs/2691-registrace-a-volny-vstup#prodlouzeni">'.$this->translate('expirace_registrace').'</a>';
            }else{
                $renew_link = '<a style="color:#b94a48" target="_blank" href="https://www.techlib.cz/en/2712-registration-free-day-pass">'.$this->translate('expirace_registrace').'</a>';
            }
            return $this->createViewModel(
                array('renew_link' => $renew_link));
        }

        // Do we have valid information?
        // Sets $this->logonURL and $this->gatheredDetails
        $gatheredDetails = $this->holds()->validateRequest($checkHolds['HMACKeys']);
        if (!$gatheredDetails) {
            return $this->redirectToRecord();
        }

        // Block invalid requests:
        if (!$catalog->checkRequestIsValid(
            $driver->getUniqueID(), $gatheredDetails, $patron
        )) {
            return $this->blockedholdAction();
        }

        // Send various values to the view so we can build the form:
        $pickup = $catalog->getPickUpLocations($patron, $gatheredDetails);
        $requestGroups = $catalog->checkCapability('getRequestGroups')
            ? $catalog->getRequestGroups($driver->getUniqueID(), $patron)
            : array();
        $extraHoldFields = isset($checkHolds['extraHoldFields'])
            ? explode(":", $checkHolds['extraHoldFields']) : array();

        // Process form submissions if necessary:
        if (!is_null($this->params()->fromPost('placeHold'))) {
            // If the form contained a pickup location or request group, make sure
            // they are valid:
            $valid = $this->holds()->validateRequestGroupInput(
                $gatheredDetails, $extraHoldFields, $requestGroups
            );
            if (!$valid) {
                $this->flashMessenger()->setNamespace('error')
                    ->addMessage('hold_invalid_request_group');
            } elseif (!$this->holds()->validatePickUpInput(
                $gatheredDetails['pickUpLocation'], $extraHoldFields, $pickup
            )) {
                $this->flashMessenger()->setNamespace('error')
                    ->addMessage('hold_invalid_pickup');
            } else {
                // If we made it this far, we're ready to place the hold;
                // if successful, we will redirect and can stop here.

                // Add Patron Data to Submitted Data
                $holdDetails = $gatheredDetails + array('patron' => $patron);

                // Attempt to place the hold:
                $function = (string)$checkHolds['function'];
                $results = $catalog->$function($holdDetails);

                // Success: Go to Display Holds
                if (isset($results['success']) && $results['success'] == true) {
                    $this->flashMessenger()->setNamespace('info')
                        ->addMessage('hold_place_success');
                    if ($this->inLightbox()) {
                        return false;
                    }
                    return $this->redirect()->toRoute('myresearch-holds');
                } else {
                    // Failure: use flash messenger to display messages, stay on
                    // the current form.
                    if (isset($results['status'])) {
                        $this->flashMessenger()->setNamespace('error')
                            ->addMessage($results['status']);
                    }
                    if (isset($results['sysMessage'])) {
                        $this->flashMessenger()->setNamespace('error')
                            ->addMessage($results['sysMessage']);
                    }
                }
            }
        }

        // Find and format the default required date:
        $defaultRequired = $catalog->getHoldDefaultRequiredDate($patron, $gatheredDetails);
        if (empty($defaultRequired)) {
            $defaultRequired = $this->holds()->getDefaultRequiredDate(
                $checkHolds, $catalog, $patron, $gatheredDetails
            );
        }

        try {
            $defaultPickup
                = $catalog->getDefaultPickUpLocation($patron, $gatheredDetails);
        } catch (\Exception $e) {
            $defaultPickup = false;
        }
        try {
            $defaultRequestGroup = empty($requestGroups)
                ? false
                : $catalog->getDefaultRequestGroup($patron, $gatheredDetails);
        } catch (\Exception $e) {
            $defaultRequestGroup = false;
        }

        $requestGroupNeeded = in_array('requestGroup', $extraHoldFields)
            && !empty($requestGroups)
            && (empty($gatheredDetails['level'])
                || $gatheredDetails['level'] != 'copy');

        return $this->createViewModel(
            array(
                'gatheredDetails' => $gatheredDetails,
                'pickup' => $pickup,
                'defaultPickup' => $defaultPickup,
                'homeLibrary' => $this->getUser()->home_library,
                'extraHoldFields' => $extraHoldFields,
                'defaultRequiredDate' => $defaultRequired,
                'requestGroups' => $requestGroups,
                'defaultRequestGroup' => $defaultRequestGroup,
                'requestGroupNeeded' => $requestGroupNeeded,
                'helpText' => isset($checkHolds['helpText'])
                    ? $checkHolds['helpText'] : null
            )
        );
    }

    /**
     * Paper Copy of a book
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function copyAction()
    {
//        // Force login if necessary:
//        $config = $this->getConfig();
//        if ((!isset($config->Mail->require_login) || $config->Mail->require_login)
//            && !$this->getUser()
//        ) {
//            return $this->forceLogin();
//        }

        // Retrieve the record driver:
        $driver = $this->loadRecord();

        // Create view
        $view = $this->createViewModel();
        // Set up reCaptcha
        $view->useRecaptcha = $this->recaptcha()->active('copy');
        // Process form submission:
        if ($this->formWasSubmitted('submit', $view->useRecaptcha)) {
            // Attempt to send the email and show an appropriate flash message:
            try {
                $this->getServiceLocator()->get('VuFind\Mailer')->sendRecord(
                    $view->to, $view->from, $view->message, $driver,
                    $this->getViewRenderer()
                );
                if ($this->params()->fromPost('ccself')
                    && $view->from != $view->to
                ) {
                    $this->getServiceLocator()->get('VuFind\Mailer')->sendRecord(
                        $view->from, $view->from, $view->message, $driver,
                        $this->getViewRenderer()
                    );
                }
                $this->flashMessenger()->setNamespace('info')
                    ->addMessage('email_success');
                return $this->redirectToRecord();
            } catch (MailException $e) {
                $this->flashMessenger()->setNamespace('error')
                    ->addMessage($e->getMessage());
            }
        }

        // Display the template:
        $view->setTemplate('record/copy');
        return $view;
    }
}
