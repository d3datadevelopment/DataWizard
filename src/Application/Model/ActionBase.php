<?php

/**
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * https://www.d3data.de
 *
 * @copyright (C) D3 Data Development (Inh. Thomas Dartsch)
 * @author    D3 Data Development - Daniel Seifert <info@shopmodule.com>
 * @link      https://www.oxidmodule.com
 */

declare(strict_types=1);

namespace D3\DataWizard\Application\Model;

use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\Eshop\Core\Exception\DatabaseConnectionException;
use OxidEsales\Eshop\Core\Exception\DatabaseErrorException;
use OxidEsales\Eshop\Core\Registry;

abstract class ActionBase implements QueryBase
{
    /**
     * @return string
     */
    public function getDescription() : string
    {
        return '';
    }

    /**
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     */
    public function run()
    {
        $this->executeAction( $this->getQuery() );
    }

    /**
     * @param array $query
     *
     * @return int
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     */
    public function executeAction( array $query ): int
    {
        [ $queryString, $parameters ] = $query;

        $queryString = trim($queryString);

        if ( strtolower( substr( $queryString, 0, 6 ) ) === 'select' ) {
            throw oxNew(
                Exceptions\TaskException::class,
                $this,
                Registry::getLang()->translateString( 'D3_DATAWIZARD_ERR_ACTIONSELECT' )
            );
        }

        $affected = DatabaseProvider::getDb( DatabaseProvider::FETCH_MODE_ASSOC )->execute( $queryString, $parameters );

        throw oxNew(
            Exceptions\TaskException::class,
            $this,
            sprintf(
                Registry::getLang()->translateString(
                    $affected === 1 ? 'D3_DATAWIZARD_ERR_ACTIONRESULT' : 'D3_DATAWIZARD_ERR_ACTIONRESULTS'
                ),
                $affected
            )
        );
    }

    /**
     * @return string
     */
    public function getButtonText() : string
    {
        return "D3_DATAWIZARD_ACTION_SUBMIT";
    }
}