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
   * Query
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
   *
   * TODO Refactor to complex query builder / parser / DSL.
   */
  class Query
  {
    // PREDEFINED PROPERTIES
    const ORDER_ASC='ASC';
    const ORDER_DESC='DESC';
    //--------------------------------------------------------------------------


    // STATIC ACCESSORS
    /**
     * @param \Components\Container $container_
     * @param array $conditions_
     *
     * @return string
     */
    public static function buildQueryString(Container $container_, array $conditions_)
    {
      $conditions=array();
      foreach($conditions_ as $segmentType=>$segment)
        $conditions[$segmentType]=static::{self::$m_queryStringSegmentProcessors[strtolower($segmentType)]}($container_, $segment);

      if(isset($conditions['select']))
        array_unshift($conditions, 'WHERE');

      return implode(' ', $conditions);
    }

    // TODO Implement (thats just naive prototyping)
    public static function mergeConditions(array $conditionsA_, array $conditionsB_)
    {
      $conditions=array();
      foreach(self::$m_queryStringSegmentProcessors as $segmentType=>$segmentProcessor)
      {
        if(isset($conditionsA_[$segmentType]))
        {
          if(isset($conditionsB_[$segmentType]))
          {
            // merge
            if('select'===$segementType)
            {
              $conditions[$segmentType]=array_merge(
                array("{$conditionsA_[0]} AND ({$conditionsB_[0]})"),
                array_slice($conditionsA_, 1),
                array_slice($conditionsB_, 1)
              );
            }
            else
            {
              $conditions[$segmentType]=$conditionsB_[$segmentType];
            }
          }
          else
          {
            $conditions[$segmentType]=$conditionsA_[$segmentType];
          }
        }
        else if(isset($conditionsB_[$segmentType]))
        {
          $conditions[$segmentType]=$conditionsB_[$segmentType];
        }
      }

      return $conditions;
    }
    //--------------------------------------------------------------------------


    // IMLEMENTATION
    private static $m_queryStringSegmentProcessors=array(
      'select'=>'buildQueryStringSelect',
      'groupby'=>'buildQueryStringGroupBy',
      'having'=>'buildQueryStringHaving',
      'orderby'=>'buildQueryStringOrderBy',
      'limit'=>'buildQueryStringLimit'
    );
    //-----


    private static function buildQueryStringSelect(Container $container_, $condition_)
    {
      $statement=array_shift($condition_);

      $arguments=array();
      foreach($condition_ as $argument)
      {
        if(false===($pos=strpos($statement, '?')))
        {
          throw new Exception_IllegalArgument('components/db/query',
            'Query statement placeholder<>argument mismatch.'
          );
        }

        $statement=
          substr($statement, 0, $pos).
          '\''.
          $container_->escape($argument).
          '\''.
          substr($statement, $pos+1);
      }

      return $statement;
    }

    private static function mergeQueryStringSelect($conditionA_, $conditionB_)
    {

    }

    private static function buildQueryStringLimit(Container $container_, $limit_)
    {
      return sprintf('LIMIT %1$d', (int)$limit_);
    }

    private static function buildQueryStringOrderBy(Container $container_, $orderBy_)
    {
      $order=self::ORDER_ASC;
      if(false!==($pos=strpos($orderBy_, self::ORDER_ASC)))
      {
        $orderBy_=trim(substr($orderBy_, 0, $pos));
      }
      else if(false!==($pos=strpos($orderBy_, self::ORDER_DESC)))
      {
        $orderBy_=trim(substr($orderBy_, 0, $pos));
        $order=self::ORDER_DESC;
      }

      // TODO Validate columns ...
      $columns=array();
      foreach(explode(',', $orderBy_) as $column)
        $columns[$column=trim($column)]=$column;

      return sprintf('ORDER BY %1$s %2$s', implode(',', $columns), $order);
    }
    //--------------------------------------------------------------------------
  }
?>
