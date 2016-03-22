<?php

/**
 * Limp - less is more in PHP
 * @copyright   Bill Rocha - http://plus.google.com/+BillRocha
 * @license     MIT
 * @author      Bill Rocha - prbr@ymail.com
 * @version     0.2.1
 * @package     Limp
 * @access      public
 * @since       0.3.0
 *
 */

namespace Limp\Cli\Plugin;

use Model;
use Limp\App\App as Dock;
use Limp\Data;
use Config\Database;

class Table
{
    private $content;

    function __construct($cmd, $args)
    {

    	echo ' ok! | '.$cmd.' | '.\Limp\App\App::p($args, true);
    	if($cmd == 'list') $this->content = $this->listDb();
    }



    function run(){
    	return "\n\n".$this->content;
    }

    function listDb(){

        $dbname = 'webfil';

        $db = new Data\Db(Database::get());
        

        //GET TABLES --------------------------------------
        $db->query('SHOW TABLES From '.$dbname);

                Dock::e($db->result());
    /*

        $nm = 'Tables_in_'.$dbname;

        foreach ($db->result() as $table) {
            $tables[] = $table->{$nm};
        }
        */

        //Dock::p($tables, true);
        
        //GET COLUMNS --------------------------------------
        $tables = $db->result();
        
        foreach ($tables as $table) {
            $nm = 'Tables_in_'.$dbname;
            $db->query('SHOW COLUMNS FROM '.$dbname.'.'.$table->{$nm});

            //Dock::e($db->result());

            //echo '<hr><h2>'.$table.'</h2>';
            foreach ($db->result() as $col) {

                $tabs[$table->{$nm}][$col->Field] = ['type'=>$col->Type,
                                               'key'=>$col->Key,
                                               'null'=>$col->Null,
                                               'default'=>$col->Default,
                                               'extra'=>$col->Extra
                                              ];
                /*DOCK::p($col, true);

                echo '<b>name: </b>'.$col->Field.'<br>';
                echo '<b>type: </b>'.$col->Type.'<br>';
                echo '<b>key: </b>'.$col->Key.'<br>';
                echo '<b>extra: </b>'.$col->Extra.'<br>';
                echo '<b>null: </b>'.$col->Null.'<br>';
                echo '<b>default: </b>'.$col->Default.'<br>'
                */
            }
        }

        Dock::e($tabs);

    }


}