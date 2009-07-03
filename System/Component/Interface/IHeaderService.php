<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2009-05-27
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage Interface
 */
interface IHeaderService
{
    /**
     * show globally embeddable, or embeds for given alias
     * @param string $forAlias global embeds if not set
     * @return array [section => [guid => title]]
     */
    public static function getHeaderServideItems($forAlias = null);
    
    /**
     * @param string $embedGUID
     * @param EWillSendHeadersEvent$e
     * @return void
     */
    public static function sendHeaderService($embedGUID, EWillSendHeadersEvent $e);
}
?>