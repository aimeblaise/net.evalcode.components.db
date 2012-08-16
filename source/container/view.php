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
   * @subpackage db
   *
   * @since 1.0
   * @access public
   *
   * @author Carsten Schipke <carsten.schipke@evalcode.net>
   * @copyright Copyright (C) 2012 evalcode.net
   * @license GNU General Public License 3
   */
  class Container_View extends Container
  {
    // CONSTRUCTION
    public function __construct(Container $container_, array $filterConditions_=null)
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

    public function __get($name_)
    {
      return $this->m_container->$name_;
    }
    //--------------------------------------------------------------------------


    // IMPLEMENTATION
    /**
     * @var \Components\Container
     */
    private $m_container;
    private $m_filterConditions;
    //-----


    protected function buildQueryString(array $conditions_)
    {
      $conditions=array();
      foreach($conditions_ as $segmentType=>$segment)
        $conditions[$segmentType]=static::{self::$m_queryStringSegmentProcessors[strtolower($segmentType)]}($segment);

      if(isset($conditions['select']))
        array_unshift($conditions, 'WHERE');

      if(null!==$this->m_viewFilterConditions)
      {
        $viewFilterConditions=parent::buildQueryString($this->m_viewFilterConditions);

        if(isset($conditions['select']))
          $conditions['select']="$conditions[select] AND ($viewFilterConditions)";
        else
          $conditions['select']=$viewFilterConditions;
      }

      var_dump(implode(' ', $conditions));
      return implode(' ', $conditions);
    }
    //--------------------------------------------------------------------------
  }
?>
