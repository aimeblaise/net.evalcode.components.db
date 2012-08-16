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
   * Container
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
  class Container
  {
    // PREDEFINED PROPERTIES
    const ORDER_ASC='ASC';
    const ORDER_DESC='DESC';
    const ORDER_DEFAULT=self::ORDER_ASC;

    const PRIMARY_KEY_DEFAULT='id';

    const TYPE_RECORD_DEFAULT='\Components\Record';
    //--------------------------------------------------------------------------


    // PROPERTIES
    public $connection;

    public $tableName;

    public $orderBy;
    public $primaryKey;
    public $recordType;
    //--------------------------------------------------------------------------


    // CONSTRUCTION
    public function __construct($connection_, $tableName_)
    {
      $this->connection=$connection_;
      $this->tableName=$tableName_;

      $this->primaryKey=static::PRIMARY_KEY_DEFAULT;
      $this->recordType=static::TYPE_RECORD_DEFAULT;

      $this->orderBy=$this->primaryKey.' '.static::ORDER_DEFAULT;
    }
    //--------------------------------------------------------------------------


    // STATIC ACCESSORS
    /**
     * @return \Components\Container
     *
     * @example
     * class Countries extends \Components\Container
     * {
     *   // PREDEFINED PROPERTIES
     *   const TABLE_NAME='countries';
     *   const TYPE_RECORD='Country'
     *   //---------------------------------------------------------------------
     * }
     *
     * class Country extends \Components\Record
     * {
     *   // PROPERTIES
     *   /\**
     *    * @var string
     *    *\/
     *   public $name;
     *   //---------------------------------------------------------------------
     * }
     *
     *
     * // Register container
     * Container::add('Container_Countries');
     *
     * // Access container
     * Container::countries()->find();
     */
    public static function add($type_, $name_=null)
    {
      if(null===$name_)
        $name_=$type_::TABLE_NAME;

      self::$m_containerTypesByTable[$name_]=$type_;
    }

    /**
     * @return \Components\Container
     */
    public static function get()
    {
      if(false===isset(self::$m_containersByType[$type]))
        return static::{$type::TABLE_NAME}();

      return self::$m_containersByType[$type];
    }

    /**
     * @return \Components\Container
     */
    public static function __callStatic($name_, array $args_=array())
    {
      if(false===isset(self::$m_containersByTable[$name_]))
      {
        if(false===isset(self::$m_containerTypesByTable[$name_])
          || false===@class_exists($containerType=self::$m_containerTypesByTable[$name_]))
        {
          throw new Exception_illegalState('components/container', sprintf(
            'Unknown container requested [%1$s].', $name_
          ));
        }

        // TODO Implement connection handling ...
        self::$m_containersByTable[$name_]=new $containerType(null, $name_);
        self::$m_containersByType[$containerType]=self::$m_containersByTable[$name_];
      }

      return self::$m_containersByTable[$name_];
    }
    //--------------------------------------------------------------------------


    // ACCESSORS/MUTATORS
    /**
     * @return \Components\Container
     */
    public function container()
    {
      return $this;
    }

    /**
     * @param array $conditions_
     *
     * @return \Components\View
     */
    public function view(array $conditions_=null)
    {
      return new Container_View($this, $conditions_);
    }

    /**
     * @param array $conditions_
     *
     * @return int
     */
    public function count(array $conditions_=null)
    {
      return $this->container()->countImpl($conditions_);
    }

    /**
     * @param array $conditions_
     *
     * @return array|\Components\Record
     */
    public function find(array $conditions_=null)
    {
      return $this->container()->findImpl($conditions_);
    }

    /**
     * @param array $conditions_
     *
     * @return \Components\Record
     */
    public function findFirst(array $conditions_=null)
    {
      return $this->container()->findFirstImpl($conditions_);
    }

    /**
     * @param mixed $primaryKey_
     *
     * @return \Components\Record
     */
    public function findByPk($primaryKey_)
    {
      return $this->container()->findByPkImpl($primaryKey_);
    }

    /**
     * @param \Components\Record $record_
     */
    public function save(Record $record_)
    {
      return $this->container()->saveImpl($record_);
    }

    /**
     * @param \Components\Record $record_
     */
    public function delete(Record $record_)
    {
      return $this->container()->deleteImpl($record_);
    }

    /**
     * @param \Components\Record $record_
     * @param mixed $primaryKey_
     */
    public function refresh(Record $record_, $primaryKey_=null)
    {
      return $this->container()->refreshImpl($record_, $primaryKey_);
    }

    /**
     * @param string $name_
     * @param string $referenceContainerType_
     * @param string $referenceColumnName_
     */
    public function registerReference($name_, $referenceContainerType_, $referenceColumnName_)
    {
      $this->container()->m_referenceColumnNames[$name_]=$referenceColumnName_;
      $this->container()->m_referenceContainerTypes[$name_]=$referenceContainerType_;
    }

    /**
     * @param string $name_
     * @param string $foreignKeyContainerType_
     * @param string $foreignKeyColumnName_
     */
    public function registerForeignKey($name_, $foreignKeyContainerType_, $foreignKeyColumnName_)
    {
      $this->container()->m_foreignKeyColumnNames[$name_]=$foreignKeyColumnName_;
      $this->container()->m_foreignKeyContainerTypes[$name_]=$foreignKeyContainerType_;
    }
    //--------------------------------------------------------------------------


    // OVERRIDES/IMPLEMENTS
    public function __call($name_, array $args_=array())
    {
      if(isset($this->container()->m_referenceContainerTypes[$name_]))
      {
        $referenceContainerType=$this->container()->m_referenceContainerTypes[$name_];

        return $referenceContainerType::get()->findByPk(
          reset($args_)->{$this->container()->m_referenceColumnNames[$name_]}
        );
      }

      if(isset($this->container()->m_foreignKeyContainerTypes[$name_]))
      {
        $foreignKeyContainerType=$this->container()->m_foreignKeyContainerTypes[$name_];

        return $foreignKeyContainerType::get()->view(array(
          'select'=>array(
            $this->container()->m_foreignKeyColumnNames[$name_].' = ?',
              reset($args_)->primaryKeyValue()
          )
        ));
      }
    }
    //--------------------------------------------------------------------------


    // IMPLEMENTATION
    protected static $m_containersByTable=array();
    protected static $m_containersByType=array();
    protected static $m_containerTypesByTable=array();

    private static $m_queryStringSegmentProcessors=array(
      'groupby'=>'buildQueryStringGroupBy',
      'having'=>'buildQueryStringHaving',
      'limit'=>'buildQueryStringLimit',
      'orderby'=>'buildQueryStringOrderBy',
      'select'=>'buildQueryStringSelect'
    );

    private $m_foreignKeyColumnNames=array();
    private $m_foreignKeyTableNames=array();
    private $m_referenceColumnNames=array();
    private $m_referenceTableNames=array();
    //-----


    /**
     * @param array $conditions_
     *
     * @return int
     */
    protected function countImpl(array $conditions_=null)
    {
      // TODO Implement ...

      return 0;
    }

    /**
     * @param array $conditions_
     *
     * @return array|\Components\Record
     */
    protected function findImpl(array $conditions_=null)
    {
      // TODO Implement ...

      return array();
    }

    /**
     * @param array $conditions_
     *
     * @return \Components\Record
     */
    protected function findFirstImpl(array $conditions_=null)
    {
      // TODO Implement ...

      return null;
    }

    /**
     * @param mixed $primaryKey_
     *
     * @return \Components\Record
     */
    protected function findByPkImpl($primaryKey_)
    {
      // TODO Implement ...
    }

    /**
     * @param \Components\Record $record_
     */
    protected function saveImpl(Record $record_)
    {
      // TODO Implement ...
    }

    /**
     * @param \Components\Record $record_
     */
    protected function deleteImpl(Record $record_)
    {
      // TODO Implement ...
    }

    /**
     * @param \Components\Record $record_
     * @param mixed $primaryKey_
     */
    protected function refreshImpl(Record $record_, $primaryKey_=null)
    {
      // TODO Implement ...
    }

    protected function buildQueryString(array $conditions_)
    {
      $conditions=array();
      foreach($conditions_ as $segmentType=>$segment)
        $conditions[$segmentType]=static::{self::$m_queryStringSegmentProcessors[strtolower($segmentType)]}($segment);

      if(isset($conditions['select']))
        array_unshift($conditions, 'WHERE');

      return implode(' ', $conditions);
    }

    protected function buildQueryStringSelect($condition_)
    {
      $statement=array_shift($condition_);

      $arguments=array();
      foreach($condition_ as $argument)
      {
        if(false===($pos=strpos($statement, '?')))
        {
          throw new Exception_IllegalArgument('components/container',
            'Query statement placeholder<>argument mismatch.'
          );
        }

        $statement=
          substr($statement, 0, $pos).
          '\''.
          $this->escape($argument).
          '\''.
          substr($statement, $pos+1);
      }

      return $statement;
    }

    protected function buildQueryStringLimit($limit_)
    {
      return sprintf('LIMIT %1$d', (int)$limit_);
    }

    protected function buildQueryStringOrderBy($orderBy_)
    {
      $order=static::ORDER_DEFAULT;
      if(false!==($pos=strpos($orderBy_, 'ASC')))
      {
        $orderBy_=trim(substr($orderBy_, 0, $pos));
      }
      else if(false!==($pos=strpos($orderBy_, 'DESC')))
      {
        $orderBy_=trim(substr($orderBy_, 0, $pos));
        $order='DESC';
      }

      // TODO Validate columns...
      $columns=array();
      foreach(explode(',', $orderBy_) as $column)
        $columns[$column=trim($column)]=$column;

      return sprintf('ORDER BY %1$s %2$s', implode(',', $columns), $order);
    }

    protected function escape($value_)
    {
      // TODO Delegate ...

      return $value_;
    }
    //--------------------------------------------------------------------------
  }
?>
