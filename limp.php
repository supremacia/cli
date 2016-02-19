<?php
echo 'Always remember: less is more in PHP!';
if (php_sapi_name() !== 'cli') exit('It\'s no cli!');

//Constants:
define('CLI_PATH', __DIR__.'/');
define('CONFIG_KEYS_PATH', ¢CONFIG.'keys/');
define('TIMEON', microtime(true));

//Command line settings...
echo request($argv);


exit("\n  Finished in ".number_format((microtime(true)-TIMEON)*1000,3)." ms.\n");


// FUNCTIONS --------------------------------------------------------------------

function request($rqst)
{
    array_shift($rqst);
    $ax = $rqst;
    foreach($rqst as $a){
        array_shift($ax);
        if(strpos($a, '-h') !== false || strpos($a, '?') !== false) return _help();
        if(strpos($a, 'optimize') !== false) return _optimize(substr($a, 8), $ax);
        if(strpos($a, 'key:') !== false) return _key(substr($a, 4), $ax);
        if(strpos($a, 'make:') !== false) return _make(substr($a, 5), $ax);
    }
    //or show help...
    return _help();
}

function _key($v, $arg)
{
    echo '  key: '.$v;

    if(count($arg) > 0) {
        echo "\n\n  Arguments:";
        foreach ($arg as $a) {echo "\n\t$a";}
    }

    if($v == 'generate'){

        if(!is_dir(CONFIG_KEYS_PATH)) mkdir(CONFIG_KEYS_PATH, 0777);

        $base = ['I','u','h','5','B','A','r','i','7','9','z','d','n','t','F','2','W','X','f','e','x','v','_','8','m','T','N','R','L','c','6','P','k','Q','q','j','Y','M','4','S','G','o','0','$','K','s','g','H','E','b','a','J','U','Z','l','1','O','3','y','p','V','D','C','w'];
        $extra = ['$','!','#','%','&','*','+','-','?','@','(',')','/','\\','[',']','_','0','1','2','3','4','5','6','7','8','9','A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z'];
        shuffle($base);
        shuffle($extra);
        file_put_contents(CONFIG_KEYS_PATH.'can.key', implode($base)."\n".implode($extra));

        echo "\n\n  New CAN key generated - success!";

        //Now, OPEN_SSL
        include CLI_PATH.'open.php';

        echo   "\n  OpenSSL keys & certificates - success!";
        return "\n  Location: ".CONFIG_KEYS_PATH."\n\n";
    }

    elseif($v == 'list'){
        echo "\n\n  Ciphers:";
        foreach (mcrypt_list_algorithms() as $x) {echo "\n\t".$x;}
        echo "\n\n  Cipher Modes:";
        foreach (mcrypt_list_modes() as $x) {echo "\n\t".$x;}
    }

    else return "\n\n  ----- ERROR: Command 'key:$v' not found!\n"._help();
}

function _make($v, $arg)
{
    echo '  make: '.$v;

    if(isset($arg[0])) $arg[0] = str_replace('\\', '/', $arg[0]);
    else return "\n\n  ERROR: indique o NOME do arquivo!\n";

    $type = strtolower(trim($v));

    if($type != 'controller' && $type != 'model' && $type != 'html'){
        return "\n\n  ----- ERROR: Command 'make:$v' not found!\n"._help();
    }

    return createFile($arg[0], $type);
}

function _optimize($v, $arg)
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

// create file (controller/model/library)
function createFile($name, $type = 'controller')
{
    $name = strtolower($name);
    $path = ¢APP.$type.'/';
    $ext = $name == 'html'?'.html':'.php';

    if(file_exists($path.$name.$ext))
        return "\n\n  WARNNING: this file already exists!\n  ".$path.$name.$ext."\n\n";

    if(!checkAndOrCreateDir(dirname($path.$name.$ext),true))
        return "\n\n  WARNNING: access denied in directory '".dirname($path.$name.$ext)."'\n\n";

    //get template
    $file = file_get_contents(CLI_PATH.'templates/'.$type.'.tpl');

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

    if($ok) return "\n\n  Arquivo '".$path.$name."' criado com sucesso!\n\n";
    else return "\n\n  Não foi possível criar '".$path.$name."'!\n\n";
}

//Help display
function _help()
{
    return '
  Usage: php <path/to/.app/>limp [command:type] [options]

  key:generate              Generate new keys
  key:check                 Check is valid key par
  key:list                  List all installed Cyphers

  make:controller <name>    Create a controller with <name>
  make:model <name>         Create a model with <name>
  make:html <name>          Create a html file with <name>

  optimize                  Optimize entire Limp application

  -h or ?                   Show this help
  ';
}