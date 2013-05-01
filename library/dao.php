<?php
 
class DAO implements Iterator
{
    public static $db;
    public $table;
    public $rships ;
    private $pk = 'id';
    private $fk ;
    public $fields;
    public $id;
    public $conditions = array();
    // Iterator relevant variables
    public $ids_query;
    public $offset = 0;
    public $start = 0;
    public $step = 1;
    public $limit = null;
    public $iterator_id;
    
    public function __construct($obj, $id = null, $load = true)
    {   
        self::$db = new db;
        
        $this->table = $obj;
        
        $this->fields = db_conn::instance()->tables[$this->table]['fields'];
        $this->fk = preg_replace("/s$/i", "", $this->table) . "_" . $this->pk;

        //map out the relationships between different entities and store in rships array
        $this->rships = db_conn::instance()->tables[$this->table]['rships']; 
        
        if (is_array($id)) {
            
            $this->ids_query = db::factory()
                            ->select('id')
                            ->from($this->table)
                            ->where(db::stmt_keys($id))
                            ->params(db::stmt_values($id))
                            ;
            $id = null;
        
        } else if ($id instanceof db) {
            
            $this->ids_query = $id;
            $id = null;
            
        } else if ($id == "*") {
            
            $this->ids_query = db::factory()
                            ->select('id')
                            ->from($this->table)
                            ;
            $id = null;
        }
        
        if ((!isset($id) or !$id) and $this->ids_query instanceof db ) {
            $id = $this->ids_query->get('id')->run();
        }
            
        $this->id = $id; 
        
        return $this;
        
    }

    public function __get($var)
    {
        if (in_array($var . 's', $this->rships['has_one'])) {
            return $this->load_has_one()->$var;    
        }
        
        else if (in_array($var, $this->rships['has_many'])) {
            return $this->load_has_many()->$var;    
        }
        
        else if (in_array($var, $this->rships['many_to_many'])) {
            return $this->load_many_to_many()->$var;    
        }
        
        else {
            return $this->load_simple()->$var;
        }
        
    }

    public static function factory($obj, $id = null){
        return new self($obj, $id);   
    }

    public function is_collection () 
    {
        return (bool) $this->ids_query ;
    }

    /*
     * @method load : we get attributes of an object
     * @return DAO $this
     * */
    
    public function load_simple($property = '*') 
    {
        //load the table/objects own 1-to-1 attributes from the table's fields
        $attrs_r = self::$db->select($property)
                        ->from($this->table)
                        ->where(array("id"=>'?'))
                        ->run($this->id);
                       
        if (isset($attrs_r[0])) {
            
            foreach ($attrs_r[0] as $attr => $value) {
                
                $this->$attr = $value ;
                
            }
            
        }
        
        return $this;
        
    }
        
    public function load_has_one()
    {
        //load the objects 1-to-1 attributes whose info is referenced in other tables
        foreach ($this->rships['has_one'] as $key => $table) {
            
            $attr = preg_replace("/s$/", "", $table);
            $attr_fk = $attr . "_id";
            
            if (in_array($attr_fk, $this->fields)) {
                
                if (class_exists($model = preg_replace("/s$/", "", $table))) {
                    
                    $this->$attr = new $model($this->$attr_fk, true);
                
                } else {
                    
                    $this->$attr = new DAO($table, $this->$attr_fk, true);
                    
                }
            }
        }
        
        return $this;
        
    }
    
    public function load_has_many()
    {
     
        //get the has_many (one-to-many) attribtes
        foreach ($this->rships['has_many'] as $key => $attr) {
            
            $attr_fk = preg_replace("/s$/", "_id", $attr);
            
            $ids_query = self::$db->select("id")
                        ->from($attr)
                        ->where(array("$this->fk" => "?"))
                        ->params($this->id);
            
            if (class_exists($model = preg_replace("/s$/", "", $attr))) {
                
                $this->$attr = new $model($ids_query, false);
            
            } else {
                
                $this->$attr = new DAO($attr, $ids_query, false);
            
            }
                
        }
        
        return $this;
        
    }
    
    public function load_many_to_many()
    {
        //get the many-to-many attributes
        foreach ($this->rships['many_to_many'] as $key => $attr) {
            
            $attr_fk = preg_replace("/s$/", "_id", $attr);
            $pivot = self::get_pivot($attr, $this->table);
            
            $ids_query = self::$db->select("{$attr}.id")
                        ->from(array($pivot, $attr, $this->table))
                        ->join(array("{$pivot}.{$this->fk}" => "{$this->table}.id", "{$attr}.id" => "{$pivot}.{$attr_fk}"))
                        ->where(array("{$this->fk}" => "?"))
                        ->params($this->id);
        
            if (class_exists($attribute = preg_replace("/s$/", "", $attr))) {
                
                $this->$attr = new $attribute($ids_query, false);
                
            } else {
                 
                $this->$attr = new DAO($attr, $ids_query, false);
                
            }
                
        }
     
        return $this;
        
    }
    
    /* add a many-to-many attribute association
     * @param   string  $table  the other table in the many to many rship
     * @param   string  $attr   the attribute from $table to be added
     * 
     * @return  DAO $this   returns itself
     * */
    public function add($table, $attr)
    {
        $obj = new self($table, array(preg_replace("/s$/i", "", $table) => $attr));
        return $this->associate($obj);
    }
    
    public function belongs_and_has($obj) {
        
        $pivot = self::get_pivot($this->table, $obj->table);
        
        return self::$db->insert($pivot,array("{$this->fk}" => "?",
                                              "{$obj->fk}" => "?"))
                ->run($this->id, $obj->id);
                
    }

/*
 * !belongs_and_has
 * undoes a `belongs_and_has()`
 * */
    public function associate($obj) {
      
        if (is_array($obj)) {
            
            foreach ($obj as $dao) {
                
                $this->associate($dao); 
                                  
            }
            
            return true;
            
        } else {
//echo "$this->id, $obj->id";
            return !$this->has($obj) and 
                self::$db->insert(self::get_pivot($this->table, $obj->table),
                array("{$this->fk}" => "?",
                       "{$obj->fk}" => "?"))
                ->run($this->id, $obj->id);
            }
            
    }

/*
 * !belongs_and_has
 * undoes a `belongs_and_has()`
 * */
    public function dissociate(DAO $obj) {
        
        if ($obj->is_collection()) {
            
            $obj_ids = array();
            
            foreach ($obj as $dao) {
                $obj_ids[] = $dao->id;
            }
    
            foreach ($obj_ids as $obj_id) {
                
                self::dissociate(self::factory($obj->table, $obj_id));
                                    
            }
            
            return true;
            
        } else {

            return self::$db->delete(self::get_pivot($this->table, $obj->table))
                    ->where( 
                         array("{$this->fk}" => "?",
                               "{$obj->fk}" => "?"))
                    ->run($this->id, $obj->id);
                
            }
    }

    /* add a many-to-many attribute association
     * @param   string  $table  the other table in the many to many rship
     * @param   string  $attr   the attribute from $table to be added
     * 
     * @return  DAO $this   returns itself
     * */
    public function remove($table, $attr)
    {
        //select $attr id
        $attr_r = self::$db->select("id")->from($table)
                       ->where(array(preg_replace("/s$/i", "", $table) => "?"))
                       ->run($attr);
             
        $attr_fk = preg_replace("/s$/i", "", $table) . "_id";
        
        self::$db->delete(self::get_pivot($this->table, $table))
             ->where(array("{$this->fk}" => $this->id,
                          "{$attr_fk}" => "?"))
             ->run($attr_r[0]['id']);

        return $this;
    }
    
    /*
     * delete method : deleted the db object and its attribs
     * */
    
    public function del () 
    {
        //dissociate with one-to-many tables
        foreach ($this->rships['has_many'] as $table) {

            self::$db->delete($table)
                 ->where(array("{$this->fk}" => "?"))
                 ->run($this->id);
                 
        }
        //dissociate with many-to-many tables        
        foreach ($this->rships['many_to_many'] as $table) {

            self::$db->delete(self::get_pivot($this->table, $table))
                 ->where(array("{$this->fk}" => "?"))
                 ->run($this->id);
                 
        }

        //remove the item
        self::$db->delete($this->table)->where($this->id)->run();
        
        return true;
    }

    public function has (DAO $obj)
    {
        $r = db::factory()->select('user_id')
                     ->from(self::get_pivot($this->table, $obj->table))
                     ->where(array("{$this->fk}" => "?", "{$obj->fk}" => "?"))
                     ->run($this->id, $obj->id);

        return isset($r[0]['user_id']) and (bool)$r[0]['user_id'];
    }
    
    public function has_one_of ($objs)
    {
        $thisobj = preg_replace("/s$/", "", $this->table);
        foreach ($objs as $obj)
            if ($this->id == $obj->$thisobj->id)
                return $obj;
        
        return false;
    }
    
    /*
     * save method
     * 1. save only the updated fields ; has faster db writes :)
     * 2. insert if new record, update if existing
     * @return  db $db   this object
     * */
    public function save()
    {
        $fields = array();
        
        if (isset($this->id) and $this->id) {
            
            foreach ($this->fields as $field) {
                if (isset($this->$field))
                    $fields[$field] = $this->$field;
            }

            $this->load_simple();
            
            $update = array();
            foreach ($fields as $field => $value) {
                
                if ($value != $this->$field) {
                    $keys[$field] = "?";
                    if ($value == "null") $values[] = "null";
                    else $values[] = $value;
                }
            }
            
            if (isset($keys))
                $update = self::$db->update($this->table, $keys)->where($this->id)->run($values);
                //reload
                $this->load_simple();                
                return  $update;
                
        } else {
            $insert = array();
            foreach ($this->fields as $field) {
                if (isset($this->$field)) {
                    $keys[$field] = "?";
                    $values[] = $this->$field;
                }
            } 
            
            if (isset($keys))  {  
                $insert = self::$db->insert($this->table, $keys)->run($values);       
                $this->id = db_conn::instance()->lastInsertId();
                //reload
                $this->load_simple();
                
                return $insert;
            }
            
        }
  
    }

    /*
     * get_pivot : returns the pivot table of table1 and table2
     * @param   string  $table1
     * @param   string  $table2
     * 
     * @return  string  $table_name;
     * */    
    private function get_pivot($table1, $table2) 
    {
        foreach (db_conn::instance()->tables as $table_name => $desc) {
            
            if (preg_match("/^$table1\_$table2$/", $table_name))
                return $table_name ;
            else if (preg_match("/^$table2\_$table1$/", $table_name))
                return $table_name ;
                
        }
    }
    
    /*
     * Iterator functions
     * */
    public function rewind()
    {
        $this->offset = $this->start;
    }
    
    public function next()
    {
        $this->offset += $this->step;
    }
    
    public function current()
    {
        return $this->element;
    }
    
    public function key()
    {
        return $this->offset;
    }
    
    public public function valid()
    {
        if (isset($this->limit) and (($this->limit + $this->start) <= $this->offset)) return false;
        
        if (!$this->iterator_id = $this->ids_query->limit($this->offset, $this->step)->get('id')->run()) {

            return false;
        
        }
        
        if (class_exists($model = preg_replace("/s$/", "", $this->table)))
            $this->element = new $model($this->iterator_id);
        else
            $this->element = new DAO($this->table, $this->iterator_id);

        return (bool)$this->element->id;
               
    } 

/*
 * some methods to navigate objects of this type
 * these r hacks really, if you can think of anything better please bump me in the head :(
 * */
    public function before()
    {
        $before = $this->id - 1;

        if ($before)
            return new DAO($this->table, $before);
        else
            return null;
    }

    public function after()
    {
        $after = $this->id + 1;
        
        if ($after)
            return new DAO($this->table, $after);
        else
            return null;
    }
    
    public function last()
    {
        if ($this->count() > 0) {
            $rs = $this->ids_query->order()->limit(0, 1)->run();
            $last = $rs[0]['id'];
            
            return new DAO($this->table, $last);
        } else {
            return null;
        }
    }
    
    public function count()
    {
        return count($this->ids_query->run());    
    }   
    
    public function as_array()
    {
        return $this->ids_query->run();
    }
    
    public function order($by, $asc_desc)
    {
        if (preg_match("/limit/i", $this->ids_query->query)) {
            
            $this->ids_query->query = 
            preg_replace("/limit/i", " order by {$by} {$asc_desc} LIMIT ", $this->ids_query->query);
            
        } else {
            
            $this->ids_query->query .= " order by {$by} {$asc_desc} ";
            
        }

        return $this;
    }
        
}


/* 
 * **************
 * Some Examples
 * **************
$user = new DAO("users");
$user->username = "niceguy";
$user->email = "niceguy@goodplace.com";
$user->save() and $user->add("roles", "login");
// you have now created a user with the login role
$user->delete();
// you have deleted niceguy, together with all other associated entries (!! so be careful or override this method)

$rs = $db->insert("users", array("username"=> "?",
                            "password" => "?",
                            "email" => "?"))
         ->run("alizabeth",
                "password('ng'a maina')", 
                "ndungi@gmail.com");
            
$rs = $db->select(array("email" => "username"))->from("users")->run();
echo "<pre>" .  print_r($rs, 1) . "</pre> {$db->query}";
foreach ($rs as $row)
    echo $row['username'] . "<br/>";


echo "<pre>" .  print_r($user, 1) . "</pre> ";



* 
 *******/ 
