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

namespace Limp\Cli;

use Limp\Data;

class Limp 
{

    private $configKeysPath = null;
    private $cliPath = null;
    private $timer = 0;

    function __construct($argv)
    {
        echo ' Less is more in PHP!';
        if (php_sapi_name() !== 'cli') exit('It\'s no cli!');

        //Constants:
        $this->cliPath = __DIR__.'/';
        $this->configKeysPath = _CONFIG.'keys/';
        $this->timer = microtime(true);

        //Command line settings...
        echo $this->request($argv);

        exit("\n  Finished in ".number_format((microtime(true)-$this->timer)*1000,3)." ms.\n");
    }

    //CORE Request
    function request($rqst)
    {
        array_shift($rqst);
        $ax = $rqst;
        foreach($rqst as $a){
            array_shift($ax);
            if(strpos($a, '-h') !== false || strpos($a, '?') !== false) return $this->help();
            if(strpos($a, 'optimize') !== false) return $this->cmdOptimize(substr($a, 8), $ax);
            if(strpos($a, 'key:') !== false) return $this->cmdKey(substr($a, 4), $ax);
            if(strpos($a, 'make:') !== false) return $this->cmdMake(substr($a, 5), $ax);
        }
        //or show help...
        return $this->help();
    }

    //Command KEY
    function cmdKey($v, $arg)
    {
        echo '  key: '.$v;

        if(count($arg) > 0) {
            echo "\n\n  Arguments:";
            foreach ($arg as $a) {echo "\n\t$a";}
        }

        if($v == 'generate'){
            //check if path exists
            if(!is_dir($this->configKeysPath)) mkdir($this->configKeysPath, 0777);
            
            //Create Can Keys
            Data\Can::createKeys();
            echo "\n\n  New CAN key generated - success!";

            //Now, OPEN_SSL
            (new Data\Openssltools)->createKeys();
            echo "\n  OpenSSL keys & certificates - success!";

            return "\n  Location: ".$this->configKeysPath."\n\n";
        }

        elseif($v == 'list'){
            echo "\n\n  Ciphers:";
            foreach (mcrypt_list_algorithms() as $x) {echo "\n\t".$x;}
            echo "\n\n  Cipher Modes:";
            foreach (mcrypt_list_modes() as $x) {echo "\n\t".$x;}
        }

        else return "\n\n  ----- ERROR: Command 'key:$v' not found!\n".$this->help();
    }

    //Command MAKE
    function cmdMake($v, $arg)
    {
        echo '  make: '.$v;

        if(isset($arg[0])) $arg[0] = str_replace('\\', '/', $arg[0]);
        else return "\n\n  ERROR: indique o NOME do arquivo!\n";

        $type = strtolower(trim($v));

        if($type != 'controller' && $type != 'model' && $type != 'html'){
            return "\n\n  ----- ERROR: Command 'make:$v' not found!\n".$this->help();
        }

        return $this->createFile($arg[0], $type);
    }

    //Command OPTIMIZE
    function cmdOptimize($v, $arg)
    {
        //TODO : optimize!
        echo "\n  >> Optimized - success!\n";
    }

    // Checa um diretório e cria se não existe - retorna false se não conseguir ou não existir
    function checkAndOrCreateDir($dir, $create = false, $perm = '0777')
    {
        if(is_dir($dir) && is_writable($dir)) return true;
        elseif($create === false) return false;

        @mkdir($dir, $perm, true);
        @chmod($dir, $perm);

        if(is_writable($dir)) return true;
        return false;
    }

    // Create file (controller/model/library)
    function createFile($name, $type = 'controller')
    {
        $name = $type == 'html'?strtolower($name):$name;
        $path = _APP.ucfirst($type).'/';
        $ext = $type == 'html'?'.html':'.php';

        if(file_exists($path.$name.$ext))
            return "\n\n  WARNNING: this file already exists!\n  ".$path.$name.$ext."\n\n";

        if(!$this->checkAndOrCreateDir(dirname($path.$name.$ext),true))
            return "\n\n  WARNNING: access denied in directory '".dirname($path.$name.$ext)."'\n\n";

        //get template
        $file = file_get_contents($this->cliPath.'templates/'.$type.'.tpl');

        //replace %namespace% and %name%
        $file = str_replace('%name%', ucfirst(basename($name)), $file);
        $namespace = ucfirst($type).'\\';
        foreach(explode('/', dirname($name)) as $namespc){
            if($namespc == '.') break;
            $namespace .= ucfirst($namespc).'\\';
        }
        $file = str_replace('%namespace%', trim($namespace, '\\'), $file);

        //saving the file
        $ok = file_put_contents($path.$name.$ext, $file);

        if($ok) return "\n\n  Arquivo '".$path.$name.$ext."' criado com sucesso!\n\n";
        else return "\n\n  Não foi possível criar '".$path.$name."'!\n\n";
    }

    //Help display
    function help()
    {
        return '
      Usage: php <path/to/.app/>limp [command:type] [options]

      key:generate              Generate new keys
      key:list                  List all installed Cyphers

      make:controller <name>    Create a controller with <name>
      make:model <name>         Create a model with <name>
      make:html <name>          Create a html file with <name>

      optimize                  Optimize entire Limp application

      -h or ?                   Show this help
      ';
    }
}
