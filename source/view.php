<?php
/**
 * Copyright (C) 2012 evalcode.net
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package net.evalcode.components
 */
namespace Components;


  /**
   * View
   *
   * <p>
   *   Public interface for (filtered) container access.
   * </p>
   *
   * @todo Refactor/merge with type/Collection, type/Map.
   * Build towards an unified interface for e.g. ui components to
   * access collections/maps(arrays) and containers/views etc.
   * Could make latest additions to ui component regarding databinding
   * unneccessary.
   *
   * @package net.evalcode.components
   * @subpackage db
   *
   * @since 1.0
   * @access public
   *
   * @author Carsten Schipke <carsten.schipke@evalcode.net>
   * @copyright Copyright (C) 2012 evalcode.net
   * @license GNU General Public License 3
   */
  interface View
  {
    // ACCESSORS/MUTATORS
    /**
     * @return \Components\Container
     */
    function container();

    /**
     * @param array $conditions_
     *
     * @return int
     */
    function count(array $conditions_=array());

    /**
     * @param array $conditions_
     *
     * @return array|\Components\Record
     */
    function find(array $conditions_=array());

    /**
     * @param array $conditions_
     *
     * @return \Components\Record
     */
    function findFirst(array $conditions_=array());

    /**
     * @param mixed $primaryKey_
     *
     * @return \Components\Record
     */
    function findByPk($primaryKey_);

    /**
     * @param \Components\Record $record_
     */
    function save(Record $record_);

    /**
     * @param \Components\Record $record_
     */
    function delete(Record $record_);
    //--------------------------------------------------------------------------
  }
?>
