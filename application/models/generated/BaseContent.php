<?php

/**
 * BaseContent
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property integer $id
 * @property string $code
 * @property string $title
 * @property string $description
 * @property string $authorstr
 * @property string $tagstr
 * @property string $content
 * @property boolean $system
 * @property integer $avatar_id
 * @property integer $route_id
 * @property timestamp $sent_at
 * @property integer $sent_all
 * @property integer $sent_remaining
 * @property enum $sent_status
 * @property File $Avatar
 * @property Route $Route
 * @property Doctrine_Collection $Subscribers
 * @property Doctrine_Collection $ContentAndSubscriber
 * 
 * @package    ##PACKAGE##
 * @subpackage ##SUBPACKAGE##
 * @author     ##NAME## <##EMAIL##>
 * @version    SVN: $Id: Builder.php 6508 2009-10-14 06:28:49Z jwage $
 */
abstract class BaseContent extends Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->setTableName('content');
        $this->hasColumn('id', 'integer', 4, array(
             'primary' => true,
             'type' => 'integer',
             'unsigned' => true,
             'length' => '4',
             ));
        $this->hasColumn('code', 'string', 30, array(
             'type' => 'string',
             'notblank' => true,
             'length' => '30',
             ));
        $this->hasColumn('title', 'string', 50, array(
             'type' => 'string',
             'notblank' => true,
             'length' => '50',
             ));
        $this->hasColumn('description', 'string', null, array(
             'type' => 'string',
             'notblank' => true,
             'extra' => 
             array(
              'html' => 'rich',
             ),
             ));
        $this->hasColumn('authorstr', 'string', 50, array(
             'type' => 'string',
             'notnull' => true,
             'default' => '',
             'length' => '50',
             ));
        $this->hasColumn('tagstr', 'string', 255, array(
             'type' => 'string',
             'notnull' => true,
             'default' => '',
             'length' => '255',
             ));
        $this->hasColumn('content', 'string', null, array(
             'type' => 'string',
             'notblank' => true,
             'extra' => 
             array(
              'html' => 'rich',
             ),
             ));
        $this->hasColumn('system', 'boolean', null, array(
             'type' => 'boolean',
             'default' => false,
             'notnull' => true,
             ));
        $this->hasColumn('avatar_id', 'integer', 4, array(
             'type' => 'integer',
             'unsigned' => true,
             'length' => '4',
             ));
        $this->hasColumn('route_id', 'integer', 4, array(
             'type' => 'integer',
             'unsigned' => true,
             'unique' => true,
             'notnull' => true,
             'length' => '4',
             ));
        $this->hasColumn('sent_at', 'timestamp', null, array(
             'type' => 'timestamp',
             ));
        $this->hasColumn('sent_all', 'integer', 4, array(
             'type' => 'integer',
             'length' => '4',
             ));
        $this->hasColumn('sent_remaining', 'integer', 4, array(
             'type' => 'integer',
             'length' => '4',
             ));
        $this->hasColumn('sent_status', 'enum', null, array(
             'type' => 'enum',
             'values' => 
             array(
              0 => 'none',
              1 => 'pending',
              2 => 'completed',
             ),
             'default' => 'none',
             'notblank' => true,
             ));
    }

    public function setUp()
    {
        parent::setUp();
        $this->hasOne('File as Avatar', array(
             'local' => 'avatar_id',
             'foreign' => 'id'));

        $this->hasOne('Route', array(
             'local' => 'route_id',
             'foreign' => 'id'));

        $this->hasMany('Subscriber as Subscribers', array(
             'refClass' => 'ContentAndSubscriber',
             'local' => 'content_id',
             'foreign' => 'subscriber_id'));

        $this->hasMany('ContentAndSubscriber', array(
             'local' => 'id',
             'foreign' => 'content_id'));

        $nestedset0 = new Doctrine_Template_NestedSet(array(
             'hasManyRoots' => true,
             'rootColumnName' => 'root_id',
             ));
        $balauditable0 = new BalAuditable(array(
             'status' => 
             array(
              'disabled' => false,
             ),
             'enabled' => 
             array(
              'disabled' => false,
             ),
             'author' => 
             array(
              'disabled' => false,
             ),
             'created_at' => 
             array(
              'disabled' => false,
             ),
             'updated_at' => 
             array(
              'disabled' => false,
             ),
             'published_at' => 
             array(
              'disabled' => false,
             ),
             ));
        $searchable0 = new Doctrine_Template_Searchable(array(
             'fields' => 
             array(
              0 => 'code',
              1 => 'tagstr',
              2 => 'authorstr',
              3 => 'title',
              4 => 'description',
             ),
             ));
        $taggable0 = new Doctrine_Template_Taggable();
        $this->actAs($nestedset0);
        $this->actAs($balauditable0);
        $this->actAs($searchable0);
        $this->actAs($taggable0);
    }
}