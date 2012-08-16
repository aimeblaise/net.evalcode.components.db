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
   * Record
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
  class Record
  {
    // CONSTRUCTION
    public function __construct(Container $container_=null, array $properties_=array())
    {
      $this->m_container=$container_;
      $this->m_properties=HashMap::valueOf($properties_);
    }
    //--------------------------------------------------------------------------


    // ACCESSORS/MUTATORS
    /**
     * @return \Components\HashMap
     */
    public function properties()
    {
      return $this->m_properties;
    }

    /**
     * @return \Components\Container
     */
    public function container()
    {
      return $this->m_container;
    }

    public function primaryKey()
    {
      return $this->primaryKey;
    }

    /**
     * @return mixed
     */
    public function primaryKeyValue()
    {
      return $this->{$this->primaryKey};
    }
    //--------------------------------------------------------------------------


    // OVERRIDES/IMPLEMENTS
    public function attach(Container $container_)
    {
      $this->m_container=$container_;
      $this->refresh();
    }

    public function detach()
    {
      $this->t_primaryKey=$this->m_properties->remove($this->primaryKey);
      $this->m_container=null;
    }

    public function refresh()
    {
      $this->m_container->refresh($this, $this->t_primaryKey);
      $this->t_primaryKey=null;
    }

    public function __get($name_)
    {
      if(false===$this->m_properties->containsKey($name_))
      {
        if(isset($this->m_container->$name_))
          return $this->m_container->$name_;

        return $this->m_container->$name_($this);
      }

      return $this->m_properties->get($name_);
    }

    public function __set($name_, $value_)
    {
      return $this->m_properties->put($name_, $value_);
    }

    public function hashCode()
    {
      if(null===$this->m_container)
        return spl_object_hash($this);

      return sprintf('%1$s#%2$s',
        $this->m_container->tableName, $this->primaryKeyValue()
      );
    }

    public function equals($object_)
    {
      if($object_ instanceof self)
        return $this->hashCode()===$object_->hashCode();

      return false;
    }

    /**
     * @see Components.Object::__toString()
     */
    public function __toString()
    {
      return (string)$this->m_properties;
    }
    //--------------------------------------------------------------------------


    // IMPLEMENTATION
    /**
     * @var \Components\HashMap
     */
    private $m_properties;
    /**
     * @var \Components\Container
     */
    private $m_container;

    /**
     * @internal
     */
    private $t_primaryKey;
    //--------------------------------------------------------------------------
  }
?>
