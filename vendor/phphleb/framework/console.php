<?php

$arguments = $argv[1] ?? null;

// End of script execution (before starting the main project).
if (!function_exists('hl_preliminary_exit')) {
    function hl_preliminary_exit($text = '') {
        exit($text);
    }
}
// Auto update packages
if (!empty($arguments) && strpos($arguments, 'phphleb/') !== false && file_exists(dirname(__DIR__, 2) . '/' . $arguments . '/' . 'start.php')) {
    require dirname(__DIR__, 2) . '/' . $arguments . '/' . 'start.php';
    hl_preliminary_exit();
}

if (!defined('HLEB_GLOBAL_DIRECTORY')) define('HLEB_GLOBAL_DIRECTORY', dirname(__DIR__, 3));

define('HLEB_STORAGE_CACHE_ROUTES_DIRECTORY', (defined('HLEB_STORAGE_DIRECTORY') ? HLEB_STORAGE_DIRECTORY : HLEB_GLOBAL_DIRECTORY . DIRECTORY_SEPARATOR . "/storage") . "/cache/routes");

define('HLEB_VENDOR_DIRECTORY', dirname(__DIR__, 2));

define('HLEB_VENDOR_DIR_NAME', array_reverse(explode(DIRECTORY_SEPARATOR, HLEB_VENDOR_DIRECTORY))[0]);

const HLEB_PROJECT_DIRECTORY = HLEB_VENDOR_DIRECTORY . '/phphleb/framework';

const HLEB_PROJECT_DEBUG = false;

const HLEB_HTTP_TYPE_SUPPORT = ['get', 'post', 'delete', 'put', 'patch', 'options'];

const HLEB_TEMPLATE_CACHED_PATH = '/storage/cache/templates';

const HL_TWIG_CACHED_PATH = '/storage/cache/twig/compilation';

define('HL_TWIG_CONNECTED', file_exists(HLEB_VENDOR_DIRECTORY . "/twig/twig"));

if (!defined('HLEB_PROJECT_CLASSES_AUTOLOAD')) {
    define('HLEB_PROJECT_CLASSES_AUTOLOAD', true);
}

function hl_print_fulfillment_inspector(string $firstPartOfPath, string $secondPartOfPath) {
    require_once $firstPartOfPath . $secondPartOfPath;
}

$setArguments = array_splice($argv, 2);

include_once HLEB_PROJECT_DIRECTORY . '/Main/Console/MainConsole.php';

$fn = new \Hleb\Main\Console\MainConsole();

if ($arguments) {

    switch ($arguments) {
        case '--version':
        case '-v':
            $ver = [hlGetFrameVersion(), hlGetFrameworkVersion()];
            $bsp = $fn->addBsp($ver);
            echo PHP_EOL .
                " ╔═ ══ ══ ══ ══ ══ ══ ══ ══ ══ ══ ══ ══ ═╗ " . PHP_EOL .
                " ║   " . "HLEB frame" . " project version " . $ver[0] . $bsp[0] . "║" . PHP_EOL .
                " ║   " . "phphleb/framework" . " version  " . $ver[1] . $bsp[1] . "║" . PHP_EOL .
                " ║     " . hlConsoleCopyright() . "       ║" . PHP_EOL .
                " ╚═ ══ ══ ══ ══ ══ ══ ══ ══ ══ ══ ══ ══ ═╝ " . PHP_EOL;
            echo PHP_EOL;
            break;
        case '--clear-cache':
        case '-cc':
            $files = glob(HLEB_GLOBAL_DIRECTORY . HLEB_TEMPLATE_CACHED_PATH . '/*/*.cache', GLOB_NOSORT);
            hlClearCacheFiles($files, HLEB_TEMPLATE_CACHED_PATH, $fn, HLEB_TEMPLATE_CACHED_PATH . '/*/*.cache');
            echo PHP_EOL, PHP_EOL;
            break;
        case '--forced-cc':
            hlForcedClearCacheFiles(HLEB_GLOBAL_DIRECTORY . HLEB_TEMPLATE_CACHED_PATH);
            echo PHP_EOL;
            break;
        case '--forced-cc-twig':
            hlForcedClearCacheFiles(HLEB_GLOBAL_DIRECTORY . HL_TWIG_CACHED_PATH);
            echo PHP_EOL;
            break;
        case '--clear-cache--twig':
        case '-cc-twig':
            if (HL_TWIG_CONNECTED) {
                $files = glob(HLEB_GLOBAL_DIRECTORY . HL_TWIG_CACHED_PATH . '/*/*.php', GLOB_NOSORT);
                hlClearCacheFiles($files, HL_TWIG_CACHED_PATH, $fn, HL_TWIG_CACHED_PATH . '/*/*.php');
                echo PHP_EOL, PHP_EOL;
                break;
            }
        case '--help':
        case '-h':
            echo PHP_EOL;
            echo " --version or -v" . PHP_EOL . " --clear-cache -- or -cc" . PHP_EOL . " --forced-cc" . PHP_EOL .
                " --info or -i" . PHP_EOL . " --help or -h" . PHP_EOL . " --routes or -r" . PHP_EOL . " --list or -l" .
                PHP_EOL . " --logs or -lg" . PHP_EOL .
                (HL_TWIG_CONNECTED ? ' --clear-cache--twig or -cc-twig'  . PHP_EOL . ' --forced-cc-twig'  . PHP_EOL : '');
            echo PHP_EOL;
            break;
        case '--routes':
        case '-r':
            echo $fn->searchNanorouter() . $fn->getRoutes();
            echo PHP_EOL;
            break;
        case '--list':
        case '-l':
            hlUploadAll();
            echo $fn->listing();
            echo PHP_EOL . PHP_EOL;
            break;
        case '--info':
        case '-i':
            $fn->getInfo();
            break;
        case '--logs':
        case '-lg':
            $fn->getLogs();
            break;
        default:
            $file = $fn->convertCommandToTask($arguments);

            if (file_exists(HLEB_GLOBAL_DIRECTORY . "/app/Commands/$file.php")) {

                hlUploadAll();

                hlCreateUsersTask(HLEB_GLOBAL_DIRECTORY, $file, $setArguments, $fn);

            } else {
                echo "Missing required arguments after `console`. Add --help to display more options.", PHP_EOL;
            }
    }
} else {
    echo "Missing arguments after `console`. Add --help to display more options.", PHP_EOL;
}


function hlConsoleCopyright() {
    $start = "2019";
    $cp = date("Y") != $start ? "$start - " . date("Y") : $start;
    return "(c)$cp Foma Tuturov";
}

function hlAllowedHttpTypes($type) {
    return empty($type) ? "GET" : ((in_array(strtolower($type), HLEB_HTTP_TYPE_SUPPORT)) ? $type : $type . " [NOT SUPPORTED]");
}

function hlUploadAll() {

    require HLEB_PROJECT_DIRECTORY . '/Main/Insert/DeterminantStaticUncreated.php';

    require HLEB_PROJECT_DIRECTORY . '/Main/Info.php';

    require HLEB_PROJECT_DIRECTORY . '/Scheme/App/Commands/MainTask.php';

    require HLEB_PROJECT_DIRECTORY . '/Scheme/App/Controllers/MainController.php';

    require HLEB_PROJECT_DIRECTORY . '/Scheme/App/Middleware/MainMiddleware.php';

    require HLEB_PROJECT_DIRECTORY . '/Scheme/App/Models/MainModel.php';

    require HLEB_PROJECT_DIRECTORY . '/Scheme/Home/Main/Connector.php';

    require HLEB_GLOBAL_DIRECTORY . '/app/Optional/MainConnector.php';

    require HLEB_PROJECT_DIRECTORY . '/Main/MainAutoloader.php';

    require HLEB_PROJECT_DIRECTORY . '/Main/HomeConnector.php';

    // Third party class autoloader.
    // Сторонний автозагрузчик классов.
    if (file_exists(HLEB_VENDOR_DIRECTORY . '/autoload.php')) {
        require HLEB_VENDOR_DIRECTORY . '/autoload.php';
    }

    // Custom class autoloader.
    // Собственный автозагрузчик классов.
    function hl_main_autoloader($class) {
        \Hleb\Main\MainAutoloader::get($class);
    }

    if (HLEB_PROJECT_CLASSES_AUTOLOAD) spl_autoload_register('hl_main_autoloader', true, true);
}

function hlCreateUsersTask($path, $class, $arg, Hleb\Main\Console\MainConsole $fn) {
    $realPath = $path . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Commands' . DIRECTORY_SEPARATOR . $class . ".php";
    include_once "$realPath";

    $searchNames = $fn->searchOnceNamespace($realPath, $path . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Commands');
    if ($searchNames) {
        foreach ($searchNames as $search_name) {
            if (class_exists('App\Commands\\' . $search_name)) {
                $className = 'App\Commands\\' . $search_name;
                (new $className())->createTask($arg);
                break;
            }
        }
    }
}


function hlGetFrameVersion() {
    return hlSearchVersion(HLEB_PUBLIC_DIR . '/index.php', 'HLEB_FRAME_VERSION');
}

function hlGetFrameworkVersion() {
    return hlSearchVersion(HLEB_PROJECT_DIRECTORY . '/init.php', 'HLEB_PROJECT_FULL_VERSION');
}

function hlSearchVersion($file, $const) {
    $content = file_get_contents($file, true);
    preg_match_all("|define\(\s*\'" . $const . "\'\s*\,\s*([^\)]+)\)|u", $content, $def);
    return trim($def[1][0] ?? 'undefined', "' \"");
}

function hlClearCacheFiles($files, $path, $fn, $scan_path) {
    echo PHP_EOL, "Clearing cache [          ] 0% ";
    $all = count($files);
    $error = 0;
    if (count($files)) {
        $counter = 1;
        foreach ($files as $k => $value) {
            if(file_exists($value) && !is_writable($value)) {
                $error++;
            } else {
                @chmod($value, 0777);
            }
            @unlink($value);
            $fn->progressConsole(count($files), $k);
            echo " (", $counter, "/", $all, ")";
            $counter++;
        }
        $pathDirectory = glob(HLEB_GLOBAL_DIRECTORY . $scan_path);
        if(!empty($pathDirectory)) {
            @array_map('unlink', $pathDirectory);
        }
        $directories = glob(HLEB_GLOBAL_DIRECTORY . $path . '/*', GLOB_NOSORT);
        foreach ($directories as $key => $directory) {
            if (!file_exists($directory)) break;
            $listDirectory = scandir($directory);
            if ([] === (array_diff((is_array($listDirectory)? $listDirectory : []), ['.', '..']))) {
                @rmdir($directory);
            }
        }
        if (count($files) < 100) {
            fwrite(STDOUT, "\r");
            fwrite(STDOUT, "Clearing cache [//////////] - 100% ($all/$all)");
        }
    } else {
        fwrite(STDOUT, "\r");
        fwrite(STDOUT, "No files in " . $path . ". Cache cleared.");
    }
    if($error) {
        fwrite(STDOUT, "\r");
        fwrite(STDOUT, "Permission denied! Try executing via 'sudo' before the command.");
    }

}

function hlForcedClearCacheFiles($path) {
    $standardPath = str_replace('\\', '/', $path);
    if (!file_exists($path)) {
        hl_preliminary_exit("No files in " . $standardPath . ". Cache cleared." . PHP_EOL);
    }
    if (!is_writable($path)) {
        hl_preliminary_exit("Permission denied! Try executing via 'sudo' before the command." . PHP_EOL);
    }
    $newPath = rtrim($path, "/") . "_" . md5(microtime() . rand());
    rename($path, $newPath);
    if (file_exists($newPath) && !is_writable($newPath)) {
        hl_preliminary_exit("Permission denied! Try executing via 'sudo' before the command." . PHP_EOL);
    }
    if (!file_exists($newPath)) {
        hl_preliminary_exit("Error! Couldn't move directory." . PHP_EOL);
    }
    echo "Moving files from a folder " . $standardPath . ". Cache cleared." . PHP_EOL;
    fwrite(STDOUT, "Delete files...");
    hlRemoveDir($newPath);
    fwrite(STDOUT, "\r");
    fwrite(STDOUT, "Delete files [//////////] 100% ");
}

function hlRemoveDir($path) {
    if (file_exists($path) && is_dir($path)) {
        $dir = opendir($path);
        if (!is_resource($dir)) return;
            while (false !== ($element = readdir($dir))) {
                if ($element != '.' && $element != '..') {
                    $tmp = $path . '/' . $element;
                    chmod($tmp, 0777);
                    if (is_dir($tmp)) {
                        hlRemoveDir($tmp);
                    } else {
                        unlink($tmp);
                    }
                }
            }
            closedir($dir);
            if (file_exists($path)) {
                rmdir($path);
            }
    }
}

