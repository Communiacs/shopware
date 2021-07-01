<?php











namespace Composer;

use Composer\Autoload\ClassLoader;
use Composer\Semver\VersionParser;








class InstalledVersions
{
private static $installed = array (
  'root' => 
  array (
    'pretty_version' => 'dev-version/5.6.0',
    'version' => 'dev-version/5.6.0',
    'aliases' => 
    array (
    ),
    'reference' => 'a4f71c8bd91aa60a6795287ef71a8e22a9f9c5d2',
    'name' => '__root__',
  ),
  'versions' => 
  array (
    '__root__' => 
    array (
      'pretty_version' => 'dev-version/5.6.0',
      'version' => 'dev-version/5.6.0',
      'aliases' => 
      array (
      ),
      'reference' => 'a4f71c8bd91aa60a6795287ef71a8e22a9f9c5d2',
    ),
    'knplabs/gaufrette' => 
    array (
      'pretty_version' => 'v0.8.1',
      'version' => '0.8.1.0',
      'aliases' => 
      array (
      ),
      'reference' => '80cc42e1c5f008a8fc9d02e856c60140c7d1ea19',
    ),
    'paragonie/random_compat' => 
    array (
      'pretty_version' => 'v2.0.11',
      'version' => '2.0.11.0',
      'aliases' => 
      array (
      ),
      'reference' => '5da4d3c796c275c55f057af5a643ae297d96b4d8',
    ),
    'pimple/pimple' => 
    array (
      'pretty_version' => 'v3.2.3',
      'version' => '3.2.3.0',
      'aliases' => 
      array (
      ),
      'reference' => '9e403941ef9d65d20cba7d54e29fe906db42cf32',
    ),
    'psr/container' => 
    array (
      'pretty_version' => '1.0.0',
      'version' => '1.0.0.0',
      'aliases' => 
      array (
      ),
      'reference' => 'b7ce3b176482dbbc1245ebf52b181af44c2cf55f',
    ),
    'psr/log' => 
    array (
      'pretty_version' => '1.1.0',
      'version' => '1.1.0.0',
      'aliases' => 
      array (
      ),
      'reference' => '6c001f1daafa3a3ac1d8ff69ee4db8e799a654dd',
    ),
    'psr/log-implementation' => 
    array (
      'provided' => 
      array (
        0 => '1.0',
      ),
    ),
    'ramsey/uuid' => 
    array (
      'pretty_version' => '3.5.2',
      'version' => '3.5.2.0',
      'aliases' => 
      array (
      ),
      'reference' => '5677cfe02397dd6b58c861870dfaa5d9007d3954',
    ),
    'rhumsaa/uuid' => 
    array (
      'replaced' => 
      array (
        0 => '3.5.2',
      ),
    ),
    'slim/slim' => 
    array (
      'pretty_version' => '2.6.3',
      'version' => '2.6.3.0',
      'aliases' => 
      array (
      ),
      'reference' => '9224ed81ac1c412881e8d762755e3d76ebf580c0',
    ),
    'symfony/console' => 
    array (
      'pretty_version' => 'v3.4.22',
      'version' => '3.4.22.0',
      'aliases' => 
      array (
      ),
      'reference' => '069bf3f0e8f871a2169a06e43d9f3f03f355e9be',
    ),
    'symfony/debug' => 
    array (
      'pretty_version' => 'v4.2.8',
      'version' => '4.2.8.0',
      'aliases' => 
      array (
      ),
      'reference' => '2d279b6bb1d582dd5740d4d3251ae8c18812ed37',
    ),
    'symfony/finder' => 
    array (
      'pretty_version' => 'v3.4.22',
      'version' => '3.4.22.0',
      'aliases' => 
      array (
      ),
      'reference' => '7c0c627220308928e958a87c293108e5891cde1d',
    ),
    'symfony/polyfill-mbstring' => 
    array (
      'pretty_version' => 'v1.11.0',
      'version' => '1.11.0.0',
      'aliases' => 
      array (
      ),
      'reference' => 'fe5e94c604826c35a32fa832f35bd036b6799609',
    ),
  ),
);
private static $canGetVendors;
private static $installedByVendor = array();







public static function getInstalledPackages()
{
$packages = array();
foreach (self::getInstalled() as $installed) {
$packages[] = array_keys($installed['versions']);
}

if (1 === \count($packages)) {
return $packages[0];
}

return array_keys(array_flip(\call_user_func_array('array_merge', $packages)));
}









public static function isInstalled($packageName)
{
foreach (self::getInstalled() as $installed) {
if (isset($installed['versions'][$packageName])) {
return true;
}
}

return false;
}














public static function satisfies(VersionParser $parser, $packageName, $constraint)
{
$constraint = $parser->parseConstraints($constraint);
$provided = $parser->parseConstraints(self::getVersionRanges($packageName));

return $provided->matches($constraint);
}










public static function getVersionRanges($packageName)
{
foreach (self::getInstalled() as $installed) {
if (!isset($installed['versions'][$packageName])) {
continue;
}

$ranges = array();
if (isset($installed['versions'][$packageName]['pretty_version'])) {
$ranges[] = $installed['versions'][$packageName]['pretty_version'];
}
if (array_key_exists('aliases', $installed['versions'][$packageName])) {
$ranges = array_merge($ranges, $installed['versions'][$packageName]['aliases']);
}
if (array_key_exists('replaced', $installed['versions'][$packageName])) {
$ranges = array_merge($ranges, $installed['versions'][$packageName]['replaced']);
}
if (array_key_exists('provided', $installed['versions'][$packageName])) {
$ranges = array_merge($ranges, $installed['versions'][$packageName]['provided']);
}

return implode(' || ', $ranges);
}

throw new \OutOfBoundsException('Package "' . $packageName . '" is not installed');
}





public static function getVersion($packageName)
{
foreach (self::getInstalled() as $installed) {
if (!isset($installed['versions'][$packageName])) {
continue;
}

if (!isset($installed['versions'][$packageName]['version'])) {
return null;
}

return $installed['versions'][$packageName]['version'];
}

throw new \OutOfBoundsException('Package "' . $packageName . '" is not installed');
}





public static function getPrettyVersion($packageName)
{
foreach (self::getInstalled() as $installed) {
if (!isset($installed['versions'][$packageName])) {
continue;
}

if (!isset($installed['versions'][$packageName]['pretty_version'])) {
return null;
}

return $installed['versions'][$packageName]['pretty_version'];
}

throw new \OutOfBoundsException('Package "' . $packageName . '" is not installed');
}





public static function getReference($packageName)
{
foreach (self::getInstalled() as $installed) {
if (!isset($installed['versions'][$packageName])) {
continue;
}

if (!isset($installed['versions'][$packageName]['reference'])) {
return null;
}

return $installed['versions'][$packageName]['reference'];
}

throw new \OutOfBoundsException('Package "' . $packageName . '" is not installed');
}





public static function getRootPackage()
{
$installed = self::getInstalled();

return $installed[0]['root'];
}







public static function getRawData()
{
return self::$installed;
}



















public static function reload($data)
{
self::$installed = $data;
self::$installedByVendor = array();
}





private static function getInstalled()
{
if (null === self::$canGetVendors) {
self::$canGetVendors = method_exists('Composer\Autoload\ClassLoader', 'getRegisteredLoaders');
}

$installed = array();

if (self::$canGetVendors) {
foreach (ClassLoader::getRegisteredLoaders() as $vendorDir => $loader) {
if (isset(self::$installedByVendor[$vendorDir])) {
$installed[] = self::$installedByVendor[$vendorDir];
} elseif (is_file($vendorDir.'/composer/installed.php')) {
$installed[] = self::$installedByVendor[$vendorDir] = require $vendorDir.'/composer/installed.php';
}
}
}

$installed[] = self::$installed;

return $installed;
}
}
