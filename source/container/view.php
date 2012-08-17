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
   * Container_View
   *
   * @package net.evalcode.components
   * @subpackage db.container
   *
   * @since 1.0
   * @access public
   *
   * @author Carsten Schipke <carsten.schipke@evalcode.net>
   * @copyright Copyright (C) 2012 evalcode.net
   * @license GNU General Public License 3
   */
  class Container_View implements View
  {
    // CONSTRUCTION
    public function __construct(Container $container_, array $filterConditions_=array())
    {
      $this->m_container=$container_;
      $this->m_filterConditions=$filterConditions_;
    }
    //--------------------------------------------------------------------------


    // OVERRIDES/IMPLEMENTS
    public function container()
    {
      return $this->m_container;
    }

    public function count(array $conditions_=array())
    {
      return $this->m_container->count(Query::mergeConditions(
        $conditions_, $this->m_filterConditions
      ));
    }

    public function find(array $conditions_=array())
    {
      return $this->m_container->find(Query::mergeConditions(
        $conditions_, $this->m_filterConditions
      ));
    }

    public function findFirst(array $conditions_=array())
    {
      return $this->m_container->findFirst(Query::mergeConditions(
        $conditions_, $this->m_filterConditions
      ));
    }

    public function findByPk($primaryKey_)
    {
      return $this->m_container->findFirst(Query::mergeConditions(
        array('select'=>array($this->m_container->primaryKey.' = ?', $primaryKey_)),
        $this->m_filterConditions
      ));
    }

    public function save(Record $record_)
    {
      // TODO Implement
    }

    public function delete(Record $record_)
    {
      // TODO Implement
    }
    //--------------------------------------------------------------------------


    // IMPLEMENTATION
    private $m_filterConditions=array();
    /**
     * @var \Components\Container
     */
    private $m_container;
    //--------------------------------------------------------------------------
  }
?>
