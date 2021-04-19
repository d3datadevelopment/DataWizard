<?php

/**
 * This Software is the property of Data Development and is protected
 * by copyright law - it is NOT Freeware.
 * Any unauthorized use of this software without a valid license
 * is a violation of the license agreement and will be prosecuted by
 * civil and criminal law.
 * http://www.shopmodule.com
 *
 * @copyright (C) D3 Data Development (Inh. Thomas Dartsch)
 * @author        D3 Data Development - Daniel Seifert <support@shopmodule.com>
 * @link          http://www.oxidmodule.com
 */

namespace D3\DataWizard\Application\Model;

interface QueryBase
{
    public function run();

    /**
     * @return string
     */
    public function getExportFilenameBase() : string;

    /**
     * @return string
     */
    public function getTitle() : string;

    /**
     * @return string
     */
    public function getDescription() : string;

    /**
     * @return string
     */
    public function getButtonText() : string;

    /**
     * @return string
     */
    public function getQuery() : string;
}