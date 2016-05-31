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
    use HoldsTrait;

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
