<?php
/**
 * Cover Controller
 *
 * PHP Version 5
 *
 * Copyright (C) Villanova University 2011.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License version 2,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.    See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA    02111-1307    USA
 *
 * @category VuFind2
 * @package  Controller
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://www.vufind.org  Main Page
 */
namespace ntk_module\Controller;
use VuFind\Controller\CoverController as CoverControllerBase;

/**
 * Generates covers for book entries
 *
 * @category VuFind2
 * @package  Controller
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://www.vufind.org  Main Page
 */
class CoverController extends CoverControllerBase
{
    /**
     * Convert image parameters into an array for use by the image loader.
     *
     * @return array
     */
    protected function getImageParams()
    {
        $params = $this->params();  // shortcut for readability
        return [
            // Legacy support for "isn" param which has been superseded by isbn:
            'isbn' => $params()->fromQuery('isbn') ?: $params()->fromQuery('isn'),
            'size' => $params()->fromQuery('size'),
            'type' => $params()->fromQuery('contenttype'),
            'title' => $params()->fromQuery('title'),
            'author' => $params()->fromQuery('author'),
            'callnumber' => $params()->fromQuery('callnumber'),
            'issn' => $params()->fromQuery('issn'),
            'oclc' => $params()->fromQuery('oclc'),
            'upc' => $params()->fromQuery('upc'),
            'uid' => $params()->fromQuery('uid')
        ];
    }
}

