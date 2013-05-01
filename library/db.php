<?php

class db implements Iterator
{
    public          $query;
    public          $params;
    protected       $limit = array();
    public  static  $conn;
    public          $count;
    
    public          $select;
    public          $from;
    public          $join;
    public          $where;
    public          $and;
    public          $or;
    public          $in;
    public          $order;
    public          $insert;
    public          $update;
    public          $delete;
    public          $set;
    
    public          $get = null;
    
    public function __construct ($user = DB_USER, $pass = DB_PASSWORD)
    {
        try {

        } catch (PDOException $e) {
            error_log($e->getMessage());
        }
           
    }

    public static function instance()
    {
        static $db = null;
        
        if ($db == null) {
          $db = new self;
        }
        
        return $db;
    }

    public static function factory()
    {
        return new self;
    }

    public function insert ($table, $fields = null)
    {
        $table = self::table ($table) ;
        
        $insert = array();
        if (is_array($fields)) {
            foreach ($fields as $field => $value)
                $insert[] = "$field = '$value'";
            
        $insert = implode(",", $insert);
        } else {
            throw new Exception("Please supply an array(key=>value) pairs to insert");
        }            
        $this->query = " INSERT INTO {$table} SET {$insert} ";
        return $this;
    }

    public function update ($table, $fields = null)
    {
        $table = self::table ($table) ;
                
        $updates = array();
        if (is_array($fields)) {
            foreach ($fields as $field => $value)
                $updates[] = "$field = '$value'";
            
        $updates = implode(",", $updates);
        } else {
            throw new Exception("Please supply an array(key=>value) pairs to update");
        }            
        $this->query = " UPDATE {$table} SET {$updates} WHERE 1 ";
        return $this;
    }

    public function delete ($table)
    {
        $table = self::table ($table) ;        
        
        if (!isset($table))
            throw new Exception("Please supply an array(key=>value) pairs to insert");
                
        $this->query = " DELETE FROM {$table} WHERE 1 ";
        
        return $this; 
    }
    
    public function select ($fields = null)
    {
        $select = array();
        
        if (isset($fields)) {
            if (!is_array($fields))
                $fields = func_get_args();
            
            foreach ($fields as $field => $alias) {
                if (!is_numeric($field))
                    $select[] = "$field AS $alias";
                else
                    $select[] = "$alias";
            }
            $select = implode(",", $select);
        } else {
            $select = "*";
        }

        
        $this->query = " SELECT {$select} ";
        return $this; 
    }

    public function from ($tables)
    {
        if (!is_array($tables))        
            $tables = func_get_args();
            
        if (is_array($tables)) {
            
            $from = array();
            
            foreach ($tables as $table => $alias) {
                
                if ($table) {
                    
                    if (is_int($table)) {
                        
                        $table = self::table ($alias) ;
                        $from[] = " {$alias} ";
                        
                    } else {
                        
                        $table = self::table ($table) ;
                        $from[] = " {$table} AS {$alias} ";
                        
                    }
                        
                } else {
                    
                    $alias = self::table ($alias) ;
                    $from[] = " {$alias} ";
                    
                }
            }
            
        } else {
            
            $from = $tables;
            
        }
            
        $this->query .= "FROM " . implode(',', $from) . " WHERE 1 ";
        
        return $this;
    }    

    public function join ($joins = array())
    {
        $join = array();
        foreach ($joins as $field => $value) {
            $join[] = " {$field} = {$value} ";
        }
        $this->query .= " AND " . implode(" AND ", $join);
        
        return $this;
    }

    public function where ($conditions = array())
    {
        if (!is_array($conditions)) {
            $params = func_get_args();
            $conditions = array("id" => $params[0]);
        }
        
        foreach ($conditions as $field => $value) {
            if (preg_match("/^ *(between|is not null|is null|\>|\<)/i", $value) 
                or preg_match("/ *(between|is not null|is null|\>|\<) *$/i", $field))
                $where[] = " {$field} {$value} ";
            else
                $where[] = " {$field} = '{$value}' ";
        }
        
        if (count($conditions))
            $this->query .= " AND " . implode(" AND ", $where);

        return $this;
    }

    public function _and($conditions = array())
    {
        if (!is_array($conditions)) {
            $params = func_get_args();
            $conditions = array("id" => $params[0]);
        }
        
        foreach ($conditions as $field => $value) {
            if (preg_match("/^ *(between|is not null|is null|\>|\<)/i", $value) 
                or preg_match("/ *(between|is not null|is null|\>|\<) $/i", $field))
                $where[] = " {$field} {$value} ";
            else
                $where[] = " {$field} = '{$value}' ";
        }
        $this->query .= " AND " . implode(" AND ", $where);

        return $this;
    }

    public function _or($conditions = array())
    {
        if (!is_array($conditions)) {
            $params = func_get_args();
            $conditions = array("id" => $params[0]);
        }
        
        foreach ($conditions as $field => $value) {
            if (preg_match("/^ *(between|is not null|is null|\>|\<)/i", $value) 
                or preg_match("/ *(between|is not null|is null|\>|\<) $/i", $field))
                $where[] = " {$field} {$value} ";
            else
                $where[] = " {$field} = '{$value}' ";
        }
        $this->query .= " OR " . implode(" OR ", $where);

        return $this;
    }

    public function and_in ($field, $values = array())
    {
        if (is_array($values)) {
            $values = join(", ", $values) ;
        }

        $this->query .= " AND {$field} IN(" . $values . ") ";

        return $this;
    }

    public function or_in ($field, $values = array())
    {
        if (!is_array($values)) {
            throw Exception ('database::in usage -> in ($field, $values = array())');
        }

        $this->query .= " AND {$field} IN(" . join(", ", $values) . ") ";

        return $this;
    }

    public function limit ($offset = 0, $limit = 10) 
    {
        $this->limit = array($offset, $limit);
        
        $this->query = preg_replace("/ *LIMIT (\\?|[0-9]+), *(\\?|[0-9]+)/i", "", $this->query);
        
        if (preg_match("/select .+ from .+/i", $this->query))
            $this->query .= ' LIMIT ?, ?';
        
        $this->limit_params[0] = $offset;
        $this->limit_params[1] = $limit;
        
        return $this;
    }

    public function order ($by = "id", $order = "DESC") 
    {
        if (preg_match("/order +by /i", $this->query ))
            $this->query  = preg_replace("/order +by +[0-9]+ *, *[0-9]+ +(desc|asc)*/i", " ORDER BY {$by} {$order} ", $this->query );
        else
            $this->query .= " ORDER BY {$by} {$order} ";
        
        return $this;
    }

    public function group_by ($field = "") 
    {
        $this->query .= " GROUP BY {$field} ";
        return $this;
    }
    
    public function params($params = null) 
    {
        if (!is_array($params)) {
            $this->params = func_get_args();
        } else {
            $this->params = $params;       
        }
        
        return $this;
    }
    
    public function run ($params = null)
    {   
        //handle quoted params
        $this->query = preg_replace("/= *\'\?\'/", "= ?", $this->query);
        
        //prevent user from doing something extremely stupid .. 
        if (preg_match ("/^delete from * where 1 $/i", $this->query))
            throw Exception ("What the heck are you trying!! {$this->query} ?! ");
            
        //prevent something else stupid
        if (preg_match ("/^update * where 1 $/i", $this->query))
            throw Exception ("What the heck are you trying!! {$this->query} ?! ");
                        
        if (!is_array($params))
            $params = func_get_args() ;

        if (isset($this->params))
            $params = array_merge($params, $this->params);

        if (isset($this->limit_params))
            $params = array_merge($params, $this->limit_params);
            
        //retrieve a previously prepared stmt
        if (!$stmt = util::find_stmt($this->query, db_conn::instance()->prepared_stmt)) {
            
            //dont waste the prepared stmt; store for later use
            $stmt = db_conn::instance()->prepare($this->query);
            db_conn::instance()->prepared_stmt[] = $stmt;
            
            db_conn::instance()->unprepared_queries ++;
            
        } else { 
            
            db_conn::instance()->prepared_queries ++;
               
        }
        
        
        try {
            
            foreach ($params as $key => $param) {
                $param = (is_int($param)) ? intval($param) : $param ;
                $type = (is_int($param) ? PDO::PARAM_INT : PDO::PARAM_STR );
                $stmt->bindValue($key + 1, $param, $type) ;
            }
            
            if (!$stmt->execute()) {
                throw new Exception();  
            }
                      
        } catch (Exception $e) {

            error_log('Here is the troublesome query : ' . $this->query . print_r($params ,1));
            error_log($e->getMessage());
            error_log($e->getTraceAsString());
        }
        
        $this->limit_params = array();
        
        if (preg_match("/^ *select/i", $this->query)) {
            
            $rs = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $rs = self::stripslashes($rs);
            $this->count = count($rs);
            
            if (count($rs)) {
                
                if ($this->get and isset($rs[0][$this->get])) return $rs[0][$this->get];
                return $rs;
                
            } else {
                
                return null;  
                  
            }
            
        } else {
            
            return $stmt->rowCount();
            
        }
            
    }

    public function get($field)
    {
        $this->get = $field;
        
        return $this;
    }
    
    public static function stmt_keys($params)
    {
        $keys = array();
        foreach ($params as $key => $value) {
            $keys[$key] = "?";
        }
        return $keys;
    }

    public static function stmt_values($params)
    {
        $values = array();
        foreach ($params as $value) {
            $values[] = $value;
        }
        return $values;
    }
    
    public static function stripslashes ($array = array())
    {
        $rows = $array;
        
        foreach ($rows as $k => $row) {
            if (is_array($row)) {
                $rows[$k] = self::stripslashes($row);    
            } else {
                $rows[$k] = stripslashes($row);    
            }  
        }
        
        return $rows;
    }
    
    public function table ($table) {

        ($key = array_search($table, db_conn::instance()->table_aliases)) and 
        !is_int($key);

        if ($key) return $key;
        else return $table;
        
    }
    
    /*
     * Iterator functions
     * */
    public function rewind()
    {
        ;
    }
    
    public function next()
    {
        ;
    }
    
    public function current()
    {
        
    }
    
    public function key()
    {
        ;
    }
    
    public public function valid()
    {
        ;       
    } 

    
         
}



/* 
 * 
 * Some tests
 * 
$user = new DAO("users",1);


$rs = $db->insert("users", array("username"=> "?",
                            "password" => "?",
                            "email" => "?"))
         ->exec("alizabeth",
                "password('ng'a maina')", 
                "ndungi@gmail.com");
            
$rs = $db->select(array("email" => "username"))->from("users")->exec();
echo "<pre>" .  print_r($rs, 1) . "</pre> {$db->query}";
foreach ($rs as $row)
    echo $row['username'] . "<br/>";


echo "<pre>" .  print_r($user->get(), 1) . "</pre> ";
 */ 
