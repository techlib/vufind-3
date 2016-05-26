<?php
/**
 * Abstract base for cover loader plug-ins.
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
 * @package  Content
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/vufind2:developer_manual Wiki
 */
namespace ntk_module\Content;
use VuFind\Content\AbstractCover as AbstractCoverBase;

/**
 * Abstract base for cover loader plug-ins.
 *
 * @category VuFind2
 * @package  Content
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/vufind2:developer_manual Wiki
 */
abstract class AbstractCover extends AbstractCoverBase
{
    /**
     * Does this plugin support UID numbers?
     *
     * @var bool
     */
    protected $supportsUid = true;

    /**
     * Does this plugin support the provided ID array?
     *
     * @param array $ids IDs that will later be sent to load() -- see below.
     *
     * @return bool
     */
    public function supports($ids)
    {
        return
            ($this->supportsIsbn && isset($ids['isbn']))
            || ($this->supportsIssn && isset($ids['issn']))
            || ($this->supportsOclc && isset($ids['oclc']))
            || ($this->supportsUpc && isset($ids['upc']))
            || ($this->supportsUid && isset($ids['uid']));
    }
}
