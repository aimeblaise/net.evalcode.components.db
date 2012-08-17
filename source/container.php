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
  class Container implements View
  {
    // PREDEFINED PROPERTIES
    const ORDER_DEFAULT=Query::ORDER_ASC;
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
      $tableName=$type::TABLE_NAME;
      if(false===isset(self::$m_containersByType[$type]))
        return static::$tableName();

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
          throw new Exception_illegalState('components/db/container', sprintf(
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
     * @param array $conditions_
     *
     * @return \Components\View
     */
    public function view(array $conditions_=array())
    {
      return new Container_View($this, $conditions_);
    }

    /**
     * @param mixed $value_
     *
     * @return string
     */
    public function escape($value_)
    {
      // TODO Delegate to connection/driver/backend ...

      return $value_;
    }

    /**
     * @param Record $record_
     * @param mixed $primaryKey_
     */
    public function refresh(Record $record_, $primaryKey_=null)
    {
      // TODO Implement ...
    }

    /**
     * @param string $name_
     * @param string $referenceContainerType_
     * @param string $referenceColumnName_
     */
    public function registerReference($name_, $referenceContainerType_, $referenceColumnName_)
    {
      $this->m_referenceColumnNames[$name_]=$referenceColumnName_;
      $this->m_referenceContainerTypes[$name_]=$referenceContainerType_;
    }

    /**
     * @param string $name_
     * @param string $foreignKeyContainerType_
     * @param string $foreignKeyColumnName_
     */
    public function registerForeignKey($name_, $foreignKeyContainerType_, $foreignKeyColumnName_)
    {
      $this->m_foreignKeyColumnNames[$name_]=$foreignKeyColumnName_;
      $this->m_foreignKeyContainerTypes[$name_]=$foreignKeyContainerType_;
    }
    //--------------------------------------------------------------------------


    // OVERRIDES/IMPLEMENTS
    public function container()
    {
      return $this;
    }

    public function count(array $conditions_=array())
    {
      // TODO Implement ...

      return 0;
    }

    public function find(array $conditions_=array())
    {
      // TODO Implement ...

      return array();
    }

    public function findFirst(array $conditions_=array())
    {
      // TODO Implement ...

      return null;
    }

    public function findByPk($primaryKey_)
    {
      // TODO Implement ...
    }

    public function save(Record $record_)
    {
      // TODO Implement ...
    }

    public function delete(Record $record_)
    {
      // TODO Implement ...
    }

    public function __call($name_, array $args_=array())
    {
      // TODO Cache local
      if(isset($this->m_referenceContainerTypes[$name_]))
      {
        $referenceContainerType=$this->m_referenceContainerTypes[$name_];

        return $referenceContainerType::get()->findByPk(
          reset($args_)->{$this->m_referenceColumnNames[$name_]}
        );
      }

      if(isset($this->m_foreignKeyContainerTypes[$name_]))
      {
        $foreignKeyContainerType=$this->m_foreignKeyContainerTypes[$name_];

        return $foreignKeyContainerType::get()->view(array(
          'select'=>array(
            $this->m_foreignKeyColumnNames[$name_].' = ?',
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

    private $m_foreignKeyColumnNames=array();
    private $m_foreignKeyContainerTypes=array();
    private $m_referenceColumnNames=array();
    private $m_referenceContainerTypes=array();
    //--------------------------------------------------------------------------
  }
?>
