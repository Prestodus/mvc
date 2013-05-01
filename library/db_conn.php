<?php

class db_conn extends PDO
{
    public $prepared_stmt = array();
    public $tables = array();
    public $prepared_queries = 0;
    public $unprepared_queries = 0;
    public $Qstats = array();
    public $have_errors = FALSE;
    public $table_aliases = array(
                                BIBLE_VERSION => "verses"
                                );
    
    public function __construct ($user = DB_USER, $pass = DB_PASSWORD)
    {
        try {
            $conn = parent::__construct("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, 
                                        $user, $pass, 
                                        array(  PDO::ATTR_PERSISTENT => true, 
                                                PDO::ATTR_ERRMODE => true, 
                                                PDO::ERRMODE_EXCEPTION => true));
                            
            $tables = $this->query("SHOW tables")->fetchAll();
            $tbls = $tables;

            foreach ($tables as $table) {
                
                $table = $table[0];
                
                $descs = $this->query("DESC {$table}")->fetchAll(PDO::FETCH_ASSOC);
                
                isset($this->table_aliases[$table]) and ($table = $this->table_aliases[$table]);
                
                $this->tables[$table]['fields'] = array();

                foreach ($descs as $desc) {
                    
                    $this->tables[$table]['fields'][] = $desc['Field']; 
                      
                }

            } 

            // map out the relationships between tables
            // to-do : design a more efficient rship mapper
            // to-do : multiple rships with the same table eg user_id, referer_user_id
            
            foreach ($tables as $table) { 
                
                $table = $table[0];
                
                isset($this->table_aliases[$table]) and ($table = $this->table_aliases[$table]);
                
                $table_fk = preg_replace("/s$/", "", $table) . "_id";
                
                $this->tables[$table]['rships'] = array('has_one' => array(), 'has_many' => array(), 'many_to_many' => array());
 
                $table_fields = $this->tables[$table]['fields'] ; 
                
                foreach ($tbls as $tbl) {
                    
                    $tbl = $tbl[0]; 
                    
                    isset($this->table_aliases[$tbl]) and ($tbl = $this->table_aliases[$tbl]);
                    
                    $tbl_fk = preg_replace("/s$/", "", $tbl) . "_id";
                    
                    $tbl_fields = $this->tables[$tbl]['fields'] ;

                    if (self::in_fields($table_fk, $tbl_fields)) {
                        
                        if (preg_match("/\_{$table}|{$table}\_/", $tbl)) {
                            
                            $this->tables[$table]['rships']['many_to_many'][] = preg_replace("/\_{$table}|{$table}\_/", "", $tbl);
                            
                        } else {
                            
                            $this->tables[$table]['rships']['has_many'][] = $tbl;
                            
                        }
                                            
                    }
                                        
                    if (self::in_fields($tbl_fk, $table_fields)) {
                        
                        $this->tables[$table]['rships']['has_one'][] = $tbl; 

                    } 

                }
                
            }

            return $this;
            
        } catch (PDOException $e) {
            
            error_log($e->getMessage());
            
        }
           
    }

    public static function instance()
    {
        static $db = null;
        
        if ($db == null) {
          $db = new db_conn;
        }

        return $db;
    }
    
    private function in_fields( $fk, array $fields)
    {
        foreach ($fields as $field) {

            if (preg_match("/{$fk}$/i", $field))  {

                return true;
            
            }
      
        }
        
    }
    public function __destruct()
    {
        if (DEVELOPMENT) {
        
            error_log("Prepared Queries : " . $this->prepared_queries);
            error_log("Unprepared Queries : " . $this->unprepared_queries);
            
            error_log("Total Queries : " . ($this->prepared_queries + $this->unprepared_queries));
            
            error_log("Prepared Statements : " . count($this->prepared_stmt));
            error_log("=======================================");
            
            foreach ($this->Qstats as $key => $times) {
                error_log($this->prepared_stmt[$key]->queryString . " --> " .  $times);
            }

        }

    }
    
}
