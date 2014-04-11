<?php
namespace ePals;

use \ePals\Base\Record;
use \Exception;

class Assignment extends Record {
    
    public $id;
    public $assignmentName;
    protected $description;
    protected $users;
    protected $metadata;
    protected $groupID;
    protected $activities;
    protected $startDate;
    protected $endDate;
    protected $parent;
    protected $project;
    
    function getID() {
        return $this->id;
    }
    
    public function getName() {
        return $this->assignmentName;
    }

    public function getGroupID() {
        return $this->groupID;
    }

    public function getStartDate() {
        return $this->startDate;
    }

    public function getEndDate() {
        return $this->endDate;
    }

    public function getParent() {
        return $this->parent;
    }

    public function getProject() {
        return $this->project;
    }

    public function setName($name) {
        $this->assignmentName = $name;
    }

    public function setGroupID($groupID) {
        $this->groupID = $groupID;
    }

    public function setStartDate($startDate) {
        $this->startDate = $startDate;
    }

    public function setEndDate($endDate) {
        $this->endDate = $endDate;
    }

    public function setParent($parent) {
        $this->parent = $parent;
    }

    public function setProject($project) {
        $this->project = $project;
    }
    
    function setMetadata($key,$value) {
        if ((empty($key)) || is_null($key)) {
            throw new Exception("Key can't be null");
        }
        $tmp = array($key => $value);
        if (is_null($this->metadata)) {
            $this->metadata = $tmp;
            $res = parent::add();
        } else {
            $this->metadata = array_merge($this->metadata,$tmp);
            $res = parent::update();
        }
        return $res;
    }
    
    function getMetadata($key) {
        return $this->metadata[$key];
    }
    
    function addUser($user) {
        if ((empty($user)) || is_null($user)) {
            throw new Exception("User can't be null");
        }
        if (is_null($this->users)) {
            $this->users = $user;
            $res = parent::add();
        } else {
            $this->users = array_merge($this->users,$user);
            $res = parent::update();
        }
        return $res;
    }
    
    function getUsers () {
        return $this->users;
    }
    
    function addActivity($type,$id) {
        if ((empty($type)) || is_null($type)) {
            throw new Exception("Type can't be null");
        }
        $tmp = array($type => $id);
        if (is_null($this->activities)) {
            $this->activities = $tmp;
            $res = parent::add();
        } else {
            $this->activities = array_merge($this->activities,$tmp);
            $res = parent::update();
        }
        return $res;
    }
    
    function getActivities() {
        return $this->activities;
    }
    
    function getUserAssignments ($user) {
    }
    
    public function getDescription() {
        return $this->description;
    }

    public function setDescription($description) {
        $this->description = $description;
    }
    
    function load($id) {
        if(empty($id)) {
            throw new Exception("id variable cannot be left empty in the load() method!");
            return ;
        }
        $this->id = $id;
        parent::get();
        if(empty($this->assignmentName)){
            $this->id = null;
        }
    }
}
